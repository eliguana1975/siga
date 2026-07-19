<?php

namespace App\Http\Controllers;

use App\Models\ConfiguracionIntervaloServicio;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ConfiguracionIntervaloServicioController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->get('search', ''));
        $sistemas = ConfiguracionIntervaloServicio::SISTEMAS;
        $unidades = ConfiguracionIntervaloServicio::UNIDADES;

        $intervalos = ConfiguracionIntervaloServicio::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('nombre', 'like', "%{$search}%")
                        ->orWhere('sistema', 'like', "%{$search}%")
                        ->orWhere('unidad_intervalo', 'like', "%{$search}%")
                        ->orWhere('estado', 'like', "%{$search}%")
                        ->orWhere('observaciones', 'like', "%{$search}%");
                });
            })
            ->orderBy('sistema')
            ->orderBy('unidad_intervalo')
            ->orderBy('kilometros_intervalo')
            ->orderBy('nombre')
            ->paginate(10)
            ->withQueryString();

        return view('admin.configuracion-intervalos-servicio.index', compact('intervalos', 'search', 'sistemas', 'unidades'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateData($request);

        ConfiguracionIntervaloServicio::create($validated);

        return redirect()
            ->route('admin.configuracion-intervalos-servicio.index')
            ->with('success', 'Intervalo de servicio creado correctamente.');
    }

    public function update(Request $request, string $id)
    {
        $intervalo = ConfiguracionIntervaloServicio::findOrFail($id);
        $validated = $this->validateData($request, $intervalo->id);

        $intervalo->update($validated);

        return redirect()
            ->route('admin.configuracion-intervalos-servicio.index')
            ->with('success', 'Intervalo de servicio actualizado correctamente.');
    }

    public function destroy(string $id)
    {
        $intervalo = ConfiguracionIntervaloServicio::findOrFail($id);
        $intervalo->delete();

        return redirect()
            ->route('admin.configuracion-intervalos-servicio.index')
            ->with('success', 'Intervalo de servicio eliminado correctamente.');
    }

    private function validateData(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'sistema' => ['required', 'string', 'max:120'],
            'nombre' => [
                'required',
                'string',
                'max:120',
                Rule::unique('configuracion_intervalos_servicio')
                    ->where(fn ($query) => $query
                        ->where('sistema', $request->input('sistema'))
                        ->where('unidad_intervalo', $request->input('unidad_intervalo')))
                    ->ignore($ignoreId),
            ],
            'kilometros_intervalo' => ['required', 'integer', 'min:1', 'max:999999'],
            'unidad_intervalo' => ['required', Rule::in(array_keys(ConfiguracionIntervaloServicio::UNIDADES))],
            'estado' => ['required', Rule::in(['activo', 'inactivo'])],
            'observaciones' => ['nullable', 'string'],
        ]);
    }
}
