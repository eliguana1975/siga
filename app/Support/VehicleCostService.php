<?php

namespace App\Support;

use App\Models\Flota;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class VehicleCostService
{
    public function ranking(Carbon $desde, Carbon $hasta, int $limit = 25): array
    {
        $repuestos = $this->costosRepuestos($desde, $hasta);
        $cubiertas = $this->costosCubiertas($desde, $hasta);
        $categorias = $this->costosPorCategoria($desde, $hasta);
        $kilometros = $this->kilometrosPeriodo($desde, $hasta);
        $flotas = Flota::query()
            ->with(['marcaVehiculo:id,nombre', 'tipoVehiculo:id,nombre'])
            ->orderBy('nro_interno')
            ->get()
            ->keyBy('id');

        $rows = $repuestos->keys()
            ->merge($cubiertas->keys())
            ->unique()
            ->map(function ($flotaId) use ($flotas, $repuestos, $cubiertas, $kilometros) {
                $repuestosTotal = (float) ($repuestos[$flotaId] ?? 0);
                $cubiertasTotal = (float) ($cubiertas[$flotaId] ?? 0);
                $total = $repuestosTotal + $cubiertasTotal;
                $kmPeriodo = (int) ($kilometros[$flotaId] ?? 0);
                $flota = $flotas->get($flotaId);

                return [
                    'flota_id' => (int) $flotaId,
                    'interno' => $flota?->nro_interno ?? '-',
                    'dominio' => $flota?->dominio ?? '-',
                    'vehiculo' => trim(($flota?->marcaVehiculo?->nombre ?? '') . ' ' . ($flota?->tipoVehiculo?->nombre ?? '')),
                    'repuestos' => $repuestosTotal,
                    'cubiertas' => $cubiertasTotal,
                    'mano_obra' => 0.0,
                    'servicios' => 0.0,
                    'combustible' => 0.0,
                    'total' => $total,
                    'km_periodo' => $kmPeriodo,
                    'costo_por_km' => $kmPeriodo > 0 ? $total / $kmPeriodo : null,
                ];
            })
            ->sortByDesc('total')
            ->values();

        $graficos = $flotas
            ->map(function (Flota $flota) use ($categorias) {
                $categoriasVehiculo = collect($categorias->get($flota->id, []))
                    ->sortByDesc('total')
                    ->values();

                return [
                    'flota_id' => (int) $flota->id,
                    'interno' => $flota->nro_interno ?? '-',
                    'dominio' => $flota->dominio ?? '-',
                    'vehiculo' => trim(($flota->marcaVehiculo?->nombre ?? '') . ' ' . ($flota->tipoVehiculo?->nombre ?? '')),
                    'total' => (float) $categoriasVehiculo->sum('total'),
                    'categorias' => $categoriasVehiculo->all(),
                ];
            })
            ->values();

        return [
            'desde' => $desde,
            'hasta' => $hasta,
            'totales' => [
                'vehiculos_con_costo' => $rows->where('total', '>', 0)->count(),
                'repuestos' => (float) $rows->sum('repuestos'),
                'cubiertas' => (float) $rows->sum('cubiertas'),
                'mano_obra' => 0.0,
                'servicios' => 0.0,
                'combustible' => 0.0,
                'total' => (float) $rows->sum('total'),
            ],
            'ranking' => $rows->take($limit)->all(),
            'graficos' => $graficos->all(),
        ];
    }

    private function costosRepuestos(Carbon $desde, Carbon $hasta): Collection
    {
        return DB::table('orden_trabajo_articulos')
            ->join('ordenes_trabajo', 'ordenes_trabajo.id', '=', 'orden_trabajo_articulos.orden_trabajo_id')
            ->leftJoin('detalle_cambio_cubiertas', 'detalle_cambio_cubiertas.orden_trabajo_articulo_id', '=', 'orden_trabajo_articulos.id')
            ->whereNull('detalle_cambio_cubiertas.id')
            ->whereNotNull('ordenes_trabajo.flota_id')
            ->whereBetween('ordenes_trabajo.fecha_orden', [$desde->toDateString(), $hasta->toDateString()])
            ->groupBy('ordenes_trabajo.flota_id')
            ->selectRaw('ordenes_trabajo.flota_id, COALESCE(SUM(orden_trabajo_articulos.cantidad * orden_trabajo_articulos.valor_unitario), 0) as total')
            ->pluck('total', 'flota_id')
            ->map(fn ($total) => (float) $total);
    }

    private function costosCubiertas(Carbon $desde, Carbon $hasta): Collection
    {
        return DB::table('detalle_cambio_cubiertas')
            ->join('cambios_cubiertas', 'cambios_cubiertas.id', '=', 'detalle_cambio_cubiertas.cambio_cubierta_id')
            ->whereNotNull('cambios_cubiertas.flota_id')
            ->whereBetween('cambios_cubiertas.fecha', [$desde->toDateString(), $hasta->toDateString()])
            ->groupBy('cambios_cubiertas.flota_id')
            ->selectRaw('cambios_cubiertas.flota_id, COALESCE(SUM(detalle_cambio_cubiertas.valor_unitario), 0) as total')
            ->pluck('total', 'flota_id')
            ->map(fn ($total) => (float) $total);
    }

    private function costosPorCategoria(Carbon $desde, Carbon $hasta): Collection
    {
        return $this->costosRepuestosPorCategoria($desde, $hasta)
            ->concat($this->costosCubiertasPorCategoria($desde, $hasta))
            ->groupBy('flota_id')
            ->map(function (Collection $items) {
                return $items
                    ->groupBy('categoria')
                    ->map(fn (Collection $categoriaItems, string $categoria) => [
                        'categoria' => $categoria,
                        'total' => (float) $categoriaItems->sum('total'),
                    ])
                    ->values()
                    ->all();
            });
    }

    private function costosRepuestosPorCategoria(Carbon $desde, Carbon $hasta): Collection
    {
        return DB::table('orden_trabajo_articulos')
            ->join('ordenes_trabajo', 'ordenes_trabajo.id', '=', 'orden_trabajo_articulos.orden_trabajo_id')
            ->join('articulos', 'articulos.id', '=', 'orden_trabajo_articulos.articulo_id')
            ->leftJoin('categorias', 'categorias.id', '=', 'articulos.categoria_id')
            ->leftJoin('detalle_cambio_cubiertas', 'detalle_cambio_cubiertas.orden_trabajo_articulo_id', '=', 'orden_trabajo_articulos.id')
            ->whereNull('detalle_cambio_cubiertas.id')
            ->whereNotNull('ordenes_trabajo.flota_id')
            ->whereBetween('ordenes_trabajo.fecha_orden', [$desde->toDateString(), $hasta->toDateString()])
            ->groupBy('ordenes_trabajo.flota_id', 'categorias.nombre')
            ->selectRaw("ordenes_trabajo.flota_id, COALESCE(categorias.nombre, 'SIN CATEGORIA') as categoria, COALESCE(SUM(orden_trabajo_articulos.cantidad * orden_trabajo_articulos.valor_unitario), 0) as total")
            ->get()
            ->map(fn ($row) => [
                'flota_id' => (int) $row->flota_id,
                'categoria' => (string) $row->categoria,
                'total' => (float) $row->total,
            ]);
    }

    private function costosCubiertasPorCategoria(Carbon $desde, Carbon $hasta): Collection
    {
        return DB::table('detalle_cambio_cubiertas')
            ->join('cambios_cubiertas', 'cambios_cubiertas.id', '=', 'detalle_cambio_cubiertas.cambio_cubierta_id')
            ->whereNotNull('cambios_cubiertas.flota_id')
            ->whereBetween('cambios_cubiertas.fecha', [$desde->toDateString(), $hasta->toDateString()])
            ->groupBy('cambios_cubiertas.flota_id')
            ->selectRaw("cambios_cubiertas.flota_id, 'CUBIERTAS' as categoria, COALESCE(SUM(detalle_cambio_cubiertas.valor_unitario), 0) as total")
            ->get()
            ->map(fn ($row) => [
                'flota_id' => (int) $row->flota_id,
                'categoria' => (string) $row->categoria,
                'total' => (float) $row->total,
            ]);
    }

    private function kilometrosPeriodo(Carbon $desde, Carbon $hasta): Collection
    {
        $lecturas = DB::query()
            ->fromSub(function ($query) use ($desde, $hasta) {
                $query->from('ordenes_trabajo')
                    ->selectRaw('flota_id, fecha_orden as fecha, kilometraje as lectura')
                    ->whereNotNull('flota_id')
                    ->whereNotNull('kilometraje')
                    ->whereBetween('fecha_orden', [$desde->toDateString(), $hasta->toDateString()])
                    ->unionAll(
                        DB::table('controles_unidad')
                            ->selectRaw('flota_id, created_at as fecha, kilometraje_actual as lectura')
                            ->whereNotNull('flota_id')
                            ->whereNotNull('kilometraje_actual')
                            ->whereBetween(DB::raw('DATE(created_at)'), [$desde->toDateString(), $hasta->toDateString()])
                    );
            }, 'lecturas')
            ->selectRaw('flota_id, MIN(lectura) as lectura_minima, MAX(lectura) as lectura_maxima')
            ->groupBy('flota_id')
            ->get();

        return $lecturas->mapWithKeys(fn ($row) => [
            (int) $row->flota_id => max(0, (int) $row->lectura_maxima - (int) $row->lectura_minima),
        ]);
    }
}
