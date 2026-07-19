<?php

namespace App\Http\Controllers;

use App\Models\Base;
use App\Models\Deposito;
use App\Models\Provincia;
use App\Models\Ciudad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class BaseController extends Controller
{
    /**
     * Instantiate a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('check.permission:administrar-bases');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));

        $query = Base::query()
            ->with(['deposito', 'provincia', 'ciudad'])
            ->orderBy('nombre');

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query->where('nombre', 'like', '%' . $search . '%')
                    ->orWhere('direccion', 'like', '%' . $search . '%')
                    ->orWhere('telefono', 'like', '%' . $search . '%');
            });
        }

        $bases = $query->paginate(5)->withQueryString();
        $depositos = Deposito::orderBy('nombre')->get();
        $provincias = Provincia::orderBy('nombre')->get();
        $ciudades = Ciudad::orderBy('nombre')->get();

        return view('admin.bases.index', compact('bases', 'depositos', 'provincias', 'ciudades', 'search'));
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
            'deposito_id' => ['required', 'exists:depositos,id'],
            'provincia_id' => ['required', 'exists:provincias,id'],
            'ciudad_id' => ['required', 'exists:ciudades,id'],
            'nombre' => ['required', 'string', 'max:150'],
            'direccion' => ['nullable', 'string'],
            'telefono' => ['nullable', 'string', 'max:50'],
            'estado' => ['required', Rule::in(['activa', 'inactiva'])],
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('admin.bases.index')
                ->withErrors($validator)
                ->withInput()
                ->with('open_modal', 'createBaseModal');
        }

        Base::create($request->only(['deposito_id', 'provincia_id', 'ciudad_id', 'nombre', 'direccion', 'telefono', 'estado']));

        return redirect()
            ->route('admin.bases.index')
            ->with('success', 'Base creada correctamente.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $base = Base::findOrFail($id);

        $request->merge([
            'nombre' => mb_strtoupper(trim((string) $request->input('nombre')), 'UTF-8'),
            'telefono' => trim((string) $request->input('telefono')),
        ]);

        $validator = Validator::make($request->all(), [
            'deposito_id' => ['required', 'exists:depositos,id'],
            'provincia_id' => ['required', 'exists:provincias,id'],
            'ciudad_id' => ['required', 'exists:ciudades,id'],
            'nombre' => ['required', 'string', 'max:150'],
            'direccion' => ['nullable', 'string'],
            'telefono' => ['nullable', 'string', 'max:50'],
            'estado' => ['required', Rule::in(['activa', 'inactiva'])],
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('admin.bases.index')
                ->withErrors($validator)
                ->withInput()
                ->with('open_modal', 'editBaseModal-' . $base->id);
        }

        $base->update($request->only(['deposito_id', 'provincia_id', 'ciudad_id', 'nombre', 'direccion', 'telefono', 'estado']));

        return redirect()
            ->route('admin.bases.index')
            ->with('success', 'Base actualizada correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $base = Base::findOrFail($id);
        $base->delete();

        return redirect()
            ->route('admin.bases.index')
            ->with('success', 'Base eliminada correctamente.');
    }
}
