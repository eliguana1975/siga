<?php

namespace App\Http\Controllers;

use App\Models\Provincia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProvinciaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('check.permission:administrar-provincias');
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));

        $query = Provincia::query()->orderBy('nombre');

        if ($search !== '') {
            $query->where('nombre', 'like', '%' . $search . '%');
        }

        $provincias = $query->paginate(5)->withQueryString();

        return view('admin.provincias.index', compact('provincias', 'search'));
    }

    public function store(Request $request)
    {
        $request->merge([
            'nombre' => mb_strtoupper(trim((string) $request->input('nombre')), 'UTF-8'),
        ]);

        $validator = Validator::make($request->all(), [
            'nombre' => ['required', 'string', 'max:150', 'unique:provincias,nombre'],
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('admin.provincias.index')
                ->withErrors($validator)
                ->withInput()
                ->with('open_modal', 'createProvinciaModal');
        }

        Provincia::create($request->only('nombre'));

        return redirect()
            ->route('admin.provincias.index')
            ->with('success', 'Provincia creada correctamente.');
    }

    public function update(Request $request, string $id)
    {
        $provincia = Provincia::findOrFail($id);

        $request->merge([
            'nombre' => mb_strtoupper(trim((string) $request->input('nombre')), 'UTF-8'),
        ]);

        $validator = Validator::make($request->all(), [
            'nombre' => ['required', 'string', 'max:150', 'unique:provincias,nombre,' . $provincia->id],
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('admin.provincias.index')
                ->withErrors($validator)
                ->withInput()
                ->with('open_modal', 'editProvinciaModal-' . $provincia->id);
        }

        $provincia->update($request->only('nombre'));

        return redirect()
            ->route('admin.provincias.index')
            ->with('success', 'Provincia actualizada correctamente.');
    }

    public function destroy(string $id)
    {
        $provincia = Provincia::findOrFail($id);
        $provincia->delete();

        return redirect()
            ->route('admin.provincias.index')
            ->with('success', 'Provincia eliminada correctamente.');
    }
}
