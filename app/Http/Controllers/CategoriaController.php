<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CategoriaController extends Controller
{
    /**
     * Instantiate a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('check.permission:administrar-categorias');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));

        $query = Categoria::query()->orderBy('nombre');

        if ($search !== '') {
            $query->where('nombre', 'like', '%' . $search . '%');
        }

        $categorias = $query->paginate(5)->withQueryString();

        return view('admin.categorias.index', compact('categorias', 'search'));
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
            'nombre' => ['required', 'string', 'max:150', 'unique:categorias,nombre'],
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('admin.categorias.index')
                ->withErrors($validator)
                ->withInput()
                ->with('open_modal', 'createCategoriaModal');
        }

        Categoria::create($request->only('nombre'));

        return redirect()
            ->route('admin.categorias.index')
            ->with('success', 'Categoría creada correctamente.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $categoria = Categoria::findOrFail($id);

        $request->merge([
            'nombre' => mb_strtoupper(trim((string) $request->input('nombre')), 'UTF-8'),
        ]);

        $validator = Validator::make($request->all(), [
            'nombre' => [
                'required',
                'string',
                'max:150',
                Rule::unique('categorias', 'nombre')->ignore($categoria->id),
            ],
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('admin.categorias.index')
                ->withErrors($validator)
                ->withInput()
                ->with('open_modal', 'editCategoriaModal-' . $categoria->id);
        }

        $categoria->update($request->only('nombre'));

        return redirect()
            ->route('admin.categorias.index')
            ->with('success', 'Categoría actualizada correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $categoria = Categoria::findOrFail($id);
        $categoria->delete();

        return redirect()
            ->route('admin.categorias.index')
            ->with('success', 'Categoría eliminada correctamente.');
    }
}
