<?php

namespace App\Http\Controllers;

use App\Models\Compra;
use Illuminate\Http\Request;

class CompraController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->get('search', ''));
        $ordenesPendientes = Compra::query()
            ->where('estado', 'pendiente')
            ->count();

        $compras = Compra::query()
            ->with(['deposito', 'proveedor', 'pedidoArticulo', 'usuario', 'detalles.articulo.unidadMedida', 'detalles.proveedor', 'pagos'])
            ->whereIn('estado', ['aprobada', 'recibido'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('comprobante', 'like', "%{$search}%")
                        ->orWhere('forma_pago', 'like', "%{$search}%")
                        ->orWhere('datos_pago', 'like', "%{$search}%")
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

        return view('admin.compras.index', compact('compras', 'search', 'ordenesPendientes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return redirect()->route('admin.ordenes-compra.show', $id);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Compra $compra)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Compra $compra)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Compra $compra)
    {
        try {
            $compra->detalles()->delete();
            $compra->delete();

            return redirect()
                ->route('admin.compras.index')
                ->with('success', 'Compra eliminada correctamente.');
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.compras.index')
                ->with('error', 'Error al eliminar la compra: ' . $e->getMessage());
        }
    }
}
