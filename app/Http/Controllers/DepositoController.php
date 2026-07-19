<?php

namespace App\Http\Controllers;

use App\Models\Deposito;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class DepositoController extends Controller
{
    /**
     * Instantiate a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('check.permission:administrar-depositos');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));

        $query = Deposito::query()->orderBy('nombre');

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query->where('nombre', 'like', '%' . $search . '%')
                    ->orWhere('direccion', 'like', '%' . $search . '%')
                    ->orWhere('telefono', 'like', '%' . $search . '%');
            });
        }

        $depositos = $query->paginate(5)->withQueryString();

        return view('admin.depositos.index', compact('depositos', 'search'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->merge([
            'nombre' => mb_strtoupper(trim((string) $request->input('nombre')), 'UTF-8'),
            'telefono' => trim((string) $request->input('telefono')),
        ]);

        $validator = Validator::make($request->all(), [
            'nombre' => ['required', 'string', 'max:150'],
            'direccion' => ['nullable', 'string'],
            'telefono' => ['nullable', 'string', 'max:50'],
            'estado' => ['required', Rule::in(['activa', 'inactiva'])],
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('admin.depositos.index')
                ->withErrors($validator)
                ->withInput()
                ->with('open_modal', 'createDepositoModal');
        }

        Deposito::create($request->only(['nombre', 'direccion', 'telefono', 'estado']));

        return redirect()
            ->route('admin.depositos.index')
            ->with('success', 'Depósito creado correctamente.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $deposito = Deposito::findOrFail($id);

        $request->merge([
            'nombre' => mb_strtoupper(trim((string) $request->input('nombre')), 'UTF-8'),
            'telefono' => trim((string) $request->input('telefono')),
        ]);

        $validator = Validator::make($request->all(), [
            'nombre' => ['required', 'string', 'max:150'],
            'direccion' => ['nullable', 'string'],
            'telefono' => ['nullable', 'string', 'max:50'],
            'estado' => ['required', Rule::in(['activa', 'inactiva'])],
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('admin.depositos.index')
                ->withErrors($validator)
                ->withInput()
                ->with('open_modal', 'editDepositoModal-' . $deposito->id);
        }

        $deposito->update($request->only(['nombre', 'direccion', 'telefono', 'estado']));

        return redirect()
            ->route('admin.depositos.index')
            ->with('success', 'Depósito actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $deposito = Deposito::findOrFail($id);
        $deposito->delete();

        return redirect()
            ->route('admin.depositos.index')
            ->with('success', 'Depósito eliminado correctamente.');
    }
}
