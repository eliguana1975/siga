<?php

namespace App\Http\Controllers;

use App\Models\Articulo;
use App\Models\Bitacora;
use App\Models\Categoria;
use App\Models\Deposito;
use App\Models\DocumentoOperativo;
use App\Models\Flota;
use App\Models\OrdenTrabajo;
use App\Models\PedidoArticulo;
use App\Models\PedidoDetalleArticulo;
use App\Models\SolicitudRepuesto;
use App\Models\UnidadMedida;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SolicitudRepuestoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('check.permission:solicitudes-repuestos.ver')->only(['index', 'show']);
        $this->middleware('check.permission:solicitudes-repuestos.crear')->only(['create', 'store']);
        $this->middleware('check.permission:solicitudes-repuestos.editar')->only(['edit', 'update']);
        $this->middleware('check.permission:solicitudes-repuestos.aprobar')->only(['aprobar']);
        $this->middleware('check.permission:solicitudes-repuestos.rechazar')->only(['rechazar']);
        $this->middleware('check.permission:solicitudes-repuestos.catalogar')->only(['asociarArticulo', 'crearArticulo']);
        $this->middleware('check.permission:solicitudes-repuestos.generar-pedido')->only(['generarPedido']);
        $this->middleware('check.permission:solicitudes-repuestos.cerrar')->only(['actualizarEstado']);
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $estado = (string) $request->input('estado', '');

        $solicitudes = SolicitudRepuesto::query()
            ->with(['solicitante:id,name', 'flota:id,nro_interno,dominio,nro_chasis', 'ordenTrabajo:id,titulo', 'articulo:id,nombre,codigo_producto', 'pedidoArticulo:id,estado'])
            ->when($estado !== '', fn ($query) => $query->where('estado', $estado))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('descripcion_repuesto', 'like', "%{$search}%")
                        ->orWhere('codigo_repuesto', 'like', "%{$search}%")
                        ->orWhere('nro_chasis', 'like', "%{$search}%")
                        ->orWhere('proveedor_sugerido', 'like', "%{$search}%")
                        ->orWhereHas('flota', fn ($flota) => $flota->where('nro_interno', 'like', "%{$search}%")
                            ->orWhere('dominio', 'like', "%{$search}%")
                            ->orWhere('nro_chasis', 'like', "%{$search}%"))
                        ->orWhereHas('solicitante', fn ($usuario) => $usuario->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('articulo', fn ($articulo) => $articulo->where('nombre', 'like', "%{$search}%")
                            ->orWhere('codigo_producto', 'like', "%{$search}%"));
                });
            })
            ->latest('fecha_solicitud')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('admin.solicitudes-repuestos.index', compact('solicitudes', 'search', 'estado'));
    }

    public function create(Request $request)
    {
        $orden = null;

        if ($request->filled('orden_trabajo_id')) {
            $orden = OrdenTrabajo::with('flota:id,nro_chasis')
                ->select('id', 'titulo', 'flota_id')
                ->find($request->integer('orden_trabajo_id'));
        }

        $flotaId = $orden?->flota_id ?: ($request->integer('flota_id') ?: null);
        $nroChasis = $orden?->flota?->nro_chasis ?: null;

        return view('admin.solicitudes-repuestos.create', $this->formData($orden?->id) + [
            'solicitud' => new SolicitudRepuesto([
                'flota_id' => $flotaId,
                'orden_trabajo_id' => $orden?->id ?: ($request->integer('orden_trabajo_id') ?: null),
                'nro_chasis' => $nroChasis,
                'motivo' => $orden ? "Pedido generado desde OT #{$orden->id} - {$orden->titulo}" : null,
                'cantidad' => 1,
                'prioridad' => 'normal',
                'fecha_solicitud' => now(),
            ]),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validatedData($request);
        $validated['solicitante_user_id'] = Auth::id();
        $validated['fecha_solicitud'] = now();
        $validated['estado'] = 'pendiente';

        foreach (['foto_repuesto_path' => 'foto_repuesto', 'foto_contexto_path' => 'foto_contexto'] as $target => $field) {
            if ($request->hasFile($field)) {
                $validated[$target] = Storage::disk('public')->putFile('solicitudes-repuestos', $request->file($field));
            }
        }

        $solicitud = SolicitudRepuesto::create($validated);

        return redirect()
            ->route('admin.solicitudes-repuestos.show', $solicitud)
            ->with('success', 'Solicitud de repuesto registrada correctamente.');
    }

    public function show(string $id)
    {
        $solicitud = SolicitudRepuesto::with([
            'solicitante:id,name',
            'procesadoPor:id,name',
            'flota:id,nro_interno,dominio,nro_chasis',
            'ordenTrabajo:id,titulo,estado',
            'articulo.unidadMedida',
            'pedidoArticulo.detalles.articulo.unidadMedida',
            'deposito:id,nombre',
        ])->findOrFail($id);

        $historial = Bitacora::query()
            ->with('usuario:id,name')
            ->where('entidad_type', SolicitudRepuesto::class)
            ->where('entidad_id', $solicitud->id)
            ->latest()
            ->latest('id')
            ->limit(30)
            ->get();
        $documentos = DocumentoOperativo::query()
            ->with('usuario:id,name')
            ->where('documentable_type', SolicitudRepuesto::class)
            ->where('documentable_id', $solicitud->id)
            ->latest()
            ->get();

        return view('admin.solicitudes-repuestos.show', $this->formData($solicitud->orden_trabajo_id) + compact('solicitud', 'historial', 'documentos'));
    }

    public function edit(string $id)
    {
        $solicitud = SolicitudRepuesto::findOrFail($id);

        return view('admin.solicitudes-repuestos.edit', $this->formData($solicitud->orden_trabajo_id) + compact('solicitud'));
    }

    public function update(Request $request, string $id)
    {
        $solicitud = SolicitudRepuesto::findOrFail($id);
        $validated = $this->validatedData($request);

        foreach (['foto_repuesto_path' => 'foto_repuesto', 'foto_contexto_path' => 'foto_contexto'] as $target => $field) {
            if ($request->hasFile($field)) {
                $validated[$target] = Storage::disk('public')->putFile('solicitudes-repuestos', $request->file($field));
            }
        }

        $solicitud->update($validated);

        return redirect()
            ->route('admin.solicitudes-repuestos.show', $solicitud)
            ->with('success', 'Solicitud actualizada correctamente.');
    }

    public function aprobar(Request $request, string $id)
    {
        $solicitud = SolicitudRepuesto::findOrFail($id);
        $antes = $this->auditPayload($solicitud);
        $validated = $request->validate([
            'proveedor_sugerido' => ['nullable', 'string', 'max:160'],
            'observaciones_compras' => ['nullable', 'string'],
        ]);

        $solicitud->update([
            'estado' => 'aprobado',
            'procesado_por_user_id' => Auth::id(),
            'proveedor_sugerido' => $validated['proveedor_sugerido'] ?? $solicitud->proveedor_sugerido,
            'observaciones_compras' => $validated['observaciones_compras'] ?? $solicitud->observaciones_compras,
        ]);

        $this->registrarBitacoraSolicitud($request, 'aprobar', $solicitud->fresh(), $antes, 'Solicitud aprobada.');

        return back()->with('success', 'Solicitud aprobada correctamente.');
    }

    public function rechazar(Request $request, string $id)
    {
        $solicitud = SolicitudRepuesto::findOrFail($id);
        $antes = $this->auditPayload($solicitud);
        $validated = $request->validate(['observaciones_compras' => ['required', 'string']]);

        $solicitud->update([
            'estado' => 'rechazado',
            'procesado_por_user_id' => Auth::id(),
            'observaciones_compras' => $validated['observaciones_compras'],
        ]);

        $this->registrarBitacoraSolicitud($request, 'rechazar', $solicitud->fresh(), $antes, 'Solicitud rechazada.');

        return back()->with('success', 'Solicitud rechazada correctamente.');
    }

    public function asociarArticulo(Request $request, string $id)
    {
        $solicitud = SolicitudRepuesto::findOrFail($id);
        $antes = $this->auditPayload($solicitud);
        $validated = $request->validate([
            'articulo_id' => ['required', 'integer', 'exists:articulos,id'],
        ]);

        $solicitud->update([
            'articulo_id' => $validated['articulo_id'],
            'estado' => 'catalogado',
            'procesado_por_user_id' => Auth::id(),
        ]);

        $this->registrarBitacoraSolicitud($request, 'catalogar', $solicitud->fresh(), $antes, 'Articulo asociado a la solicitud.');

        return back()->with('success', 'Articulo asociado a la solicitud.');
    }

    public function crearArticulo(Request $request, string $id)
    {
        $solicitud = SolicitudRepuesto::findOrFail($id);
        $antes = $this->auditPayload($solicitud);
        $validated = $request->validate([
            'categoria_id' => ['required', 'exists:categorias,id'],
            'unidad_medida_id' => ['required', 'exists:unidad_medidas,id'],
            'nombre' => ['required', 'string', 'max:255'],
            'codigo_producto' => ['nullable', 'string', 'max:255'],
            'stock_minimo' => ['nullable', 'integer', 'min:0'],
            'stock_maximo' => ['nullable', 'integer', 'min:0'],
            'stock_pedido' => ['nullable', 'integer', 'min:0'],
            'observaciones' => ['nullable', 'string'],
        ]);

        $articulo = Articulo::create([
            'categoria_id' => $validated['categoria_id'],
            'unidad_medida_id' => $validated['unidad_medida_id'],
            'nombre' => mb_strtoupper(trim((string) $validated['nombre']), 'UTF-8'),
            'codigo_producto' => isset($validated['codigo_producto']) ? mb_strtoupper(trim((string) $validated['codigo_producto']), 'UTF-8') : null,
            'stock_minimo' => (int) ($validated['stock_minimo'] ?? 0),
            'stock_maximo' => (int) ($validated['stock_maximo'] ?? 0),
            'stock_pedido' => (int) ($validated['stock_pedido'] ?? 0),
            'reposicion_modo' => 'manual',
            'estado_item' => 'activo',
            'observaciones' => trim(($validated['observaciones'] ?? '') . "\nSolicitud repuesto #{$solicitud->id}") ?: null,
        ]);

        $solicitud->update([
            'articulo_id' => $articulo->id,
            'estado' => 'catalogado',
            'procesado_por_user_id' => Auth::id(),
        ]);

        $this->registrarBitacoraSolicitud($request, 'crear_articulo', $solicitud->fresh(), $antes, 'Articulo creado y asociado a la solicitud.', [
            'articulo_id' => $articulo->id,
        ]);

        return back()->with('success', 'Articulo creado y asociado a la solicitud.');
    }

    public function generarPedido(Request $request, string $id)
    {
        $solicitud = SolicitudRepuesto::with('articulo')->findOrFail($id);
        $antes = $this->auditPayload($solicitud);

        if (! $solicitud->articulo_id) {
            return back()->with('error', 'Primero debe catalogar o asociar un articulo.');
        }

        $validated = $request->validate([
            'deposito_id' => ['required', 'exists:depositos,id'],
            'cantidad' => ['required', 'integer', 'min:1'],
            'notas' => ['nullable', 'string'],
        ]);

        $pedidoId = null;

        DB::transaction(function () use ($solicitud, $validated, &$pedidoId) {
            $pedido = PedidoArticulo::create([
                'deposito_id' => $validated['deposito_id'],
                'usuario_id' => Auth::id(),
                'fecha_pedido' => now(),
                'estado' => 'pendiente',
                'notas' => trim(($validated['notas'] ?? '') . "\nGenerado desde solicitud de repuesto #{$solicitud->id}") ?: null,
            ]);

            $pedidoId = $pedido->id;

            PedidoDetalleArticulo::create([
                'pedidos_articulo_id' => $pedido->id,
                'articulo_id' => $solicitud->articulo_id,
                'cantidad' => $validated['cantidad'],
            ]);

            $solicitud->update([
                'pedido_articulo_id' => $pedido->id,
                'deposito_id' => $validated['deposito_id'],
                'cantidad' => $validated['cantidad'],
                'estado' => 'pedido_generado',
                'procesado_por_user_id' => Auth::id(),
            ]);
        });

        $this->registrarBitacoraSolicitud($request, 'generar_pedido', $solicitud->fresh(), $antes, 'Pedido generado desde solicitud.', [
            'pedido_articulo_id' => $pedidoId,
        ]);

        return back()->with('success', 'Pedido de articulos generado desde la solicitud.');
    }

    public function actualizarEstado(Request $request, string $id)
    {
        $solicitud = SolicitudRepuesto::findOrFail($id);
        $antes = $this->auditPayload($solicitud);
        $validated = $request->validate([
            'estado' => ['required', Rule::in(['comprado', 'ingresado', 'entregado_taller', 'colocado', 'cerrado'])],
            'observaciones_compras' => ['nullable', 'string'],
        ]);

        $solicitud->update([
            'estado' => $validated['estado'],
            'procesado_por_user_id' => Auth::id(),
            'observaciones_compras' => $validated['observaciones_compras'] ?? $solicitud->observaciones_compras,
        ]);

        $this->registrarBitacoraSolicitud($request, 'cambiar_estado', $solicitud->fresh(), $antes, 'Estado de solicitud actualizado.');

        return back()->with('success', 'Estado de la solicitud actualizado.');
    }

    public function bulk(Request $request)
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:solicitudes_repuestos,id'],
            'accion_masiva' => ['required', Rule::in(['aprobar', 'rechazar', 'estado'])],
            'estado' => ['nullable', Rule::in(['comprado', 'ingresado', 'entregado_taller', 'colocado', 'cerrado'])],
            'observaciones_compras' => ['nullable', 'string'],
        ]);

        $permission = match ($validated['accion_masiva']) {
            'aprobar' => 'solicitudes-repuestos.aprobar',
            'rechazar' => 'solicitudes-repuestos.rechazar',
            default => 'solicitudes-repuestos.cerrar',
        };
        $user = $request->user();

        abort_unless($user && ($user->isSuperUsuario() || $user->can($permission)), 403);

        if ($validated['accion_masiva'] === 'rechazar' && blank($validated['observaciones_compras'] ?? null)) {
            return back()->with('error', 'Para rechazar en lote debe indicar una observacion.');
        }

        if ($validated['accion_masiva'] === 'estado' && blank($validated['estado'] ?? null)) {
            return back()->with('error', 'Seleccione el estado de destino para la accion masiva.');
        }

        $actualizadas = 0;
        $omitidas = 0;

        SolicitudRepuesto::query()
            ->whereIn('id', $validated['ids'])
            ->orderBy('id')
            ->get()
            ->each(function (SolicitudRepuesto $solicitud) use ($request, $validated, &$actualizadas, &$omitidas) {
                $antes = $this->auditPayload($solicitud);
                $updates = [
                    'procesado_por_user_id' => Auth::id(),
                ];
                $descripcion = 'Solicitud actualizada en lote.';
                $accion = 'masivo';

                if ($validated['accion_masiva'] === 'aprobar') {
                    if (! in_array($solicitud->estado, ['pendiente', 'en_revision'], true)) {
                        $omitidas++;
                        return;
                    }

                    $updates['estado'] = 'aprobado';
                    $updates['observaciones_compras'] = $validated['observaciones_compras'] ?? $solicitud->observaciones_compras;
                    $descripcion = 'Solicitud aprobada en lote.';
                    $accion = 'aprobar_masivo';
                }

                if ($validated['accion_masiva'] === 'rechazar') {
                    if (in_array($solicitud->estado, ['cerrado', 'colocado'], true)) {
                        $omitidas++;
                        return;
                    }

                    $updates['estado'] = 'rechazado';
                    $updates['observaciones_compras'] = $validated['observaciones_compras'];
                    $descripcion = 'Solicitud rechazada en lote.';
                    $accion = 'rechazar_masivo';
                }

                if ($validated['accion_masiva'] === 'estado') {
                    $updates['estado'] = $validated['estado'];
                    $updates['observaciones_compras'] = $validated['observaciones_compras'] ?? $solicitud->observaciones_compras;
                    $descripcion = 'Estado de solicitud actualizado en lote.';
                    $accion = 'estado_masivo';
                }

                $solicitud->update($updates);
                $actualizadas++;

                $this->registrarBitacoraSolicitud($request, $accion, $solicitud->fresh(), $antes, $descripcion, [
                    'accion_masiva' => $validated['accion_masiva'],
                    'ids_solicitados' => $validated['ids'],
                ]);
            });

        return back()->with('success', "Accion masiva aplicada a {$actualizadas} solicitud(es). Omitidas: {$omitidas}.");
    }

    private function registrarBitacoraSolicitud(Request $request, string $accion, SolicitudRepuesto $solicitud, array $antes, string $descripcion, array $metadata = []): void
    {
        $usuario = $request->user();

        Bitacora::create([
            'user_id' => $usuario?->id,
            'usuario_nombre' => $usuario?->name,
            'accion' => 'solicitudes_repuestos.' . $accion,
            'modulo' => 'Solicitudes de repuestos',
            'entidad_type' => SolicitudRepuesto::class,
            'entidad_id' => $solicitud->id,
            'descripcion' => $descripcion . ' #' . $solicitud->id,
            'datos_anteriores' => $antes,
            'datos_nuevos' => $this->auditPayload($solicitud),
            'metadata' => $metadata + [
                'estado_anterior' => $antes['estado'] ?? null,
                'estado_nuevo' => $solicitud->estado,
            ],
            'ip_address' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'route_name' => $request->route()?->getName(),
        ]);
    }

    private function auditPayload(SolicitudRepuesto $solicitud): array
    {
        return $solicitud->only([
            'id',
            'estado',
            'prioridad',
            'cantidad',
            'descripcion_repuesto',
            'codigo_repuesto',
            'proveedor_sugerido',
            'observaciones_taller',
            'observaciones_compras',
            'articulo_id',
            'pedido_articulo_id',
            'deposito_id',
            'procesado_por_user_id',
        ]);
    }

    private function validatedData(Request $request): array
    {
        $validated = $request->validate([
            'flota_id' => ['nullable', 'integer', 'exists:flota,id'],
            'orden_trabajo_id' => ['nullable', 'integer', 'exists:ordenes_trabajo,id'],
            'prioridad' => ['required', Rule::in(array_keys(SolicitudRepuesto::PRIORIDADES))],
            'cantidad' => ['required', 'integer', 'min:1'],
            'descripcion_repuesto' => ['required', 'string', 'max:255'],
            'codigo_repuesto' => ['nullable', 'string', 'max:120'],
            'nro_chasis' => ['nullable', 'string', 'max:80'],
            'proveedor_sugerido' => ['nullable', 'string', 'max:160'],
            'motivo' => ['nullable', 'string'],
            'observaciones_taller' => ['nullable', 'string'],
            'foto_repuesto' => ['nullable', 'image', 'max:4096'],
            'foto_contexto' => ['nullable', 'image', 'max:4096'],
        ]);

        foreach (['descripcion_repuesto', 'codigo_repuesto', 'nro_chasis'] as $field) {
            if (! empty($validated[$field])) {
                $validated[$field] = mb_strtoupper(trim((string) $validated[$field]), 'UTF-8');
            }
        }

        return $validated;
    }

    private function formData(?int $selectedOrdenId = null): array
    {
        $ordenesTrabajo = OrdenTrabajo::query()
            ->select('id', 'titulo', 'flota_id', 'estado')
            ->latest('id')
            ->limit(80)
            ->get();

        if ($selectedOrdenId && ! $ordenesTrabajo->contains('id', $selectedOrdenId)) {
            $ordenSeleccionada = OrdenTrabajo::query()
                ->select('id', 'titulo', 'flota_id', 'estado')
                ->find($selectedOrdenId);

            if ($ordenSeleccionada) {
                $ordenesTrabajo->prepend($ordenSeleccionada);
            }
        }

        return [
            'flotas' => Flota::query()->select('id', 'nro_interno', 'dominio', 'nro_chasis')->orderBy('nro_interno')->get(),
            'ordenesTrabajo' => $ordenesTrabajo,
            'articulos' => Articulo::query()->select('id', 'nombre', 'codigo_producto', 'unidad_medida_id')->with('unidadMedida:id,nombre')->orderBy('nombre')->get(),
            'categorias' => Categoria::query()->select('id', 'nombre')->orderBy('nombre')->get(),
            'unidadesMedida' => UnidadMedida::query()->select('id', 'nombre')->orderBy('nombre')->get(),
            'depositos' => Deposito::query()->select('id', 'nombre')->orderBy('nombre')->get(),
        ];
    }
}
