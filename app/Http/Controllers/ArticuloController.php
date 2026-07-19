<?php

namespace App\Http\Controllers;

use App\Models\Articulo;
use App\Models\Ajuste;
use App\Models\Categoria;
use App\Models\Ciudad;
use App\Models\Deposito;
use App\Models\Inventario;
use App\Models\OrdenTrabajoArticulo;
use App\Models\Provincia;
use App\Models\UnidadMedida;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ArticuloController extends Controller
{
    /**
     * Instantiate a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('check.permission:administrar-articulos');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));

        $query = Articulo::query()
            ->with([
                'categoria:id,nombre',
                'unidadMedida:id,nombre',
                'inventarios' => fn ($query) => $query
                    ->select('id', 'articulo_id', 'cantidad')
                    ->where('cantidad', '>', 0)
                    ->orderByDesc('cantidad')
                    ->orderBy('id'),
            ])
            ->orderBy('nombre');

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query->where('nombre', 'like', '%' . $search . '%')
                    ->orWhere('codigo_producto', 'like', '%' . $search . '%')
                    ->orWhere('pasillo', 'like', '%' . $search . '%')
                    ->orWhere('estanteria', 'like', '%' . $search . '%')
                    ->orWhere('casillero', 'like', '%' . $search . '%')
                    ->orWhere('estado_item', 'like', '%' . $search . '%')
                    ->orWhereHas('categoria', function ($query) use ($search) {
                        $query->where('nombre', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('unidadMedida', function ($query) use ($search) {
                        $query->where('nombre', 'like', '%' . $search . '%');
                    });
            });
        }

        $articulos = $query->paginate(5)->withQueryString();

        return view('admin.articulos.index', compact('articulos', 'search'));
    }

    public function listado(Request $request)
    {
        $reportes = [
            'control_categoria' => 'Control de stock por categoria',
            'bajo_stock' => 'Articulos bajo stock',
            'sin_stock' => 'Articulos sin stock',
            'stock_deposito' => 'Stock por deposito',
            'ubicacion' => 'Articulos por ubicacion fisica',
            'sin_ubicacion' => 'Articulos sin ubicacion',
            'valorizacion' => 'Valorizacion de stock',
            'manuales_ot' => 'Articulos cargados manualmente en ordenes',
        ];

        $reporte = $request->input('reporte', 'control_categoria');

        if (! array_key_exists($reporte, $reportes)) {
            $reporte = 'control_categoria';
        }

        $filtros = [
            'reporte' => $reporte,
            'categoria_id' => $request->input('categoria_id'),
            'deposito_id' => $request->input('deposito_id'),
            'estado_item' => $request->input('estado_item'),
            'search' => trim((string) $request->input('search', '')),
        ];

        $categorias = Categoria::orderBy('nombre')->get();
        $depositos = Deposito::orderBy('nombre')->get();
        $resultado = $this->buildListadoReporte($filtros);
        $resultado['paginated_items'] = $this->paginateListadoItems($resultado['items'], $request);
        $empresa = $this->empresaReporte();

        return view('admin.articulos.listados', compact('reportes', 'reporte', 'filtros', 'categorias', 'depositos', 'resultado', 'empresa'));
    }

    private function paginateListadoItems($items, Request $request): LengthAwarePaginator
    {
        $perPage = 10;
        $pageName = 'page';
        $page = LengthAwarePaginator::resolveCurrentPage($pageName);
        $collection = collect($items)->values();

        return new LengthAwarePaginator(
            $collection->forPage($page, $perPage)->values(),
            $collection->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'pageName' => $pageName,
                'query' => $request->query(),
            ]
        );
    }

    private function empresaReporte(): array
    {
        $ajuste = Ajuste::query()->first();
        $provincia = $ajuste?->provincia_id ? Provincia::find($ajuste->provincia_id) : null;
        $ciudad = $ajuste?->ciudad_id ? Ciudad::find($ajuste->ciudad_id) : null;

        return [
            'nombre' => $ajuste?->nombre ?: config('app.name', 'SIGA'),
            'descripcion' => $ajuste?->descripcion,
            'direccion' => $ajuste?->direccion,
            'telefono' => $ajuste?->telefono,
            'email' => $ajuste?->email,
            'web' => $ajuste?->web,
            'localidad' => collect([$ciudad?->nombre, $provincia?->nombre])->filter()->implode(', '),
            'logo' => $ajuste?->logo ? asset('storage/' . $ajuste->logo) : null,
        ];
    }

    private function buildListadoReporte(array $filtros): array
    {
        return match ($filtros['reporte']) {
            'control_categoria' => $this->reporteControlCategoria($filtros),
            'bajo_stock' => $this->reporteInventario($filtros, fn ($query) => $query->whereColumn('cantidad', '<=', 'stock_minimo')),
            'sin_stock' => $this->reporteSinStock($filtros),
            'stock_deposito' => $this->reporteInventario($filtros),
            'ubicacion' => $this->reporteArticulosUbicacion($filtros, false),
            'sin_ubicacion' => $this->reporteArticulosUbicacion($filtros, true),
            'valorizacion' => $this->reporteInventario($filtros),
            'manuales_ot' => $this->reporteManualesOrdenTrabajo($filtros),
            default => $this->reporteControlCategoria($filtros),
        };
    }

    private function reporteControlCategoria(array $filtros): array
    {
        $articulos = Articulo::query()
            ->with(['categoria', 'unidadMedida'])
            ->where(fn ($query) => $this->applyArticuloFilters($query, $filtros))
            ->orderBy('nombre')
            ->get();

        $inventarios = Inventario::query()
            ->with('deposito')
            ->whereIn('articulo_id', $articulos->pluck('id'))
            ->when($filtros['deposito_id'], fn ($query) => $query->where('deposito_id', $filtros['deposito_id']))
            ->get()
            ->groupBy('articulo_id');

        $selectedDeposito = $filtros['deposito_id'] ? Deposito::find($filtros['deposito_id']) : null;

        $items = $articulos->map(function ($articulo) use ($inventarios, $selectedDeposito) {
            $inventariosArticulo = $inventarios->get($articulo->id, collect());
            $primerInventario = $inventariosArticulo->first();
            $cantidad = (int) $inventariosArticulo->sum('cantidad');
            $precio = (float) ($primerInventario?->precio_compra_unidad ?? 0);
            $deposito = $selectedDeposito ?: ($inventariosArticulo->count() === 1 ? $primerInventario?->deposito : null);

            return (object) [
                'articulo' => $articulo,
                'deposito' => $deposito,
                'cantidad' => $cantidad,
                'stock_minimo' => (int) ($primerInventario?->stock_minimo ?? $articulo->stock_minimo ?? 0),
                'stock_maximo' => (int) ($primerInventario?->stock_maximo ?? $articulo->stock_maximo ?? 0),
                'precio_compra_unidad' => $precio,
            ];
        });

        return $this->withResumen($items, [
            'cantidad' => $items->sum('cantidad'),
            'valor' => $items->sum(fn ($item) => (float) $item->cantidad * (float) $item->precio_compra_unidad),
        ]);
    }

    private function reporteInventario(array $filtros, ?callable $scope = null): array
    {
        $query = Inventario::query()
            ->with(['articulo.categoria', 'articulo.unidadMedida', 'deposito'])
            ->whereHas('articulo', fn ($articulo) => $this->applyArticuloFilters($articulo, $filtros));

        if ($filtros['deposito_id']) {
            $query->where('deposito_id', $filtros['deposito_id']);
        }

        if ($scope) {
            $scope($query);
        }

        $items = $query
            ->orderBy('deposito_id')
            ->orderBy('articulo_id')
            ->get();

        return $this->withResumen($items, [
            'cantidad' => $items->sum('cantidad'),
            'valor' => $items->sum(fn ($item) => (float) $item->cantidad * (float) $item->precio_compra_unidad),
        ]);
    }

    private function reporteSinStock(array $filtros): array
    {
        $items = Articulo::query()
            ->with(['categoria', 'unidadMedida'])
            ->withSum('inventarios as cantidad_total', 'cantidad')
            ->where(fn ($query) => $this->applyArticuloFilters($query, $filtros))
            ->orderBy('nombre')
            ->get()
            ->filter(fn ($articulo) => (int) ($articulo->cantidad_total ?? 0) <= 0)
            ->values();

        return $this->withResumen($items, [
            'cantidad' => 0,
            'valor' => 0,
        ]);
    }

    private function reporteArticulosUbicacion(array $filtros, bool $sinUbicacion): array
    {
        $items = Articulo::query()
            ->with(['categoria', 'unidadMedida'])
            ->withSum('inventarios as cantidad_total', 'cantidad')
            ->where(fn ($query) => $this->applyArticuloFilters($query, $filtros))
            ->when($sinUbicacion, function ($query) {
                $query->where(function ($query) {
                    $query->whereNull('pasillo')->orWhere('pasillo', '')
                        ->orWhereNull('estanteria')->orWhere('estanteria', '')
                        ->orWhereNull('casillero')->orWhere('casillero', '');
                });
            }, function ($query) {
                $query->where(function ($query) {
                    $query->whereNotNull('pasillo')->where('pasillo', '!=', '')
                        ->orWhereNotNull('estanteria')->where('estanteria', '!=', '')
                        ->orWhereNotNull('casillero')->where('casillero', '!=', '');
                });
            })
            ->orderBy('pasillo')
            ->orderBy('estanteria')
            ->orderBy('casillero')
            ->orderBy('nombre')
            ->get();

        return $this->withResumen($items, [
            'cantidad' => $items->sum(fn ($item) => (int) ($item->cantidad_total ?? 0)),
            'valor' => 0,
        ]);
    }

    private function reporteManualesOrdenTrabajo(array $filtros): array
    {
        $items = OrdenTrabajoArticulo::query()
            ->with(['articulo.categoria', 'articulo.unidadMedida', 'ordenTrabajo.flota', 'ordenTrabajo.base'])
            ->where('inventario_descontado', false)
            ->whereHas('articulo', fn ($articulo) => $this->applyArticuloFilters($articulo, $filtros))
            ->orderByDesc('id')
            ->get();

        return $this->withResumen($items, [
            'cantidad' => $items->sum('cantidad'),
            'valor' => $items->sum(fn ($item) => (float) $item->cantidad * (float) $item->valor_unitario),
        ]);
    }

    private function applyArticuloFilters($query, array $filtros): void
    {
        if ($filtros['categoria_id']) {
            $query->where('categoria_id', $filtros['categoria_id']);
        }

        if ($filtros['estado_item']) {
            $query->where('estado_item', $filtros['estado_item']);
        }

        if ($filtros['search'] !== '') {
            $search = $filtros['search'];
            $query->where(function ($query) use ($search) {
                $query->where('nombre', 'like', '%' . $search . '%')
                    ->orWhere('codigo_producto', 'like', '%' . $search . '%')
                    ->orWhere('pasillo', 'like', '%' . $search . '%')
                    ->orWhere('estanteria', 'like', '%' . $search . '%')
                    ->orWhere('casillero', 'like', '%' . $search . '%');
            });
        }
    }

    private function withResumen($items, array $extra): array
    {
        return [
            'items' => $items,
            'total_items' => $items->count(),
            'cantidad_total' => $extra['cantidad'] ?? 0,
            'valor_total' => $extra['valor'] ?? 0,
        ];
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categorias = Categoria::orderBy('nombre')->get();
        $unidadesMedida = UnidadMedida::orderBy('nombre')->get();

        return view('admin.articulos.create', compact('categorias', 'unidadesMedida'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $articulo = Articulo::findOrFail($id);
        $categorias = Categoria::orderBy('nombre')->get();
        $unidadesMedida = UnidadMedida::orderBy('nombre')->get();

        return view('admin.articulos.edit', compact('articulo', 'categorias', 'unidadesMedida'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = [
            'nombre' => mb_strtoupper(trim((string) $request->input('nombre')), 'UTF-8'),
        ];

        if ($request->filled('codigo_producto')) {
            $data['codigo_producto'] = mb_strtoupper(trim((string) $request->input('codigo_producto')), 'UTF-8');
        }

        $request->merge($data);

        $validator = Validator::make($request->all(), [
            'categoria_id' => ['required', 'exists:categorias,id'],
            'unidad_medida_id' => ['required', 'exists:unidad_medidas,id'],
            'nombre' => ['required', 'string', 'max:255'],
            'codigo_producto' => ['nullable', 'string', 'max:255'],
            'foto_articulo_1' => ['nullable', 'image', 'max:2048'],
            'foto_articulo_2' => ['nullable', 'image', 'max:2048'],
            'foto_articulo_3' => ['nullable', 'image', 'max:2048'],
            'stock_minimo' => ['nullable', 'integer', 'min:0'],
            'stock_maximo' => ['nullable', 'integer', 'min:0'],
            'stock_pedido' => ['nullable', 'integer', 'min:0'],
            'reposicion_modo' => ['required', Rule::in(['manual', 'automatico'])],
            'es_herramienta' => ['nullable', 'boolean'],
            'es_ropa_epp' => ['nullable', 'boolean'],
            'pasillo' => ['nullable', 'string', 'max:50'],
            'estanteria' => ['nullable', 'string', 'max:50'],
            'casillero' => ['nullable', 'string', 'max:50'],
            'estado_item' => ['required', Rule::in(['activo', 'inactivo'])],
            'observaciones' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('admin.articulos.create')
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->only([
            'categoria_id', 'unidad_medida_id', 'nombre', 'codigo_producto',
            'stock_minimo', 'stock_maximo', 'stock_pedido', 'reposicion_modo', 'pasillo', 'estanteria', 'casillero',
            'estado_item', 'observaciones'
        ]);
        $data['es_herramienta'] = $request->boolean('es_herramienta');
        $data['es_ropa_epp'] = $request->boolean('es_ropa_epp');

        foreach (['foto_articulo_1', 'foto_articulo_2', 'foto_articulo_3'] as $field) {
            if ($request->hasFile($field)) {
                $data[$field] = Storage::disk('public')->putFile('articulos', $request->file($field));
            }
        }

        Articulo::create($data);

        return redirect()
            ->route('admin.articulos.index')
            ->with('success', 'Artículo creado correctamente.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $articulo = Articulo::findOrFail($id);

        $data = [
            'nombre' => mb_strtoupper(trim((string) $request->input('nombre')), 'UTF-8'),
        ];

        if ($request->filled('codigo_producto')) {
            $data['codigo_producto'] = mb_strtoupper(trim((string) $request->input('codigo_producto')), 'UTF-8');
        } else {
            $data['codigo_producto'] = null;
        }

        $request->merge($data);

        $validator = Validator::make($request->all(), [
            'categoria_id' => ['required', 'exists:categorias,id'],
            'unidad_medida_id' => ['required', 'exists:unidad_medidas,id'],
            'nombre' => ['required', 'string', 'max:255'],
            'codigo_producto' => ['nullable', 'string', 'max:255'],
            'foto_articulo_1' => ['nullable', 'image', 'max:2048'],
            'foto_articulo_2' => ['nullable', 'image', 'max:2048'],
            'foto_articulo_3' => ['nullable', 'image', 'max:2048'],
            'stock_minimo' => ['nullable', 'integer', 'min:0'],
            'stock_maximo' => ['nullable', 'integer', 'min:0'],
            'stock_pedido' => ['nullable', 'integer', 'min:0'],
            'reposicion_modo' => ['required', Rule::in(['manual', 'automatico'])],
            'es_herramienta' => ['nullable', 'boolean'],
            'es_ropa_epp' => ['nullable', 'boolean'],
            'pasillo' => ['nullable', 'string', 'max:50'],
            'estanteria' => ['nullable', 'string', 'max:50'],
            'casillero' => ['nullable', 'string', 'max:50'],
            'estado_item' => ['required', Rule::in(['activo', 'inactivo'])],
            'observaciones' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('admin.articulos.edit', $articulo->id)
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->only([
            'categoria_id', 'unidad_medida_id', 'nombre', 'codigo_producto',
            'stock_minimo', 'stock_maximo', 'stock_pedido', 'reposicion_modo', 'pasillo', 'estanteria', 'casillero',
            'estado_item', 'observaciones'
        ]);
        $data['es_herramienta'] = $request->boolean('es_herramienta');
        $data['es_ropa_epp'] = $request->boolean('es_ropa_epp');

        foreach (['foto_articulo_1', 'foto_articulo_2', 'foto_articulo_3'] as $field) {
            if ($request->hasFile($field)) {
                $data[$field] = Storage::disk('public')->putFile('articulos', $request->file($field));
            }
        }

        $articulo->update($data);

        return redirect()
            ->route('admin.articulos.index')
            ->with('success', 'Artículo actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $articulo = Articulo::findOrFail($id);
        $articulo->delete();

        return redirect()
            ->route('admin.articulos.index')
            ->with('success', 'Artículo eliminado correctamente.');
    }

    public function show(string $id)
    {
        $articulo = Articulo::query()
            ->with(['categoria', 'unidadMedida', 'inventarios'])
            ->findOrFail($id);  
        return view('admin.articulos.show', compact('articulo'));
    }
}
