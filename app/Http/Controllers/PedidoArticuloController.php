<?php

namespace App\Http\Controllers;

use App\Models\Articulo;
use App\Models\Ajuste;
use App\Models\CompraDetalle;
use App\Models\Deposito;
use App\Models\Inventario;
use App\Models\PedidoArticulo;
use App\Models\PedidoDetalleArticulo;
use App\Models\TmpPedidoArticulo;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PedidoArticuloController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->get('search', ''));
        $pedidosAutomaticosActivos = $this->pedidosAutomaticosActivos();

        $pedidos = PedidoArticulo::query()
            ->with(['deposito', 'usuario', 'detalles.articulo.unidadMedida'])
            ->withCount(['detalles', 'compras'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('estado', 'like', "%{$search}%")
                        ->orWhere('notas', 'like', "%{$search}%")
                        ->orWhereHas('deposito', fn ($deposito) => $deposito->where('nombre', 'like', "%{$search}%"))
                        ->orWhereHas('usuario', fn ($usuario) => $usuario->where('name', 'like', "%{$search}%"));
                });
            })
            ->latest('fecha_pedido')
            ->paginate(10)
            ->withQueryString();

        $sugeridos = $pedidosAutomaticosActivos ? $this->articulosEnNivelPedido() : collect();

        return view('admin.pedidos-articulos.index', compact('pedidos', 'search', 'sugeridos', 'pedidosAutomaticosActivos'));
    }

    public function generarSugeridos(Request $request)
    {
        if (! $this->pedidosAutomaticosActivos()) {
            return redirect()
                ->route('admin.pedidos-articulos.index')
                ->with('info', 'Active pedidos automaticos en ajustes para agregar articulos sugeridos.');
        }

        $validated = $request->validate([
            'articulo_id' => ['required', 'integer', 'exists:articulos,id'],
        ]);

        $created = $this->generarPedidoPorArticulo((int) $validated['articulo_id']);

        return redirect()
            ->route('admin.pedidos-articulos.index')
            ->with($created > 0 ? 'success' : 'info', $created > 0
                ? 'Articulo agregado al pedido pendiente correctamente.'
                : 'El articulo ya estaba en un pedido pendiente o no esta en nivel de pedido.');
    }

    public function create()
    {
        TmpPedidoArticulo::query()
            ->where('usuario_id', Auth::id())
            ->delete();

        $depositos = Deposito::orderBy('nombre')->get();
        $articulos = Articulo::with('unidadMedida')->orderBy('nombre')->get();
        $stockPorDeposito = $this->stockPorDeposito();
        $items = collect();
        $canBypassStockBlock = $this->canBypassStockPedidoBlock(Auth::user());

        return view('admin.pedidos-articulos.create', compact('depositos', 'articulos', 'stockPorDeposito', 'items', 'canBypassStockBlock'));
    }

    public function addItem(Request $request)
    {
        $validated = $request->validate([
            'deposito_id' => ['required', 'exists:depositos,id'],
            'articulo_id' => ['required', 'exists:articulos,id'],
        ]);

        $item = TmpPedidoArticulo::query()
            ->with(['articulo.unidadMedida', 'deposito'])
            ->where('usuario_id', Auth::id())
            ->where('deposito_id', $validated['deposito_id'])
            ->where('articulo_id', $validated['articulo_id'])
            ->first();

        if ($item) {
            return response()->json([
                'success' => true,
                'item' => $item,
                'already_exists' => true,
                'message' => 'El articulo ya esta cargado en el pedido.',
            ]);
        }

        if ($message = $this->stockPedidoBlockMessage((int) $validated['articulo_id'], (int) $validated['deposito_id'])) {
            return response()->json([
                'success' => false,
                'message' => $message,
            ], 422);
        }

        if ($message = $this->pedidoDuplicadoBlockMessage((int) $validated['articulo_id'], (int) $validated['deposito_id'])) {
            return response()->json([
                'success' => false,
                'message' => $message,
            ], 422);
        }

        $item = TmpPedidoArticulo::create([
            'usuario_id' => Auth::id(),
            'deposito_id' => $validated['deposito_id'],
            'articulo_id' => $validated['articulo_id'],
            'cantidad' => 1,
            'fecha_creacion' => now(),
            'estado' => 'pendiente',
        ]);

        $item->load(['articulo.unidadMedida', 'deposito']);

        return response()->json([
            'success' => true,
            'item' => $item,
            'message' => 'Articulo agregado correctamente.',
        ], 201);
    }

    public function updateItem(Request $request, string $itemId)
    {
        $item = TmpPedidoArticulo::query()
            ->where('usuario_id', Auth::id())
            ->findOrFail($itemId);

        $validated = $request->validate([
            'cantidad' => ['sometimes', 'required', 'integer', 'min:1'],
            'estado' => ['sometimes', 'required', 'in:activo,inactivo,pendiente,confirmado,cancelado'],
        ]);

        $item->update($validated);
        $item->load(['articulo.unidadMedida', 'deposito']);

        return response()->json([
            'success' => true,
            'item' => $item,
            'message' => 'Item actualizado correctamente.',
        ]);
    }

    public function removeItem(string $itemId)
    {
        $item = TmpPedidoArticulo::query()
            ->where('usuario_id', Auth::id())
            ->findOrFail($itemId);

        $item->delete();

        return response()->json([
            'success' => true,
            'deleted' => true,
            'message' => 'Articulo eliminado correctamente.',
        ]);
    }

    public function clearItems()
    {
        $deleted = TmpPedidoArticulo::query()
            ->where('usuario_id', Auth::id())
            ->delete();

        return response()->json([
            'success' => true,
            'deleted' => $deleted,
            'message' => 'Pedido temporal limpiado correctamente.',
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'notas' => ['nullable', 'string'],
        ]);

        $canBypassStockBlock = $this->canBypassStockPedidoBlock(Auth::user());
        $aplicoExcepcionStock = false;

        $items = TmpPedidoArticulo::query()
            ->where('usuario_id', Auth::id())
            ->get();

        if ($items->isEmpty()) {
            return redirect()
                ->route('admin.pedidos-articulos.create')
                ->with('error', 'Debe agregar al menos un articulo al pedido.');
        }

        if ($items->pluck('deposito_id')->unique()->count() > 1) {
            return redirect()
                ->route('admin.pedidos-articulos.create')
                ->with('error', 'Todos los articulos deben pertenecer al mismo deposito.');
        }

        foreach ($items as $item) {
            if ($canBypassStockBlock && $this->isStockSobrePuntoPedido((int) $item->articulo_id, (int) $item->deposito_id)) {
                $aplicoExcepcionStock = true;
            }

            if ($message = $this->stockPedidoBlockMessage((int) $item->articulo_id, (int) $item->deposito_id)) {
                return redirect()
                    ->route('admin.pedidos-articulos.create')
                    ->with('error', $message);
            }

            if ($message = $this->pedidoDuplicadoBlockMessage((int) $item->articulo_id, (int) $item->deposito_id)) {
                return redirect()
                    ->route('admin.pedidos-articulos.create')
                    ->with('error', $message);
            }
        }

        DB::transaction(function () use ($items, $validated, $aplicoExcepcionStock) {
            $pedido = PedidoArticulo::create([
                'deposito_id' => $items->first()->deposito_id,
                'usuario_id' => Auth::id(),
                'fecha_pedido' => now(),
                'estado' => 'pendiente',
                'notas' => $validated['notas'] ?? null,
            ]);

            if ($aplicoExcepcionStock) {
                $pedido->update([
                    'notas' => $this->appendStockBypassMarker($pedido->notas),
                ]);
            }

            foreach ($items as $item) {
                PedidoDetalleArticulo::create([
                    'pedidos_articulo_id' => $pedido->id,
                    'articulo_id' => $item->articulo_id,
                    'cantidad' => $item->cantidad,
                ]);
            }

            TmpPedidoArticulo::query()
                ->where('usuario_id', Auth::id())
                ->delete();
        });

        return redirect()
            ->route('admin.pedidos-articulos.index')
            ->with('success', 'Pedido de articulos creado correctamente.');
    }

    public function show(string $id)
    {
        $pedido = PedidoArticulo::with(['deposito', 'usuario', 'detalles.articulo.unidadMedida', 'compras'])
            ->withCount('compras')
            ->findOrFail($id);

        return view('admin.pedidos-articulos.show', compact('pedido'));
    }

    public function edit(string $id)
    {
        $pedido = PedidoArticulo::with(['deposito', 'usuario', 'detalles.articulo.unidadMedida'])->findOrFail($id);
        $depositos = Deposito::orderBy('nombre')->get();
        $articulos = Articulo::with('unidadMedida')->orderBy('nombre')->get();
        $stockPorDeposito = $this->stockPorDeposito();
        $canBypassStockBlock = $this->canBypassStockPedidoBlock(Auth::user());

        return view('admin.pedidos-articulos.edit', compact('pedido', 'depositos', 'articulos', 'stockPorDeposito', 'canBypassStockBlock'));
    }

    public function update(Request $request, string $id)
    {
        $pedido = PedidoArticulo::findOrFail($id);

        $validated = $request->validate([
            'deposito_id' => ['required', 'exists:depositos,id'],
            'fecha_pedido' => ['required', 'date'],
            'estado' => ['required', 'in:pendiente,confirmado,ingresado,cancelado'],
            'notas' => ['nullable', 'string'],
        ]);

        $canBypassStockBlock = $this->canBypassStockPedidoBlock(Auth::user());
        $aplicoExcepcionStock = false;

        if (in_array($validated['estado'], ['pendiente', 'confirmado'], true)) {
            $pedido->loadMissing('detalles');

            foreach ($pedido->detalles as $detalle) {
                if ($canBypassStockBlock && $this->isStockSobrePuntoPedido((int) $detalle->articulo_id, (int) $validated['deposito_id'])) {
                    $aplicoExcepcionStock = true;
                }

                if ($message = $this->stockPedidoBlockMessage((int) $detalle->articulo_id, (int) $validated['deposito_id'])) {
                    return redirect()
                        ->route('admin.pedidos-articulos.edit', $pedido->id)
                        ->with('error', $message);
                }

                if ($message = $this->pedidoDuplicadoBlockMessage((int) $detalle->articulo_id, (int) $validated['deposito_id'], (int) $pedido->id)) {
                    return redirect()
                        ->route('admin.pedidos-articulos.edit', $pedido->id)
                        ->with('error', $message);
                }
            }
        }

        if ($aplicoExcepcionStock) {
            $validated['notas'] = $this->appendStockBypassMarker($validated['notas'] ?? null);
        }

        $pedido->update($validated);

        return redirect()
            ->route('admin.pedidos-articulos.index')
            ->with('success', 'Pedido de articulos actualizado correctamente.');
    }

    public function destroy(string $id)
    {
        $pedido = PedidoArticulo::findOrFail($id);
        $pedido->delete();

        return redirect()
            ->route('admin.pedidos-articulos.index')
            ->with('success', 'Pedido de articulos eliminado correctamente.');
    }

    public function storeDetalle(Request $request, string $pedidoId)
    {
        $pedido = PedidoArticulo::findOrFail($pedidoId);

        $validated = $request->validate([
            'articulo_id' => ['required', 'exists:articulos,id'],
        ]);

        $detalle = PedidoDetalleArticulo::query()
            ->with('articulo.unidadMedida')
            ->where('pedidos_articulo_id', $pedido->id)
            ->where('articulo_id', $validated['articulo_id'])
            ->first();

        if ($detalle) {
            return response()->json([
                'success' => true,
                'detalle' => $detalle,
                'already_exists' => true,
                'message' => 'El articulo ya esta cargado en el pedido.',
            ]);
        }

        if ($message = $this->stockPedidoBlockMessage((int) $validated['articulo_id'], (int) $pedido->deposito_id)) {
            return response()->json([
                'success' => false,
                'message' => $message,
            ], 422);
        }

        if ($message = $this->pedidoDuplicadoBlockMessage((int) $validated['articulo_id'], (int) $pedido->deposito_id, (int) $pedido->id)) {
            return response()->json([
                'success' => false,
                'message' => $message,
            ], 422);
        }

        if (
            $this->canBypassStockPedidoBlock(Auth::user())
            && $this->isStockSobrePuntoPedido((int) $validated['articulo_id'], (int) $pedido->deposito_id)
        ) {
            $pedido->update([
                'notas' => $this->appendStockBypassMarker($pedido->notas),
            ]);
        }

        $detalle = PedidoDetalleArticulo::create([
            'pedidos_articulo_id' => $pedido->id,
            'articulo_id' => $validated['articulo_id'],
            'cantidad' => 1,
        ]);

        $detalle->load('articulo.unidadMedida');

        return response()->json([
            'success' => true,
            'detalle' => $detalle,
            'message' => 'Articulo agregado correctamente.',
        ], 201);
    }

    public function updateDetalle(Request $request, string $pedidoId, string $detalleId)
    {
        $pedido = PedidoArticulo::findOrFail($pedidoId);
        $detalle = PedidoDetalleArticulo::query()
            ->where('pedidos_articulo_id', $pedido->id)
            ->findOrFail($detalleId);

        $validated = $request->validate([
            'cantidad' => ['sometimes', 'required', 'integer', 'min:1'],
        ]);

        $detalle->update($validated);
        $detalle->load('articulo.unidadMedida');

        return response()->json([
            'success' => true,
            'detalle' => $detalle,
            'message' => 'Detalle actualizado correctamente.',
        ]);
    }

    public function destroyDetalle(string $pedidoId, string $detalleId)
    {
        $pedido = PedidoArticulo::findOrFail($pedidoId);
        $detalle = PedidoDetalleArticulo::query()
            ->where('pedidos_articulo_id', $pedido->id)
            ->findOrFail($detalleId);

        $detalle->delete();

        return response()->json([
            'success' => true,
            'deleted' => true,
            'message' => 'Articulo eliminado correctamente.',
        ]);
    }

    private function articulosEnNivelPedido()
    {
        $pendientes = $this->detallesPendientesKeys();
        $depositos = $this->visibleDepositosQuery(Auth::user())->orderBy('nombre')->get();
        $depositoIds = $depositos->pluck('id')->map(fn ($id) => (int) $id)->all();
        $depositoPedidoId = $this->defaultPedidoDepositoId(Auth::user());
        $duplicadosActivos = $depositoPedidoId ? $this->articulosDuplicadosActivosKeys($depositoPedidoId) : [];

        $stocks = Inventario::query()
            ->whereIn('deposito_id', $depositoIds)
            ->selectRaw('articulo_id, COALESCE(SUM(cantidad), 0) as stock_total')
            ->groupBy('articulo_id')
            ->get()
            ->keyBy('articulo_id');

        return Articulo::query()
            ->with('unidadMedida')
            ->where('estado_item', 'activo')
            ->where('reposicion_modo', 'automatico')
            ->where('stock_pedido', '>', 0)
            ->orderBy('nombre')
            ->get()
            ->map(function (Articulo $articulo) use ($stocks, $pendientes, $duplicadosActivos) {
                $tieneInventario = $stocks->has($articulo->id);
                $stockActual = (int) ($stocks->get($articulo->id)?->stock_total ?? 0);

                return [
                    'articulo_id' => $articulo->id,
                    'articulo' => $articulo,
                    'tiene_inventario' => $tieneInventario,
                    'stock_actual' => $stockActual,
                    'stock_pedido' => (int) $articulo->stock_pedido,
                    'cantidad_sugerida' => max(1, (int) $articulo->stock_pedido),
                    'modo' => $articulo->reposicion_modo ?? 'manual',
                    'ya_pedido' => isset($pendientes[(int) $articulo->id]) || isset($duplicadosActivos[(int) $articulo->id]),
                ];
            })
            ->filter(fn (array $item) => $item['stock_actual'] <= $item['stock_pedido'])
            ->filter(fn (array $item) => ! $item['ya_pedido'])
            ->values();
    }

    private function pedidosAutomaticosActivos(): bool
    {
        $ajuste = Ajuste::query()->first(['pedidos_automaticos_activos']);

        if (! $ajuste) {
            return true;
        }

        return (bool) $ajuste->pedidos_automaticos_activos;
    }

    private function generarPedidoPorArticulo(int $articuloId): int
    {
        $sugerido = $this->articulosEnNivelPedido()
            ->first(fn (array $item) => (int) $item['articulo_id'] === $articuloId);

        if (! $sugerido || $sugerido['ya_pedido']) {
            return 0;
        }

        return DB::transaction(function () use ($sugerido) {
            $depositoId = $this->defaultPedidoDepositoId(Auth::user());

            if (! $depositoId) {
                return 0;
            }

            $pedido = PedidoArticulo::query()
                ->where('deposito_id', $depositoId)
                ->where('estado', 'pendiente')
                ->where('notas', 'like', 'Pedido sugerido por stock%')
                ->latest('id')
                ->first();

            if (! $pedido) {
                $pedido = PedidoArticulo::create([
                    'deposito_id' => $depositoId,
                    'usuario_id' => Auth::id(),
                    'fecha_pedido' => now(),
                    'estado' => 'pendiente',
                    'notas' => 'Pedido sugerido por stock - generado por articulo.',
                ]);
            }

            PedidoDetalleArticulo::create([
                'pedidos_articulo_id' => $pedido->id,
                'articulo_id' => $sugerido['articulo']->id,
                'cantidad' => $sugerido['cantidad_sugerida'],
            ]);

            return 1;
        });
    }

    private function detallesPendientesKeys(): array
    {
        return PedidoDetalleArticulo::query()
            ->join('pedidos_articulo', 'pedidos_articulo.id', '=', 'pedido_detalle_articulo.pedidos_articulo_id')
            ->where('pedidos_articulo.estado', 'pendiente')
            ->pluck('pedido_detalle_articulo.articulo_id')
            ->mapWithKeys(fn ($articuloId) => [(int) $articuloId => true])
            ->all();
    }

    private function stockPedidoBlockMessage(int $articuloId, int $depositoId): ?string
    {
        if ($this->canBypassStockPedidoBlock(Auth::user())) {
            return null;
        }

        $articulo = Articulo::query()->find($articuloId);

        if (! $articulo) {
            return 'El articulo seleccionado no existe.';
        }

        $puntoPedido = (int) $articulo->stock_pedido;

        if ($puntoPedido <= 0) {
            return null;
        }

        $stockActual = (int) Inventario::query()
            ->where('deposito_id', $depositoId)
            ->where('articulo_id', $articuloId)
            ->sum('cantidad');

        if ($stockActual <= $puntoPedido) {
            return null;
        }

        return "No se puede pedir {$articulo->nombre}: el stock actual ({$stockActual}) esta por encima del punto de pedido ({$puntoPedido}).";
    }

    private function canBypassStockPedidoBlock(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->isSuperUsuario()) {
            return true;
        }

        return $user->hasRole('JEFE DE COMPRAS');
    }

    private function isStockSobrePuntoPedido(int $articuloId, int $depositoId): bool
    {
        $articulo = Articulo::query()->select('id', 'stock_pedido')->find($articuloId);

        if (! $articulo) {
            return false;
        }

        $puntoPedido = (int) $articulo->stock_pedido;

        if ($puntoPedido <= 0) {
            return false;
        }

        $stockActual = (int) Inventario::query()
            ->where('deposito_id', $depositoId)
            ->where('articulo_id', $articuloId)
            ->sum('cantidad');

        return $stockActual > $puntoPedido;
    }

    private function appendStockBypassMarker(?string $notas): string
    {
        $current = trim((string) $notas);
        $marker = PedidoArticulo::STOCK_BYPASS_MARKER;

        if ($current === '') {
            return $marker;
        }

        if (str_contains($current, $marker)) {
            return $current;
        }

        return $current . ' ' . $marker;
    }

    private function pedidoDuplicadoBlockMessage(int $articuloId, int $depositoId, ?int $exceptPedidoId = null): ?string
    {
        $articulo = Articulo::query()->select('id', 'nombre')->find($articuloId);

        if (! $articulo) {
            return 'El articulo seleccionado no existe.';
        }

        $pedidoDuplicado = PedidoDetalleArticulo::query()
            ->join('pedidos_articulo', 'pedidos_articulo.id', '=', 'pedido_detalle_articulo.pedidos_articulo_id')
            ->where('pedido_detalle_articulo.articulo_id', $articuloId)
            ->where('pedidos_articulo.deposito_id', $depositoId)
            ->whereIn('pedidos_articulo.estado', ['pendiente', 'confirmado'])
            ->when($exceptPedidoId, fn ($query) => $query->where('pedidos_articulo.id', '!=', $exceptPedidoId))
            ->orderByDesc('pedidos_articulo.id')
            ->select('pedidos_articulo.id', 'pedidos_articulo.estado')
            ->first();

        if ($pedidoDuplicado) {
            return "No se puede pedir {$articulo->nombre}: ya esta en el pedido #{$pedidoDuplicado->id} ({$pedidoDuplicado->estado}).";
        }

        $ordenDuplicada = CompraDetalle::query()
            ->join('compras', 'compras.id', '=', 'compra_detalles.compra_id')
            ->where('compra_detalles.articulo_id', $articuloId)
            ->where('compras.deposito_id', $depositoId)
            ->whereIn('compras.estado', ['pendiente', 'aprobada'])
            ->orderByDesc('compras.id')
            ->select('compras.id', 'compras.estado')
            ->first();

        if ($ordenDuplicada) {
            return "No se puede pedir {$articulo->nombre}: ya esta en la orden de compra #{$ordenDuplicada->id} ({$ordenDuplicada->estado}).";
        }

        return null;
    }

    private function articulosDuplicadosActivosKeys(int $depositoId): array
    {
        $pedidos = PedidoDetalleArticulo::query()
            ->join('pedidos_articulo', 'pedidos_articulo.id', '=', 'pedido_detalle_articulo.pedidos_articulo_id')
            ->where('pedidos_articulo.deposito_id', $depositoId)
            ->whereIn('pedidos_articulo.estado', ['pendiente', 'confirmado'])
            ->pluck('pedido_detalle_articulo.articulo_id');

        $ordenes = CompraDetalle::query()
            ->join('compras', 'compras.id', '=', 'compra_detalles.compra_id')
            ->where('compras.deposito_id', $depositoId)
            ->whereIn('compras.estado', ['pendiente', 'aprobada'])
            ->pluck('compra_detalles.articulo_id');

        return $pedidos
            ->merge($ordenes)
            ->mapWithKeys(fn ($articuloId) => [(int) $articuloId => true])
            ->all();
    }

    private function stockPorDeposito(): array
    {
        return Inventario::query()
            ->selectRaw('deposito_id, articulo_id, COALESCE(SUM(cantidad), 0) as stock_total')
            ->groupBy('deposito_id', 'articulo_id')
            ->get()
            ->groupBy(fn ($row) => (string) $row->deposito_id)
            ->map(fn ($rows) => $rows
                ->mapWithKeys(fn ($row) => [(string) $row->articulo_id => (int) $row->stock_total])
                ->all())
            ->all();
    }

    private function visibleDepositosQuery(?User $user)
    {
        $query = Deposito::query()->whereIn('estado', ['activo', 'activa']);
        $depositoIds = $this->visibleDepositoIds($user);

        if (! $this->canSeeAllInventarios($user)) {
            $query->whereIn('id', $depositoIds);
        }

        return $query;
    }

    private function visibleDepositoIds(?User $user): array
    {
        if ($this->canSeeAllInventarios($user)) {
            return Deposito::query()
                ->whereIn('estado', ['activo', 'activa'])
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();
        }

        $depositoId = $user?->base?->deposito_id;

        return $depositoId ? [(int) $depositoId] : [];
    }

    private function defaultPedidoDepositoId(?User $user): ?int
    {
        $depositoId = $user?->base?->deposito_id;

        if ($depositoId) {
            return (int) $depositoId;
        }

        return $this->visibleDepositosQuery($user)
            ->orderBy('id')
            ->value('id');
    }

    private function canSeeAllInventarios(?User $user): bool
    {
        return (bool) ($user?->isSuperUsuario() || $user?->puede_ver_todos_inventarios);
    }
}
