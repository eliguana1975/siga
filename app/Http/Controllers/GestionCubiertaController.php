<?php

namespace App\Http\Controllers;

use App\Models\Articulo;
use App\Models\Cubierta;
use App\Models\DetalleCambioCubierta;
use App\Models\OrdenTrabajo;
use App\Models\OrdenTrabajoArticulo;
use App\Services\ArticleClassificationService;
use Illuminate\Http\Request;

class GestionCubiertaController extends Controller
{
    public function __construct(private ArticleClassificationService $articleClassifier)
    {
        $this->middleware('auth');
        $this->middleware('check.permission:administrar-ordenes-trabajo');
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $medidaRecienteArticuloId = $request->integer('medida_reciente_articulo_id') ?: null;

        $ordenesQuery = $this->ordenesCubiertasQuery()
            ->with(['empleado', 'reparador', 'flota', 'base', 'motivos', 'articulosUsados.articulo.categoria'])
            ->orderByDesc('fecha_orden')
            ->orderByDesc('id');

        if ($search !== '') {
            $ordenesQuery->where(function ($query) use ($search) {
                $query->where('id', $search)
                    ->orWhere('titulo', 'like', "%{$search}%")
                    ->orWhere('kilometraje', 'like', "%{$search}%")
                    ->orWhere('estado', 'like', "%{$search}%")
                    ->orWhere('descripcion', 'like', "%{$search}%")
                    ->orWhere('observaciones', 'like', "%{$search}%")
                    ->orWhereHas('motivos', function ($motivo) use ($search) {
                        $motivo->where('nombre', 'like', "%{$search}%")
                            ->orWhere('codigo', 'like', "%{$search}%");
                    })
                    ->orWhereHas('flota', function ($flota) use ($search) {
                        $flota->where('nro_interno', 'like', "%{$search}%")
                            ->orWhere('dominio', 'like', "%{$search}%");
                    })
                    ->orWhereHas('empleado', function ($empleado) use ($search) {
                        $empleado->where('nombres', 'like', "%{$search}%")
                            ->orWhere('apellidos', 'like', "%{$search}%");
                    })
                    ->orWhereHas('articulosUsados.articulo', function ($articulo) use ($search) {
                        $articulo->where('nombre', 'like', "%{$search}%")
                            ->orWhere('codigo_producto', 'like', "%{$search}%");
                    });
            });
        }

        $ordenes = $ordenesQuery->paginate(10)->withQueryString();
        $seguimientoQuery = DetalleCambioCubierta::query()
            ->with(['cambioCubierta.flota', 'cambioCubierta.ordenTrabajo', 'articuloColocado'])
            ->where(function ($query) {
                $query->whereNotNull('nro_cubierta_sacada')
                    ->orWhereNotNull('nro_cubierta_colocada');
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('nro_cubierta_sacada', 'like', "%{$search}%")
                        ->orWhere('nro_cubierta_colocada', 'like', "%{$search}%")
                        ->orWhere('estado_cubierta_sacada', 'like', "%{$search}%")
                        ->orWhere('destino_cubierta_sacada', 'like', "%{$search}%")
                        ->orWhereHas('cambioCubierta.flota', fn ($flota) => $flota
                            ->where('nro_interno', 'like', "%{$search}%")
                            ->orWhere('dominio', 'like', "%{$search}%"));
                });
            });

        $seguimiento = (clone $seguimientoQuery)
            ->latest('id')
            ->limit(20)
            ->get();

        $cubiertasDisponiblesQuery = Cubierta::query()
            ->with(['articulo.categoria', 'deposito'])
            ->whereIn('estado', ['nueva', 'reutilizable'])
            ->whereNull('flota_id')
            ->whereNull('posicion');

        $cubiertasNumeradas = (clone $cubiertasDisponiblesQuery)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('numero', 'like', "%{$search}%")
                        ->orWhere('medida', 'like', "%{$search}%")
                        ->orWhere('estado', 'like', "%{$search}%")
                        ->orWhere('posicion', 'like', "%{$search}%")
                        ->orWhereHas('articulo', function ($articulo) use ($search) {
                            $articulo->where('nombre', 'like', "%{$search}%")
                                ->orWhere('codigo_producto', 'like', "%{$search}%");
                        });
                });
            })
            ->orderBy('articulo_id')
            ->orderBy('secuencia')
            ->get();

        $articulosCubiertasDisponibles = $cubiertasNumeradas
            ->map(fn (Cubierta $cubierta) => $cubierta->articulo)
            ->filter()
            ->unique('id')
            ->sortBy('nombre')
            ->values();

        $medidasCubiertasRecientes = Cubierta::query()
            ->with('articulo:id,nombre,codigo_producto')
            ->whereNotNull('entrada_id')
            ->orderByDesc('fecha_ingreso')
            ->orderByDesc('created_at')
            ->get()
            ->unique('articulo_id')
            ->sortBy(fn (Cubierta $cubierta) => $cubierta->articulo?->nombre ?? $cubierta->medida)
            ->values();

        $ultimaEntradaCubiertasQuery = Cubierta::query()
            ->whereNotNull('entrada_id')
            ->when($medidaRecienteArticuloId, fn ($query) => $query->where('articulo_id', $medidaRecienteArticuloId));

        $ultimaEntradaCubiertas = (clone $ultimaEntradaCubiertasQuery)
            ->orderByDesc('fecha_ingreso')
            ->orderByDesc('created_at')
            ->orderByDesc('entrada_id')
            ->first();

        $cubiertasRecientes = collect();

        if ($ultimaEntradaCubiertas) {
            $cubiertasRecientes = Cubierta::query()
                ->with(['articulo.categoria', 'deposito', 'entrada'])
                ->where('entrada_id', $ultimaEntradaCubiertas->entrada_id)
                ->when($medidaRecienteArticuloId, fn ($query) => $query->where('articulo_id', $medidaRecienteArticuloId))
                ->orderByRaw('CAST(numero AS UNSIGNED) ASC')
                ->orderBy('numero')
                ->get();
        }

        $resumen = [
            'articulos_cubiertas' => Articulo::query()
                ->where(fn ($query) => $this->articuloCubiertaFilter($query))
                ->count(),
            'stock_cubiertas' => Cubierta::query()
                ->whereIn('estado', ['nueva', 'reutilizable'])
                ->whereNull('flota_id')
                ->whereNull('posicion')
                ->count(),
            'ordenes_cubiertas' => $this->ordenesCubiertasQuery()->count(),
            'ordenes_pendientes' => $this->ordenesCubiertasQuery()->whereIn('estado', ['pendiente', 'en_proceso'])->count(),
            'ordenes_completadas' => $this->ordenesCubiertasQuery()->where('estado', 'completada')->count(),
            'cubiertas_reutilizables' => DetalleCambioCubierta::where('estado_cubierta_sacada', 'buena')->count(),
            'cubiertas_baja' => DetalleCambioCubierta::where('estado_cubierta_sacada', 'baja')->count(),
            'valor_aplicado' => (float) OrdenTrabajoArticulo::whereHas('articulo', fn ($articulo) => $articulo
                ->where(fn ($query) => $this->articuloCubiertaFilter($query)))
                ->selectRaw('COALESCE(SUM(cantidad * valor_unitario), 0) as total')
                ->value('total'),
        ];

        return view('admin.gestion-cubiertas.index', compact('ordenes', 'resumen', 'search', 'seguimiento', 'cubiertasNumeradas', 'cubiertasRecientes', 'medidasCubiertasRecientes', 'medidaRecienteArticuloId', 'ultimaEntradaCubiertas', 'articulosCubiertasDisponibles'));
    }

    private function ordenesCubiertasQuery()
    {
        return OrdenTrabajo::query()
            ->where(function ($query) {
                $query->whereHas('motivos', fn ($motivo) => $motivo->where('codigo', 'cubiertas'))
                    ->orWhereHas('articulosUsados.articulo', fn ($articulo) => $articulo
                    ->where(fn ($query) => $this->articuloCubiertaFilter($query)))
                    ->orWhere('titulo', 'like', '%cubierta%')
                    ->orWhere('descripcion', 'like', '%cubierta%')
                    ->orWhere('observaciones', 'like', '%cubierta%')
                    ->orWhere('titulo', 'like', '%neumatic%')
                    ->orWhere('descripcion', 'like', '%neumatic%')
                    ->orWhere('observaciones', 'like', '%neumatic%');
            });
    }

    private function articuloCubiertaFilter($query): void
    {
        $this->articleClassifier->applyCubiertaFilter($query);
    }
}
