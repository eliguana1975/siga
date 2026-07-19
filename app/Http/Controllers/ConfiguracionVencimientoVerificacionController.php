<?php

namespace App\Http\Controllers;

use App\Models\ConfiguracionVencimientoVerificacion;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ConfiguracionVencimientoVerificacionController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->get('search', ''));
        $tipos = ConfiguracionVencimientoVerificacion::TIPOS;

        $configuraciones = ConfiguracionVencimientoVerificacion::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('tipo', 'like', "%{$search}%")
                        ->orWhere('nombre', 'like', "%{$search}%")
                        ->orWhere('estado', 'like', "%{$search}%")
                        ->orWhere('observaciones', 'like', "%{$search}%");
                });
            })
            ->orderBy('nombre')
            ->paginate(10)
            ->withQueryString();

        return view('admin.configuracion-vencimientos-verificacion.index', compact('configuraciones', 'search', 'tipos'));
    }

    public function store(Request $request)
    {
        ConfiguracionVencimientoVerificacion::create($this->validateData($request));

        return redirect()
            ->route('admin.configuracion-vencimientos-verificacion.index')
            ->with('success', 'Tipo de verificacion creado correctamente.');
    }

    public function update(Request $request, string $id)
    {
        $configuracion = ConfiguracionVencimientoVerificacion::findOrFail($id);
        $configuracion->update($this->validateData($request, $configuracion->id));

        return redirect()
            ->route('admin.configuracion-vencimientos-verificacion.index')
            ->with('success', 'Tipo de verificacion actualizado correctamente.');
    }

    public function destroy(string $id)
    {
        $configuracion = ConfiguracionVencimientoVerificacion::findOrFail($id);
        $configuracion->delete();

        return redirect()
            ->route('admin.configuracion-vencimientos-verificacion.index')
            ->with('success', 'Tipo de verificacion eliminado correctamente.');
    }

    private function validateData(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'tipo' => [
                'required',
                'string',
                'max:80',
                Rule::unique('configuracion_vencimientos_verificacion', 'tipo')->ignore($ignoreId),
            ],
            'nombre' => ['required', 'string', 'max:120'],
            'dias_alerta' => ['required', 'integer', 'min:1', 'max:365'],
            'estado' => ['required', Rule::in(['activo', 'inactivo'])],
            'observaciones' => ['nullable', 'string'],
        ]);
    }
}
