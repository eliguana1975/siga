<?php

namespace App\Http\Controllers;

use App\Models\Articulo;
use App\Models\Deposito;
use App\Models\DocumentoOperativo;
use App\Models\Empleado;
use App\Models\EntregaRopaEpp;
use App\Models\EntregaRopaEppDetalle;
use App\Models\Inventario;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EntregaRopaEppController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));

        $entregas = EntregaRopaEpp::query()
            ->with([
                'empleado:id,nombres,apellidos,numero_doc,tipo_doc',
                'deposito:id,nombre',
                'usuario:id,name',
                'detalles:id,entrega_ropa_epp_id,articulo_id,cantidad_entregada,condicion_entrega',
                'detalles.articulo:id,nombre,codigo_producto',
            ])
            ->withCount('detalles')
            ->withSum('detalles', 'cantidad_entregada')
            ->when($search !== '', function ($query) use ($search) {
                $query->where('estado', 'like', "%{$search}%")
                    ->orWhere('observaciones', 'like', "%{$search}%")
                    ->orWhereHas('empleado', fn ($empleado) => $empleado
                        ->where('nombres', 'like', "%{$search}%")
                        ->orWhere('apellidos', 'like', "%{$search}%")
                        ->orWhere('numero_doc', 'like', "%{$search}%"))
                    ->orWhereHas('deposito', fn ($deposito) => $deposito->where('nombre', 'like', "%{$search}%"))
                    ->orWhereHas('detalles.articulo', fn ($articulo) => $articulo
                        ->where('nombre', 'like', "%{$search}%")
                        ->orWhere('codigo_producto', 'like', "%{$search}%"));
            })
            ->latest('fecha_entrega')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('admin.entregas-ropa-epp.index', compact('entregas', 'search'));
    }

    public function create(Request $request)
    {
        return view('admin.entregas-ropa-epp.create', $this->formData($request));
    }

    public function edit(Request $request, string $id)
    {
        $entrega = $this->entregaQuery()->findOrFail($id);

        if ($entrega->detalles->contains(fn ($detalle) => (int) $detalle->cantidad_devuelta > 0)) {
            return redirect()
                ->route('admin.entregas-ropa-epp.show', $entrega->id)
                ->with('error', 'No se puede editar una entrega que ya tiene devoluciones registradas.');
        }

        return view('admin.entregas-ropa-epp.edit', $this->formData($request, $entrega) + compact('entrega'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'empleado_id' => ['required', 'exists:empleados,id'],
            'deposito_id' => ['required', 'exists:depositos,id'],
            'fecha_entrega' => ['required', 'date'],
            'observaciones' => ['nullable', 'string'],
            'detalles' => ['required', 'array', 'min:1'],
            'detalles.*.articulo_id' => ['required', 'exists:articulos,id'],
            'detalles.*.cantidad' => ['required', 'integer', 'min:1'],
            'detalles.*.condicion_entrega' => ['nullable', 'string', 'max:120'],
        ]);

        $entrega = DB::transaction(function () use ($validated) {
            $articuloIds = collect($validated['detalles'])
                ->pluck('articulo_id')
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values();
            $articulos = Articulo::query()
                ->select('id', 'nombre')
                ->whereIn('id', $articuloIds)
                ->where('es_ropa_epp', true)
                ->get()
                ->keyBy('id');
            $inventarios = Inventario::query()
                ->where('deposito_id', $validated['deposito_id'])
                ->whereIn('articulo_id', $articuloIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('articulo_id');

            $cantidadesPorArticulo = collect($validated['detalles'])
                ->groupBy(fn ($detalle) => (int) $detalle['articulo_id'])
                ->map(fn ($detalles) => $detalles->sum(fn ($detalle) => (int) $detalle['cantidad']));

            foreach ($cantidadesPorArticulo as $articuloId => $cantidadRequerida) {
                $articulo = $articulos->get((int) $articuloId);
                $inventario = $inventarios->get((int) $articuloId);

                if (! $articulo || ! $inventario || (int) $inventario->cantidad < (int) $cantidadRequerida) {
                    $detalleIndex = collect($validated['detalles'])->search(fn ($detalle) => (int) $detalle['articulo_id'] === (int) $articuloId);

                    throw ValidationException::withMessages([
                        "detalles.{$detalleIndex}.cantidad" => 'La cantidad supera el stock disponible de ' . ($articulo?->nombre ?? 'la prenda/EPP seleccionada') . '.',
                    ]);
                }
            }

            $entrega = EntregaRopaEpp::create([
                'empleado_id' => $validated['empleado_id'],
                'deposito_id' => $validated['deposito_id'],
                'usuario_id' => Auth::id(),
                'fecha_entrega' => $validated['fecha_entrega'],
                'estado' => 'entregada',
                'observaciones' => $validated['observaciones'] ?? null,
            ]);

            foreach ($validated['detalles'] as $index => $detalle) {
                $articulo = $articulos->get((int) $detalle['articulo_id']);

                if (! $articulo) {
                    throw ValidationException::withMessages([
                        "detalles.{$index}.articulo_id" => 'El articulo seleccionado no esta marcado como prenda/EPP.',
                    ]);
                }

                $inventario = $inventarios->get((int) $detalle['articulo_id']);
                $cantidad = (int) $detalle['cantidad'];

                if (! $inventario instanceof Inventario) {
                    throw ValidationException::withMessages([
                        "detalles.{$index}.cantidad" => 'No existe inventario para la prenda/EPP seleccionada en el deposito indicado.',
                    ]);
                }

                $inventario->decrement('cantidad', $cantidad);

                $entrega->detalles()->create([
                    'articulo_id' => $detalle['articulo_id'],
                    'cantidad_entregada' => $cantidad,
                    'cantidad_devuelta' => 0,
                    'estado' => 'entregada',
                    'condicion_entrega' => $detalle['condicion_entrega'] ?? null,
                ]);
            }

            return $entrega;
        });

        return redirect()
            ->route('admin.entregas-ropa-epp.show', $entrega->id)
            ->with('success', 'Entrega de ropa y EPP registrada correctamente.');
    }

    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'empleado_id' => ['required', 'exists:empleados,id'],
            'deposito_id' => ['required', 'exists:depositos,id'],
            'fecha_entrega' => ['required', 'date'],
            'observaciones' => ['nullable', 'string'],
            'detalles' => ['required', 'array', 'min:1'],
            'detalles.*.articulo_id' => ['required', 'exists:articulos,id'],
            'detalles.*.cantidad' => ['required', 'integer', 'min:1'],
            'detalles.*.condicion_entrega' => ['nullable', 'string', 'max:120'],
        ]);

        DB::transaction(function () use ($id, $validated) {
            $entrega = EntregaRopaEpp::query()
                ->with('detalles')
                ->lockForUpdate()
                ->findOrFail($id);

            if ($entrega->detalles->contains(fn ($detalle) => (int) $detalle->cantidad_devuelta > 0)) {
                throw ValidationException::withMessages([
                    'detalles' => 'No se puede editar una entrega que ya tiene devoluciones registradas.',
                ]);
            }

            $entrega->detalles
                ->groupBy('articulo_id')
                ->each(function ($detalles, $articuloId) use ($entrega) {
                    $cantidad = $detalles->sum('cantidad_entregada');
                    $inventario = Inventario::firstOrCreate(
                        [
                            'deposito_id' => $entrega->deposito_id,
                            'articulo_id' => $articuloId,
                        ],
                        [
                            'precio_compra_unidad' => 0,
                            'cantidad' => 0,
                            'stock_minimo' => 0,
                            'stock_maximo' => 0,
                            'estado' => 'ajuste',
                        ]
                    );

                    $inventario->increment('cantidad', $cantidad);
                    $inventario->forceFill(['estado' => 'ajuste'])->save();
                });

            $articuloIds = collect($validated['detalles'])
                ->pluck('articulo_id')
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values();
            $articulos = Articulo::query()
                ->select('id', 'nombre')
                ->whereIn('id', $articuloIds)
                ->where('es_ropa_epp', true)
                ->get()
                ->keyBy('id');
            $inventarios = Inventario::query()
                ->where('deposito_id', $validated['deposito_id'])
                ->whereIn('articulo_id', $articuloIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('articulo_id');
            $cantidadesPorArticulo = collect($validated['detalles'])
                ->groupBy(fn ($detalle) => (int) $detalle['articulo_id'])
                ->map(fn ($detalles) => $detalles->sum(fn ($detalle) => (int) $detalle['cantidad']));

            foreach ($cantidadesPorArticulo as $articuloId => $cantidadRequerida) {
                $articulo = $articulos->get((int) $articuloId);
                $inventario = $inventarios->get((int) $articuloId);

                if (! $articulo || ! $inventario || (int) $inventario->cantidad < (int) $cantidadRequerida) {
                    $detalleIndex = collect($validated['detalles'])->search(fn ($detalle) => (int) $detalle['articulo_id'] === (int) $articuloId);

                    throw ValidationException::withMessages([
                        "detalles.{$detalleIndex}.cantidad" => 'La cantidad supera el stock disponible de ' . ($articulo?->nombre ?? 'la prenda/EPP seleccionada') . '.',
                    ]);
                }
            }

            $entrega->update([
                'empleado_id' => $validated['empleado_id'],
                'deposito_id' => $validated['deposito_id'],
                'usuario_id' => Auth::id(),
                'fecha_entrega' => $validated['fecha_entrega'],
                'estado' => 'entregada',
                'observaciones' => $validated['observaciones'] ?? null,
            ]);

            $entrega->detalles()->delete();

            foreach ($validated['detalles'] as $index => $detalle) {
                $inventario = $inventarios->get((int) $detalle['articulo_id']);
                $cantidad = (int) $detalle['cantidad'];

                if (! $inventario instanceof Inventario) {
                    throw ValidationException::withMessages([
                        "detalles.{$index}.cantidad" => 'No existe inventario para la prenda/EPP seleccionada en el deposito indicado.',
                    ]);
                }

                $inventario->decrement('cantidad', $cantidad);

                $entrega->detalles()->create([
                    'articulo_id' => $detalle['articulo_id'],
                    'cantidad_entregada' => $cantidad,
                    'cantidad_devuelta' => 0,
                    'estado' => 'entregada',
                    'condicion_entrega' => $detalle['condicion_entrega'] ?? null,
                ]);
            }
        });

        return redirect()
            ->route('admin.entregas-ropa-epp.show', $id)
            ->with('success', 'Entrega de ropa y EPP actualizada correctamente.');
    }

    public function show(string $id)
    {
        $entrega = $this->entregaQuery()->findOrFail($id);
        $documentos = DocumentoOperativo::query()
            ->with('usuario:id,name')
            ->where('documentable_type', EntregaRopaEpp::class)
            ->where('documentable_id', $entrega->id)
            ->latest()
            ->get();

        return view('admin.entregas-ropa-epp.show', compact('entrega', 'documentos'));
    }

    public function planilla(string $id)
    {
        return redirect()->route('admin.entregas-ropa-epp.show', $id);
    }

    public function devolver(Request $request, string $id, string $detalleId)
    {
        $entrega = EntregaRopaEpp::findOrFail($id);
        $detalle = EntregaRopaEppDetalle::query()
            ->where('entrega_ropa_epp_id', $entrega->id)
            ->findOrFail($detalleId);

        $validated = $request->validate([
            'cantidad_devuelta' => ['required', 'integer', 'min:1'],
            'fecha_devolucion' => ['required', 'date'],
            'estado' => ['required', 'in:devuelta,rota,perdida'],
            'condicion_devolucion' => ['nullable', 'string', 'max:120'],
            'observaciones' => ['nullable', 'string'],
        ]);

        if ((int) $validated['cantidad_devuelta'] > $detalle->cantidadPendiente()) {
            return back()->with('error', 'La cantidad devuelta supera lo pendiente.');
        }

        DB::transaction(function () use ($entrega, $detalle, $validated) {
            $cantidadDevuelta = (int) $validated['cantidad_devuelta'];
            $nuevoDevuelto = (int) $detalle->cantidad_devuelta + $cantidadDevuelta;
            $estadoDetalle = $nuevoDevuelto >= (int) $detalle->cantidad_entregada
                ? $validated['estado']
                : 'parcial';

            $detalle->update([
                'cantidad_devuelta' => $nuevoDevuelto,
                'estado' => $estadoDetalle,
                'fecha_devolucion' => $validated['fecha_devolucion'],
                'condicion_devolucion' => $validated['condicion_devolucion'] ?? null,
                'observaciones' => $validated['observaciones'] ?? $detalle->observaciones,
            ]);

            if ($validated['estado'] === 'devuelta') {
                $inventario = Inventario::firstOrCreate(
                    [
                        'deposito_id' => $entrega->deposito_id,
                        'articulo_id' => $detalle->articulo_id,
                    ],
                    [
                        'precio_compra_unidad' => 0,
                        'cantidad' => 0,
                        'stock_minimo' => $detalle->articulo?->stock_minimo ?? 0,
                        'stock_maximo' => $detalle->articulo?->stock_maximo ?? 0,
                        'estado' => 'ajuste',
                    ]
                );

                $inventario->increment('cantidad', $cantidadDevuelta);
                $inventario->forceFill(['estado' => 'ajuste'])->save();
            }

            $entrega->refreshEstado();
        });

        return redirect()
            ->route('admin.entregas-ropa-epp.show', $entrega->id)
            ->with('success', 'Devolucion registrada correctamente.');
    }

    private function formData(Request $request, ?EntregaRopaEpp $entrega = null): array
    {
        $depositoId = $request->integer('deposito_id') ?: $entrega?->deposito_id;
        $cantidadesActuales = $entrega
            ? $entrega->detalles->groupBy('articulo_id')->map(fn ($detalles) => $detalles->sum('cantidad_entregada'))
            : collect();

        return [
            'empleados' => Empleado::query()->select('id', 'nombres', 'apellidos', 'numero_doc')->where('estado', 'activo')->orderBy('apellidos')->orderBy('nombres')->get(),
            'depositos' => Deposito::select('id', 'nombre')->orderBy('nombre')->get(),
            'ropaEpp' => $this->ropaEppDisponibles($depositoId, $cantidadesActuales),
            'selectedDepositoId' => $depositoId,
        ];
    }

    private function ropaEppDisponibles(?int $depositoId, ?Collection $cantidadesActuales = null)
    {
        if (! $depositoId) {
            return collect();
        }

        $cantidadesActuales = $cantidadesActuales ?? collect();
        $articulosActuales = $cantidadesActuales->keys()->map(fn ($id) => (int) $id)->all();

        return Inventario::query()
            ->select('id', 'deposito_id', 'articulo_id', 'cantidad')
            ->with([
                'articulo:id,nombre,codigo_producto,unidad_medida_id,es_ropa_epp',
                'articulo.unidadMedida:id,nombre',
                'articulo.categoria:id,nombre',
                'deposito:id,nombre',
            ])
            ->where(function ($query) use ($articulosActuales) {
                $query->where('cantidad', '>', 0)
                    ->when($articulosActuales, fn ($query) => $query->orWhereIn('articulo_id', $articulosActuales));
            })
            ->whereHas('articulo', fn ($articulo) => $articulo
                ->where('es_ropa_epp', true)
                ->where('estado_item', 'activo'))
            ->when($depositoId, fn ($query) => $query->where('deposito_id', $depositoId))
            ->orderBy('articulo_id')
            ->get()
            ->each(function ($inventario) use ($cantidadesActuales) {
                $inventario->stock_para_entrega = (int) $inventario->cantidad + (int) ($cantidadesActuales->get($inventario->articulo_id, 0));
            });
    }

    private function inventarioDisponible(int $depositoId, int $articuloId): ?Inventario
    {
        return Inventario::query()
            ->where('deposito_id', $depositoId)
            ->where('articulo_id', $articuloId)
            ->lockForUpdate()
            ->first();
    }

    private function entregaQuery()
    {
        return EntregaRopaEpp::query()
            ->with(['empleado', 'deposito', 'usuario', 'detalles.articulo.unidadMedida']);
    }
}
