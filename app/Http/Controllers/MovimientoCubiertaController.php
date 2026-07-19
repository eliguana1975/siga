<?php

namespace App\Http\Controllers;

use App\Models\Articulo;
use App\Models\CambioCubierta;
use App\Models\Cubierta;
use App\Models\DetalleCambioCubierta;
use App\Models\Empleado;
use App\Models\Flota;
use App\Models\Inventario;
use App\Models\OrdenTrabajo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class MovimientoCubiertaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('check.permission:administrar-ordenes-trabajo');
    }

    public function create(Request $request)
    {
        $cubiertasDisponibles = Cubierta::query()
            ->with('articulo:id,nombre,codigo_producto')
            ->whereNotNull('articulo_id')
            ->whereIn('estado', ['nueva', 'reutilizable'])
            ->orderBy('numero')
            ->get();

        // Prioriza articulos con stock real de cubiertas disponibles.
        // Si no hubiera registros, usa el catalogo por categoria como respaldo.
        $articulosCubiertas = $cubiertasDisponibles
            ->pluck('articulo')
            ->filter()
            ->unique('id')
            ->sortBy('nombre')
            ->values();

        if ($articulosCubiertas->isEmpty()) {
            $articulosCubiertas = Articulo::query()
                ->with('categoria')
                ->whereHas('categoria', fn ($query) => $query->where('nombre', 'like', '%cubierta%'))
                ->orderBy('nombre')
                ->get();
        }

        $flotas = Flota::query()
            ->with('cubiertaEjes.articuloCubierta')
            ->select('id', 'nro_interno', 'med_cub_delanteras', 'med_cub_traseras')
            ->orderBy('nro_interno')
            ->get();

        $flotaMedidasPorId = $flotas
            ->mapWithKeys(fn (Flota $flota) => [
                (string) $flota->id => [
                    'delanteras' => trim((string) ($flota->med_cub_delanteras ?? '')),
                    'traseras' => trim((string) ($flota->med_cub_traseras ?? '')),
                ],
            ])
            ->all();

        $empleados = Empleado::query()
            ->where('estado', 'activo')
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->get();

        $ordenTrabajo = null;
        $cambio = null;

        if ($request->filled('orden_trabajo_id')) {
            $ordenTrabajo = OrdenTrabajo::with(['empleado', 'reparador', 'flota.cubiertaEjes.articuloCubierta'])
                ->find($request->integer('orden_trabajo_id'));

            if ($ordenTrabajo) {
                $cambio = CambioCubierta::with('detalles.articuloColocado')
                    ->where('orden_trabajo_id', $ordenTrabajo->id)
                    ->latest('id')
                    ->first();
            }
        }

        $flotaSeleccionada = $request->filled('flota_id')
            ? $flotas->firstWhere('id', $request->integer('flota_id'))
            : $ordenTrabajo?->flota;
        $cubiertaLayout = $flotaSeleccionada?->cubiertaLayout() ?? (new Flota())->cubiertaLayout();
        $posicionesCubiertas = collect($cubiertaLayout)
            ->flatMap(fn (array $eje) => array_merge($eje['posiciones_izquierda'], $eje['posiciones_derecha']))
            ->values()
            ->all();

        if ($cambio) {
            $codigosActuales = collect($posicionesCubiertas)->pluck('codigo')->all();
            $posicionesHistoricas = $cambio->detalles
                ->pluck('posicion')
                ->filter(fn ($posicion) => ! in_array($posicion, $codigosActuales, true))
                ->unique()
                ->map(fn ($posicion) => [
                    'codigo' => $posicion,
                    'etiqueta' => "{$posicion} (anterior)",
                    'lado' => null,
                    'orden' => 999,
                    'numero_eje' => null,
                ])
                ->values()
                ->all();

            $posicionesCubiertas = array_merge($posicionesCubiertas, $posicionesHistoricas);
        }
        $flotaCubiertaLayouts = $flotas
            ->mapWithKeys(fn (Flota $flota) => [(string) $flota->id => $flota->cubiertaLayout()])
            ->all();

        $cubiertasDisponiblesPorArticulo = $cubiertasDisponibles
            ->groupBy('articulo_id')
            ->map(fn ($cubiertas, $articuloId) => $cubiertas->map(fn (Cubierta $cubierta) => [
                'id' => $cubierta->id,
                'articulo_id' => (int) $articuloId,
                'numero' => $this->numeroCubiertaVisible($cubierta),
                'label' => trim((string) ($cubierta->articulo?->nombre ?? 'Articulo'))
                    . ' - Nro ' . $this->numeroCubiertaVisible($cubierta)
                    . ' (' . $cubierta->estado . ')',
            ])->values())
            ->all();
        $cubiertasEnUsoPorFlotaPosicion = Cubierta::query()
            ->whereNotNull('flota_id')
            ->where('estado', 'en_uso')
            ->get()
            ->groupBy('flota_id')
            ->map(fn ($cubiertas) => $cubiertas->keyBy('posicion')->map(fn (Cubierta $cubierta) => [
                'id' => $cubierta->id,
                'numero' => $this->numeroCubiertaVisible($cubierta),
                'articulo_id' => $cubierta->articulo_id,
                'posicion' => $cubierta->posicion,
            ])->all())
            ->all();
        $cubiertasEnUsoPorPosicion = collect($flotaSeleccionada ? ($cubiertasEnUsoPorFlotaPosicion[$flotaSeleccionada->id] ?? []) : []);

        return view('admin.movimiento-cubiertas.index', compact('articulosCubiertas', 'flotas', 'empleados', 'ordenTrabajo', 'cambio', 'cubiertaLayout', 'posicionesCubiertas', 'flotaCubiertaLayouts', 'cubiertasDisponiblesPorArticulo', 'cubiertasEnUsoPorPosicion', 'cubiertasEnUsoPorFlotaPosicion', 'flotaMedidasPorId'));
    }

    public function store(Request $request)
    {
        $cambioExistente = null;

        if ($request->filled('orden_trabajo_id')) {
            $cambioExistente = CambioCubierta::where('orden_trabajo_id', $request->integer('orden_trabajo_id'))
                ->latest('id')
                ->first();
        }

        // Validar nro_colocada como string y distinto dentro del arreglo.
        // La unicidad por cambio se aplica en la base de datos mediante
        // una clave única compuesta (`cambio_cubierta_id`, `nro_cubierta_colocada`).
        $nroColocadaRule = ['nullable', 'string', 'max:80', 'distinct'];

        $validator = Validator::make($request->all(), [
            'orden_trabajo_id' => ['required', 'integer', 'exists:ordenes_trabajo,id'],
            'fecha' => ['required', 'date'],
            'flota_id' => ['required', 'integer', 'exists:flota,id'],
            'km' => ['nullable', 'integer', 'min:0'],
            'operario_empleado_id' => ['required', 'integer', 'exists:empleados,id'],
            'observaciones' => ['nullable', 'string'],
            'sacada' => ['array'],
            'sacada.*' => ['nullable', 'string', 'max:80'],
            'estado_sacada' => ['array'],
            'estado_sacada.*' => ['nullable', Rule::in(array_keys(DetalleCambioCubierta::ESTADOS_CUBIERTA_SACADA))],
            'destino_sacada' => ['array'],
            'destino_sacada.*' => ['nullable', Rule::in(array_keys(DetalleCambioCubierta::DESTINOS_CUBIERTA_SACADA))],
            'motivo_baja_sacada' => ['array'],
            'motivo_baja_sacada.*' => ['nullable', 'string', 'max:180'],
            'observacion_sacada' => ['array'],
            'observacion_sacada.*' => ['nullable', 'string'],
            'colocada' => ['array'],
            'colocada.*' => ['nullable', 'integer', 'exists:articulos,id'],
            'cubierta_colocada_id' => ['array'],
            'cubierta_colocada_id.*' => ['nullable', 'integer', 'exists:cubiertas,id', 'distinct'],
            'nro_colocada' => ['array'],
            'nro_colocada.*' => $nroColocadaRule,
        ]);

        $validator->after(function ($validator) use ($request) {
            $posicionesPorCodigo = collect(Flota::with('cubiertaEjes')->find((int) $request->input('flota_id'))?->cubiertaPosiciones() ?? [])
                ->keyBy('codigo');

            foreach ($this->posicionesRequest((int) $request->input('flota_id'), $request) as $posicion) {
                $cubiertaId = $request->input("cubierta_colocada_id.{$posicion}");

                if (! $cubiertaId) {
                    continue;
                }

                $cubierta = Cubierta::find($cubiertaId);

                if (! $cubierta || ! in_array($cubierta->estado, ['nueva', 'reutilizable'], true)) {
                    $validator->errors()->add("cubierta_colocada_id.{$posicion}", "La cubierta seleccionada en {$posicion} no esta disponible.");
                    continue;
                }

                $articuloEsperado = $posicionesPorCodigo->get($posicion)['articulo_cubierta_id'] ?? null;

                if ($articuloEsperado && (int) $cubierta->articulo_id !== (int) $articuloEsperado) {
                    $validator->errors()->add("cubierta_colocada_id.{$posicion}", "La cubierta seleccionada en {$posicion} no corresponde a la medida configurada para ese eje.");
                }
            }
        });

        if ($validator->fails()) {
            return redirect()
                ->route('admin.movimiento-cubiertas.index', ['orden_trabajo_id' => $request->input('orden_trabajo_id')])
                ->withErrors($validator)
                ->withInput();
        }

        $data = $validator->validated();
        $orden = OrdenTrabajo::with('base')->findOrFail($data['orden_trabajo_id']);

        if ((int) $orden->flota_id !== (int) $data['flota_id']) {
            return redirect()
                ->route('admin.movimiento-cubiertas.index', ['orden_trabajo_id' => $orden->id])
                ->withInput()
                ->with('error', 'El interno seleccionado no coincide con la orden de trabajo.');
        }

        // Ya no requerimos que la orden tenga un depósito asociado: buscarmos inventario
        // en cualquier depósito que tenga stock disponible.

        $posiciones = $this->posicionesRequest((int) $data['flota_id'], $request);
        $articulosPorPosicion = $this->articulosPorPosicion((int) $data['flota_id']);

        $cubiertasColocadas = Cubierta::query()
            ->whereIn('id', collect($data['cubierta_colocada_id'] ?? [])->filter()->values())
            ->get()
            ->keyBy('id');
        $colocadas = collect($data['cubierta_colocada_id'] ?? [])
            ->filter(fn ($cubiertaId, $posicion) => in_array($posicion, $posiciones, true) && filled($cubiertaId))
            ->filter(fn ($cubiertaId) => optional($cubiertasColocadas->get((int) $cubiertaId))->estado === 'nueva')
            ->map(fn ($cubiertaId) => optional($cubiertasColocadas->get((int) $cubiertaId))->articulo_id)
            ->filter();

        $stockError = null;

        try {
            DB::transaction(function () use ($data, $orden, $colocadas, $posiciones, $articulosPorPosicion, &$stockError) {
                $inventarios = [];

                foreach ($colocadas->groupBy(fn ($articuloId) => (int) $articuloId) as $articuloId => $posicionesArticulo) {
                    $inventario = $this->inventarioDisponible($orden, (int) $articuloId);
                    $requerido = $posicionesArticulo->count();

                    $articulo = Articulo::select('id', 'nombre', 'codigo_producto')->find($articuloId);
                    $articuloLabel = $articulo
                        ? ($articulo->nombre . ($articulo->codigo_producto ? ' (' . $articulo->codigo_producto . ')' : ''))
                        : "ID {$articuloId}";

                    if (! $inventario || (int) $inventario->cantidad < $requerido) {
                        $disponible = (int) ($inventario?->cantidad ?? 0);

                        $otherInventarios = Inventario::query()
                            ->where('articulo_id', (int) $articuloId)
                            ->get()
                            ->map(fn ($i) => [
                                'deposito_id' => $i->deposito_id,
                                'cantidad' => $i->cantidad,
                            ])->values()->all();


                        $depositoBuscado = $orden->base?->deposito_id ?? 'N/A';
                        $stockError = "Stock insuficiente para {$articuloLabel}. Disponible en deposito {$depositoBuscado}: {$disponible}. Requerido: {$requerido} cubierta(s). El Nro control es solo identificación/control, no cantidad. Inventarios encontrados: " . json_encode($otherInventarios);

                        Log::warning('Stock insuficiente en MovimientoCubiertaController', [
                            'articulo_id' => $articuloId,
                            'articulo' => $articuloLabel,
                            'deposito_busqueda' => $depositoBuscado,
                            'inventario_encontrado' => $inventario ? $inventario->toArray() : null,
                            'otros_inventarios' => $otherInventarios,
                            'requerido' => $requerido,
                        ]);

                        throw new \RuntimeException($stockError);
                    }

                    $inventarios[(int) $articuloId] = $inventario;
                }

                $cambio = CambioCubierta::create([
                    'orden_trabajo_id' => $orden->id,
                    'flota_id' => $orden->flota_id,
                    'empleado_id' => $data['operario_empleado_id'],
                    'user_id' => Auth::id(),
                    'fecha' => $data['fecha'],
                    'kilometraje' => $data['km'] ?? null,
                    'observaciones' => $data['observaciones'] ?? null,
                ]);

                foreach ($posiciones as $posicion) {
                    $cubiertaColocadaId = $data['cubierta_colocada_id'][$posicion] ?? null;
                    $cubiertaColocada = $cubiertaColocadaId ? Cubierta::lockForUpdate()->find($cubiertaColocadaId) : null;
                    $articuloId = $cubiertaColocada?->articulo_id;
                    $articuloSacadaId = $articuloId ?: ($articulosPorPosicion[$posicion] ?? null);
                    $nroSacada = trim((string) ($data['sacada'][$posicion] ?? ''));
                    $estadoSacada = $data['estado_sacada'][$posicion] ?? null;
                    $destinoSacada = $data['destino_sacada'][$posicion] ?? null;
                    $cubiertaSacada = $nroSacada !== ''
                        ? $this->buscarOCrearCubiertaSacada($nroSacada, $articuloSacadaId, $estadoSacada, $destinoSacada, true)
                        : null;
                    $motivoBajaSacada = trim((string) ($data['motivo_baja_sacada'][$posicion] ?? ''));
                    $observacionSacada = trim((string) ($data['observacion_sacada'][$posicion] ?? ''));
                    $nroColocada = $cubiertaColocada ? $this->numeroCubiertaVisible($cubiertaColocada) : trim((string) ($data['nro_colocada'][$posicion] ?? ''));
                    $detalleOrden = null;
                    $valorUnitario = 0;

                    if ($articuloId) {
                        $cubiertaNueva = $cubiertaColocada?->estado === 'nueva';
                        $inventario = $cubiertaNueva ? ($inventarios[(int) $articuloId] ?? null) : null;
                        $valorUnitario = $cubiertaNueva ? (float) ($inventario?->precio_compra_unidad ?? 0) : 0;
                        $detalleOrden = $orden->articulosUsados()->create([
                            'articulo_id' => $articuloId,
                            'cantidad' => 1,
                            'valor_unitario' => $valorUnitario,
                            'inventario_descontado' => $cubiertaNueva,
                            'observaciones' => $this->observacionDetalle($posicion, $nroSacada, $nroColocada),
                        ]);

                        if ($cubiertaNueva && $inventario) {
                            $inventario->cantidad = (int) $inventario->cantidad - 1;
                            $inventario->save();
                        }

                        $cubiertaColocada?->update([
                            'estado' => 'en_uso',
                            'flota_id' => $orden->flota_id,
                            'posicion' => $posicion,
                            'deposito_id' => null,
                        ]);
                    }

                    $this->actualizarCubiertaSacada($cubiertaSacada, $estadoSacada, $destinoSacada);

                    if ($articuloId || $nroSacada !== '' || $nroColocada !== '') {
                        $cambio->detalles()->create([
                            'articulo_colocado_id' => $articuloId,
                            'cubierta_colocada_id' => $cubiertaColocada?->id,
                            'cubierta_sacada_id' => $cubiertaSacada?->id,
                            'orden_trabajo_articulo_id' => $detalleOrden?->id,
                            'posicion' => $posicion,
                            'nro_cubierta_sacada' => $nroSacada !== '' ? $nroSacada : null,
                            'estado_cubierta_sacada' => $nroSacada !== '' ? $estadoSacada : null,
                            'destino_cubierta_sacada' => $nroSacada !== '' ? $destinoSacada : null,
                            'motivo_baja_cubierta_sacada' => $motivoBajaSacada !== '' ? $motivoBajaSacada : null,
                            'observacion_cubierta_sacada' => $observacionSacada !== '' ? $observacionSacada : null,
                            'nro_cubierta_colocada' => $nroColocada !== '' ? $nroColocada : null,
                            'valor_unitario' => $valorUnitario,
                        ]);
                    }
                }

                $this->marcarOrdenActualizada($orden);
            });
        } catch (\RuntimeException $exception) {
            if ($stockError === null) {
                throw $exception;
            }
        }

        if ($stockError !== null) {
            return redirect()
                ->route('admin.movimiento-cubiertas.index', ['orden_trabajo_id' => $orden->id])
                ->withInput()
                ->with('error', $stockError);
        }

        return redirect()
            ->route('admin.movimiento-cubiertas.index', ['orden_trabajo_id' => $orden->id])
            ->with('success', 'Cambio de cubiertas registrado correctamente. El stock se descontó solo para las cubiertas seleccionadas.');
    }

    public function update(Request $request, $id)
    {
        $nroColocadaRule = ['nullable', 'string', 'max:80', 'distinct'];
        $cubiertasActualesCambio = CambioCubierta::query()
            ->with('detalles:id,cambio_cubierta_id,cubierta_colocada_id')
            ->find($id)
            ?->detalles
            ->pluck('cubierta_colocada_id')
            ->filter()
            ->map(fn ($cubiertaId) => (int) $cubiertaId)
            ->all() ?? [];

        $validator = Validator::make($request->all(), [
            'orden_trabajo_id' => ['required', 'integer', 'exists:ordenes_trabajo,id'],
            'fecha' => ['required', 'date'],
            'flota_id' => ['required', 'integer', 'exists:flota,id'],
            'km' => ['nullable', 'integer', 'min:0'],
            'operario_empleado_id' => ['required', 'integer', 'exists:empleados,id'],
            'observaciones' => ['nullable', 'string'],
            'sacada' => ['array'],
            'sacada.*' => ['nullable', 'string', 'max:80'],
            'estado_sacada' => ['array'],
            'estado_sacada.*' => ['nullable', Rule::in(array_keys(DetalleCambioCubierta::ESTADOS_CUBIERTA_SACADA))],
            'destino_sacada' => ['array'],
            'destino_sacada.*' => ['nullable', Rule::in(array_keys(DetalleCambioCubierta::DESTINOS_CUBIERTA_SACADA))],
            'motivo_baja_sacada' => ['array'],
            'motivo_baja_sacada.*' => ['nullable', 'string', 'max:180'],
            'observacion_sacada' => ['array'],
            'observacion_sacada.*' => ['nullable', 'string'],
            'colocada' => ['array'],
            'colocada.*' => ['nullable', 'integer', 'exists:articulos,id'],
            'cubierta_colocada_id' => ['array'],
            'cubierta_colocada_id.*' => ['nullable', 'integer', 'exists:cubiertas,id', 'distinct'],
            'nro_colocada' => ['array'],
            'nro_colocada.*' => $nroColocadaRule,
        ]);

        $validator->after(function ($validator) use ($request, $cubiertasActualesCambio) {
            $posicionesPorCodigo = collect(Flota::with('cubiertaEjes')->find((int) $request->input('flota_id'))?->cubiertaPosiciones() ?? [])
                ->keyBy('codigo');

            foreach ($this->posicionesRequest((int) $request->input('flota_id'), $request) as $posicion) {
                $cubiertaId = $request->input("cubierta_colocada_id.{$posicion}");

                if (! $cubiertaId) {
                    continue;
                }

                $cubierta = Cubierta::find($cubiertaId);
                $articuloEsperado = $posicionesPorCodigo->get($posicion)['articulo_cubierta_id'] ?? null;

                if ($cubierta && ! in_array((int) $cubierta->id, $cubiertasActualesCambio, true) && ! in_array($cubierta->estado, ['nueva', 'reutilizable'], true)) {
                    $validator->errors()->add("cubierta_colocada_id.{$posicion}", "La cubierta seleccionada en {$posicion} no esta disponible.");
                }

                if ($cubierta && $articuloEsperado && (int) $cubierta->articulo_id !== (int) $articuloEsperado) {
                    $validator->errors()->add("cubierta_colocada_id.{$posicion}", "La cubierta seleccionada en {$posicion} no corresponde a la medida configurada para ese eje.");
                }
            }
        });

        if ($validator->fails()) {
            return redirect()
                ->route('admin.movimiento-cubiertas.index', ['orden_trabajo_id' => $request->input('orden_trabajo_id')])
                ->withErrors($validator)
                ->withInput();
        }

        $data = $validator->validated();

        $cambio = CambioCubierta::findOrFail($id);
        $orden = OrdenTrabajo::findOrFail($data['orden_trabajo_id']);
        $posiciones = $this->posicionesRequest((int) $data['flota_id'], $request);
        $articulosPorPosicion = $this->articulosPorPosicion((int) $data['flota_id']);

        if ((int) $orden->flota_id !== (int) $data['flota_id']) {
            return redirect()
                ->route('admin.movimiento-cubiertas.index', ['orden_trabajo_id' => $orden->id, 'edit' => 1])
                ->withInput()
                ->with('error', 'El interno seleccionado no coincide con la orden de trabajo.');
        }

        // No ajustamos stock en edición; solo actualizamos cabecera y detalles.
        DB::transaction(function () use ($cambio, $orden, $data, $posiciones, $articulosPorPosicion) {
            $detallesPrevios = $cambio->detalles()->get();
            $cubiertasPrevias = $detallesPrevios
                ->pluck('cubierta_colocada_id')
                ->filter()
                ->map(fn ($cubiertaId) => (int) $cubiertaId)
                ->values();
            $cubiertasNuevas = collect($data['cubierta_colocada_id'] ?? [])
                ->filter()
                ->map(fn ($cubiertaId) => (int) $cubiertaId)
                ->values();
            $cubiertasAgregadas = $cubiertasNuevas->diff($cubiertasPrevias)->values();
            $cubiertasPreviasSinUso = Cubierta::query()
                ->whereIn('id', $cubiertasPrevias)
                ->where(function ($query) use ($orden) {
                    $query->where('estado', '!=', 'en_uso')
                        ->orWhere('flota_id', '!=', $orden->flota_id)
                        ->orWhereNull('flota_id')
                        ->orWhereNull('posicion');
                })
                ->pluck('id')
                ->map(fn ($cubiertaId) => (int) $cubiertaId);
            $cubiertasAgregadas = $cubiertasAgregadas
                ->merge($cubiertasPreviasSinUso)
                ->unique()
                ->values();
            $cubiertasQuitadas = $cubiertasPrevias->diff($cubiertasNuevas)->values();

            if ($cubiertasQuitadas->isNotEmpty()) {
                Cubierta::query()
                    ->whereIn('id', $cubiertasQuitadas)
                    ->get()
                    ->each(function (Cubierta $cubierta) {
                        $estabaEnUso = $cubierta->estado === 'en_uso' || $cubierta->flota_id !== null || $cubierta->posicion !== null;

                        $cubierta->update([
                            'estado' => 'reutilizable',
                            'flota_id' => null,
                            'posicion' => null,
                        ]);

                        if ($estabaEnUso && $cubierta->inventario_id) {
                            Inventario::whereKey($cubierta->inventario_id)->increment('cantidad');
                        }
                    });
            }

            $cambio->update([
                'flota_id' => $data['flota_id'],
                'empleado_id' => $data['operario_empleado_id'],
                'user_id' => Auth::id(),
                'fecha' => $data['fecha'],
                'kilometraje' => $data['km'] ?? null,
                'observaciones' => $data['observaciones'] ?? null,
            ]);

            // Reemplazar detalles: eliminamos y volvemos a crear según el formulario.
            $articuloIdsDetalle = $cambio->detalles()
                ->whereNotNull('orden_trabajo_articulo_id')
                ->pluck('orden_trabajo_articulo_id');

            if ($articuloIdsDetalle->isNotEmpty()) {
                $orden->articulosUsados()
                    ->whereIn('id', $articuloIdsDetalle)
                    ->delete();
            }

            $orden->articulosUsados()
                ->where('observaciones', 'like', 'Cambio cubierta %')
                ->delete();

            $cambio->detalles()->delete();

            foreach ($posiciones as $posicion) {
                $cubiertaColocadaId = $data['cubierta_colocada_id'][$posicion] ?? null;
                $cubiertaColocada = $cubiertaColocadaId ? Cubierta::find($cubiertaColocadaId) : null;
                $articuloId = $cubiertaColocada?->articulo_id ?: ($data['colocada'][$posicion] ?? null);
                $articuloSacadaId = $articuloId ?: ($articulosPorPosicion[$posicion] ?? null);
                $inventarioMovimiento = null;
                $cubiertaDebeDescontar = $cubiertaColocada
                    && $cubiertasAgregadas->contains((int) $cubiertaColocada->id)
                    && $cubiertaColocada->estado === 'nueva';

                if ($cubiertaColocada && $cubiertasAgregadas->contains((int) $cubiertaColocada->id)) {
                    if ($cubiertaDebeDescontar) {
                        $inventarioMovimiento = $cubiertaColocada->inventario_id
                            ? Inventario::lockForUpdate()->find($cubiertaColocada->inventario_id)
                            : $this->inventarioDisponible($orden, (int) $cubiertaColocada->articulo_id);

                        if ($inventarioMovimiento && (int) $inventarioMovimiento->cantidad > 0) {
                            $inventarioMovimiento->cantidad = (int) $inventarioMovimiento->cantidad - 1;
                            $inventarioMovimiento->save();
                        }
                    }

                    $cubiertaColocada->update([
                        'estado' => 'en_uso',
                        'flota_id' => $orden->flota_id,
                        'posicion' => $posicion,
                        'deposito_id' => null,
                    ]);
                } elseif ($cubiertaColocada) {
                    $cubiertaColocada->update([
                        'flota_id' => $orden->flota_id,
                        'posicion' => $posicion,
                        'deposito_id' => null,
                    ]);
                }

                $nroSacada = trim((string) ($data['sacada'][$posicion] ?? ''));
                $estadoSacada = $data['estado_sacada'][$posicion] ?? null;
                $destinoSacada = $data['destino_sacada'][$posicion] ?? null;
                $cubiertaSacada = $nroSacada !== ''
                    ? $this->buscarOCrearCubiertaSacada($nroSacada, $articuloSacadaId, $estadoSacada, $destinoSacada)
                    : null;
                $motivoBajaSacada = trim((string) ($data['motivo_baja_sacada'][$posicion] ?? ''));
                $observacionSacada = trim((string) ($data['observacion_sacada'][$posicion] ?? ''));
                $nroColocada = $cubiertaColocada ? $this->numeroCubiertaVisible($cubiertaColocada) : trim((string) ($data['nro_colocada'][$posicion] ?? ''));
                $valorUnitario = 0;
                $detalleOrden = null;

                if ($articuloId) {
                    $valorUnitario = $cubiertaDebeDescontar ? (float) ($inventarioMovimiento?->precio_compra_unidad ?? Inventario::query()
                        ->where('articulo_id', (int) $articuloId)
                        ->orderByDesc('precio_compra_unidad')
                        ->value('precio_compra_unidad') ?? 0) : 0;

                    $detalleOrden = $orden->articulosUsados()->create([
                        'articulo_id' => $articuloId,
                        'cantidad' => 1,
                        'valor_unitario' => $valorUnitario,
                        'inventario_descontado' => $cubiertaDebeDescontar,
                        'observaciones' => $this->observacionDetalle($posicion, $nroSacada, $nroColocada),
                    ]);
                }

                $this->actualizarCubiertaSacada($cubiertaSacada, $estadoSacada, $destinoSacada);

                if ($articuloId || $nroSacada !== '' || $nroColocada !== '') {
                    $cambio->detalles()->create([
                        'articulo_colocado_id' => $articuloId,
                        'cubierta_colocada_id' => $cubiertaColocada?->id,
                        'cubierta_sacada_id' => $cubiertaSacada?->id,
                        'orden_trabajo_articulo_id' => $detalleOrden?->id,
                        'posicion' => $posicion,
                        'nro_cubierta_sacada' => $nroSacada !== '' ? $nroSacada : null,
                        'estado_cubierta_sacada' => $nroSacada !== '' ? $estadoSacada : null,
                        'destino_cubierta_sacada' => $nroSacada !== '' ? $destinoSacada : null,
                        'motivo_baja_cubierta_sacada' => $motivoBajaSacada !== '' ? $motivoBajaSacada : null,
                        'observacion_cubierta_sacada' => $observacionSacada !== '' ? $observacionSacada : null,
                        'nro_cubierta_colocada' => $nroColocada !== '' ? $nroColocada : null,
                        'valor_unitario' => $valorUnitario,
                    ]);
                }
            }

            $this->marcarOrdenActualizada($orden);
        });

        return redirect()
            ->route('admin.movimiento-cubiertas.index', ['orden_trabajo_id' => $data['orden_trabajo_id']])
            ->with('success', 'Cambio de cubiertas actualizado correctamente.');
    }

    private function inventarioDisponible(OrdenTrabajo $orden, int $articuloId): ?Inventario
    {
        // Buscar cualquier inventario para el articulo con cantidad disponible.
        // Priorizar inventarios con mayor cantidad.
        return Inventario::query()
            ->where('articulo_id', $articuloId)
            ->where('cantidad', '>', 0)
            ->orderByDesc('cantidad')
            ->lockForUpdate()
            ->first();
    }

    private function marcarOrdenActualizada(OrdenTrabajo $orden): void
    {
        $orden->forceFill([
            'actualizado_por_user_id' => Auth::id(),
            'updated_at' => now(),
        ])->save();
    }

    private function numeroCubiertaVisible(Cubierta $cubierta): string
    {
        $numero = trim((string) $cubierta->numero);
        $codigo = trim((string) ($cubierta->articulo?->codigo_producto ?? ''));

        if ($codigo !== '' && str_starts_with($numero, $codigo . '-')) {
            return substr($numero, strlen($codigo) + 1);
        }

        return $numero;
    }

    private function buscarCubiertaPorNumeroVisible(string $numero, bool $lock = false): ?Cubierta
    {
        $numero = trim($numero);

        if ($numero === '') {
            return null;
        }

        $query = Cubierta::query();

        if ($lock) {
            $query->lockForUpdate();
        }

        return $query
            ->where('numero', $numero)
            ->orWhere('numero', 'like', '%-' . $numero)
            ->orderByRaw('CASE WHEN numero = ? THEN 0 ELSE 1 END', [$numero])
            ->first();
    }

    private function buscarOCrearCubiertaSacada(string $numero, ?int $articuloId, ?string $estadoSacada, ?string $destinoSacada, bool $lock = false): ?Cubierta
    {
        $cubierta = $this->buscarCubiertaPorNumeroVisible($numero, $lock);

        if ($cubierta || ! $articuloId) {
            return $cubierta;
        }

        $articulo = Articulo::find($articuloId);

        if (! $articulo) {
            return null;
        }

        $secuencia = (int) Cubierta::query()
            ->where('articulo_id', $articulo->id)
            ->lockForUpdate()
            ->max('secuencia');
        $numeroNormalizado = preg_replace('/^[^-]+-/', '', trim($numero));
        $estado = ($estadoSacada === 'baja' || $destinoSacada === 'descarte') ? 'baja' : 'reutilizable';

        return Cubierta::create([
            'articulo_id' => $articulo->id,
            'medida' => Cubierta::medidaDesdeArticulo($articulo),
            'secuencia' => $secuencia + 1,
            'numero' => $numeroNormalizado,
            'estado' => $estado,
            'fecha_ingreso' => now()->toDateString(),
            'fecha_baja' => $estado === 'baja' ? now()->toDateString() : null,
        ]);
    }

    private function actualizarCubiertaSacada(?Cubierta $cubierta, ?string $estadoSacada, ?string $destinoSacada): void
    {
        if (! $cubierta) {
            return;
        }

        $baja = $estadoSacada === 'baja' || $destinoSacada === 'descarte';

        $cubierta->update([
            'estado' => $baja ? 'baja' : 'reutilizable',
            'flota_id' => null,
            'posicion' => null,
            'fecha_baja' => $baja ? now()->toDateString() : null,
        ]);
    }

    private function observacionDetalle(string $posicion, string $nroSacada, string $nroColocada): string
    {
        return collect([
            "Cambio cubierta {$posicion}",
            $nroSacada !== '' ? "Nro control sacada: {$nroSacada}" : null,
            $nroColocada !== '' ? "Nro control colocada: {$nroColocada}" : null,
        ])->filter()->implode(' - ');
    }

    private function posicionesParaFlota(int $flotaId): array
    {
        $flota = Flota::with('cubiertaEjes')->find($flotaId);

        return $flota
            ? collect($flota->cubiertaPosiciones())->pluck('codigo')->all()
            : collect((new Flota())->cubiertaPosiciones())->pluck('codigo')->all();
    }

    private function articulosPorPosicion(int $flotaId): array
    {
        $flota = Flota::with('cubiertaEjes')->find($flotaId);

        return collect($flota ? $flota->cubiertaPosiciones() : (new Flota())->cubiertaPosiciones())
            ->mapWithKeys(fn ($posicion) => [
                $posicion['codigo'] => $posicion['articulo_cubierta_id'] ?? null,
            ])
            ->filter()
            ->all();
    }

    private function posicionesRequest(int $flotaId, Request $request): array
    {
        $posiciones = $this->posicionesParaFlota($flotaId);
        $posicionesRequest = collect([
            array_keys($request->input('sacada', [])),
            array_keys($request->input('colocada', [])),
            array_keys($request->input('cubierta_colocada_id', [])),
            array_keys($request->input('nro_colocada', [])),
        ])->flatten()->filter()->unique()->all();

        return array_values(array_unique(array_merge($posiciones, $posicionesRequest)));
    }
}
