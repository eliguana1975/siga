<?php

namespace App\Http\Controllers;

use App\Models\Ciudad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CiudadController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('check.permission:administrar-ciudades');
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));

        $query = Ciudad::query()->orderBy('nombre');

        if ($search !== '') {
            $query->where('nombre', 'like', '%' . $search . '%');
        }

        $ciudades = $query->paginate(5)->withQueryString();

        return view('admin.ciudades.index', compact('ciudades', 'search'));
    }

    public function store(Request $request)
    {
        $request->merge([
            'nombre' => mb_strtoupper(trim((string) $request->input('nombre')), 'UTF-8'),
        ]);

        $validator = Validator::make($request->all(), [
            'nombre' => ['required', 'string', 'max:150', 'unique:ciudades,nombre'],
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('admin.ciudades.index')
                ->withErrors($validator)
                ->withInput()
                ->with('open_modal', 'createCiudadModal');
        }

        Ciudad::create($request->only('nombre'));

        return redirect()
            ->route('admin.ciudades.index')
            ->with('success', 'Ciudad creada correctamente.');
    }

    public function update(Request $request, string $id)
    {
        $ciudad = Ciudad::findOrFail($id);

        $request->merge([
            'nombre' => mb_strtoupper(trim((string) $request->input('nombre')), 'UTF-8'),
        ]);

        $validator = Validator::make($request->all(), [
            'nombre' => ['required', 'string', 'max:150', 'unique:ciudades,nombre,' . $ciudad->id],
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('admin.ciudades.index')
                ->withErrors($validator)
                ->withInput()
                ->with('open_modal', 'editCiudadModal-' . $ciudad->id);
        }

        $ciudad->update($request->only('nombre'));

        return redirect()
            ->route('admin.ciudades.index')
            ->with('success', 'Ciudad actualizada correctamente.');
    }

    public function destroy(string $id)
    {
        $ciudad = Ciudad::findOrFail($id);
        $ciudad->delete();

        return redirect()
            ->route('admin.ciudades.index')
            ->with('success', 'Ciudad eliminada correctamente.');
    }
}
