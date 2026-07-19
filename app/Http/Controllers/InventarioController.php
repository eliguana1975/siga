<?php

namespace App\Http\Controllers;

use App\Models\Articulo;
use App\Models\Ajuste;
use App\Models\Ciudad;
use App\Models\CompraDetalle;
use App\Models\Deposito;
use App\Models\Inventario;
use App\Models\Provincia;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class InventarioController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('check.permission:administrar-inventarios');
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));

        $query = $this->visibleInventarioQuery($request->user())
            ->with([
                'articulo:id,nombre,codigo_producto,categoria_id,unidad_medida_id,pasillo,estanteria,casillero',
                'articulo.categoria:id,nombre',
                'articulo.unidadMedida:id,nombre',
                'deposito:id,nombre',
            ])
            ->latest('fecha_registro')
            ->latest('id');

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query->whereHas('articulo', function ($articulo) use ($search) {
                    $articulo->where('nombre', 'like', '%' . $search . '%')
                        ->orWhere('codigo_producto', 'like', '%' . $search . '%')
                        ->orWhere('pasillo', 'like', '%' . $search . '%')
                        ->orWhere('estanteria', 'like', '%' . $search . '%')
                        ->orWhere('casillero', 'like', '%' . $search . '%');
                })
                    ->orWhereHas('deposito', fn ($deposito) => $deposito->where('nombre', 'like', '%' . $search . '%'));
            });
        }

        $inventarios = $query->paginate(5)->withQueryString();
        $articulos = Articulo::select('id', 'nombre', 'codigo_producto')->orderBy('nombre')->get();
        $depositos = $this->visibleDepositosQuery($request->user())->select('id', 'nombre')->orderBy('nombre')->get();
        $estados = ['compra', 'ajuste', 'traslado'];
        $resumenQuery = $this->visibleInventarioQuery($request->user());
        $valorTotalQuery = $this->visibleInventarioQuery($request->user());
        $resumen = [
            'total_articulos' => Articulo::count(),
            'total_depositos' => $depositos->count(),
            'articulos_en_inventario' => (clone $resumenQuery)->distinct('articulo_id')->count('articulo_id'),
            'cantidad_total' => (int) (clone $resumenQuery)->sum('cantidad'),
            'stock_minimo' => (clone $resumenQuery)->whereColumn('cantidad', '<=', 'stock_minimo')->count(),
            'sin_stock' => (clone $resumenQuery)->where('cantidad', 0)->count(),
            'sobre_stock_maximo' => (clone $resumenQuery)->where('stock_maximo', '>', 0)
                ->whereColumn('cantidad', '>=', 'stock_maximo')
                ->count(),
            'pendientes_entrega' => $this->cantidadPendienteEntrega(),
            'valor_total' => (float) $valorTotalQuery
                ->selectRaw('COALESCE(SUM(cantidad * precio_compra_unidad), 0) as total')
                ->value('total'),
        ];
        return view('admin.inventarios.index', compact(
            'inventarios',
            'articulos',
            'depositos',
            'estados',
            'search',
            'resumen'
        ));
    }

    public function bajoStock(Request $request)
    {
        $search = trim((string) $request->input('search', ''));

        $query = $this->filteredInventarioQuery($search, $request->user())
            ->whereColumn('cantidad', '<=', 'stock_minimo')
            ->latest('fecha_registro')
            ->latest('id');

        $inventarios = $query->paginate(10)->withQueryString();

        return view('admin.inventarios.bajo-stock', compact('inventarios', 'search'));
    }

    public function store(Request $request)
    {
        $validator = $this->validator($request, $this->visibleDepositoIds($request->user()));

        if ($validator->fails()) {
            return redirect()
                ->route('admin.inventarios.index')
                ->withErrors($validator)
                ->withInput()
                ->with('open_modal', 'createInventarioModal');
        }

        Inventario::create($this->dataWithArticuloStock($validator->validated()));

        return redirect()
            ->route('admin.inventarios.index')
            ->with('success', 'Inventario creado correctamente.');
    }

    public function sinStock(Request $request)
    {
        $search = trim((string) $request->input('search', ''));

        $query = $this->filteredInventarioQuery($search, $request->user())
            ->where('cantidad', 0)
            ->latest('fecha_registro')
            ->latest('id');

        $inventarios = $query->paginate(10)->withQueryString();

        return view('admin.inventarios.sin-stock', compact('inventarios', 'search'));
    }

    public function etiqueta(Request $request, string $id)
    {
        $inventario = Inventario::query()
            ->with(['articulo.categoria', 'articulo.unidadMedida', 'deposito'])
            ->findOrFail($id);

        $this->authorizeInventarioScope($inventario, $request->user());

        $articulo = $inventario->articulo;
        $ubicacion = collect([
            $articulo?->pasillo ? 'P: ' . $articulo->pasillo : null,
            $articulo?->estanteria ? 'E: ' . $articulo->estanteria : null,
            $articulo?->casillero ? 'N: ' . $articulo->casillero : null,
        ])->filter()->implode(' / ') ?: 'Sin ubicacion';
        $empresa = $this->empresaEtiqueta();
        $qrUrl = route('admin.articulos.show', $inventario->articulo_id) . '?inventario=' . $inventario->id;

        return view('admin.inventarios.etiqueta', compact('inventario', 'ubicacion', 'empresa', 'qrUrl'));
    }

    public function update(Request $request, string $id)
    {
        $inventario = Inventario::findOrFail($id);
        $this->authorizeInventarioScope($inventario, $request->user());
        $validator = $this->validator($request, $this->visibleDepositoIds($request->user()));

        if ($validator->fails()) {
            return redirect()
                ->route('admin.inventarios.index')
                ->withErrors($validator)
                ->withInput()
                ->with('open_modal', 'editInventarioModal-' . $inventario->id);
        }

        $inventario->update($this->dataWithArticuloStock($validator->validated()));

        return redirect()
            ->route('admin.inventarios.index')
            ->with('success', 'Inventario actualizado correctamente.');
    }

    public function destroy(Request $request, string $id)
    {
        $inventario = Inventario::findOrFail($id);
        $this->authorizeInventarioScope($inventario, $request->user());
        $inventario->delete();

        return redirect()
            ->route('admin.inventarios.index')
            ->with('success', 'Inventario eliminado correctamente.');
    }

    private function validator(Request $request, array $depositoIds)
    {
        $validator = Validator::make($request->all(), [
            'deposito_id' => ['required', 'integer', Rule::in($depositoIds)],
            'articulo_id' => ['required', 'integer', 'exists:articulos,id'],
            'precio_compra_unidad' => ['nullable', 'numeric', 'min:0'],
            'cantidad' => ['required', 'integer', 'min:0'],
            'estado' => ['required', Rule::in(['compra', 'ajuste', 'traslado'])],
        ]);

        return $validator;
    }

    private function dataWithArticuloStock(array $data): array
    {
        $articulo = Articulo::findOrFail($data['articulo_id']);
        $data['stock_minimo'] = (int) ($articulo->stock_minimo ?? 0);
        $data['stock_maximo'] = (int) ($articulo->stock_maximo ?? 0);

        return $data;
    }

    private function filteredInventarioQuery(string $search, ?User $user)
    {
        $query = $this->visibleInventarioQuery($user)
            ->with([
                'articulo:id,nombre,codigo_producto,categoria_id,unidad_medida_id,pasillo,estanteria,casillero',
                'articulo.categoria:id,nombre',
                'articulo.unidadMedida:id,nombre',
                'deposito:id,nombre',
            ]);

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query->whereHas('articulo', function ($articulo) use ($search) {
                    $articulo->where('nombre', 'like', '%' . $search . '%')
                        ->orWhere('codigo_producto', 'like', '%' . $search . '%')
                        ->orWhere('pasillo', 'like', '%' . $search . '%')
                        ->orWhere('estanteria', 'like', '%' . $search . '%')
                        ->orWhere('casillero', 'like', '%' . $search . '%');
                })
                    ->orWhereHas('deposito', fn ($deposito) => $deposito->where('nombre', 'like', '%' . $search . '%'));
            });
        }

        return $query;
    }

    private function visibleInventarioQuery(?User $user)
    {
        $query = Inventario::query();
        $depositoIds = $this->visibleDepositoIds($user);

        if (! $this->canSeeAllInventarios($user)) {
            $query->whereIn('deposito_id', $depositoIds);
        }

        return $query;
    }

    private function visibleDepositosQuery(?User $user)
    {
        $query = Deposito::query();
        $depositoIds = $this->visibleDepositoIds($user);

        if (! $this->canSeeAllInventarios($user)) {
            $query->whereIn('id', $depositoIds);
        }

        return $query;
    }

    private function visibleDepositoIds(?User $user): array
    {
        if ($this->canSeeAllInventarios($user)) {
            return Deposito::pluck('id')->map(fn ($id) => (int) $id)->all();
        }

        $depositoId = $user?->base?->deposito_id;

        return $depositoId ? [(int) $depositoId] : [];
    }

    private function canSeeAllInventarios(?User $user): bool
    {
        return (bool) ($user?->isSuperUsuario() || $user?->puede_ver_todos_inventarios);
    }

    private function authorizeInventarioScope(Inventario $inventario, ?User $user): void
    {
        if ($this->canSeeAllInventarios($user)) {
            return;
        }

        if (in_array((int) $inventario->deposito_id, $this->visibleDepositoIds($user), true)) {
            return;
        }

        abort(403, 'No tienes permiso para operar inventario de otro pañol.');
    }

    private function cantidadPendienteEntrega(): int
    {
        return (int) CompraDetalle::query()
            ->join('compras', 'compras.id', '=', 'compra_detalles.compra_id')
            ->leftJoinSub(
                'select compra_detalle_id, sum(cantidad) as cantidad_recibida from detalle_entrada group by compra_detalle_id',
                'ingresos',
                'ingresos.compra_detalle_id',
                '=',
                'compra_detalles.id'
            )
            ->where('compras.estado', 'aprobada')
            ->selectRaw('COALESCE(SUM(GREATEST(compra_detalles.cantidad - COALESCE(ingresos.cantidad_recibida, 0), 0)), 0) as pendiente')
            ->value('pendiente');
    }

    private function empresaEtiqueta(): array
    {
        $ajuste = Ajuste::query()->first();
        $provincia = $ajuste?->provincia_id ? Provincia::find($ajuste->provincia_id) : null;
        $ciudad = $ajuste?->ciudad_id ? Ciudad::find($ajuste->ciudad_id) : null;

        return [
            'nombre' => $ajuste?->nombre ?: config('app.name', 'SIGA'),
            'descripcion' => $ajuste?->descripcion,
            'localidad' => collect([$ciudad?->nombre, $provincia?->nombre])->filter()->implode(', '),
        ];
    }
}
