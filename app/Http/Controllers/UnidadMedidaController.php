<?php

namespace App\Http\Controllers;

use App\Models\UnidadMedida;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UnidadMedidaController extends Controller
{
    /**
     * Instantiate a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('check.permission:administrar-unidad-medidas');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));

        $query = UnidadMedida::query()->orderBy('nombre');

        if ($search !== '') {
            $query->where('nombre', 'like', '%' . $search . '%');
        }

        $unidadMedidas = $query->paginate(5)->withQueryString();

        return view('admin.unidad-medidas.index', compact('unidadMedidas', 'search'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->merge([
            'nombre' => mb_strtoupper(trim((string) $request->input('nombre')), 'UTF-8'),
        ]);

        $validator = Validator::make($request->all(), [
            'nombre' => ['required', 'string', 'max:150', 'unique:unidad_medidas,nombre'],
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('admin.unidad-medidas.index')
                ->withErrors($validator)
                ->withInput()
                ->with('open_modal', 'createUnidadMedidaModal');
        }

        UnidadMedida::create($request->only('nombre'));

        return redirect()
            ->route('admin.unidad-medidas.index')
            ->with('success', 'Unidad de medida creada correctamente.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $unidadMedida = UnidadMedida::findOrFail($id);

        $request->merge([
            'nombre' => mb_strtoupper(trim((string) $request->input('nombre')), 'UTF-8'),
        ]);

        $validator = Validator::make($request->all(), [
            'nombre' => [
                'required',
                'string',
                'max:150',
                Rule::unique('unidad_medidas', 'nombre')->ignore($unidadMedida->id),
            ],
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('admin.unidad-medidas.index')
                ->withErrors($validator)
                ->withInput()
                ->with('open_modal', 'editUnidadMedidaModal-' . $unidadMedida->id);
        }

        $unidadMedida->update($request->only('nombre'));

        return redirect()
            ->route('admin.unidad-medidas.index')
            ->with('success', 'Unidad de medida actualizada correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $unidadMedida = UnidadMedida::findOrFail($id);
        $unidadMedida->delete();

        return redirect()
            ->route('admin.unidad-medidas.index')
            ->with('success', 'Unidad de medida eliminada correctamente.');
    }
}
