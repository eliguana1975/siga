<?php

namespace App\Http\Controllers;

use App\Models\Articulo;
use App\Models\Ajuste;
use App\Models\Base;
use App\Models\Categoria;
use App\Models\Ciudad;
use App\Models\ConfiguracionIntervaloServicio;
use App\Models\DocumentoOperativo;
use App\Models\Empleado;
use App\Models\Flota;
use App\Models\FlotaRepuesto;
use App\Models\Inventario;
use App\Models\OrdenTrabajo;
use App\Models\OrdenTrabajoArticulo;
use App\Models\OrdenTrabajoMotivo;
use App\Models\PedidoArticulo;
use App\Models\PedidoDetalleArticulo;
use App\Models\Provincia;
use App\Models\RegistroServicioKilometraje;
use App\Models\UnidadMedida;
use App\Models\User;
use App\Services\ArticleClassificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class OrdenTrabajoController extends Controller
{
    public function __construct(private ArticleClassificationService $articleClassifier)
    {
        $this->middleware('auth');
        $this->middleware('check.permission:administrar-ordenes-trabajo');
    }

    private const MOTIVOS_VEHICULO_PARADO = [
        'repuestos',
        'terceros',
        'taller',
        'compras',
        'autorizacion',
        'otro',
    ];

    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));

        $query = OrdenTrabajo::with([
                'empleado:id,nombres,apellidos,numero_doc',
                'actualizadoPor:id,name,email',
                'reparador:id,nombres,apellidos,numero_doc',
                'flota:id,nro_interno,dominio',
                'base:id,nombre',
                'motivos:id,nombre,codigo',
                'articulosUsados' => fn ($query) => $query
                    ->whereHas('articulo', fn ($articulo) => $this->articuloNoCubiertaFilter($articulo))
                    ->select('id', 'orden_trabajo_id', 'articulo_id', 'cantidad', 'valor_unitario', 'inventario_descontado', 'numero_movimiento', 'matafuego_numero', 'matafuego_fecha_vencimiento'),
                'articulosUsados.articulo:id,nombre,codigo_producto,unidad_medida_id',
                'articulosUsados.articulo.unidadMedida:id,nombre',
            ])
            ->withCount(['articulosUsados' => fn ($query) => $query->whereHas('articulo', fn ($articulo) => $this->articuloNoCubiertaFilter($articulo))])
            ->withSum(['articulosUsados' => fn ($query) => $query->whereHas('articulo', fn ($articulo) => $this->articuloNoCubiertaFilter($articulo))], 'cantidad')
            ->orderByDesc('fecha_orden')
            ->orderByDesc('id');

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query->where('id', $search)
                    ->orWhere('titulo', 'like', '%' . $search . '%')
                    ->orWhere('kilometraje', 'like', '%' . $search . '%')
                    ->orWhere('tipo_trabajo', 'like', '%' . $search . '%')
                    ->orWhere('prioridad', 'like', '%' . $search . '%')
                    ->orWhere('estado', 'like', '%' . $search . '%')
                    ->orWhere('motivo_vehiculo_parado', 'like', '%' . $search . '%')
                    ->orWhere('descripcion', 'like', '%' . $search . '%')
                    ->orWhereHas('actualizadoPor', function ($query) use ($search) {
                        $query->where('name', 'like', '%' . $search . '%')
                            ->orWhere('email', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('motivos', function ($query) use ($search) {
                        $query->where('nombre', 'like', '%' . $search . '%')
                            ->orWhere('codigo', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('empleado', function ($query) use ($search) {
                        $query->where('nombres', 'like', '%' . $search . '%')
                            ->orWhere('apellidos', 'like', '%' . $search . '%')
                            ->orWhere('numero_doc', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('flota', function ($query) use ($search) {
                        $query->where('nro_interno', 'like', '%' . $search . '%')
                            ->orWhere('dominio', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('base', function ($query) use ($search) {
                        $query->where('nombre', 'like', '%' . $search . '%');
                    });
            });
        }

        $ordenes = $query->paginate(10)->withQueryString();
        $empleados = Empleado::where('estado', 'activo')->orderBy('apellidos')->orderBy('nombres')->get();
        $flotas = Flota::whereIn('estado', ['activo', 'mantenimiento'])->orderBy('nro_interno')->get();
        $bases = Base::where('estado', 'activa')->orderBy('nombre')->get();
        $articulos = $this->articulosNoCubiertasQuery()->get();
        $motivosOrdenTrabajo = OrdenTrabajoMotivo::where('activo', true)->orderBy('nombre')->get();
        $motivoVehiculoParadoLabels = $this->motivoVehiculoParadoLabels();
        $empresa = $this->empresaReporte();

        return view('admin.ordenes-trabajo.index', compact('ordenes', 'empleados', 'flotas', 'bases', 'articulos', 'search', 'motivosOrdenTrabajo', 'motivoVehiculoParadoLabels', 'empresa'));
    }

    public function edit(string $id)
    {
        if (! $this->puedeModificarOrdenTrabajo()) {
            return redirect()
                ->route('admin.ordenes-trabajo.index')
                ->with('error', 'Solo el superusuario puede editar ordenes de trabajo.');
        }

        $orden = OrdenTrabajo::with(['empleado', 'reparador', 'flota', 'base', 'motivos'])->findOrFail($id);

        if ($orden->estaCerrada() && ! $this->puedeModificarOrdenTrabajo()) {
            return redirect()
                ->route('admin.ordenes-trabajo.index')
                ->with('error', 'La orden de trabajo esta cerrada y no se puede editar.');
        }

        $empleados = Empleado::where('estado', 'activo')->orderBy('apellidos')->orderBy('nombres')->get();
        $flotas = Flota::whereIn('estado', ['activo', 'mantenimiento'])->orderBy('nro_interno')->get();
        $bases = Base::where('estado', 'activa')->orderBy('nombre')->get();
        $motivosOrdenTrabajo = OrdenTrabajoMotivo::where('activo', true)->orderBy('nombre')->get();

        $tipoTrabajoLabels = [
            'preventivo' => 'Preventivo',
            'correctivo' => 'Correctivo',
            'inspeccion' => 'Inspeccion',
            'reparacion' => 'Reparacion',
        ];

        $prioridadLabels = [
            'baja' => 'Baja',
            'media' => 'Media',
            'alta' => 'Alta',
            'urgente' => 'Urgente',
        ];

        $estadoLabels = [
            'pendiente' => 'Pendiente',
            'en_proceso' => 'En proceso',
            'completada' => 'Completada',
            'cancelada' => 'Cancelada',
        ];

        $motivoVehiculoParadoLabels = $this->motivoVehiculoParadoLabels();

        return view('admin.ordenes-trabajo.edit', compact(
            'orden',
            'empleados',
            'flotas',
            'bases',
            'motivosOrdenTrabajo',
            'tipoTrabajoLabels',
            'prioridadLabels',
            'estadoLabels',
            'motivoVehiculoParadoLabels'
        ));
    }

    public function articulos(string $id)
    {
        $orden = OrdenTrabajo::with([
            'empleado',
            'reparador',
            'flota',
            'base',
            'articulosUsados' => fn ($query) => $query->whereHas('articulo', fn ($articulo) => $this->articuloNoCubiertaFilter($articulo)),
            'articulosUsados.articulo.unidadMedida',
        ])->findOrFail($id);
        $articulos = $this->articulosNoCubiertasQuery()->get();
        $categorias = Categoria::orderBy('nombre')->get();
        $unidadesMedida = UnidadMedida::orderBy('nombre')->get();
        $intervalosServicio = ConfiguracionIntervaloServicio::query()
            ->where('estado', 'activo')
            ->orderByRaw("CASE WHEN LOWER(sistema) = 'motor' OR LOWER(nombre) LIKE '%motor%' THEN 0 ELSE 1 END")
            ->orderBy('sistema')
            ->orderBy('unidad_intervalo')
            ->orderBy('kilometros_intervalo')
            ->orderBy('nombre')
            ->get();
        $servicioKits = $this->servicioKitsParaOrden($orden);
        $documentos = DocumentoOperativo::query()
            ->with('usuario:id,name')
            ->where('documentable_type', OrdenTrabajo::class)
            ->where('documentable_id', $orden->id)
            ->latest()
            ->get();

        return view('admin.ordenes-trabajo.articulos', compact('orden', 'articulos', 'categorias', 'unidadesMedida', 'intervalosServicio', 'servicioKits', 'documentos'));
    }

    public function store(Request $request)
    {
        $request->merge($this->normalizedInput($request));

        $validator = Validator::make($request->all(), $this->rules());
        $this->validateKilometraje($validator, $request);

        if ($validator->fails()) {
            return redirect()
                ->route('admin.ordenes-trabajo.index')
                ->withErrors($validator)
                ->withInput()
                ->with('open_modal', 'createOrdenTrabajoModal');
        }

        DB::transaction(function () use ($validator) {
            $data = $validator->validated();
            $motivos = $data['motivos'] ?? [];
            unset($data['motivos']);
            $data['descripcion'] = $this->descripcionConMotivos($data['descripcion'] ?? '', $motivos);
            $data['actualizado_por_user_id'] = Auth::id();

            $orden = OrdenTrabajo::create($data);
            $orden->motivos()->sync($motivos);
        });

        return redirect()
            ->route('admin.ordenes-trabajo.index')
            ->with('success', 'Orden de trabajo creada correctamente.');
    }

    public function update(Request $request, string $id)
    {
        if (! $this->puedeModificarOrdenTrabajo()) {
            return redirect()
                ->route('admin.ordenes-trabajo.index')
                ->with('error', 'Solo el superusuario puede editar ordenes de trabajo.');
        }

        $orden = OrdenTrabajo::findOrFail($id);

        if ($orden->estaCerrada() && ! $this->puedeModificarOrdenTrabajo()) {
            return redirect()
                ->route('admin.ordenes-trabajo.index')
                ->with('error', 'La orden de trabajo esta cerrada y no se puede editar.');
        }

        $request->merge($this->normalizedInput($request));
        $request->merge([
            'titulo' => $orden->titulo,
            'kilometraje' => $orden->kilometraje,
            'fecha_orden' => optional($orden->fecha_orden)->format('Y-m-d'),
            'flota_id' => $orden->flota_id,
            'base_id' => $orden->base_id,
            'descripcion' => $orden->descripcion,
        ]);

        $validator = Validator::make($request->all(), $this->rules($orden->id));

        if ($validator->fails()) {
            return redirect()
                ->route('admin.ordenes-trabajo.edit', $orden->id)
                ->withErrors($validator)
                ->withInput();
        }

        DB::transaction(function () use ($orden, $validator) {
            $data = $validator->validated();
            $motivos = $data['motivos'] ?? [];
            unset($data['motivos']);
            $data['descripcion'] = $this->descripcionConMotivos($data['descripcion'] ?? '', $motivos);
            $data['actualizado_por_user_id'] = Auth::id();

            $orden->update($data);
            $orden->motivos()->sync($motivos);
        });

        return redirect()
            ->route('admin.ordenes-trabajo.index')
            ->with('success', 'Orden de trabajo actualizada correctamente.');
    }

    public function destroy(string $id)
    {
        if (! $this->puedeModificarOrdenTrabajo()) {
            return redirect()
                ->route('admin.ordenes-trabajo.index')
                ->with('error', 'Solo el superusuario puede eliminar ordenes de trabajo.');
        }

        $orden = OrdenTrabajo::with(['base', 'articulosUsados.articulo'])->findOrFail($id);

        if ($orden->estaCerrada() && ! $this->puedeModificarOrdenTrabajo()) {
            return redirect()
                ->route('admin.ordenes-trabajo.index')
                ->with('error', 'La orden de trabajo esta cerrada y no se puede eliminar.');
        }

        DB::transaction(function () use ($orden) {
            foreach ($orden->articulosUsados as $detalle) {
                $this->devolverStockDetalle($orden, $detalle);
            }

            $orden->delete();
        });

        return redirect()
            ->route('admin.ordenes-trabajo.index')
            ->with('success', 'Orden de trabajo eliminada correctamente.');
    }

    public function storeArticulo(Request $request, string $ordenId)
    {
        $orden = OrdenTrabajo::findOrFail($ordenId);

        if ($orden->estaCerrada()) {
            return redirect()
                ->route('admin.ordenes-trabajo.articulos', $orden->id)
                ->with('error', 'La orden de trabajo esta cerrada y no permite agregar articulos.');
        }

        if ($request->boolean('crear_articulo')) {
            abort_unless($request->user()?->can('articulos.crear'), 403);

            return $this->storeArticuloRapido($request, $orden);
        }

        $request->merge([
            'cantidad' => $request->filled('cantidad') ? $request->input('cantidad') : 1,
            'observaciones' => $request->filled('observaciones') ? trim((string) $request->input('observaciones')) : null,
        ]);

        if (! $request->filled('articulo_id') && $request->filled('articulo_codigo')) {
            $articuloEscaneado = $this->buscarArticuloPorCodigoParaOrden((string) $request->input('articulo_codigo'));

            if ($articuloEscaneado) {
                $request->merge(['articulo_id' => $articuloEscaneado->id]);
            }
        }

        $validator = Validator::make($request->all(), [
            'articulo_id' => ['required', 'integer', 'exists:articulos,id'],
            'articulo_codigo' => ['nullable', 'string', 'max:255'],
            'cantidad' => ['required', 'integer', 'min:1'],
            'matafuego_numero' => ['nullable', 'string', 'max:120'],
            'matafuego_fecha_vencimiento' => ['nullable', 'date'],
            'observaciones' => ['nullable', 'string'],
        ], [
            'articulo_id.required' => $request->filled('articulo_codigo')
                ? 'No se encontro un articulo activo con ese codigo de barras.'
                : 'Seleccione un articulo o escanee un codigo valido.',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('admin.ordenes-trabajo.articulos', $orden->id)
                ->withErrors($validator)
                ->withInput();
        }

        $data = $validator->validated();

        $articulo = Articulo::with('categoria')->find($data['articulo_id']);

        if ($articulo && $this->esArticuloCubierta($articulo)) {
            return redirect()
                ->route('admin.ordenes-trabajo.articulos', $orden->id)
                ->withInput()
                ->with('error', 'Las cubiertas no se cargan desde Articulos usados. Use Gestion de cubiertas.');
        }

        if ($articulo && $this->esArticuloRopaEpp($articulo)) {
            return redirect()
                ->route('admin.ordenes-trabajo.articulos', $orden->id)
                ->withInput()
                ->with('error', 'Las prendas y EPP no se cargan desde orden de trabajo. Use Entrega de ropa y EPP.');
        }

        if ($this->esArticuloMatafuego($articulo)) {
            $request->validate([
                'matafuego_numero' => ['required', 'string', 'max:120'],
                'matafuego_fecha_vencimiento' => ['required', 'date'],
            ], [], [
                'matafuego_numero' => 'numero del matafuego',
                'matafuego_fecha_vencimiento' => 'fecha de vencimiento del matafuego',
            ]);

            $data['matafuego_numero'] = mb_strtoupper(trim((string) $request->input('matafuego_numero')), 'UTF-8');
            $data['matafuego_fecha_vencimiento'] = $request->input('matafuego_fecha_vencimiento');
        } else {
            unset($data['matafuego_numero'], $data['matafuego_fecha_vencimiento']);
        }

        $stockError = null;

        DB::transaction(function () use ($orden, &$data, &$stockError) {
            $inventario = $this->inventarioDisponibleParaOrden($orden, (int) $data['articulo_id']);

            if (! $inventario) {
                $stockError = 'No hay inventario disponible para este articulo en el deposito de la base de la orden.';
                return;
            }

            if ((int) $inventario->cantidad < (int) $data['cantidad']) {
                $stockError = 'Stock insuficiente. Disponible: ' . (int) $inventario->cantidad . ' unidad(es).';
                return;
            }

            $data['valor_unitario'] = (float) ($inventario->precio_compra_unidad ?? 0);
            $data['inventario_descontado'] = true;
            $orden->articulosUsados()->create($data);
            $this->marcarOrdenActualizada($orden);

            $inventario->cantidad = (int) $inventario->cantidad - (int) $data['cantidad'];
            $inventario->save();
        });

        if ($stockError !== null) {
            return redirect()
                ->route('admin.ordenes-trabajo.articulos', $orden->id)
                ->withInput()
                ->with('error', $stockError);
        }

        return redirect()
            ->route('admin.ordenes-trabajo.articulos', $orden->id)
            ->with('success', 'Articulo agregado a la orden correctamente.');
    }

    public function cargarKitServicio(Request $request, string $ordenId)
    {
        $orden = OrdenTrabajo::with('base')->findOrFail($ordenId);

        if ($orden->estaCerrada()) {
            return redirect()
                ->route('admin.ordenes-trabajo.articulos', $orden->id)
                ->with('error', 'La orden de trabajo esta cerrada y no permite cargar kits de servicio.');
        }

        $validator = Validator::make($request->all(), [
            'configuracion_intervalo_servicio_id' => ['required', 'integer', 'exists:configuracion_intervalos_servicio,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.usar' => ['nullable', 'boolean'],
            'items.*.cantidad' => ['required', 'integer', 'min:1'],
            'generar_pedido_faltantes' => ['nullable', 'boolean'],
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('admin.ordenes-trabajo.articulos', $orden->id)
                ->withErrors($validator)
                ->withInput();
        }

        $data = $validator->validated();
        $servicioId = (int) $data['configuracion_intervalo_servicio_id'];
        $items = collect($data['items'])
            ->filter(fn ($item) => (bool) ($item['usar'] ?? false))
            ->map(fn ($item, $repuestoId) => [
                'repuesto_id' => (int) $repuestoId,
                'cantidad' => max(1, (int) ($item['cantidad'] ?? 1)),
            ])
            ->values();

        if ($items->isEmpty()) {
            return redirect()
                ->route('admin.ordenes-trabajo.articulos', $orden->id)
                ->with('error', 'Seleccione al menos un articulo del kit de servicio.');
        }

        $repuestos = FlotaRepuesto::query()
            ->with(['articulo.unidadMedida', 'configuracionIntervaloServicio'])
            ->where('flota_id', $orden->flota_id)
            ->where('configuracion_intervalo_servicio_id', $servicioId)
            ->where('estado', 'activo')
            ->whereNotNull('articulo_id')
            ->whereIn('id', $items->pluck('repuesto_id'))
            ->get()
            ->keyBy('id');

        if ($repuestos->isEmpty()) {
            return redirect()
                ->route('admin.ordenes-trabajo.articulos', $orden->id)
                ->with('error', 'No se encontraron articulos activos para ese kit de servicio.');
        }

        $generarPedido = (bool) ($data['generar_pedido_faltantes'] ?? false);

        if ($generarPedido && ! $request->user()?->can('pedidos-articulos.crear')) {
            return redirect()
                ->route('admin.ordenes-trabajo.articulos', $orden->id)
                ->with('error', 'No tiene permiso para generar pedidos de articulos por faltantes.');
        }

        $faltantes = collect();
        $agregados = 0;
        $pedidoId = null;

        DB::transaction(function () use ($orden, $items, $repuestos, $generarPedido, &$faltantes, &$agregados, &$pedidoId) {
            foreach ($items as $item) {
                $repuesto = $repuestos->get($item['repuesto_id']);

                if (! $repuesto || ! $repuesto->articulo_id) {
                    continue;
                }

                if ($this->esArticuloCubierta($repuesto->articulo)) {
                    continue;
                }

                if ($this->esArticuloRopaEpp($repuesto->articulo)) {
                    continue;
                }

                $cantidadNecesaria = (int) $item['cantidad'];
                $inventario = $this->inventarioDisponibleParaOrden($orden, (int) $repuesto->articulo_id);
                $disponible = $inventario ? (int) $inventario->cantidad : 0;
                $cantidadADescontar = min($disponible, $cantidadNecesaria);

                if ($cantidadADescontar > 0 && $inventario) {
                    $orden->articulosUsados()->create([
                        'articulo_id' => $repuesto->articulo_id,
                        'cantidad' => $cantidadADescontar,
                        'valor_unitario' => (float) ($inventario->precio_compra_unidad ?? 0),
                        'inventario_descontado' => true,
                        'observaciones' => 'Cargado desde kit de servicio: ' . ($repuesto->configuracionIntervaloServicio?->nombre ?? 'servicio'),
                    ]);

                    $inventario->cantidad = $disponible - $cantidadADescontar;
                    $inventario->save();
                    $agregados++;
                }

                $cantidadFaltante = $cantidadNecesaria - $cantidadADescontar;

                if ($cantidadFaltante > 0) {
                    $faltantes->push([
                        'articulo_id' => (int) $repuesto->articulo_id,
                        'cantidad' => $cantidadFaltante,
                        'nombre' => $repuesto->articulo?->nombre ?? 'Articulo #' . $repuesto->articulo_id,
                    ]);
                }
            }

            if ($agregados > 0) {
                $this->marcarOrdenActualizada($orden);
            }

            if ($generarPedido && $faltantes->isNotEmpty()) {
                $pedidoId = $this->crearPedidoPorFaltantesKit($orden, $faltantes);
            }
        });

        $mensaje = $agregados > 0
            ? 'Kit de servicio cargado en la orden.'
            : 'No se pudo descontar stock para los articulos seleccionados.';

        if ($faltantes->isNotEmpty()) {
            $detalleFaltantes = $faltantes
                ->map(fn ($item) => $item['nombre'] . ' x' . $item['cantidad'])
                ->join(', ');

            if ($pedidoId) {
                $mensaje .= ' Se genero el pedido de articulos #' . $pedidoId . ' por faltantes: ' . $detalleFaltantes . '.';
            } else {
                return redirect()
                    ->route('admin.ordenes-trabajo.articulos', $orden->id)
                    ->with($agregados > 0 ? 'warning' : 'error', $mensaje . ' Faltantes: ' . $detalleFaltantes . '.');
            }
        }

        return redirect()
            ->route('admin.ordenes-trabajo.articulos', $orden->id)
            ->with('success', $mensaje);
    }

    private function storeArticuloRapido(Request $request, OrdenTrabajo $orden)
    {
        $request->merge([
            'nombre' => mb_strtoupper(trim((string) $request->input('nombre')), 'UTF-8'),
            'codigo_producto' => $request->filled('codigo_producto')
                ? mb_strtoupper(trim((string) $request->input('codigo_producto')), 'UTF-8')
                : null,
            'cantidad' => $request->filled('cantidad') ? $request->input('cantidad') : 1,
            'valor_unitario' => $request->filled('valor_unitario') ? $this->normalizeDecimal($request->input('valor_unitario')) : 0,
            'observaciones' => $request->filled('observaciones') ? trim((string) $request->input('observaciones')) : null,
            'matafuego_numero' => $request->filled('matafuego_numero') ? mb_strtoupper(trim((string) $request->input('matafuego_numero')), 'UTF-8') : null,
            'matafuego_fecha_vencimiento' => $request->filled('matafuego_fecha_vencimiento') ? $request->input('matafuego_fecha_vencimiento') : null,
        ]);

        $validator = Validator::make($request->all(), [
            'categoria_id' => ['required', 'exists:categorias,id'],
            'unidad_medida_id' => ['required', 'exists:unidad_medidas,id'],
            'nombre' => ['required', 'string', 'max:255'],
            'codigo_producto' => ['nullable', 'string', 'max:255'],
            'cantidad' => ['required', 'integer', 'min:1'],
            'valor_unitario' => ['required', 'numeric', 'min:0'],
            'observaciones' => ['nullable', 'string'],
            'matafuego_numero' => ['nullable', 'string', 'max:120'],
            'matafuego_fecha_vencimiento' => ['nullable', 'date'],
        ], [], [
            'categoria_id' => 'categoria',
            'unidad_medida_id' => 'unidad',
            'valor_unitario' => 'valor unitario',
            'matafuego_numero' => 'numero del matafuego',
            'matafuego_fecha_vencimiento' => 'fecha de vencimiento del matafuego',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('admin.ordenes-trabajo.articulos', $orden->id)
                ->withErrors($validator)
                ->withInput()
                ->with('open_quick_article', true);
        }

        $data = $validator->validated();
        $categoria = Categoria::find($data['categoria_id']);

        if ($this->textoEsRopaEpp($categoria?->nombre)) {
            return redirect()
                ->route('admin.ordenes-trabajo.articulos', $orden->id)
                ->withInput()
                ->with('open_quick_article', true)
                ->with('error', 'Las prendas y EPP no se cargan desde orden de trabajo. Use Entrega de ropa y EPP.');
        }

        if ($this->textoEsCubierta($data['nombre'] ?? '', $data['codigo_producto'] ?? '', $categoria?->nombre ?? '')) {
            return redirect()
                ->route('admin.ordenes-trabajo.articulos', $orden->id)
                ->withInput()
                ->with('open_quick_article', true)
                ->with('error', 'Las cubiertas no se cargan desde Articulos usados. Use Gestion de cubiertas.');
        }

        if ($this->textoEsMatafuego($data['nombre'] ?? '', $data['codigo_producto'] ?? '', $categoria?->nombre ?? '')) {
            $request->validate([
                'matafuego_numero' => ['required', 'string', 'max:120'],
                'matafuego_fecha_vencimiento' => ['required', 'date'],
            ], [], [
                'matafuego_numero' => 'numero del matafuego',
                'matafuego_fecha_vencimiento' => 'fecha de vencimiento del matafuego',
            ]);

            $data['matafuego_numero'] = mb_strtoupper(trim((string) $request->input('matafuego_numero')), 'UTF-8');
            $data['matafuego_fecha_vencimiento'] = $request->input('matafuego_fecha_vencimiento');
        } else {
            unset($data['matafuego_numero'], $data['matafuego_fecha_vencimiento']);
        }

        DB::transaction(function () use ($orden, $data) {
            $articulo = Articulo::create([
                'categoria_id' => $data['categoria_id'],
                'unidad_medida_id' => $data['unidad_medida_id'],
                'nombre' => $data['nombre'],
                'codigo_producto' => $data['codigo_producto'] ?? null,
                'es_ropa_epp' => false,
                'stock_minimo' => 0,
                'stock_maximo' => 0,
                'stock_pedido' => 0,
                'estado_item' => 'activo',
                'observaciones' => trim('Alta rapida desde orden de trabajo #' . $orden->id . (($data['observaciones'] ?? null) ? ' - ' . $data['observaciones'] : '')),
            ]);

            $orden->articulosUsados()->create([
                'articulo_id' => $articulo->id,
                'cantidad' => (int) $data['cantidad'],
                'valor_unitario' => (float) $data['valor_unitario'],
                'inventario_descontado' => false,
                'matafuego_numero' => $data['matafuego_numero'] ?? null,
                'matafuego_fecha_vencimiento' => $data['matafuego_fecha_vencimiento'] ?? null,
                'observaciones' => $data['observaciones'] ?? 'Articulo cargado sin descuento de inventario.',
            ]);
            $this->marcarOrdenActualizada($orden);
        });

        return redirect()
            ->route('admin.ordenes-trabajo.articulos', $orden->id)
            ->with('success', 'Articulo creado y agregado a la orden sin descontar stock.');
    }

    public function destroyArticulo(string $ordenId, string $detalleId)
    {
        $orden = OrdenTrabajo::with('base')->findOrFail($ordenId);

        if ($orden->estaCerrada()) {
            return redirect()
                ->route('admin.ordenes-trabajo.articulos', $orden->id)
                ->with('error', 'La orden de trabajo esta cerrada y no permite quitar articulos.');
        }

        DB::transaction(function () use ($orden, $detalleId) {
            $detalle = OrdenTrabajoArticulo::with('articulo')
                ->where('orden_trabajo_id', $orden->id)
                ->findOrFail($detalleId);

            $this->devolverStockDetalle($orden, $detalle);
            $detalle->delete();
            $this->marcarOrdenActualizada($orden);
        });

        return redirect()
            ->route('admin.ordenes-trabajo.articulos', $orden->id)
            ->with('success', 'Articulo quitado de la orden correctamente.');
    }

    public function registrarServicioKilometraje(Request $request, string $ordenId)
    {
        $orden = OrdenTrabajo::findOrFail($ordenId);

        if ($orden->estaCerrada()) {
            return redirect()
                ->route('admin.ordenes-trabajo.articulos', $orden->id)
                ->with('error', 'La orden de trabajo esta cerrada y no permite registrar servicios.');
        }

        $validator = Validator::make($request->all(), [
            'configuracion_intervalo_servicio_id' => ['required', 'exists:configuracion_intervalos_servicio,id'],
            'observaciones' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('admin.ordenes-trabajo.index')
                ->withErrors($validator)
                ->withInput()
                ->with('open_modal', 'registrarServicioOrdenModal-' . $orden->id);
        }

        RegistroServicioKilometraje::create([
            'flota_id' => $orden->flota_id,
            'configuracion_intervalo_servicio_id' => $validator->validated()['configuracion_intervalo_servicio_id'],
            'user_id' => Auth::id(),
            'kilometraje_servicio' => (int) $orden->kilometraje,
            'fecha_servicio' => now(),
            'observaciones' => $validator->validated()['observaciones'] ?? 'Registrado desde orden de trabajo #' . $orden->id,
        ]);

        $this->marcarOrdenActualizada($orden);

        return redirect()
            ->route('admin.ordenes-trabajo.index')
            ->with('success', 'Servicio por kilometraje registrado correctamente desde la orden de trabajo.');
    }

    private function normalizedInput(Request $request): array
    {
        $estado = $request->input('estado', 'pendiente');
        $vehiculoParado = $request->boolean('vehiculo_parado') ? 1 : 0;

        return [
            'empleado_id' => $request->input('empleado_id'),
            'reparador_empleado_id' => $request->filled('reparador_empleado_id') ? $request->input('reparador_empleado_id') : null,
            'flota_id' => $request->input('flota_id'),
            'base_id' => $request->input('base_id'),
            'kilometraje' => $request->input('kilometraje'),
            'fecha_orden' => $request->filled('fecha_orden') ? $request->input('fecha_orden') : now()->format('Y-m-d'),
            'tipo_trabajo' => $request->input('tipo_trabajo', 'correctivo'),
            'prioridad' => $request->input('prioridad', 'media'),
            'estado' => $estado,
            'vehiculo_parado' => $vehiculoParado,
            'motivo_vehiculo_parado' => $vehiculoParado ? $request->input('motivo_vehiculo_parado') : null,
            'fecha_vehiculo_parado' => $vehiculoParado
                ? ($request->filled('fecha_vehiculo_parado') ? $request->input('fecha_vehiculo_parado') : now()->toDateString())
                : null,
            'observacion_vehiculo_parado' => $vehiculoParado ? trim((string) $request->input('observacion_vehiculo_parado')) : null,
            'titulo' => trim((string) $request->input('titulo')),
            'descripcion' => trim((string) $request->input('descripcion')),
            'observaciones' => trim((string) $request->input('observaciones')),
            'fecha_cierre' => $estado === 'completada' && $request->filled('fecha_cierre')
                ? $request->input('fecha_cierre')
                : null,
        ];
    }

    private function marcarOrdenActualizada(OrdenTrabajo $orden): void
    {
        $orden->forceFill([
            'actualizado_por_user_id' => Auth::id(),
            'updated_at' => now(),
        ])->save();
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

    private function rules(?int $ordenId = null): array
    {
        return [
            'empleado_id' => ['required', 'integer', 'exists:empleados,id'],
            'reparador_empleado_id' => ['nullable', 'integer', 'exists:empleados,id'],
            'flota_id' => ['required', 'integer', 'exists:flota,id'],
            'base_id' => ['required', 'integer', 'exists:bases,id'],
            'kilometraje' => ['required', 'integer', 'min:0'],
            'fecha_orden' => ['required', 'date'],
            'tipo_trabajo' => ['required', Rule::in(['preventivo', 'correctivo', 'inspeccion', 'reparacion'])],
            'motivos' => ['required', 'array', 'min:1'],
            'motivos.*' => ['integer', 'exists:orden_trabajo_motivos,id'],
            'prioridad' => ['required', Rule::in(['baja', 'media', 'alta', 'urgente'])],
            'estado' => ['required', Rule::in(['pendiente', 'en_proceso', 'completada', 'cancelada'])],
            'vehiculo_parado' => ['required', 'boolean'],
            'motivo_vehiculo_parado' => ['nullable', 'required_if:vehiculo_parado,1', Rule::in(self::MOTIVOS_VEHICULO_PARADO)],
            'fecha_vehiculo_parado' => ['nullable', 'required_if:vehiculo_parado,1', 'date'],
            'observacion_vehiculo_parado' => ['nullable', 'string'],
            'titulo' => ['required', 'string', 'max:150'],
            'descripcion' => ['nullable', 'string'],
            'observaciones' => ['nullable', 'string'],
            'fecha_cierre' => ['nullable', 'date'],
        ];
    }

    private function validateKilometraje($validator, Request $request, ?int $ordenId = null): void
    {
        $validator->after(function ($validator) use ($request, $ordenId) {
            if (!$request->filled('flota_id') || !$request->filled('kilometraje')) {
                return;
            }

            $query = OrdenTrabajo::where('flota_id', $request->input('flota_id'))
                ->whereNotNull('kilometraje');

            if ($ordenId !== null) {
                $query->where('id', '<>', $ordenId);
            }

            $ultimoKilometraje = $query->max('kilometraje');

            if ($ultimoKilometraje !== null && (int) $request->input('kilometraje') < (int) $ultimoKilometraje) {
                $validator->errors()->add(
                    'kilometraje',
                    'El kilometraje no puede ser menor al ultimo registrado para este vehiculo (' . $ultimoKilometraje . ' km).'
                );
            }
        });
    }

    private function descripcionConMotivos(?string $descripcion, array $motivoIds): string
    {
        $motivos = OrdenTrabajoMotivo::query()
            ->whereIn('id', $motivoIds)
            ->get()
            ->keyBy('id');

        $motivoNombres = collect($motivoIds)
            ->map(fn ($id) => $motivos->get((int) $id)?->nombre)
            ->filter()
            ->values();

        $descripcionLimpia = collect(preg_split('/\r\n|\r|\n/', (string) $descripcion))
            ->reject(fn ($line) => preg_match('/^\s*Motivos:\s*/i', (string) $line))
            ->implode("\n");

        $descripcionLimpia = trim($descripcionLimpia);

        if ($motivoNombres->isEmpty()) {
            return $descripcionLimpia;
        }

        $lineaMotivos = 'Motivos: ' . $motivoNombres->join(', ');

        if ($descripcionLimpia === '') {
            return $lineaMotivos;
        }

        $lineas = preg_split('/\r\n|\r|\n/', $descripcionLimpia);

        if (count($lineas) === 1) {
            return $lineas[0] . "\n" . $lineaMotivos;
        }

        array_splice($lineas, 1, 0, [$lineaMotivos]);

        return implode("\n", $lineas);
    }

    private function resolveValorUnitarioArticulo(OrdenTrabajo $orden, int $articuloId): float
    {
        $orden->loadMissing('base');
        $depositoId = $orden->base?->deposito_id;

        $query = Inventario::query()
            ->where('articulo_id', $articuloId)
            ->whereNotNull('precio_compra_unidad')
            ->where('precio_compra_unidad', '>', 0);

        if ($depositoId) {
            $precioDeposito = (clone $query)
                ->where('deposito_id', $depositoId)
                ->latest('fecha_registro')
                ->latest('id')
                ->value('precio_compra_unidad');

            if ($precioDeposito !== null) {
                return (float) $precioDeposito;
            }
        }

        return (float) ($query->latest('fecha_registro')->latest('id')->value('precio_compra_unidad') ?? 0);
    }

    private function buscarArticuloPorCodigoParaOrden(string $codigo): ?Articulo
    {
        $codigoNormalizado = $this->normalizarCodigoArticulo($codigo);

        if ($codigoNormalizado === '') {
            return null;
        }

        return Articulo::query()
            ->where('estado_item', 'activo')
            ->where(fn ($query) => $this->articleClassifier->applyNoCubiertaNoRopaEppFilter($query))
            ->get()
            ->first(fn (Articulo $articulo) => $this->normalizarCodigoArticulo((string) $articulo->codigo_producto) === $codigoNormalizado);
    }

    private function normalizarCodigoArticulo(string $codigo): string
    {
        return preg_replace('/[^A-Z0-9]/', '', mb_strtoupper(trim($codigo), 'UTF-8')) ?? '';
    }

    private function articulosNoCubiertasQuery()
    {
        return Articulo::with(['unidadMedida', 'categoria'])
            ->where('estado_item', 'activo')
            ->where(fn ($query) => $this->articuloNoCubiertaFilter($query))
            ->orderBy('nombre');
    }

    private function articuloNoCubiertaFilter($query): void
    {
        $this->articleClassifier->applyNoCubiertaNoRopaEppFilter($query);
    }

    private function esArticuloCubierta(Articulo $articulo): bool
    {
        return $this->articleClassifier->isCubiertaArticulo($articulo);
    }

    private function esArticuloRopaEpp(?Articulo $articulo): bool
    {
        return $this->articleClassifier->isRopaEppArticulo($articulo);
    }

    private function esArticuloMatafuego(?Articulo $articulo): bool
    {
        return $this->articleClassifier->isMatafuegoArticulo($articulo);
    }

    private function textoEsCubierta(?string $nombre, ?string $codigo, ?string $categoria): bool
    {
        return $this->articleClassifier->isCubiertaText($nombre, $codigo, $categoria);
    }

    private function textoEsRopaEpp(?string $categoria): bool
    {
        return $this->articleClassifier->isRopaEppCategory($categoria);
    }

    private function textoEsMatafuego(?string $nombre, ?string $codigo, ?string $categoria): bool
    {
        return $this->articleClassifier->isMatafuegoText($nombre, $codigo, $categoria);
    }

    private function normalizeDecimal(mixed $value): string
    {
        $value = trim((string) $value);

        if (str_contains($value, ',')) {
            return str_replace(',', '.', str_replace('.', '', $value));
        }

        if (preg_match('/^\d{1,3}(\.\d{3})+$/', $value)) {
            return str_replace('.', '', $value);
        }

        return $value;
    }

    private function inventarioDisponibleParaOrden(OrdenTrabajo $orden, int $articuloId, ?float $valorUnitario = null): ?Inventario
    {
        $orden->loadMissing('base');
        $depositoId = $orden->base?->deposito_id;

        if (! $depositoId) {
            return null;
        }

        $query = Inventario::query()
            ->where('deposito_id', $depositoId)
            ->where('articulo_id', $articuloId)
            ->lockForUpdate();

        if ($valorUnitario !== null) {
            $exactMatch = (clone $query)
                ->where('precio_compra_unidad', $valorUnitario)
                ->orderBy('id')
                ->first();

            if ($exactMatch) {
                return $exactMatch;
            }
        }

        return $query->orderBy('id')->first();
    }

    private function devolverStockDetalle(OrdenTrabajo $orden, OrdenTrabajoArticulo $detalle): void
    {
        if (! $detalle->inventario_descontado) {
            return;
        }

        $inventario = $this->inventarioDisponibleParaOrden(
            $orden,
            (int) $detalle->articulo_id,
            $detalle->valor_unitario === null ? null : (float) $detalle->valor_unitario
        );

        if (! $inventario && $orden->base?->deposito_id) {
            $inventario = Inventario::create([
                'deposito_id' => $orden->base->deposito_id,
                'articulo_id' => $detalle->articulo_id,
                'precio_compra_unidad' => $detalle->valor_unitario,
                'cantidad' => 0,
                'stock_minimo' => $detalle->articulo?->stock_minimo ?? 0,
                'stock_maximo' => $detalle->articulo?->stock_maximo ?? 0,
                'estado' => 'ajuste',
            ]);
        }

        if ($inventario) {
            $inventario->cantidad = (int) $inventario->cantidad + (int) $detalle->cantidad;
            $inventario->save();
        }
    }

    private function servicioKitsParaOrden(OrdenTrabajo $orden)
    {
        if (! $orden->flota_id) {
            return collect();
        }

        $orden->loadMissing('base');
        $depositoId = $orden->base?->deposito_id;

        $repuestos = FlotaRepuesto::query()
            ->with(['articulo.unidadMedida', 'configuracionIntervaloServicio'])
            ->where('flota_id', $orden->flota_id)
            ->where('estado', 'activo')
            ->whereNotNull('articulo_id')
            ->whereNotNull('configuracion_intervalo_servicio_id')
            ->orderBy('configuracion_intervalo_servicio_id')
            ->get()
            ->filter(fn (FlotaRepuesto $repuesto) => $repuesto->articulo
                && ! $this->esArticuloRopaEpp($repuesto->articulo)
                && ! $this->esArticuloCubierta($repuesto->articulo));

        if ($repuestos->isEmpty()) {
            return collect();
        }

        $stock = $depositoId
            ? Inventario::query()
                ->where('deposito_id', $depositoId)
                ->whereIn('articulo_id', $repuestos->pluck('articulo_id')->unique())
                ->selectRaw('articulo_id, SUM(cantidad) as disponible')
                ->groupBy('articulo_id')
                ->pluck('disponible', 'articulo_id')
            : collect();

        return $repuestos
            ->groupBy('configuracion_intervalo_servicio_id')
            ->map(function ($items) use ($stock) {
                $servicio = $items->first()->configuracionIntervaloServicio;

                return [
                    'servicio' => $servicio,
                    'items' => $items
                        ->sortBy(fn ($repuesto) => $repuesto->articulo?->nombre)
                        ->map(function (FlotaRepuesto $repuesto) use ($stock) {
                            return [
                                'repuesto' => $repuesto,
                                'disponible' => (int) ($stock[(int) $repuesto->articulo_id] ?? 0),
                                'cantidad' => max(1, (int) ($repuesto->cantidad_servicio ?? 1)),
                                'automatico' => ($repuesto->modo_carga_servicio ?? 'manual') === 'automatico',
                            ];
                        })
                        ->values(),
                ];
            })
            ->filter(fn ($kit) => $kit['servicio'])
            ->sortBy(fn ($kit) => strtolower((string) $kit['servicio']->nombre))
            ->values();
    }

    private function crearPedidoPorFaltantesKit(OrdenTrabajo $orden, $faltantes): ?int
    {
        $orden->loadMissing('base');
        $depositoId = $orden->base?->deposito_id;

        if (! $depositoId || $faltantes->isEmpty()) {
            return null;
        }

        $pedido = PedidoArticulo::create([
            'deposito_id' => $depositoId,
            'usuario_id' => Auth::id(),
            'fecha_pedido' => now(),
            'estado' => 'pendiente',
            'notas' => 'Pedido generado por faltantes de kit de servicio en OT #' . $orden->id . ' - Interno ' . ($orden->flota?->nro_interno ?? '-'),
        ]);

        $faltantes
            ->groupBy('articulo_id')
            ->each(function ($items, $articuloId) use ($pedido) {
                PedidoDetalleArticulo::create([
                    'pedidos_articulo_id' => $pedido->id,
                    'articulo_id' => (int) $articuloId,
                    'cantidad' => (int) $items->sum('cantidad'),
                ]);
            });

        return (int) $pedido->id;
    }

    private function motivoVehiculoParadoLabels(): array
    {
        return [
            'repuestos' => 'Esperando repuestos',
            'terceros' => 'Esperando terceros',
            'taller' => 'Esperando taller',
            'compras' => 'Esperando compras',
            'autorizacion' => 'Esperando autorizacion',
            'otro' => 'Otro',
        ];
    }

    private function puedeModificarOrdenTrabajo(): bool
    {
        $user = Auth::user();

        return $user instanceof User && $user->isSuperUsuario();
    }
}
