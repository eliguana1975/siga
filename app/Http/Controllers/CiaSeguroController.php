<?php

namespace App\Http\Controllers;

use App\Models\CiaSeguro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CiaSeguroController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('check.permission:administrar-cia-seguro');
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $query = CiaSeguro::query()->orderBy('nombre');

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query->where('nombre', 'like', '%' . $search . '%')
                    ->orWhere('telefono', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('direccion', 'like', '%' . $search . '%')
                    ->orWhere('contacto', 'like', '%' . $search . '%');
            });
        }

        $ciasSeguros = $query->paginate(5)->withQueryString();

        return view('admin.cias_seguro.index', compact('ciasSeguros', 'search'));
    }

    public function store(Request $request)
    {
        $request->merge([
            'nombre' => mb_strtoupper(trim((string) $request->input('nombre')), 'UTF-8'),
            'telefono' => trim((string) $request->input('telefono')),
            'email' => trim((string) $request->input('email')),
            'direccion' => trim((string) $request->input('direccion')),
            'contacto' => trim((string) $request->input('contacto')),
            'notas' => trim((string) $request->input('notas')),
        ]);

        $validator = Validator::make($request->all(), [
            'nombre' => ['required', 'string', 'max:150'],
            'telefono' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:150'],
            'direccion' => ['nullable', 'string'],
            'contacto' => ['nullable', 'string', 'max:150'],
            'notas' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('admin.cia-seguro.index')
                ->withErrors($validator)
                ->withInput()
                ->with('open_modal', 'createCiaSeguroModal');
        }

        CiaSeguro::create($validator->validated());

        return redirect()
            ->route('admin.cia-seguro.index')
            ->with('success', 'Compañía de seguro creada correctamente.');
    }

    public function update(Request $request, string $id)
    {
        $cia = CiaSeguro::findOrFail($id);

        $request->merge([
            'nombre' => mb_strtoupper(trim((string) $request->input('nombre')), 'UTF-8'),
            'telefono' => trim((string) $request->input('telefono')),
            'email' => trim((string) $request->input('email')),
            'direccion' => trim((string) $request->input('direccion')),
            'contacto' => trim((string) $request->input('contacto')),
            'notas' => trim((string) $request->input('notas')),
        ]);

        $validator = Validator::make($request->all(), [
            'nombre' => ['required', 'string', 'max:150'],
            'telefono' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:150'],
            'direccion' => ['nullable', 'string'],
            'contacto' => ['nullable', 'string', 'max:150'],
            'notas' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('admin.cia-seguro.index')
                ->withErrors($validator)
                ->withInput()
                ->with('open_modal', 'editCiaSeguroModal-' . $cia->id);
        }

        $cia->update($validator->validated());

        return redirect()
            ->route('admin.cia-seguro.index')
            ->with('success', 'Compañía de seguro actualizada correctamente.');
    }

    public function destroy(string $id)
    {
        $cia = CiaSeguro::findOrFail($id);
        $cia->delete();

        return redirect()
            ->route('admin.cia-seguro.index')
            ->with('success', 'Compañía de seguro eliminada correctamente.');
    }
}
