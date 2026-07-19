<?php

namespace App\Http\Controllers;

use App\Models\Articulo;
use App\Models\ConfiguracionIntervaloServicio;
use App\Models\Flota;
use App\Models\FlotaRepuesto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class FlotaRepuestoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(string $flotaId)
    {
        $flota = Flota::with([
            'marcaVehiculo',
            'tipoVehiculo',
            'repuestos.articulo.unidadMedida',
            'repuestos.configuracionIntervaloServicio',
        ])->findOrFail($flotaId);

        $articulos = Articulo::with('unidadMedida')
            ->where('estado_item', 'activo')
            ->orderBy('nombre')
            ->get();

        $intervalosServicio = ConfiguracionIntervaloServicio::query()
            ->where('estado', 'activo')
            ->orderByRaw("CASE WHEN LOWER(sistema) = 'motor' OR LOWER(nombre) LIKE '%motor%' THEN 0 ELSE 1 END")
            ->orderBy('sistema')
            ->orderBy('unidad_intervalo')
            ->orderBy('kilometros_intervalo')
            ->orderBy('nombre')
            ->get();

        return view('admin.flota.repuestos', compact('flota', 'articulos', 'intervalosServicio'));
    }

    public function store(Request $request, string $flotaId)
    {
        $flota = Flota::findOrFail($flotaId);
        $validator = $this->validator($request);

        if ($validator->fails()) {
            return redirect()
                ->route('admin.flota.repuestos.index', $flota->id)
                ->withErrors($validator)
                ->withInput()
                ->with('open_modal', 'createRepuestoModal');
        }

        $flota->repuestos()->create($this->payload($validator->validated()));

        return redirect()
            ->route('admin.flota.repuestos.index', $flota->id)
            ->with('success', 'Repuesto agregado correctamente.');
    }

    public function update(Request $request, string $flotaId, string $repuestoId)
    {
        $flota = Flota::findOrFail($flotaId);
        $repuesto = FlotaRepuesto::where('flota_id', $flota->id)->findOrFail($repuestoId);
        $validator = $this->validator($request);

        if ($validator->fails()) {
            return redirect()
                ->route('admin.flota.repuestos.index', $flota->id)
                ->withErrors($validator)
                ->withInput()
                ->with('open_modal', 'editRepuestoModal-' . $repuesto->id);
        }

        $repuesto->update($this->payload($validator->validated()));

        return redirect()
            ->route('admin.flota.repuestos.index', $flota->id)
            ->with('success', 'Repuesto actualizado correctamente.');
    }

    public function destroy(string $flotaId, string $repuestoId)
    {
        $flota = Flota::findOrFail($flotaId);
        $repuesto = FlotaRepuesto::where('flota_id', $flota->id)->findOrFail($repuestoId);
        $repuesto->delete();

        return redirect()
            ->route('admin.flota.repuestos.index', $flota->id)
            ->with('success', 'Repuesto eliminado correctamente.');
    }

    private function validator(Request $request)
    {
        return Validator::make($request->all(), [
            'articulo_id' => ['nullable', 'integer', 'exists:articulos,id'],
            'configuracion_intervalo_servicio_id' => ['nullable', 'integer', 'exists:configuracion_intervalos_servicio,id'],
            'cantidad_servicio' => ['nullable', 'integer', 'min:1'],
            'modo_carga_servicio' => ['required', Rule::in(['automatico', 'manual'])],
            'obligatorio_servicio' => ['nullable', 'boolean'],
            'nombre_repuesto' => ['nullable', 'string', 'max:180', Rule::requiredIf(! $request->filled('articulo_id'))],
            'codigo_referencia' => ['nullable', 'string', 'max:100'],
            'marca' => ['nullable', 'string', 'max:100'],
            'observaciones' => ['nullable', 'string'],
            'estado' => ['required', Rule::in(['activo', 'inactivo'])],
        ], [
            'nombre_repuesto.required' => 'Ingrese un repuesto manual o seleccione un articulo.',
        ]);
    }

    private function payload(array $data): array
    {
        return [
            'articulo_id' => $data['articulo_id'] ?? null,
            'configuracion_intervalo_servicio_id' => $data['configuracion_intervalo_servicio_id'] ?? null,
            'cantidad_servicio' => max(1, (int) ($data['cantidad_servicio'] ?? 1)),
            'modo_carga_servicio' => $data['modo_carga_servicio'] ?? 'manual',
            'obligatorio_servicio' => (bool) ($data['obligatorio_servicio'] ?? false),
            'nombre_repuesto' => trim((string) ($data['nombre_repuesto'] ?? '')) ?: null,
            'codigo_referencia' => trim((string) ($data['codigo_referencia'] ?? '')) ?: null,
            'marca' => trim((string) ($data['marca'] ?? '')) ?: null,
            'observaciones' => trim((string) ($data['observaciones'] ?? '')) ?: null,
            'estado' => $data['estado'],
        ];
    }
}
