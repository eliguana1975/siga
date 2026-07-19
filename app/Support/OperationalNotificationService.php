<?php

namespace App\Support;

use App\Models\Inventario;
use App\Models\NotificacionOperativa;
use App\Models\ReparacionArticulo;
use App\Models\SolicitudRepuesto;

class OperationalNotificationService
{
    public function __construct(private readonly OperationalAlertService $alerts)
    {
    }

    public function sync(int $limit = 50): array
    {
        $summary = $this->alerts->summary($limit);
        $now = now();
        $activeKeys = [];
        $created = 0;
        $updated = 0;

        foreach ($this->stockNotifications($summary['stock_critico']) as $payload) {
            [$created, $updated] = $this->upsert($payload, $activeKeys, $created, $updated, $now);
        }

        foreach ($this->solicitudNotifications($summary['solicitudes_demoradas']) as $payload) {
            [$created, $updated] = $this->upsert($payload, $activeKeys, $created, $updated, $now);
        }

        foreach ($this->reparacionNotifications($summary['reparaciones_vencidas']) as $payload) {
            [$created, $updated] = $this->upsert($payload, $activeKeys, $created, $updated, $now);
        }

        NotificacionOperativa::query()
            ->whereIn('tipo', ['stock_critico', 'solicitud_demorada', 'reparacion_vencida'])
            ->whereNull('resolved_at')
            ->whereNotIn('clave', $activeKeys ?: ['__none__'])
            ->update([
                'resolved_at' => $now,
                'updated_at' => $now,
            ]);

        return [
            'created' => $created,
            'updated' => $updated,
            'active' => count($activeKeys),
        ];
    }

    private function stockNotifications(array $items): array
    {
        return collect($items)->map(fn (array $item) => [
            'clave' => 'stock_critico:' . $item['inventario_id'],
            'tipo' => 'stock_critico',
            'severidad' => ((int) $item['cantidad'] <= 0) ? 'critica' : 'alta',
            'titulo' => 'Stock critico: ' . $item['articulo'],
            'mensaje' => 'Deposito ' . $item['deposito'] . '. Stock actual ' . $item['cantidad'] . ', minimo ' . $item['stock_minimo'] . '.',
            'url' => route('admin.inventarios.index', ['search' => $item['codigo'] ?: $item['articulo']]),
            'entidad_type' => Inventario::class,
            'entidad_id' => $item['inventario_id'],
            'metadata' => $item,
        ])->all();
    }

    private function solicitudNotifications(array $items): array
    {
        return collect($items)->map(fn (array $item) => [
            'clave' => 'solicitud_demorada:' . $item['id'],
            'tipo' => 'solicitud_demorada',
            'severidad' => (($item['dias_abierta'] ?? 0) >= 7 || ($item['prioridad'] ?? '') === 'urgente') ? 'alta' : 'media',
            'titulo' => 'Solicitud demorada #' . $item['id'],
            'mensaje' => ($item['descripcion'] ?? 'Solicitud sin descripcion') . '. Estado ' . ($item['estado'] ?? '-') . '.',
            'url' => route('admin.solicitudes-repuestos.show', $item['id']),
            'entidad_type' => SolicitudRepuesto::class,
            'entidad_id' => $item['id'],
            'metadata' => $item,
        ])->all();
    }

    private function reparacionNotifications(array $items): array
    {
        return collect($items)->map(fn (array $item) => [
            'clave' => 'reparacion_vencida:' . $item['id'],
            'tipo' => 'reparacion_vencida',
            'severidad' => (($item['dias_vencida'] ?? 0) >= 7) ? 'critica' : 'alta',
            'titulo' => 'Reparacion vencida ' . ($item['numero_orden'] ?: '#' . $item['id']),
            'mensaje' => 'Proveedor ' . ($item['proveedor'] ?: 'sin proveedor') . '. Pendiente: ' . ($item['cantidad_pendiente_total'] ?? 0) . '.',
            'url' => route('admin.reparaciones-articulos.show', $item['id']),
            'entidad_type' => ReparacionArticulo::class,
            'entidad_id' => $item['id'],
            'metadata' => $item,
        ])->all();
    }

    private function upsert(array $payload, array &$activeKeys, int $created, int $updated, $now): array
    {
        $activeKeys[] = $payload['clave'];
        $notification = NotificacionOperativa::query()->where('clave', $payload['clave'])->first();

        if (! $notification) {
            NotificacionOperativa::query()->create($payload + [
                'first_seen_at' => $now,
                'last_seen_at' => $now,
            ]);

            return [$created + 1, $updated];
        }

        $notification->forceFill($payload + [
            'last_seen_at' => $now,
            'resolved_at' => null,
            'resolved_by_user_id' => null,
        ])->save();

        return [$created, $updated + 1];
    }
}
