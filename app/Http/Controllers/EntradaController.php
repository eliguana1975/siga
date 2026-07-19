<?php

namespace App\Http\Controllers;

use App\Models\Articulo;
use App\Models\Compra;
use App\Models\CompraDetalle;
use App\Models\Cubierta;
use App\Models\DetalleEntrada;
use App\Models\Deposito;
use App\Models\DocumentoOperativo;
use App\Models\Entrada;
use App\Models\Inventario;
use App\Models\PedidoArticulo;
use App\Models\Proveedor;
use App\Services\ArticleClassificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EntradaController extends Controller
{
    public function __construct(private ArticleClassificationService $articleClassifier)
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->get('search', ''));

        $entradas = Entrada::query()
            ->with(['compra', 'deposito', 'proveedor', 'usuario', 'detalles.articulo.unidadMedida'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('nro_orden_compra', 'like', "%{$search}%")
                        ->orWhere('nro_comprobante_proveedor', 'like', "%{$search}%")
                        ->orWhere('observaciones', 'like', "%{$search}%")
                        ->orWhereHas('deposito', fn ($deposito) => $deposito->where('nombre', 'like', "%{$search}%"))
                        ->orWhereHas('proveedor', fn ($proveedor) => $proveedor->where('nombre', 'like', "%{$search}%"))
                        ->orWhereHas('usuario', fn ($usuario) => $usuario->where('name', 'like', "%{$search}%"));
                });
            })
            ->latest('fecha_entrada')
            ->paginate(10)
            ->withQueryString();

        return view('admin.entradas.index', compact('entradas', 'search'));
    }

    public function create()
    {
        return view('admin.entradas.create', $this->formData());
    }

    public function pendientes(Request $request)
    {
        $search = trim((string) $request->get('search', ''));
        $ordenes = $this->ordenesPendientesQuery($search)
            ->latest('fecha_compra')
            ->get()
            ->map(fn (Compra $compra) => $this->buildOrdenPendiente($compra))
            ->filter(fn (array $orden) => $orden['pendientes']->isNotEmpty())
            ->values();

        return view('admin.entradas.pendientes', compact('ordenes', 'search'));
    }

    public function storePendiente(Request $request)
    {
        $validated = $request->validate([
            'compra_detalle_id' => ['required', 'integer', 'exists:compra_detalles,id'],
            'cantidad' => ['required', 'integer', 'min:1'],
            'nro_comprobante_proveedor' => ['nullable', 'string', 'max:100'],
            'fecha_entrada' => ['required', 'date'],
            'cubiertas_tiene_numeracion' => ['nullable', 'in:0,1'],
            'cubiertas_numero_inicial' => ['nullable', 'integer', 'min:1'],
        ]);

        $compraDetalle = CompraDetalle::with(['compra', 'articulo.categoria'])->findOrFail($validated['compra_detalle_id']);
        $compra = $compraDetalle->compra;

        if (! $compra || $compra->estado !== 'aprobada') {
            return redirect()
                ->route('admin.entradas.pendientes')
                ->with('error', 'La orden seleccionada no esta aprobada o ya no admite ingresos.');
        }

        $this->validateCantidadesPendientes([[
            'compra_detalle_id' => $compraDetalle->id,
            'cantidad' => $validated['cantidad'],
        ]]);
        $this->validateCubiertasNumeracion([[
            'articulo_id' => $compraDetalle->articulo_id,
            'cantidad' => $validated['cantidad'],
            'cubiertas_tiene_numeracion' => $validated['cubiertas_tiene_numeracion'] ?? null,
            'cubiertas_numero_inicial' => $validated['cubiertas_numero_inicial'] ?? null,
        ]]);

        DB::transaction(function () use ($validated, $compraDetalle, $compra) {
            $entrada = Entrada::create([
                'compra_id' => $compra->id,
                'deposito_id' => $compra->deposito_id,
                'proveedor_id' => $compraDetalle->proveedor_id ?? $compra->proveedor_id,
                'usuario_id' => Auth::id(),
                'nro_orden_compra' => (string) $compra->id,
                'nro_comprobante_proveedor' => $validated['nro_comprobante_proveedor'] ?? null,
                'fecha_entrada' => $validated['fecha_entrada'],
                'observaciones' => 'Ingreso registrado desde pendientes de orden de compra.',
                'total_entrada' => 0,
            ]);

            $cubiertasNumeracion = [];
            $total = $this->storeDetalles($entrada, [[
                'compra_detalle_id' => $compraDetalle->id,
                'articulo_id' => $compraDetalle->articulo_id,
                'cantidad' => $validated['cantidad'],
                'precio_compra_unidad' => $compraDetalle->precio_compra_unidad,
                'cubiertas_tiene_numeracion' => $validated['cubiertas_tiene_numeracion'] ?? null,
                'cubiertas_numero_inicial' => $validated['cubiertas_numero_inicial'] ?? null,
            ]], $cubiertasNumeracion);

            $entrada->update(['total_entrada' => $total]);
            $this->applyInventario($entrada, 1, $cubiertasNumeracion);
            $this->refreshCompraRecepcion($compra->id);
        });

        return redirect()
            ->route('admin.entradas.pendientes')
            ->with('success', 'Ingreso registrado y stock actualizado correctamente.');
    }

    public function store(Request $request)
    {
        $validated = $this->validatedData($request);
        $this->validateCantidadesPendientes($validated['detalles']);
        $this->validateCubiertasNumeracion($validated['detalles']);

        DB::transaction(function () use ($validated) {
            $entrada = Entrada::create([
                'compra_id' => $validated['compra_id'] ?? null,
                'deposito_id' => $validated['deposito_id'],
                'proveedor_id' => $validated['proveedor_id'] ?? null,
                'usuario_id' => Auth::id(),
                'nro_orden_compra' => $validated['nro_orden_compra'] ?? null,
                'nro_comprobante_proveedor' => $validated['nro_comprobante_proveedor'] ?? null,
                'fecha_entrada' => $validated['fecha_entrada'],
                'observaciones' => $validated['observaciones'] ?? null,
                'total_entrada' => 0,
            ]);

            $cubiertasNumeracion = [];
            $total = $this->storeDetalles($entrada, $validated['detalles'], $cubiertasNumeracion);
            $entrada->update(['total_entrada' => $total]);
            $this->applyInventario($entrada, 1, $cubiertasNumeracion);
            $this->refreshCompraRecepcion($validated['compra_id'] ?? null);
        });

        return redirect()
            ->route('admin.entradas.index')
            ->with('success', 'Ingreso de articulos registrado correctamente.');
    }

    public function show(string $id)
    {
        $entrada = Entrada::with(['compra.detalles.articulo.unidadMedida', 'compra.detalles.detallesEntrada', 'compra.detalles.proveedor', 'deposito', 'proveedor', 'usuario', 'detalles.articulo.unidadMedida', 'detalles.compraDetalle'])
            ->findOrFail($id);
        $documentos = DocumentoOperativo::query()
            ->with('usuario:id,name')
            ->where('documentable_type', Entrada::class)
            ->where('documentable_id', $entrada->id)
            ->latest()
            ->get();

        return view('admin.entradas.show', compact('entrada', 'documentos'));
    }

    public function edit(string $id)
    {
        $entrada = Entrada::with('detalles')->findOrFail($id);

        return view('admin.entradas.edit', array_merge($this->formData($entrada), compact('entrada')));
    }

    public function update(Request $request, string $id)
    {
        $entrada = Entrada::with('detalles')->findOrFail($id);
        $validated = $this->validatedData($request);
        $this->validateCantidadesPendientes($validated['detalles'], $entrada->id);
        $this->validateCubiertasNumeracion($validated['detalles'], $entrada->id);

        DB::transaction(function () use ($entrada, $validated) {
            $previousCompraId = $entrada->compra_id;
            $cubiertasInicioActual = Cubierta::query()
                ->where('entrada_id', $entrada->id)
                ->selectRaw('articulo_id, MIN(CAST(numero AS UNSIGNED)) as inicio')
                ->groupBy('articulo_id')
                ->pluck('inicio', 'articulo_id')
                ->map(fn ($inicio) => (int) $inicio)
                ->all();
            $this->applyInventario($entrada, -1);

            $entrada->update([
                'compra_id' => $validated['compra_id'] ?? null,
                'deposito_id' => $validated['deposito_id'],
                'proveedor_id' => $validated['proveedor_id'] ?? null,
                'nro_orden_compra' => $validated['nro_orden_compra'] ?? null,
                'nro_comprobante_proveedor' => $validated['nro_comprobante_proveedor'] ?? null,
                'fecha_entrada' => $validated['fecha_entrada'],
                'observaciones' => $validated['observaciones'] ?? null,
                'total_entrada' => 0,
            ]);

            $entrada->detalles()->delete();
            $cubiertasNumeracion = [];
            $total = $this->storeDetalles($entrada, $validated['detalles'], $cubiertasNumeracion);
            $entrada->update(['total_entrada' => $total]);
            $entrada->load('detalles.articulo');
            $this->applyInventario($entrada, 1, $cubiertasNumeracion, $cubiertasInicioActual);
            $this->refreshCompraRecepcion($previousCompraId);
            $this->refreshCompraRecepcion($validated['compra_id'] ?? null);
        });

        return redirect()
            ->route('admin.entradas.index')
            ->with('success', 'Ingreso de articulos actualizado correctamente.');
    }

    public function destroy(string $id)
    {
        $entrada = Entrada::with('detalles.articulo')->findOrFail($id);

        DB::transaction(function () use ($entrada) {
            $compraId = $entrada->compra_id;
            $this->applyInventario($entrada, -1);
            $entrada->delete();
            $this->refreshCompraRecepcion($compraId);
        });

        return redirect()
            ->route('admin.entradas.index')
            ->with('success', 'Ingreso de articulos eliminado correctamente.');
    }

    private function formData(?Entrada $entrada = null): array
    {
        return [
            'depositos' => Deposito::orderBy('nombre')->get(),
            'proveedores' => Proveedor::orderBy('nombre')->get(),
            'compras' => Compra::with('proveedor')
                ->where(function ($query) use ($entrada) {
                    $query->where('estado', 'aprobada')
                        ->when($entrada?->compra_id, fn ($query) => $query->orWhere('id', $entrada->compra_id));
                })
                ->latest('fecha_compra')
                ->get(),
            'comprasParaIngreso' => $this->comprasParaIngreso($entrada),
            'articulos' => Articulo::with(['unidadMedida', 'categoria'])->withCount('cubiertas')->orderBy('nombre')->get(),
        ];
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'compra_id' => ['nullable', 'integer', 'exists:compras,id'],
            'deposito_id' => ['required', 'integer', 'exists:depositos,id'],
            'proveedor_id' => ['nullable', 'integer', 'exists:proveedores,id'],
            'nro_orden_compra' => ['nullable', 'string', 'max:100'],
            'nro_comprobante_proveedor' => ['nullable', 'string', 'max:100'],
            'fecha_entrada' => ['required', 'date'],
            'observaciones' => ['nullable', 'string'],
            'detalles' => ['required', 'array', 'min:1'],
            'detalles.*.compra_detalle_id' => ['nullable', 'integer', 'exists:compra_detalles,id'],
            'detalles.*.articulo_id' => ['required', 'integer', 'exists:articulos,id'],
            'detalles.*.cantidad' => ['required', 'integer', 'min:1'],
            'detalles.*.precio_compra_unidad' => ['nullable', 'numeric', 'min:0'],
            'detalles.*.cubiertas_tiene_numeracion' => ['nullable', 'in:0,1'],
            'detalles.*.cubiertas_numero_inicial' => ['nullable', 'integer', 'min:1'],
        ]);
    }

    private function storeDetalles(Entrada $entrada, array $detalles, array &$cubiertasNumeracion = []): float
    {
        $total = 0;

        foreach ($detalles as $detalle) {
            $precio = (float) ($detalle['precio_compra_unidad'] ?? 0);
            $cantidad = (int) $detalle['cantidad'];
            $total += $cantidad * $precio;

            $detalleEntrada = $entrada->detalles()->create([
                'compra_detalle_id' => $detalle['compra_detalle_id'] ?? null,
                'articulo_id' => $detalle['articulo_id'],
                'cantidad' => $cantidad,
                'precio_compra_unidad' => $precio,
            ]);

            if (($detalle['cubiertas_tiene_numeracion'] ?? null) === '1' && ! empty($detalle['cubiertas_numero_inicial'])) {
                $cubiertasNumeracion[$detalleEntrada->id] = [
                    'numero_inicial' => (int) $detalle['cubiertas_numero_inicial'],
                ];
            }
        }

        return $total;
    }

    private function validateCantidadesPendientes(array $detalles, ?int $currentEntradaId = null): void
    {
        foreach ($detalles as $index => $detalle) {
            if (empty($detalle['compra_detalle_id'])) {
                continue;
            }

            $compraDetalle = CompraDetalle::find($detalle['compra_detalle_id']);

            if (! $compraDetalle) {
                continue;
            }

            $cantidadRecibida = DetalleEntrada::query()
                ->where('compra_detalle_id', $compraDetalle->id)
                ->when($currentEntradaId, fn ($query) => $query->whereHas('entrada', fn ($entradaQuery) => $entradaQuery->where('id', '!=', $currentEntradaId)))
                ->sum('cantidad');

            $cantidadPendiente = max(0, (int) $compraDetalle->cantidad - (int) $cantidadRecibida);

            if ((int) $detalle['cantidad'] > $cantidadPendiente) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    "detalles.{$index}.cantidad" => "La cantidad supera el pendiente de la orden. Pendiente disponible: {$cantidadPendiente}.",
                ]);
            }
        }
    }

    private function validateCubiertasNumeracion(array $detalles, ?int $currentEntradaId = null): void
    {
        foreach ($detalles as $index => $detalle) {
            $articulo = Articulo::query()
                ->with('categoria')
                ->find($detalle['articulo_id'] ?? null);

            if (! $articulo || ! $this->esArticuloCubierta($articulo)) {
                continue;
            }

            $tieneCubiertasPrevias = Cubierta::query()
                ->where('articulo_id', $articulo->id)
                ->when($currentEntradaId, fn ($query) => $query->where('entrada_id', '!=', $currentEntradaId))
                ->exists();

            if ($tieneCubiertasPrevias) {
                continue;
            }

            $tieneNumeracion = (string) ($detalle['cubiertas_tiene_numeracion'] ?? '') === '1';
            $numeroInicial = (int) ($detalle['cubiertas_numero_inicial'] ?? 0);

            if ($tieneNumeracion && $numeroInicial <= 0) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    "detalles.{$index}.cubiertas_numero_inicial" => 'Indique el numero inicial de la cubierta.',
                ]);
            }

            if (! $tieneNumeracion) {
                continue;
            }

            $cantidad = (int) ($detalle['cantidad'] ?? 0);

            for ($i = 0; $i < $cantidad; $i++) {
                $numero = Cubierta::numeroPara($articulo, $numeroInicial + $i);

                if (Cubierta::query()->where('numero', $numero)->exists()) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        "detalles.{$index}.cubiertas_numero_inicial" => "La numeracion {$numero} ya existe.",
                    ]);
                }
            }
        }
    }

    private function applyInventario(Entrada $entrada, int $direction, array $cubiertasNumeracion = [], array $cubiertasInicioActual = []): void
    {
        $entrada->loadMissing('detalles.articulo.categoria');

        foreach ($entrada->detalles as $detalle) {
            $inventario = Inventario::firstOrCreate(
                [
                    'deposito_id' => $entrada->deposito_id,
                    'articulo_id' => $detalle->articulo_id,
                ],
                [
                    'cantidad' => 0,
                    'precio_compra_unidad' => $detalle->precio_compra_unidad,
                    'stock_minimo' => $detalle->articulo->stock_minimo ?? 0,
                    'stock_maximo' => $detalle->articulo->stock_maximo ?? 0,
                    'estado' => 'compra',
                ]
            );

            $inventario->cantidad = max(0, (int) $inventario->cantidad + ((int) $detalle->cantidad * $direction));
            $inventario->precio_compra_unidad = $detalle->precio_compra_unidad ?? $inventario->precio_compra_unidad;
            $inventario->stock_minimo = (int) ($detalle->articulo->stock_minimo ?? 0);
            $inventario->stock_maximo = (int) ($detalle->articulo->stock_maximo ?? 0);
            $inventario->estado = 'compra';
            $inventario->save();

            $this->syncCubiertasEntrada($entrada, $detalle, $inventario, $direction, $cubiertasNumeracion, $cubiertasInicioActual);
        }
    }

    private function syncCubiertasEntrada(Entrada $entrada, DetalleEntrada $detalle, Inventario $inventario, int $direction, array $cubiertasNumeracion = [], array $cubiertasInicioActual = []): void
    {
        $articulo = $detalle->articulo;

        if (! $articulo || ! $this->esArticuloCubierta($articulo)) {
            return;
        }

        if ($direction < 0) {
            Cubierta::query()
                ->where('detalle_entrada_id', $detalle->id)
                ->whereIn('estado', ['nueva', 'reutilizable'])
                ->delete();

            return;
        }

        $existentes = Cubierta::query()
            ->where('detalle_entrada_id', $detalle->id)
            ->count();
        $faltantes = max(0, (int) $detalle->cantidad - (int) $existentes);

        if ($faltantes === 0) {
            return;
        }

        $ultimaSecuencia = (int) Cubierta::query()
            ->where('articulo_id', $articulo->id)
            ->lockForUpdate()
            ->max('secuencia');
        $numeroInicialManual = array_key_exists($detalle->id, $cubiertasNumeracion);
        $numeroInicial = $cubiertasNumeracion[$detalle->id]['numero_inicial']
            ?? $cubiertasInicioActual[$articulo->id]
            ?? $this->siguienteNumeroCubiertaDisponible();

        if ($ultimaSecuencia <= 0 && $numeroInicial) {
            $ultimaSecuencia = max(0, (int) $numeroInicial - 1);
        }

        $medida = Cubierta::medidaDesdeArticulo($articulo);
        $proximoNumero = (int) $numeroInicial;

        for ($i = 1; $i <= $faltantes; $i++) {
            $secuencia = $ultimaSecuencia + $i;
            $numero = $numeroInicialManual
                ? (string) $proximoNumero
                : $this->siguienteNumeroCubiertaDisponible($proximoNumero);

            Cubierta::create([
                'articulo_id' => $articulo->id,
                'inventario_id' => $inventario->id,
                'deposito_id' => $entrada->deposito_id,
                'entrada_id' => $entrada->id,
                'detalle_entrada_id' => $detalle->id,
                'medida' => $medida,
                'secuencia' => $secuencia,
                'numero' => $numero,
                'estado' => 'nueva',
                'fecha_ingreso' => $entrada->fecha_entrada?->toDateString(),
            ]);

            $proximoNumero = (int) $numero + 1;
        }
    }

    private function siguienteNumeroCubiertaDisponible(int $desde = 1): string
    {
        $desde = max(1, $desde);
        $ultimoNumero = (int) (Cubierta::query()
            ->lockForUpdate()
            ->selectRaw('MAX(CAST(numero AS UNSIGNED)) as max_numero')
            ->value('max_numero') ?? 0);
        $numero = max($desde, $ultimoNumero + 1);

        while (Cubierta::query()->where('numero', (string) $numero)->exists()) {
            $numero++;
        }

        return (string) $numero;
    }

    private function esArticuloCubierta(Articulo $articulo): bool
    {
        return $this->articleClassifier->isCubiertaArticulo($articulo);
    }

    private function comprasParaIngreso(?Entrada $entrada = null): array
    {
        $currentEntradaId = $entrada?->id;

        $compras = Compra::query()
            ->with(['deposito', 'proveedor', 'detalles.articulo.unidadMedida', 'detalles.articulo.categoria'])
            ->where(function ($query) use ($entrada) {
                $query->where('estado', 'aprobada')
                    ->when($entrada?->compra_id, fn ($query) => $query->orWhere('id', $entrada->compra_id));
            })
            ->latest('fecha_compra')
            ->get();

        return $compras->map(function (Compra $compra) use ($currentEntradaId) {
            $detalles = $compra->detalles->map(function (CompraDetalle $detalle) use ($currentEntradaId) {
                $cantidadRecibida = DetalleEntrada::query()
                    ->where('compra_detalle_id', $detalle->id)
                    ->when($currentEntradaId, fn ($query) => $query->whereHas('entrada', fn ($entradaQuery) => $entradaQuery->where('id', '!=', $currentEntradaId)))
                    ->sum('cantidad');

                $cantidadPendiente = max(0, (int) $detalle->cantidad - (int) $cantidadRecibida);

                return [
                    'id' => $detalle->id,
                    'articulo_id' => $detalle->articulo_id,
                    'articulo_label' => trim(($detalle->articulo?->nombre ?? 'Articulo') . ($detalle->articulo?->codigo_producto ? ' - ' . $detalle->articulo->codigo_producto : '')),
                    'unidad' => $detalle->articulo?->unidadMedida?->nombre ?? '',
                    'cantidad_ordenada' => (int) $detalle->cantidad,
                    'cantidad_recibida' => (int) $cantidadRecibida,
                    'cantidad_pendiente' => $cantidadPendiente,
                    'precio_compra_unidad' => (float) $detalle->precio_compra_unidad,
                    'es_cubierta' => $detalle->articulo ? $this->esArticuloCubierta($detalle->articulo) : false,
                    'cubiertas_existentes' => $detalle->articulo_id ? Cubierta::query()->where('articulo_id', $detalle->articulo_id)->exists() : false,
                ];
            })->filter(fn (array $detalle) => $detalle['cantidad_pendiente'] > 0)->values();

            return [
                'id' => $compra->id,
                'label' => 'Orden #' . $compra->id . ' - ' . ($compra->proveedor?->nombre ?? 'Sin proveedor'),
                'deposito_id' => $compra->deposito_id,
                'proveedor_id' => $compra->proveedor_id,
                'nro_orden_compra' => (string) $compra->id,
                'detalles' => $detalles,
            ];
        })->filter(fn (array $compra) => $compra['detalles']->isNotEmpty())->values()->all();
    }

    private function ordenesPendientesQuery(string $search)
    {
        return Compra::query()
            ->with(['deposito', 'proveedor', 'detalles.articulo.unidadMedida', 'detalles.articulo.categoria', 'detalles.proveedor', 'detalles.detallesEntrada'])
            ->where('estado', 'aprobada')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('id', $search)
                        ->orWhereHas('proveedor', fn ($proveedor) => $proveedor->where('nombre', 'like', "%{$search}%"))
                        ->orWhereHas('deposito', fn ($deposito) => $deposito->where('nombre', 'like', "%{$search}%"))
                        ->orWhereHas('detalles.articulo', fn ($articulo) => $articulo->where('nombre', 'like', "%{$search}%")
                            ->orWhere('codigo_producto', 'like', "%{$search}%"));
                });
            });
    }

    private function buildOrdenPendiente(Compra $compra): array
    {
        $detalles = $compra->detalles->map(function (CompraDetalle $detalle) use ($compra) {
            $ingresado = (int) $detalle->detallesEntrada->sum('cantidad');
            $pendiente = max(0, (int) $detalle->cantidad - $ingresado);

            return [
                'id' => $detalle->id,
                'articulo' => $detalle->articulo?->nombre ?? 'N/A',
                'codigo' => $detalle->articulo?->codigo_producto ?? '-',
                'unidad' => $detalle->articulo?->unidadMedida?->nombre ?? '-',
                'proveedor' => $detalle->proveedor?->nombre ?? $compra->proveedor?->nombre ?? 'Sin proveedor',
                'ordenado' => (int) $detalle->cantidad,
                'ingresado' => $ingresado,
                'pendiente' => $pendiente,
                'precio' => (float) $detalle->precio_compra_unidad,
                'es_cubierta' => $detalle->articulo ? $this->esArticuloCubierta($detalle->articulo) : false,
                'cubiertas_existentes' => $detalle->articulo_id ? Cubierta::query()->where('articulo_id', $detalle->articulo_id)->exists() : false,
            ];
        });

        $totalIngresado = $detalles->sum('ingresado');
        $pendientes = $detalles->filter(fn (array $detalle) => $detalle['pendiente'] > 0)->values();

        return [
            'compra' => $compra,
            'pendientes' => $pendientes,
            'total_pendiente' => $pendientes->sum('pendiente'),
            'total_ingresado' => $totalIngresado,
            'tiene_ingresos' => $totalIngresado > 0,
        ];
    }

    private function refreshCompraRecepcion(?int $compraId): void
    {
        if (! $compraId) {
            return;
        }

        $compra = Compra::with('detalles')->find($compraId);

        if (! $compra || ! in_array($compra->estado, ['aprobada', 'recibido'], true)) {
            return;
        }

        $allReceived = $compra->detalles->every(function (CompraDetalle $detalle) {
            $recibido = DetalleEntrada::query()
                ->where('compra_detalle_id', $detalle->id)
                ->sum('cantidad');

            return (int) $recibido >= (int) $detalle->cantidad;
        });

        $compra->forceFill([
            'estado' => $allReceived ? 'recibido' : 'aprobada',
        ])->save();

        $this->refreshPedidoRecepcion($compra->pedido_articulo_id);
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
            return;
        }

        $allReceived = $compras->every(fn (Compra $compra) => $compra->estado === 'recibido');

        $pedido->forceFill([
            'estado' => $allReceived ? 'ingresado' : 'confirmado',
        ])->save();
    }
}
