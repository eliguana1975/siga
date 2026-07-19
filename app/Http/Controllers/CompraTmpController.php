<?php

namespace App\Http\Controllers;


use App\Models\CompraTmp;
use App\Models\Compra;
use App\Models\CompraDetalle;
use App\Models\Deposito;
use App\Models\DocumentoOperativo;
use App\Models\Articulo;
use App\Models\PedidoArticulo;
use App\Models\Proveedor;
use App\Mail\OrdenCompraProveedorMail;
use App\Support\CompraImpuestos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CompraTmpController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->get('search', ''));

        $compras = Compra::query()
            ->with(['deposito', 'proveedor', 'pedidoArticulo', 'usuario', 'detalles.articulo.unidadMedida', 'detalles.proveedor', 'pagos'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('comprobante', 'like', "%{$search}%")
                        ->orWhere('forma_pago', 'like', "%{$search}%")
                        ->orWhere('datos_pago', 'like', "%{$search}%")
                        ->orWhere('estado', 'like', "%{$search}%")
                        ->orWhere('notas', 'like', "%{$search}%")
                        ->when(ctype_digit($search), fn ($query) => $query
                            ->orWhere('id', (int) $search)
                            ->orWhere('pedido_articulo_id', (int) $search))
                        ->orWhereHas('deposito', fn ($deposito) => $deposito->where('nombre', 'like', "%{$search}%"))
                        ->orWhereHas('proveedor', fn ($proveedor) => $proveedor->where('nombre', 'like', "%{$search}%"))
                        ->orWhereHas('detalles.proveedor', fn ($proveedor) => $proveedor->where('nombre', 'like', "%{$search}%"))
                        ->orWhereHas('pagos', function ($pago) use ($search) {
                            $pago->where('nro_cheque', 'like', "%{$search}%")
                                ->orWhere('banco', 'like', "%{$search}%")
                                ->orWhere('nro_comprobante_pago', 'like', "%{$search}%")
                                ->orWhere('nro_transferencia', 'like', "%{$search}%")
                                ->orWhere('nro_recibo', 'like', "%{$search}%");
                        })
                        ->orWhereHas('usuario', fn ($usuario) => $usuario->where('name', 'like', "%{$search}%"));
                });
            })
            ->latest('fecha_compra')
            ->paginate(10)
            ->withQueryString();

        return view('admin.ordenes-compra.index', compact('compras', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $depositos = Deposito::orderBy('nombre')->get();
        $proveedores = Proveedor::orderBy('nombre')->get();
        $articulos = Articulo::with('unidadMedida')->orderBy('nombre')->get();
        $pedidos = PedidoArticulo::query()
            ->with(['deposito', 'detalles.articulo.unidadMedida'])
            ->doesntHave('compras')
            ->whereNotIn('estado', ['cancelado', 'ingresado'])
            ->latest('fecha_pedido')
            ->get();

        $selectedPedido = null;

        if ($request->boolean('clear')) {
            CompraTmp::query()
                ->where('usuario_id', Auth::id())
                ->delete();
        }

        if ($request->filled('pedido_articulo_id')) {
            $selectedPedido = PedidoArticulo::query()
                ->with(['deposito', 'detalles.articulo.unidadMedida'])
                ->withCount('compras')
                ->whereNotIn('estado', ['cancelado', 'ingresado'])
                ->findOrFail($request->integer('pedido_articulo_id'));

            if ($selectedPedido->compras_count > 0) {
                return redirect()
                    ->route('admin.pedidos-articulos.index')
                    ->with('info', 'Este pedido ya tiene una orden de compra generada.');
            }

            DB::transaction(function () use ($selectedPedido) {
                CompraTmp::query()
                    ->where('usuario_id', Auth::id())
                    ->delete();

                foreach ($selectedPedido->detalles as $detalle) {
                    CompraTmp::create([
                        'usuario_id' => Auth::id(),
                        'deposito_id' => $selectedPedido->deposito_id,
                        'articulo_id' => $detalle->articulo_id,
                        'precio_compra_unidad' => 0,
                        'cantidad' => $detalle->cantidad,
                        'fecha_creacion' => now(),
                        'estado' => 'pendiente',
                    ]);
                }
            });
        }

        $items = CompraTmp::query()
            ->with(['articulos.unidadMedida', 'deposito', 'proveedor'])
            ->where('usuario_id', Auth::id())
            ->orderBy('id')
            ->get();

        return view('admin.ordenes-compra.create', compact('depositos', 'proveedores', 'articulos', 'items', 'pedidos', 'selectedPedido'));
    }

    public function addItem(Request $request)
    {
        try {
            Log::info('Agregando item a compra temporal');
            Log::info('Usuario autenticado', ['user_id' => Auth::id()]);
            Log::info('Datos recibidos', $request->all());

            $validated = $request->validate([
                'deposito_id' => 'required|exists:depositos,id',
                'articulo_id' => 'required|exists:articulos,id',
                'proveedor_id' => 'nullable|exists:proveedores,id',
            ]);

            Log::info('Datos validados', $validated);

            $existingItem = CompraTmp::query()
                ->with(['articulos.unidadMedida', 'deposito'])
                ->where('usuario_id', Auth::id())
                ->where('deposito_id', $validated['deposito_id'])
                ->where('articulo_id', $validated['articulo_id'])
                ->first();

            if ($existingItem) {
                return response()->json([
                    'success' => true,
                    'item' => $existingItem,
                    'already_exists' => true,
                    'message' => 'El articulo ya esta cargado en la orden',
                ]);
            }

            $item = CompraTmp::create([
                'usuario_id' => Auth::id(),
                'deposito_id' => $validated['deposito_id'],
                'articulo_id' => $validated['articulo_id'],
                'proveedor_id' => $validated['proveedor_id'] ?? null,
                'precio_compra_unidad' => 0,
                'cantidad' => 1,
                'fecha_creacion' => now(),
                'estado' => 'pendiente',
            ]);

            Log::info('Item agregado a compra temporal', ['item_id' => $item->id]);
            $item->load(['articulos.unidadMedida', 'deposito', 'proveedor']);

            return response()->json([
                'success' => true,
                'item' => $item,
                'message' => 'Item agregado a compra temporal'
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $ve) {
            Log::warning('Validación fallida al agregar item a compra temporal', ['errors' => $ve->errors()]);
            return response()->json([
                'success' => false,
                'errors' => $ve->errors(),
            ], 422);
        } catch (\Illuminate\Database\QueryException $qe) {
            Log::error('Error de base de datos al agregar item a compra temporal', ['error' => $qe->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error de base de datos al agregar item a compra temporal',
            ], 500);
        } catch (\Throwable $e) {
            Log::error('Error inesperado al agregar item a compra temporal', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error inesperado al agregar item a compra temporal',
            ], 500);
        }
    }

    public function updateItem(Request $request, string $itemid)
    {
        try {
            if ($request->filled('precio_compra_unidad')) {
                $request->merge([
                    'precio_compra_unidad' => $this->normalizeDecimal($request->input('precio_compra_unidad')),
                ]);
            }

            Log::info('Actualizando item de compra temporal', [
                'item_id' => $itemid,
                'user_id' => Auth::id(),
                'data' => $request->all(),
            ]);

            $item = CompraTmp::query()
                ->where('usuario_id', Auth::id())
                ->findOrFail($itemid);

            $validated = $request->validate([
                'deposito_id' => 'sometimes|required|exists:depositos,id',
                'articulo_id' => 'sometimes|required|exists:articulos,id',
                'proveedor_id' => 'sometimes|nullable|exists:proveedores,id',
                'precio_compra_unidad' => 'sometimes|required|numeric|min:0',
                'cantidad' => 'sometimes|required|integer|min:1',
                'estado' => 'sometimes|required|in:activo,inactivo,pendiente,confirmado,cancelado',
            ]);

            $item->update($validated);
            $item->load(['articulos.unidadMedida', 'deposito', 'proveedor', 'usuario']);

            return response()->json([
                'success' => true,
                'item' => $item,
                'subtotal' => (float) $item->precio_compra_unidad * (int) $item->cantidad,
                'message' => 'Item actualizado correctamente',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('Item de compra temporal no encontrado', [
                'item_id' => $itemid,
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Item no encontrado',
            ], 404);
        } catch (\Illuminate\Validation\ValidationException $ve) {
            Log::warning('Validacion fallida al actualizar item de compra temporal', ['errors' => $ve->errors()]);

            return response()->json([
                'success' => false,
                'errors' => $ve->errors(),
            ], 422);
        } catch (\Illuminate\Database\QueryException $qe) {
            Log::error('Error de base de datos al actualizar item de compra temporal', ['error' => $qe->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Error de base de datos al actualizar item de compra temporal',
            ], 500);
        } catch (\Throwable $e) {
            Log::error('Error inesperado al actualizar item de compra temporal', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Error inesperado al actualizar item de compra temporal',
            ], 500);
        }
    }

    public function removeItem(string $itemid)
    {
        try {
            Log::info('Eliminando item de compra temporal', [
                'item_id' => $itemid,
                'user_id' => Auth::id(),
            ]);

            $item = CompraTmp::query()
                ->where('usuario_id', Auth::id())
                ->findOrFail($itemid);

            $item->delete();

            return response()->json([
                'success' => true,
                'message' => 'Item eliminado correctamente',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('Item de compra temporal no encontrado para eliminar', [
                'item_id' => $itemid,
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Item no encontrado',
            ], 404);
        } catch (\Illuminate\Database\QueryException $qe) {
            Log::error('Error de base de datos al eliminar item de compra temporal', ['error' => $qe->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Error de base de datos al eliminar item de compra temporal',
            ], 500);
        } catch (\Throwable $e) {
            Log::error('Error inesperado al eliminar item de compra temporal', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Error inesperado al eliminar item de compra temporal',
            ], 500);
        }
    }

    public function clearItem()
    {
        try {
            Log::info('Limpiando compra temporal', [
                'user_id' => Auth::id(),
            ]);

            $deleted = CompraTmp::query()
                ->where('usuario_id', Auth::id())
                ->delete();

            return response()->json([
                'success' => true,
                'deleted' => $deleted,
                'message' => 'Compra temporal limpiada correctamente',
            ]);
        } catch (\Illuminate\Database\QueryException $qe) {
            Log::error('Error de base de datos al limpiar compra temporal', ['error' => $qe->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Error de base de datos al limpiar compra temporal',
            ], 500);
        } catch (\Throwable $e) {
            Log::error('Error inesperado al limpiar compra temporal', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Error inesperado al limpiar compra temporal',
            ], 500);
        }
    }



    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Log::info('Creando orden de compra', [
            'user_id' => Auth::id(),
            'pedido_articulo_id' => $request->input('pedido_articulo_id'),
        ]);

        $validated = $request->validate([
            'pedido_articulo_id' => ['nullable', 'exists:pedidos_articulo,id'],
            'comprobante' => ['nullable', 'string', 'max:100'],
            'notas' => ['nullable', 'string'],
        ]);

        $items = CompraTmp::query()
            ->where('usuario_id', Auth::id())
            ->get();

        if ($items->isEmpty()) {
            return redirect()
                ->route('admin.ordenes-compra.create')
                ->with('error', 'Debe agregar al menos un articulo a la orden.');
        }

        if ($items->pluck('deposito_id')->unique()->count() > 1) {
            return redirect()
                ->route('admin.ordenes-compra.create')
                ->with('error', 'Todos los articulos deben pertenecer al mismo deposito.');
        }

        if (! empty($validated['pedido_articulo_id'])) {
            $yaTieneOrden = PedidoArticulo::query()
                ->whereKey($validated['pedido_articulo_id'])
                ->whereHas('compras')
                ->exists();

            if ($yaTieneOrden) {
                return redirect()
                    ->route('admin.pedidos-articulos.index')
                    ->with('info', 'Este pedido ya tiene una orden de compra generada.');
            }
        }

        $createdCount = DB::transaction(function () use ($items, $validated) {
            $itemsByProveedor = $items->groupBy(function ($item) {
                return (string) ($item->proveedor_id ?? 'sin_proveedor');
            });

            foreach ($itemsByProveedor as $proveedorKey => $proveedorItems) {
                $proveedorId = $proveedorKey === 'sin_proveedor' ? null : (int) $proveedorKey;
                $proveedor = $proveedorId ? Proveedor::find($proveedorId) : null;

                $compra = Compra::create([
                    'deposito_id' => $proveedorItems->first()->deposito_id,
                    'proveedor_id' => $proveedorId,
                    'pedido_articulo_id' => $validated['pedido_articulo_id'] ?? null,
                    'usuario_id' => Auth::id(),
                    'fecha_compra' => now(),
                    'total_compra' => $proveedorItems->sum(fn ($item) => (float) $item->precio_compra_unidad * (int) $item->cantidad),
                    'estado' => 'pendiente',
                    'comprobante' => $validated['comprobante'] ?? null,
                    'forma_pago' => $proveedor?->forma_pago_preferida,
                    'datos_pago' => $proveedor?->datos_pago,
                    'notas' => $validated['notas'] ?? null,
                ]);

                foreach ($proveedorItems as $item) {
                    CompraDetalle::create([
                        'compra_id' => $compra->id,
                        'articulo_id' => $item->articulo_id,
                        'proveedor_id' => $proveedorId,
                        'precio_compra_unidad' => $item->precio_compra_unidad,
                        'cantidad' => $item->cantidad,
                    ]);
                }
            }

            CompraTmp::query()
                ->where('usuario_id', Auth::id())
                ->delete();

            if (! empty($validated['pedido_articulo_id'])) {
                PedidoArticulo::query()
                    ->where('id', $validated['pedido_articulo_id'])
                    ->where('estado', 'pendiente')
                    ->update(['estado' => 'confirmado']);
            }

            return $itemsByProveedor->count();
        });

        $message = $createdCount === 1
            ? 'Orden de compra creada correctamente.'
            : "Se crearon {$createdCount} ordenes de compra, una por cada proveedor.";

        return redirect()
            ->route('admin.ordenes-compra.index')
            ->with('success', $message);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $compra = Compra::with([
            'deposito',
            'proveedor',
            'pedidoArticulo.detalles.articulo.unidadMedida',
            'usuario',
            'detalles.articulo.unidadMedida',
            'detalles.proveedor',
            'detalles.detallesEntrada',
            'pagos.proveedor',
            'pagos.usuario',
        ])->findOrFail($id);

        $impuestosPago = CompraImpuestos::disponiblesParaPago($compra);
        $documentos = DocumentoOperativo::query()
            ->with('usuario:id,name')
            ->where('documentable_type', Compra::class)
            ->where('documentable_id', $compra->id)
            ->latest()
            ->get();

        return view('admin.ordenes-compra.show', compact('compra', 'impuestosPago', 'documentos'));
    }

    public function enviarMail(string $id)
    {
        $compra = Compra::with(['deposito', 'proveedor', 'pedidoArticulo', 'usuario', 'detalles.articulo.unidadMedida', 'detalles.proveedor', 'pagos'])->findOrFail($id);
        $proveedor = $compra->proveedor ?: $compra->detalles->pluck('proveedor')->filter()->unique('id')->first();
        $email = trim((string) ($proveedor?->email ?? ''));

        if ($email === '') {
            return redirect()
                ->route('admin.ordenes-compra.index')
                ->with('error', 'El proveedor de la orden no tiene email cargado.');
        }

        if (in_array(config('mail.default'), ['log', 'array'], true)) {
            return redirect()
                ->route('admin.ordenes-compra.index')
                ->with('error', 'El correo no se envio: la aplicacion esta configurada con MAIL_MAILER=' . config('mail.default') . '. Configure SMTP para enviar mails reales.');
        }

        Mail::to($email)->send(new OrdenCompraProveedorMail($compra));

        return redirect()
            ->route('admin.ordenes-compra.index')
            ->with('success', 'Orden de compra #' . $compra->id . ' enviada a ' . $email . '.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $compra = Compra::with([
            'deposito',
            'proveedor',
            'pedidoArticulo',
            'usuario',
            'detalles.articulo.unidadMedida',
            'detalles.proveedor',
            'pagos.proveedor',
            'pagos.usuario',
        ])->findOrFail($id);
        $depositos = Deposito::orderBy('nombre')->get();
        $proveedores = Proveedor::orderBy('nombre')->get();
        $articulos = Articulo::with('unidadMedida')->orderBy('nombre')->get();
        $impuestosPago = CompraImpuestos::disponiblesParaPago($compra);
        $pedidos = PedidoArticulo::query()
            ->with('deposito')
            ->whereNotIn('estado', ['cancelado', 'ingresado'])
            ->latest('fecha_pedido')
            ->get();

        return view('admin.ordenes-compra.edit', compact('compra', 'depositos', 'proveedores', 'articulos', 'pedidos', 'impuestosPago'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $compra = Compra::findOrFail($id);

        $validated = $request->validate([
            'deposito_id' => ['required', 'exists:depositos,id'],
            'proveedor_id' => ['nullable', 'exists:proveedores,id'],
            'pedido_articulo_id' => ['nullable', 'exists:pedidos_articulo,id'],
            'fecha_compra' => ['required', 'date'],
            'estado' => ['required', 'in:pendiente,aprobada,recibido,cancelado'],
            'forma_pago' => ['nullable', 'in:' . implode(',', array_keys(Compra::formasPago()))],
            'datos_pago' => ['nullable', 'string'],
            'notas' => ['nullable', 'string'],
        ]);

        $compra->update($validated);

        if (! empty($validated['pedido_articulo_id'])) {
            PedidoArticulo::query()
                ->where('id', $validated['pedido_articulo_id'])
                ->where('estado', 'pendiente')
                ->update(['estado' => 'confirmado']);
        }

        $this->refreshPedidoRecepcion($compra->pedido_articulo_id);

        $this->refreshCompraTotal($compra);

        return redirect()
            ->route('admin.ordenes-compra.index')
            ->with('success', 'Orden de compra actualizada correctamente.');
    }

    public function storeDetalle(Request $request, string $compraId)
    {
        return response()->json([
            'success' => false,
            'message' => 'El detalle de la orden de compra es de solo lectura.',
        ], 403);

        $compra = Compra::findOrFail($compraId);

        $validated = $request->validate([
            'articulo_id' => ['required', 'exists:articulos,id'],
            'proveedor_id' => ['nullable', 'exists:proveedores,id'],
        ]);

        $detalle = CompraDetalle::query()
            ->with('articulo.unidadMedida')
            ->where('compra_id', $compra->id)
            ->where('articulo_id', $validated['articulo_id'])
            ->first();

        if ($detalle) {
            return response()->json([
                'success' => true,
                'detalle' => $detalle,
                'already_exists' => true,
                'total' => (float) $compra->total_compra,
                'message' => 'El articulo ya esta cargado en la orden.',
            ]);
        }

        $detalle = CompraDetalle::create([
            'compra_id' => $compra->id,
            'articulo_id' => $validated['articulo_id'],
            'proveedor_id' => $validated['proveedor_id'] ?? $compra->proveedor_id,
            'precio_compra_unidad' => 0,
            'cantidad' => 1,
        ]);

        $detalle->load(['articulo.unidadMedida', 'proveedor']);
        $total = $this->refreshCompraTotal($compra);

        return response()->json([
            'success' => true,
            'detalle' => $detalle,
            'total' => $total,
            'message' => 'Articulo agregado correctamente.',
        ], 201);
    }

    public function updateDetalle(Request $request, string $compraId, string $detalleId)
    {
        return response()->json([
            'success' => false,
            'message' => 'El detalle de la orden de compra es de solo lectura.',
        ], 403);

        if ($request->filled('precio_compra_unidad')) {
            $request->merge([
                'precio_compra_unidad' => $this->normalizeDecimal($request->input('precio_compra_unidad')),
            ]);
        }

        $compra = Compra::findOrFail($compraId);
        $detalle = CompraDetalle::query()
            ->where('compra_id', $compra->id)
            ->findOrFail($detalleId);

        $validated = $request->validate([
            'cantidad' => ['sometimes', 'required', 'integer', 'min:1'],
            'proveedor_id' => ['sometimes', 'nullable', 'exists:proveedores,id'],
            'precio_compra_unidad' => ['sometimes', 'required', 'numeric', 'min:0'],
        ]);

        $detalle->update($validated);
        $detalle->load(['articulo.unidadMedida', 'proveedor']);
        $total = $this->refreshCompraTotal($compra);

        return response()->json([
            'success' => true,
            'detalle' => $detalle,
            'subtotal' => (float) $detalle->precio_compra_unidad * (int) $detalle->cantidad,
            'total' => $total,
            'message' => 'Detalle actualizado correctamente.',
        ]);
    }

    public function destroyDetalle(string $compraId, string $detalleId)
    {
        return response()->json([
            'success' => false,
            'message' => 'El detalle de la orden de compra es de solo lectura.',
        ], 403);

        $compra = Compra::findOrFail($compraId);
        $detalle = CompraDetalle::query()
            ->where('compra_id', $compra->id)
            ->findOrFail($detalleId);

        $detalle->delete();
        $total = $this->refreshCompraTotal($compra);

        return response()->json([
            'success' => true,
            'deleted' => true,
            'total' => $total,
            'message' => 'Articulo eliminado correctamente.',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $compra = Compra::findOrFail($id);
        $pedidoId = $compra->pedido_articulo_id;
        $compra->delete();
        $this->refreshPedidoRecepcion($pedidoId);

        return redirect()
            ->route('admin.ordenes-compra.index')
            ->with('success', 'Orden de compra eliminada correctamente.');
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

    private function refreshCompraTotal(Compra $compra): float
    {
        $total = (float) $compra->detalles()
            ->selectRaw('COALESCE(SUM(precio_compra_unidad * cantidad), 0) as total')
            ->value('total');

        $compra->forceFill(['total_compra' => $total])->save();

        return $total;
    }

    private function refreshPedidoRecepcion(?int $pedidoId): void
    {
        if (! $pedidoId) {
            return;
        }

        $pedido = PedidoArticulo::with('compras')->find($pedidoId);

        if (! $pedido || $pedido->estado === 'cancelado') {
            return;
        }

        $compras = $pedido->compras
            ->reject(fn (Compra $compra) => $compra->estado === 'cancelado')
            ->values();

        if ($compras->isEmpty()) {
            if ($pedido->estado === 'ingresado') {
                $pedido->forceFill(['estado' => 'confirmado'])->save();
            }

            return;
        }

        $allReceived = $compras->every(fn (Compra $compra) => $compra->estado === 'recibido');

        $pedido->forceFill([
            'estado' => $allReceived ? 'ingresado' : 'confirmado',
        ])->save();
    }
}
