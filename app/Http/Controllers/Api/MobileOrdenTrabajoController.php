<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OrdenTrabajo;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MobileOrdenTrabajoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        if ($forbidden = $this->authorizePermission($request, 'ordenes-trabajo.ver')) {
            return $forbidden;
        }

        $perPage = min(max((int) $request->integer('per_page', 10), 1), 30);
        $estado = trim((string) $request->query('estado', ''));
        $search = trim((string) $request->query('search', ''));

        $ordenes = OrdenTrabajo::query()
            ->with([
                'empleado:id,nombres,apellidos',
                'actualizadoPor:id,name,email',
                'reparador:id,nombres,apellidos',
                'flota:id,nro_interno,dominio',
                'base:id,nombre',
                'motivos:id,nombre,codigo',
            ])
            ->when($estado !== '', fn ($query) => $query->where('estado', $estado))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('id', $search)
                        ->orWhere('titulo', 'like', "%{$search}%")
                        ->orWhere('estado', 'like', "%{$search}%")
                        ->orWhere('prioridad', 'like', "%{$search}%")
                        ->orWhere('descripcion', 'like', "%{$search}%")
                        ->orWhereHas('flota', fn ($flota) => $flota
                            ->where('nro_interno', 'like', "%{$search}%")
                            ->orWhere('dominio', 'like', "%{$search}%"));
                });
            })
            ->orderByRaw("CASE estado WHEN 'en_proceso' THEN 0 WHEN 'pendiente' THEN 1 WHEN 'completada' THEN 2 WHEN 'cancelada' THEN 3 ELSE 4 END")
            ->orderByDesc('fecha_orden')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->through(fn (OrdenTrabajo $orden) => $this->payload($orden));

        return response()->json($ordenes);
    }

    public function show(Request $request, OrdenTrabajo $ordenTrabajo): JsonResponse
    {
        if ($forbidden = $this->authorizePermission($request, 'ordenes-trabajo.ver')) {
            return $forbidden;
        }

        $ordenTrabajo->load([
            'empleado:id,nombres,apellidos',
            'actualizadoPor:id,name,email',
            'reparador:id,nombres,apellidos',
            'flota:id,nro_interno,dominio',
            'base:id,nombre',
            'motivos:id,nombre,codigo',
            'articulosUsados.articulo:id,nombre,codigo_producto',
        ]);

        return response()->json([
            'data' => $this->payload($ordenTrabajo, true),
        ]);
    }

    public function update(Request $request, OrdenTrabajo $ordenTrabajo): JsonResponse
    {
        if ($forbidden = $this->authorizePermission($request, 'ordenes-trabajo.editar')) {
            return $forbidden;
        }

        $validated = $request->validate([
            'estado' => ['sometimes', 'required', 'in:pendiente,en_proceso,completada,cancelada'],
            'observaciones' => ['sometimes', 'nullable', 'string', 'max:3000'],
            'vehiculo_parado' => ['sometimes', 'boolean'],
            'motivo_vehiculo_parado' => ['sometimes', 'nullable', 'in:repuestos,terceros,taller,compras,autorizacion,otro'],
            'observacion_vehiculo_parado' => ['sometimes', 'nullable', 'string', 'max:1000'],
        ]);

        if (array_key_exists('estado', $validated) && in_array($validated['estado'], ['completada', 'cancelada'], true)) {
            $validated['fecha_cierre'] = now();
        }

        $validated['actualizado_por_user_id'] = $request->user()->id;

        DB::transaction(function () use ($ordenTrabajo, $validated) {
            $ordenTrabajo->update($validated);
        });

        $ordenTrabajo->refresh()->load([
            'empleado:id,nombres,apellidos',
            'actualizadoPor:id,name,email',
            'reparador:id,nombres,apellidos',
            'flota:id,nro_interno,dominio',
            'base:id,nombre',
            'motivos:id,nombre,codigo',
        ]);

        return response()->json([
            'message' => 'Orden de trabajo actualizada.',
            'data' => $this->payload($ordenTrabajo, true),
        ]);
    }

    private function payload(OrdenTrabajo $orden, bool $includeDetails = false): array
    {
        $data = [
            'id' => $orden->id,
            'titulo' => $orden->titulo,
            'descripcion' => $orden->descripcion,
            'observaciones' => $orden->observaciones,
            'estado' => $orden->estado,
            'estado_label' => $this->estadoLabel($orden->estado),
            'prioridad' => $orden->prioridad,
            'prioridad_label' => $this->prioridadLabel($orden->prioridad),
            'tipo_trabajo' => $orden->tipo_trabajo,
            'fecha_orden' => optional($orden->fecha_orden)->format('Y-m-d H:i'),
            'fecha_cierre' => optional($orden->fecha_cierre)->format('Y-m-d H:i'),
            'kilometraje' => $orden->kilometraje,
            'vehiculo_parado' => (bool) $orden->vehiculo_parado,
            'motivo_vehiculo_parado' => $orden->motivo_vehiculo_parado,
            'observacion_vehiculo_parado' => $orden->observacion_vehiculo_parado,
            'flota' => $orden->flota ? [
                'id' => $orden->flota->id,
                'nro_interno' => $orden->flota->nro_interno,
                'dominio' => $orden->flota->dominio,
            ] : null,
            'base' => $orden->base ? [
                'id' => $orden->base->id,
                'nombre' => $orden->base->nombre,
            ] : null,
            'empleado' => $this->empleadoPayload($orden->empleado),
            'reparador' => $this->empleadoPayload($orden->reparador),
            'actualizado_por' => $orden->actualizadoPor ? [
                'id' => $orden->actualizadoPor->id,
                'name' => $orden->actualizadoPor->name,
                'email' => $orden->actualizadoPor->email,
            ] : null,
            'updated_at' => optional($orden->updated_at)->format('Y-m-d H:i'),
            'motivos' => $orden->relationLoaded('motivos')
                ? $orden->motivos->map(fn ($motivo) => ['id' => $motivo->id, 'nombre' => $motivo->nombre])->values()
                : [],
        ];

        if ($includeDetails) {
            $data['articulos'] = $orden->articulosUsados
                ->map(fn ($detalle) => [
                    'id' => $detalle->id,
                    'articulo' => $detalle->articulo?->nombre,
                    'cantidad' => $detalle->cantidad,
                ])
                ->values();
        }

        return $data;
    }

    private function empleadoPayload($empleado): ?array
    {
        if (! $empleado) {
            return null;
        }

        return [
            'id' => $empleado->id,
            'nombre' => trim(($empleado->apellidos ?? '') . ', ' . ($empleado->nombres ?? '')),
        ];
    }

    private function estadoLabel(?string $estado): string
    {
        return [
            'pendiente' => 'Pendiente',
            'en_proceso' => 'En proceso',
            'completada' => 'Completada',
            'cancelada' => 'Cancelada',
        ][$estado] ?? ucfirst((string) $estado);
    }

    private function prioridadLabel(?string $prioridad): string
    {
        return [
            'baja' => 'Baja',
            'media' => 'Media',
            'alta' => 'Alta',
            'urgente' => 'Urgente',
        ][$prioridad] ?? ucfirst((string) $prioridad);
    }

    private function authorizePermission(Request $request, string $permission): ?JsonResponse
    {
        $user = $request->user();

        if ($user && ($user->isSuperUsuario() || $this->hasPermission($user, $permission))) {
            return null;
        }

        return response()->json([
            'message' => 'No tiene permisos para esta accion.',
        ], 403);
    }

    private function hasPermission(User $user, string $permission): bool
    {
        $direct = DB::table('permissions')
            ->join('model_has_permissions', 'permissions.id', '=', 'model_has_permissions.permission_id')
            ->where('permissions.name', $permission)
            ->where('model_has_permissions.model_type', User::class)
            ->where('model_has_permissions.model_id', $user->id)
            ->exists();

        if ($direct) {
            return true;
        }

        return DB::table('permissions')
            ->join('role_has_permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
            ->join('model_has_roles', 'role_has_permissions.role_id', '=', 'model_has_roles.role_id')
            ->where('permissions.name', $permission)
            ->where('model_has_roles.model_type', User::class)
            ->where('model_has_roles.model_id', $user->id)
            ->exists();
    }
}
