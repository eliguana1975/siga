<?php

namespace App\Http\Controllers;

use App\Models\Titular;
use App\Support\CompanyTitularSync;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class TitularController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('check.permission:administrar-titular');
    }

    public function index(Request $request)
    {
        app(CompanyTitularSync::class)->sync();

        $search = trim((string) $request->input('search', ''));
        $query = Titular::query()->orderBy('nombre');

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query->where('nombre', 'like', '%' . $search . '%')
                    ->orWhere('telefono', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        $titulares = $query->paginate(5)->withQueryString();

        return view('admin.titulares.index', compact('titulares', 'search'));
    }

    public function store(Request $request)
    {
        $request->merge([
            'nombre' => mb_strtoupper(trim((string) $request->input('nombre')), 'UTF-8'),
            'telefono' => trim((string) $request->input('telefono')),
            'email' => trim((string) $request->input('email')),
            'direccion' => trim((string) $request->input('direccion')),
        ]);

        $validator = Validator::make($request->all(), [
            'nombre' => ['required', 'string', 'max:150'],
            'telefono' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:150'],
            'direccion' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('admin.titulares.index')
                ->withErrors($validator)
                ->withInput()
                ->with('open_modal', 'createTitularModal');
        }

        Titular::create($validator->validated());

        return redirect()
            ->route('admin.titulares.index')
            ->with('success', 'Titular creado correctamente.');
    }

    public function update(Request $request, string $id)
    {
        $titular = Titular::findOrFail($id);

        $request->merge([
            'nombre' => mb_strtoupper(trim((string) $request->input('nombre')), 'UTF-8'),
            'telefono' => trim((string) $request->input('telefono')),
            'email' => trim((string) $request->input('email')),
            'direccion' => trim((string) $request->input('direccion')),
        ]);

        $validator = Validator::make($request->all(), [
            'nombre' => ['required', 'string', 'max:150'],
            'telefono' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:150'],
            'direccion' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('admin.titulares.index')
                ->withErrors($validator)
                ->withInput()
                ->with('open_modal', 'editTitularModal-' . $titular->id);
        }

        $titular->update($validator->validated());

        return redirect()
            ->route('admin.titulares.index')
            ->with('success', 'Titular actualizado correctamente.');
    }

    public function destroy(string $id)
    {
        $titular = Titular::findOrFail($id);

        if ($titular->es_empresa) {
            return redirect()
                ->route('admin.titulares.index')
                ->with('error', 'El titular de la empresa se administra desde Ajustes del sistema.');
        }

        $titular->delete();

        return redirect()
            ->route('admin.titulares.index')
            ->with('success', 'Titular eliminado correctamente.');
    }
}
