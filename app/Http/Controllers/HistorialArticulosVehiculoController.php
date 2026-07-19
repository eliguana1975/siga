<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Flota;
use App\Models\OrdenTrabajoArticulo;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class HistorialArticulosVehiculoController extends Controller
{
    public function index(Request $request)
    {
        $filters = [
            'flota_id' => $request->input('flota_id'),
            'fecha_desde' => $request->input('fecha_desde'),
            'fecha_hasta' => $request->input('fecha_hasta'),
            'categoria_id' => $request->input('categoria_id'),
        ];
        $hasSearched = $request->query->count() > 0;

        if ($hasSearched) {
            $historialQuery = OrdenTrabajoArticulo::query()
                ->with([
                    'ordenTrabajo.flota',
                    'ordenTrabajo.empleado',
                    'ordenTrabajo.reparador',
                    'articulo.categoria',
                    'articulo.unidadMedida',
                    'detalleCambioCubierta',
                ])
                ->whereHas('ordenTrabajo', function ($query) use ($filters) {
                    if (!empty($filters['flota_id'])) {
                        $query->where('flota_id', $filters['flota_id']);
                    }

                    if (!empty($filters['fecha_desde'])) {
                        $query->whereDate('fecha_orden', '>=', $filters['fecha_desde']);
                    }

                    if (!empty($filters['fecha_hasta'])) {
                        $query->whereDate('fecha_orden', '<=', $filters['fecha_hasta']);
                    }
                })
                ->when(!empty($filters['categoria_id']), function ($query) use ($filters) {
                    $query->whereHas('articulo', fn ($articulo) => $articulo->where('categoria_id', $filters['categoria_id']));
                })
                ->join('ordenes_trabajo', 'orden_trabajo_articulos.orden_trabajo_id', '=', 'ordenes_trabajo.id')
                ->select('orden_trabajo_articulos.*')
                ->orderByDesc('ordenes_trabajo.fecha_orden')
                ->orderByDesc('ordenes_trabajo.id');

            $historial = $historialQuery
                ->paginate(15)
                ->withQueryString();

            $totalCantidad = (clone $historialQuery)->sum('orden_trabajo_articulos.cantidad');
        } else {
            $historial = new LengthAwarePaginator([], 0, 15);
            $historial->withPath($request->url());
            $totalCantidad = 0;
        }

        $flotas = Flota::orderBy('nro_interno')->get();
        $categorias = Categoria::orderBy('nombre')->get();

        return view('admin.historial-articulos-vehiculo.index', compact(
            'historial',
            'flotas',
            'categorias',
            'filters',
            'totalCantidad',
            'hasSearched'
        ));
    }
}
