<?php

namespace App\Http\Controllers;

use App\Models\Deposito;
use App\Models\Inventario;
use App\Models\TransferenciaDeposito;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TransferenciaDepositoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('check.permission:administrar-inventarios');
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));

        $transferencias = TransferenciaDeposito::query()
            ->with([
                'depositoOrigen:id,nombre',
                'depositoDestino:id,nombre',
                'usuario:id,name',
                'detalles:id,transferencia_id,articulo_id,cantidad,precio_compra_unidad',
                'detalles.articulo:id,nombre,codigo_producto,unidad_medida_id',
                'detalles.articulo.unidadMedida:id,nombre',
            ])
            ->withCount('detalles')
            ->withSum('detalles', 'cantidad')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('id', $search)
                        ->orWhere('observaciones', 'like', "%{$search}%")
                        ->orWhereHas('depositoOrigen', fn ($deposito) => $deposito->where('nombre', 'like', "%{$search}%"))
                        ->orWhereHas('depositoDestino', fn ($deposito) => $deposito->where('nombre', 'like', "%{$search}%"))
                        ->orWhereHas('detalles.articulo', fn ($articulo) => $articulo->where('nombre', 'like', "%{$search}%")
                            ->orWhere('codigo_producto', 'like', "%{$search}%"));
                });
            })
            ->latest('fecha_transferencia')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('admin.inventarios.transferencias.index', compact('transferencias', 'search'));
    }

    public function create()
    {
        $depositos = Deposito::select('id', 'nombre')->orderBy('nombre')->get();
        $inventarios = Inventario::query()
            ->select('id', 'deposito_id', 'articulo_id', 'cantidad', 'precio_compra_unidad')
            ->with([
                'articulo:id,nombre,codigo_producto,unidad_medida_id',
                'articulo.unidadMedida:id,nombre',
                'deposito:id,nombre',
            ])
            ->where('cantidad', '>', 0)
            ->orderBy('deposito_id')
            ->orderBy(
                \App\Models\Articulo::select('nombre')
                    ->whereColumn('articulos.id', 'inventarios.articulo_id')
                    ->limit(1)
            )
            ->get();

        return view('admin.inventarios.transferencias.create', compact('depositos', 'inventarios'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'deposito_origen_id' => ['required', 'integer', 'exists:depositos,id'],
            'deposito_destino_id' => ['required', 'integer', 'exists:depositos,id', 'different:deposito_origen_id'],
            'fecha_transferencia' => ['required', 'date'],
            'observaciones' => ['nullable', 'string'],
            'detalles' => ['required', 'array', 'min:1'],
            'detalles.*.inventario_origen_id' => ['required', 'integer', 'exists:inventarios,id'],
            'detalles.*.cantidad' => ['required', 'integer', 'min:1'],
        ]);

        DB::transaction(function () use ($validated) {
            $transferencia = TransferenciaDeposito::create([
                'deposito_origen_id' => $validated['deposito_origen_id'],
                'deposito_destino_id' => $validated['deposito_destino_id'],
                'usuario_id' => Auth::id(),
                'fecha_transferencia' => $validated['fecha_transferencia'],
                'estado' => 'confirmada',
                'observaciones' => $validated['observaciones'] ?? null,
            ]);

            foreach ($validated['detalles'] as $index => $detalle) {
                $inventarioOrigen = Inventario::query()
                    ->with('articulo')
                    ->where('deposito_id', $validated['deposito_origen_id'])
                    ->lockForUpdate()
                    ->findOrFail($detalle['inventario_origen_id']);

                $cantidad = (int) $detalle['cantidad'];

                if ((int) $inventarioOrigen->cantidad < $cantidad) {
                    throw ValidationException::withMessages([
                        "detalles.{$index}.cantidad" => 'La cantidad supera el stock disponible de ' . ($inventarioOrigen->articulo?->nombre ?? 'este articulo') . '.',
                    ]);
                }

                $inventarioOrigen->forceFill([
                    'cantidad' => (int) $inventarioOrigen->cantidad - $cantidad,
                    'estado' => 'traslado',
                ])->save();

                $inventarioDestino = Inventario::query()
                    ->where('deposito_id', $validated['deposito_destino_id'])
                    ->where('articulo_id', $inventarioOrigen->articulo_id)
                    ->lockForUpdate()
                    ->first();

                if (! $inventarioDestino) {
                    $inventarioDestino = Inventario::create([
                        'deposito_id' => $validated['deposito_destino_id'],
                        'articulo_id' => $inventarioOrigen->articulo_id,
                        'precio_compra_unidad' => $inventarioOrigen->precio_compra_unidad,
                        'cantidad' => 0,
                        'stock_minimo' => $inventarioOrigen->stock_minimo,
                        'stock_maximo' => $inventarioOrigen->stock_maximo,
                        'estado' => 'traslado',
                    ]);
                }

                $inventarioDestino->forceFill([
                    'cantidad' => (int) $inventarioDestino->cantidad + $cantidad,
                    'precio_compra_unidad' => $inventarioOrigen->precio_compra_unidad ?? $inventarioDestino->precio_compra_unidad,
                    'stock_minimo' => $inventarioOrigen->stock_minimo,
                    'stock_maximo' => $inventarioOrigen->stock_maximo,
                    'estado' => 'traslado',
                ])->save();

                $transferencia->detalles()->create([
                    'inventario_origen_id' => $inventarioOrigen->id,
                    'articulo_id' => $inventarioOrigen->articulo_id,
                    'cantidad' => $cantidad,
                    'precio_compra_unidad' => $inventarioOrigen->precio_compra_unidad,
                ]);
            }
        });

        return redirect()
            ->route('admin.inventarios.transferencias.index')
            ->with('success', 'Transferencia entre depositos registrada correctamente.');
    }

    public function show(string $id)
    {
        $transferencia = TransferenciaDeposito::with([
            'depositoOrigen',
            'depositoDestino',
            'usuario',
            'detalles.articulo.unidadMedida',
        ])->findOrFail($id);

        return view('admin.inventarios.transferencias.show', compact('transferencia'));
    }
}
