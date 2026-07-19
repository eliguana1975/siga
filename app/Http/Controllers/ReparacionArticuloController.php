<?php

namespace App\Http\Controllers;

use App\Models\Articulo;
use App\Models\Ajuste;
use App\Models\Ciudad;
use App\Models\DocumentoOperativo;
use App\Models\Proveedor;
use App\Models\Provincia;
use App\Models\ReparacionArticulo;
use App\Models\ReparacionArticuloDetalle;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReparacionArticuloController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $soloPendientes = $request->boolean('pendientes');
        $soloVencidas = $request->boolean('vencidas');
        $empresaRemite = $this->empresaRemiteData();
        $empresaNombreEnvio = $this->empresaNombreEnvio();

        $reparaciones = ReparacionArticulo::query()
            ->with([
                'proveedor:id,nombre',
                'provincia:id,nombre',
                'ciudad:id,nombre',
                'usuario:id,name',
                'detalles:id,reparacion_articulo_id,articulo_id,descripcion_articulo_manual,codigo_articulo_manual,cantidad_enviada,cantidad_devuelta,estado',
                'detalles.articulo:id,nombre,codigo_producto',
            ])
            ->when($search !== '', function ($query) use ($search) {
                $query->where('numero_orden', 'like', "%{$search}%")
                    ->orWhere('estado', 'like', "%{$search}%")
                    ->orWhereHas('proveedor', fn ($proveedor) => $proveedor->where('nombre', 'like', "%{$search}%"))
                    ->orWhereHas('detalles', fn ($detalles) => $detalles
                        ->where('descripcion_articulo_manual', 'like', "%{$search}%")
                        ->orWhere('codigo_articulo_manual', 'like', "%{$search}%"))
                    ->orWhereHas('detalles.articulo', fn ($articulo) => $articulo
                        ->where('nombre', 'like', "%{$search}%")
                        ->orWhere('codigo_producto', 'like', "%{$search}%"));
            })
            ->when($soloPendientes, fn ($query) => $query
                ->whereHas('detalles', fn ($detalles) => $detalles->whereRaw('cantidad_enviada > cantidad_devuelta')))
            ->when($soloVencidas, fn ($query) => $query
                ->whereDate('fecha_compromiso', '<', now()->toDateString())
                ->whereHas('detalles', fn ($detalles) => $detalles->whereRaw('cantidad_enviada > cantidad_devuelta')))
            ->latest('fecha_envio')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('admin.reparaciones-articulos.index', compact('reparaciones', 'search', 'soloPendientes', 'soloVencidas', 'empresaNombreEnvio', 'empresaRemite'));
    }

    public function pendientes(Request $request)
    {
        $request->merge(['pendientes' => '1']);

        return $this->index($request);
    }

    public function create()
    {
        return view('admin.reparaciones-articulos.create', [
            'proveedores' => Proveedor::query()
                ->with(['provincia:id,nombre', 'ciudad:id,nombre'])
                ->orderBy('nombre')
                ->get(),
            'provincias' => Provincia::query()->orderBy('nombre')->get(['id', 'nombre']),
            'ciudades' => Ciudad::query()->orderBy('nombre')->get(['id', 'nombre']),
            'articulos' => Articulo::query()
                ->select('id', 'nombre', 'codigo_producto', 'estado_item')
                ->where('estado_item', 'activo')
                ->orderBy('nombre')
                ->get(),
            'empresaRemite' => $this->empresaRemiteData(),
            'empresaNombreEnvio' => $this->empresaNombreEnvio(),
        ]);
    }

    public function proveedorData(string $id): JsonResponse
    {
        $proveedor = Proveedor::query()
            ->with(['provincia:id,nombre', 'ciudad:id,nombre'])
            ->findOrFail($id);

        return response()->json([
            'id' => $proveedor->id,
            'nombre' => $proveedor->nombre,
            'contacto' => $proveedor->contacto,
            'provincia_id' => $proveedor->provincia_id,
            'provincia_nombre' => $proveedor->provincia?->nombre,
            'ciudad_id' => $proveedor->ciudades_id,
            'ciudad_nombre' => $proveedor->ciudad?->nombre,
            'direccion' => $proveedor->direccion,
            'telefono' => $proveedor->telefono,
            'codigo_postal' => $proveedor->codigo_postal,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'proveedor_id' => ['required', 'exists:proveedores,id'],
            'provincia_id' => ['nullable', 'exists:provincias,id'],
            'ciudad_id' => ['nullable', 'exists:ciudades,id'],
            'domicilio' => ['nullable', 'string', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:50'],
            'codigo_postal' => ['nullable', 'string', 'max:20'],
            'fecha_envio' => ['required', 'date'],
            'fecha_compromiso' => ['nullable', 'date', 'after_or_equal:fecha_envio'],
            'observaciones' => ['nullable', 'string'],
            'detalles' => ['required', 'array', 'min:1'],
            'detalles.*.articulo_id' => ['nullable', 'exists:articulos,id'],
            'detalles.*.descripcion_articulo_manual' => ['nullable', 'string', 'max:255'],
            'detalles.*.codigo_articulo_manual' => ['nullable', 'string', 'max:120'],
            'detalles.*.cantidad_enviada' => ['required', 'integer', 'min:1'],
            'detalles.*.costo_unitario' => ['nullable', 'numeric', 'min:0'],
            'detalles.*.observaciones' => ['nullable', 'string'],
        ]);

        $validated = $this->normalizeStorePayload($validated);
        $empresaRemite = $this->empresaRemiteData();
        $empresaNombreEnvio = $empresaRemite['nombre'] ?? $this->empresaNombreEnvio();

        $detalles = collect($validated['detalles']);

        $detalles->each(function (array $detalle, int $index): void {
            $articuloId = isset($detalle['articulo_id']) ? (int) $detalle['articulo_id'] : 0;
            $manual = trim((string) ($detalle['descripcion_articulo_manual'] ?? ''));

            if ($articuloId <= 0 && $manual === '') {
                throw ValidationException::withMessages([
                    "detalles.{$index}.articulo_id" => 'Debe seleccionar un articulo o ingresar una descripcion manual.',
                ]);
            }
        });

        $articuloIds = $detalles
            ->pluck('articulo_id')
            ->filter(fn ($id) => ! empty($id))
            ->map(fn ($id) => (int) $id);

        if ($articuloIds->count() !== $articuloIds->unique()->count()) {
            throw ValidationException::withMessages([
                'detalles' => 'No se puede repetir el mismo articulo en la misma orden de reparacion.',
            ]);
        }

        $reparacion = DB::transaction(function () use ($validated, $empresaNombreEnvio, $empresaRemite) {
            $proveedor = Proveedor::query()
                ->with(['provincia:id,nombre', 'ciudad:id,nombre'])
                ->findOrFail($validated['proveedor_id']);

            $reparacion = ReparacionArticulo::create([
                'numero_orden' => $this->nextNumeroOrden(),
                'proveedor_id' => (int) $validated['proveedor_id'],
                'provincia_id' => $validated['provincia_id'] ?? $proveedor->provincia_id,
                'ciudad_id' => $validated['ciudad_id'] ?? $proveedor->ciudades_id,
                'quien_envia_nombre' => $empresaNombreEnvio ?? $this->normalizeUpperNullable(Auth::user()?->name),
                'quien_envia_documento' => $empresaRemite['cuit'] ?? null,
                'quien_recibe_nombre' => $this->normalizeUpperNullable($proveedor->contacto ?: $proveedor->nombre),
                'quien_recibe_documento' => null,
                'domicilio' => $this->normalizeUpperNullable($validated['domicilio'] ?? $proveedor->direccion),
                'telefono' => $this->normalizeUpperNullable($validated['telefono'] ?? $proveedor->telefono),
                'codigo_postal' => $this->normalizeUpperNullable($validated['codigo_postal'] ?? $proveedor->codigo_postal),
                'fecha_envio' => $validated['fecha_envio'],
                'fecha_compromiso' => $validated['fecha_compromiso'] ?? null,
                'estado' => 'enviada',
                'observaciones' => $this->normalizeUpperNullable($validated['observaciones'] ?? null),
                'usuario_id' => Auth::id(),
            ]);

            foreach ($validated['detalles'] as $detalle) {
                $articuloId = isset($detalle['articulo_id']) && $detalle['articulo_id'] !== ''
                    ? (int) $detalle['articulo_id']
                    : null;

                $reparacion->detalles()->create([
                    'articulo_id' => $articuloId,
                    'descripcion_articulo_manual' => $articuloId ? null : trim((string) ($detalle['descripcion_articulo_manual'] ?? '')),
                    'codigo_articulo_manual' => $articuloId ? null : trim((string) ($detalle['codigo_articulo_manual'] ?? '')),
                    'cantidad_enviada' => (int) $detalle['cantidad_enviada'],
                    'cantidad_devuelta' => 0,
                    'costo_unitario' => $detalle['costo_unitario'] ?? null,
                    'estado' => 'enviada',
                    'observaciones' => $detalle['observaciones'] ?? null,
                ]);
            }

            $reparacion->refreshEstado();

            return $reparacion;
        });

        return redirect()
            ->route('admin.reparaciones-articulos.show', $reparacion->id)
            ->with('success', 'Orden de reparacion registrada correctamente.');
    }

    public function edit(string $id)
    {
        $reparacion = ReparacionArticulo::query()
            ->with([
                'detalles:id,reparacion_articulo_id,articulo_id,descripcion_articulo_manual,codigo_articulo_manual,cantidad_enviada,cantidad_devuelta,costo_unitario,observaciones',
                'detalles.articulo:id,nombre,codigo_producto',
                'provincia:id,nombre',
                'ciudad:id,nombre',
            ])
            ->findOrFail($id);

        return view('admin.reparaciones-articulos.edit', [
            'reparacion' => $reparacion,
            'proveedores' => Proveedor::query()
                ->with(['provincia:id,nombre', 'ciudad:id,nombre'])
                ->orderBy('nombre')
                ->get(),
            'articulos' => Articulo::query()
                ->select('id', 'nombre', 'codigo_producto', 'estado_item')
                ->where('estado_item', 'activo')
                ->orderBy('nombre')
                ->get(),
            'empresaRemite' => $this->empresaRemiteData(),
        ]);
    }

    public function update(Request $request, string $id)
    {
        $reparacion = ReparacionArticulo::query()->findOrFail($id);

        $validated = $request->validate([
            'proveedor_id' => ['required', 'exists:proveedores,id'],
            'provincia_id' => ['nullable', 'exists:provincias,id'],
            'ciudad_id' => ['nullable', 'exists:ciudades,id'],
            'domicilio' => ['nullable', 'string', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:50'],
            'codigo_postal' => ['nullable', 'string', 'max:20'],
            'fecha_envio' => ['required', 'date'],
            'fecha_compromiso' => ['nullable', 'date', 'after_or_equal:fecha_envio'],
            'observaciones' => ['nullable', 'string'],
            'detalles' => ['required', 'array', 'min:1'],
            'detalles.*.id' => ['nullable', 'integer'],
            'detalles.*.articulo_id' => ['nullable', 'exists:articulos,id'],
            'detalles.*.descripcion_articulo_manual' => ['nullable', 'string', 'max:255'],
            'detalles.*.codigo_articulo_manual' => ['nullable', 'string', 'max:120'],
            'detalles.*.cantidad_enviada' => ['required', 'integer', 'min:1'],
            'detalles.*.costo_unitario' => ['nullable', 'numeric', 'min:0'],
            'detalles.*.observaciones' => ['nullable', 'string'],
        ]);

        $validated = $this->normalizeStorePayload($validated);
        $empresaRemite = $this->empresaRemiteData();
        $empresaNombreEnvio = $empresaRemite['nombre'] ?? $this->empresaNombreEnvio();
        $detalles = collect($validated['detalles']);

        $detalles->each(function (array $detalle, int $index): void {
            $articuloId = isset($detalle['articulo_id']) ? (int) $detalle['articulo_id'] : 0;
            $manual = trim((string) ($detalle['descripcion_articulo_manual'] ?? ''));

            if ($articuloId <= 0 && $manual === '') {
                throw ValidationException::withMessages([
                    "detalles.{$index}.articulo_id" => 'Debe seleccionar un articulo o ingresar una descripcion manual.',
                ]);
            }
        });

        $articuloIds = $detalles
            ->pluck('articulo_id')
            ->filter(fn ($articuloId) => ! empty($articuloId))
            ->map(fn ($articuloId) => (int) $articuloId);

        if ($articuloIds->count() !== $articuloIds->unique()->count()) {
            throw ValidationException::withMessages([
                'detalles' => 'No se puede repetir el mismo articulo en la misma orden de reparacion.',
            ]);
        }

        DB::transaction(function () use ($reparacion, $validated, $empresaNombreEnvio, $empresaRemite, $detalles) {
            $proveedor = Proveedor::query()->findOrFail($validated['proveedor_id']);

            $reparacion->update([
                'proveedor_id' => (int) $validated['proveedor_id'],
                'provincia_id' => $validated['provincia_id'] ?? $proveedor->provincia_id,
                'ciudad_id' => $validated['ciudad_id'] ?? $proveedor->ciudades_id,
                'quien_envia_nombre' => $empresaNombreEnvio,
                'quien_envia_documento' => $empresaRemite['cuit'] ?? null,
                'quien_recibe_nombre' => $this->normalizeUpperNullable($proveedor->contacto ?: $proveedor->nombre),
                'quien_recibe_documento' => null,
                'domicilio' => $validated['domicilio'] ?? $this->normalizeUpperNullable($proveedor->direccion),
                'telefono' => $validated['telefono'] ?? $this->normalizeUpperNullable($proveedor->telefono),
                'codigo_postal' => $validated['codigo_postal'] ?? $this->normalizeUpperNullable($proveedor->codigo_postal),
                'fecha_envio' => $validated['fecha_envio'],
                'fecha_compromiso' => $validated['fecha_compromiso'] ?? null,
                'observaciones' => $validated['observaciones'] ?? null,
            ]);

            $existentes = $reparacion->detalles()->get()->keyBy('id');
            $idsRecibidos = [];

            foreach ($detalles as $index => $detalle) {
                $detalleId = isset($detalle['id']) && $detalle['id'] !== ''
                    ? (int) $detalle['id']
                    : null;

                $articuloId = isset($detalle['articulo_id']) && $detalle['articulo_id'] !== ''
                    ? (int) $detalle['articulo_id']
                    : null;

                $payloadDetalle = [
                    'articulo_id' => $articuloId,
                    'descripcion_articulo_manual' => $articuloId ? null : trim((string) ($detalle['descripcion_articulo_manual'] ?? '')),
                    'codigo_articulo_manual' => $articuloId ? null : trim((string) ($detalle['codigo_articulo_manual'] ?? '')),
                    'cantidad_enviada' => (int) $detalle['cantidad_enviada'],
                    'costo_unitario' => $detalle['costo_unitario'] ?? null,
                    'observaciones' => $detalle['observaciones'] ?? null,
                ];

                if ($detalleId) {
                    $linea = $existentes->get($detalleId);

                    if (! $linea) {
                        throw ValidationException::withMessages([
                            "detalles.{$index}.id" => 'La linea de detalle no pertenece a esta reparacion.',
                        ]);
                    }

                    $idsRecibidos[] = $linea->id;

                    if ((int) $payloadDetalle['cantidad_enviada'] < (int) $linea->cantidad_devuelta) {
                        throw ValidationException::withMessages([
                            "detalles.{$index}.cantidad_enviada" => 'La cantidad enviada no puede ser menor a la cantidad ya devuelta.',
                        ]);
                    }

                    if ((int) $linea->cantidad_devuelta > 0) {
                        $sameArticulo = (int) ($linea->articulo_id ?? 0) === (int) ($payloadDetalle['articulo_id'] ?? 0);
                        $sameDescripcion = trim((string) ($linea->descripcion_articulo_manual ?? '')) === trim((string) ($payloadDetalle['descripcion_articulo_manual'] ?? ''));
                        $sameCodigo = trim((string) ($linea->codigo_articulo_manual ?? '')) === trim((string) ($payloadDetalle['codigo_articulo_manual'] ?? ''));

                        if (! ($sameArticulo && $sameDescripcion && $sameCodigo)) {
                            throw ValidationException::withMessages([
                                "detalles.{$index}.articulo_id" => 'No se puede cambiar el articulo de una linea que ya tiene devoluciones.',
                            ]);
                        }
                    }

                    $linea->update($payloadDetalle);
                    continue;
                }

                $nuevaLinea = $reparacion->detalles()->create(array_merge($payloadDetalle, [
                    'cantidad_devuelta' => 0,
                    'estado' => 'enviada',
                ]));

                $idsRecibidos[] = $nuevaLinea->id;
            }

            $idsAEliminar = $existentes
                ->keys()
                ->filter(fn ($detalleIdExistente) => ! in_array((int) $detalleIdExistente, $idsRecibidos, true));

            $lineasConDevolucion = $existentes
                ->filter(fn (ReparacionArticuloDetalle $detalleExistente) => $idsAEliminar->contains($detalleExistente->id) && (int) $detalleExistente->cantidad_devuelta > 0)
                ->values();

            if ($lineasConDevolucion->isNotEmpty()) {
                throw ValidationException::withMessages([
                    'detalles' => 'No se puede eliminar una linea que ya tiene devoluciones registradas.',
                ]);
            }

            if ($idsAEliminar->isNotEmpty()) {
                ReparacionArticuloDetalle::query()
                    ->where('reparacion_articulo_id', $reparacion->id)
                    ->whereIn('id', $idsAEliminar->all())
                    ->delete();
            }

            $reparacion->refreshEstado();
        });

        return redirect()
            ->route('admin.reparaciones-articulos.show', $reparacion->id)
            ->with('success', 'Orden de reparacion actualizada correctamente.');
    }

    public function destroy(string $id)
    {
        $reparacion = ReparacionArticulo::query()->findOrFail($id);
        $reparacion->delete();

        return redirect()
            ->route('admin.reparaciones-articulos.index')
            ->with('success', 'Orden de reparacion eliminada correctamente.');
    }

    public function bulk(Request $request)
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:reparaciones_articulos,id'],
            'accion_masiva' => ['required', 'in:refrescar_estado,cancelar'],
            'observaciones' => ['nullable', 'string'],
        ]);

        $actualizadas = 0;

        ReparacionArticulo::query()
            ->whereIn('id', $validated['ids'])
            ->orderBy('id')
            ->get()
            ->each(function (ReparacionArticulo $reparacion) use ($validated, &$actualizadas) {
                if ($validated['accion_masiva'] === 'refrescar_estado') {
                    $reparacion->refreshEstado();
                    $actualizadas++;
                    return;
                }

                if ($validated['accion_masiva'] === 'cancelar' && ! in_array($reparacion->estado, ['completada', 'cancelada'], true)) {
                    $observaciones = trim((string) ($reparacion->observaciones ?? ''));
                    $nota = trim((string) ($validated['observaciones'] ?? ''));

                    $reparacion->forceFill([
                        'estado' => 'cancelada',
                        'observaciones' => trim($observaciones . ($nota !== '' ? "\nCancelacion masiva: {$nota}" : '')),
                    ])->save();
                    $actualizadas++;
                }
            });

        return back()->with('success', "Accion masiva aplicada a {$actualizadas} reparacion(es).");
    }

    public function show(string $id)
    {
        $reparacion = ReparacionArticulo::query()
            ->with([
                'proveedor:id,nombre,contacto,email,telefono',
                'provincia:id,nombre',
                'ciudad:id,nombre',
                'usuario:id,name',
                'detalles:id,reparacion_articulo_id,articulo_id,descripcion_articulo_manual,codigo_articulo_manual,cantidad_enviada,cantidad_devuelta,costo_unitario,estado,fecha_ultima_devolucion,observaciones,created_at',
                'detalles.articulo:id,nombre,codigo_producto',
                'reclamos:id,reparacion_articulo_id,reparacion_articulo_detalle_id,fecha_reclamo,medio,numero_referencia,observaciones,respuesta_proveedor,usuario_id,created_at',
                'reclamos.detalle:id,reparacion_articulo_id,articulo_id,descripcion_articulo_manual,codigo_articulo_manual',
                'reclamos.detalle.articulo:id,nombre,codigo_producto',
                'reclamos.usuario:id,name',
            ])
            ->findOrFail($id);

        $empresaRemite = $this->empresaRemiteData();
        $empresaNombreEnvio = $this->empresaNombreEnvio();
        $documentos = DocumentoOperativo::query()
            ->with('usuario:id,name')
            ->where('documentable_type', ReparacionArticulo::class)
            ->where('documentable_id', $reparacion->id)
            ->latest()
            ->get();

        return view('admin.reparaciones-articulos.show', compact('reparacion', 'empresaNombreEnvio', 'empresaRemite', 'documentos'));
    }

    public function planilla(string $id)
    {
        return redirect()->route('admin.reparaciones-articulos.show', $id);
    }

    public function devolver(Request $request, string $id, string $detalleId)
    {
        $reparacion = ReparacionArticulo::query()->findOrFail($id);
        $detalle = ReparacionArticuloDetalle::query()
            ->where('reparacion_articulo_id', $reparacion->id)
            ->findOrFail($detalleId);

        $fechaEnvio = $reparacion->fecha_envio
            ? date('Y-m-d', strtotime((string) $reparacion->fecha_envio))
            : now()->toDateString();

        $validated = $request->validate([
            'cantidad_devuelta' => ['required', 'integer', 'min:1'],
            'fecha_devolucion' => ['required', 'date', 'after_or_equal:' . $fechaEnvio],
            'costo_unitario' => ['nullable', 'numeric', 'min:0'],
            'observaciones' => ['nullable', 'string'],
        ]);

        if ((int) $validated['cantidad_devuelta'] > $detalle->cantidadPendiente()) {
            return back()->with('error', 'La cantidad devuelta supera lo pendiente de ese articulo.');
        }

        DB::transaction(function () use ($reparacion, $detalle, $validated) {
            $detalle->update([
                'cantidad_devuelta' => (int) $detalle->cantidad_devuelta + (int) $validated['cantidad_devuelta'],
                'fecha_ultima_devolucion' => $validated['fecha_devolucion'],
                'costo_unitario' => $validated['costo_unitario'] ?? $detalle->costo_unitario,
                'observaciones' => $this->normalizeUpperNullable($validated['observaciones'] ?? $detalle->observaciones),
            ]);

            $reparacion->refreshEstado();
        });

        return redirect()
            ->route('admin.reparaciones-articulos.show', $reparacion->id)
            ->with('success', 'Devolucion registrada correctamente.');
    }

    public function storeReclamo(Request $request, string $id)
    {
        $reparacion = ReparacionArticulo::query()->with('detalles')->findOrFail($id);

        $validated = $request->validate([
            'reparacion_articulo_detalle_id' => ['nullable', 'integer'],
            'fecha_reclamo' => ['required', 'date'],
            'medio' => ['required', 'in:telefono,email,whatsapp,presencial,otro'],
            'numero_referencia' => ['nullable', 'string', 'max:120'],
            'observaciones' => ['nullable', 'string'],
            'respuesta_proveedor' => ['nullable', 'string'],
        ]);

        $detalle = null;
        if (! empty($validated['reparacion_articulo_detalle_id'])) {
            $detalle = $reparacion->detalles
                ->firstWhere('id', (int) $validated['reparacion_articulo_detalle_id']);

            if (! $detalle) {
                throw ValidationException::withMessages([
                    'reparacion_articulo_detalle_id' => 'El detalle seleccionado no pertenece a esta orden.',
                ]);
            }
        }

        if ($detalle instanceof ReparacionArticuloDetalle && $detalle->cantidadPendiente() <= 0) {
            throw ValidationException::withMessages([
                'reparacion_articulo_detalle_id' => 'No se puede reclamar una linea que ya fue devuelta en su totalidad.',
            ]);
        }

        $reparacion->reclamos()->create([
            'reparacion_articulo_detalle_id' => $detalle?->id,
            'fecha_reclamo' => $validated['fecha_reclamo'],
            'medio' => $validated['medio'],
            'numero_referencia' => $this->normalizeUpperNullable($validated['numero_referencia'] ?? null),
            'observaciones' => $this->normalizeUpperNullable($validated['observaciones'] ?? null),
            'respuesta_proveedor' => $this->normalizeUpperNullable($validated['respuesta_proveedor'] ?? null),
            'usuario_id' => Auth::id(),
        ]);

        return redirect()
            ->route('admin.reparaciones-articulos.show', $reparacion->id)
            ->with('success', 'Reclamo registrado correctamente.');
    }

    private function nextNumeroOrden(): string
    {
        $prefix = 'REP-' . now()->format('Ymd') . '-';

        $lastTodayId = ReparacionArticulo::query()
            ->whereDate('created_at', now()->toDateString())
            ->max('id');

        $next = ((int) $lastTodayId) + 1;

        return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    private function normalizeStorePayload(array $validated): array
    {
        $validated['domicilio'] = $this->normalizeUpperNullable($validated['domicilio'] ?? null);
        $validated['telefono'] = $this->normalizeUpperNullable($validated['telefono'] ?? null);
        $validated['codigo_postal'] = $this->normalizeUpperNullable($validated['codigo_postal'] ?? null);
        $validated['observaciones'] = $this->normalizeUpperNullable($validated['observaciones'] ?? null);

        if (isset($validated['detalles']) && is_array($validated['detalles'])) {
            $validated['detalles'] = collect($validated['detalles'])
                ->map(function ($detalle) {
                    if (! is_array($detalle)) {
                        return $detalle;
                    }

                    $detalle['descripcion_articulo_manual'] = $this->normalizeUpperNullable($detalle['descripcion_articulo_manual'] ?? null);
                    $detalle['codigo_articulo_manual'] = $this->normalizeUpperNullable($detalle['codigo_articulo_manual'] ?? null);
                    $detalle['observaciones'] = $this->normalizeUpperNullable($detalle['observaciones'] ?? null);

                    return $detalle;
                })
                ->all();
        }

        return $validated;
    }

    private function normalizeUpperNullable(?string $value): ?string
    {
        $normalized = trim((string) $value);

        if ($normalized === '') {
            return null;
        }

        return mb_strtoupper($normalized, 'UTF-8');
    }

    private function empresaNombreEnvio(): ?string
    {
        return $this->normalizeUpperNullable(Ajuste::query()->value('nombre'));
    }

    private function empresaRemiteData(): array
    {
        $ajuste = Ajuste::query()->first(['nombre', 'cuit', 'direccion', 'codigo_postal', 'telefono']);

        return [
            'nombre' => $this->normalizeUpperNullable($ajuste?->nombre),
            'cuit' => $this->normalizeUpperNullable($ajuste?->cuit),
            'direccion' => $this->normalizeUpperNullable($ajuste?->direccion),
            'codigo_postal' => $this->normalizeUpperNullable($ajuste?->codigo_postal),
            'telefono' => $this->normalizeUpperNullable($ajuste?->telefono),
        ];
    }
}
