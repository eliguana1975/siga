<?php

namespace App\Http\Controllers;

use App\Models\CiaSeguro;
use App\Models\Articulo;
use App\Models\Cubierta;
use App\Models\Flota;
use App\Models\FlotaCubiertaEje;
use App\Models\FlotaServicioAsignadoHistorial;
use App\Models\MarcaCarroceria;
use App\Models\ModeloCaja;
use App\Models\ModeloMotor;
use App\Models\ServicioAsignado;
use App\Models\TipoCaja;
use App\Models\TipoMotor;
use App\Models\TipoVehiculo;
use App\Models\MarcaVehiculo;
use App\Models\Titular;
use App\Support\CompanyTitularSync;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class FlotaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));

        $query = Flota::with([
            'tipoMotor',
            'modeloMotor',
            'marcaCarroceria',
            'titular',
            'tipoVehiculo',
            'ciaSeguro',
            'modeloCaja',
            'tipoCaja',
            'servicioAsignadoActual',
            'repuestos.articulo',
            
        ])->orderBy('nro_interno');

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query->where('nro_interno', 'like', '%' . $search . '%')
                    ->orWhere('dominio', 'like', '%' . $search . '%')
                    ->orWhere('nro_motor', 'like', '%' . $search . '%')
                    ->orWhere('nro_chasis', 'like', '%' . $search . '%')
                    ->orWhere('tipo_aceite_motor', 'like', '%' . $search . '%')
                    ->orWhere('tipo_aceite_caja', 'like', '%' . $search . '%')
                    ->orWhereHas('servicioAsignadoActual', function ($query) use ($search) {
                        $query->where('nombre', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('repuestos', function ($query) use ($search) {
                        $query->where('nombre_repuesto', 'like', '%' . $search . '%')
                            ->orWhere('codigo_referencia', 'like', '%' . $search . '%')
                            ->orWhere('marca', 'like', '%' . $search . '%')
                            ->orWhere('observaciones', 'like', '%' . $search . '%')
                            ->orWhereHas('articulo', function ($articulo) use ($search) {
                                $articulo->where('nombre', 'like', '%' . $search . '%')
                                    ->orWhere('codigo_producto', 'like', '%' . $search . '%');
                            });
                    });
            });
        }

        $flotas = $query->paginate(10)->withQueryString();

        return view('admin.flota.index', compact('flotas', 'search'));
    }

    public function create()
    {
        return view('admin.flota.create', $this->formOptions());
    }

    public function edit(string $id)
    {
        $flota = Flota::with('cubiertaEjes')->findOrFail($id);

        return view('admin.flota.edit', array_merge(
            ['flota' => $flota],
            $this->formOptions()
        ));
    }

    public function editServicioAsignado(string $id)
    {
        $flota = Flota::with([
            'servicioAsignadoActual',
            'historialServiciosAsignados.servicioAsignado',
        ])->findOrFail($id);

        return view('admin.flota.servicio-asignado', [
            'flota' => $flota,
            'serviciosAsignados' => ServicioAsignado::select(['id', 'nombre'])->orderBy('nombre')->get(),
        ]);
    }

    private function formOptions(): array
    {
        app(CompanyTitularSync::class)->sync();

        $tipoMotores = TipoMotor::select(['id', 'nombre'])->orderBy('nombre')->get();
        $modeloMotores = ModeloMotor::select(['id', 'nombre'])->orderBy('nombre')->get();
        $marcaCarrocerias = MarcaCarroceria::select(['id', 'nombre'])->orderBy('nombre')->get();
        $titulares = Titular::select(['id', 'nombre'])->orderBy('nombre')->get();
        $tipoVehiculos = TipoVehiculo::select(['id', 'nombre'])->orderBy('nombre')->get();
        $ciasSeguros = CiaSeguro::select(['id', 'nombre'])->orderBy('nombre')->get();
        $modeloCajas = ModeloCaja::select(['id', 'nombre'])->orderBy('nombre')->get();
        $tipoCajas = TipoCaja::select(['id', 'nombre'])->orderBy('nombre')->get();
        $marcasVehiculos = MarcaVehiculo::select(['id', 'nombre'])->orderBy('nombre')->get();
        $tiposMedidorServicio = Flota::TIPOS_MEDIDOR_SERVICIO;
        $tiposEjeCubierta = FlotaCubiertaEje::TIPOS;

        // Prioriza articulos que realmente tienen cubiertas asociadas
        // para que la configuracion de medida/articulo no quede vacia.
        $articulosCubiertas = Cubierta::query()
            ->with('articulo:id,nombre,codigo_producto')
            ->whereNotNull('articulo_id')
            ->get()
            ->pluck('articulo')
            ->filter()
            ->unique('id')
            ->sortBy('nombre')
            ->values();

        if ($articulosCubiertas->isEmpty()) {
            $articulosCubiertas = Articulo::query()
                ->select(['id', 'nombre', 'codigo_producto'])
                ->whereHas('categoria', fn ($query) => $query->where('nombre', 'like', '%cubierta%'))
                ->orderBy('nombre')
                ->get();
        }

        return compact(
            'tipoMotores',
            'modeloMotores',
            'marcaCarrocerias',
            'titulares',
            'tipoVehiculos',
            'ciasSeguros',
            'modeloCajas',
            'tipoCajas',
            'marcasVehiculos',
            'tiposMedidorServicio',
            'tiposEjeCubierta',
            'articulosCubiertas'
        );
    }

    public function store(Request $request)
    {
        // handle file uploads first
        $fileFields = ['foto_flota', 'foto_flota_2', 'foto_flota_3', 'foto_flota_4'];
        $payload = $request->all();

        foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                $payload[$field] = $request->file($field)->store('flota', 'public');
            }
        }

        $validator = Validator::make($payload, $this->rules());

        if ($validator->fails()) {
            return redirect()
                ->route('admin.flota.create')
                ->withErrors($validator)
                ->withInput();
        }

        DB::transaction(function () use ($validator, $request) {
            $flota = Flota::create($validator->validated());
            $this->syncCubiertaEjes($flota, $request->input('cubierta_ejes', []));
        });

        return redirect()
            ->route('admin.flota.index')
            ->with('success', 'Registro de flota creado correctamente.');
    }

    public function update(Request $request, string $id)
    {
        $flota = Flota::findOrFail($id);
        // handle file uploads
        $fileFields = ['foto_flota', 'foto_flota_2', 'foto_flota_3', 'foto_flota_4'];
        $payload = $request->all();

        foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                // remove old file if exists
                if ($flota->{$field}) {
                    try {\Illuminate\Support\Facades\Storage::disk('public')->delete($flota->{$field}); } catch (\Throwable $e) {}
                }
                $payload[$field] = $request->file($field)->store('flota', 'public');
            }
        }

        $validator = Validator::make($payload, array_merge($this->rules(), [
            'nro_interno' => ['required', 'string', 'max:50', Rule::unique('flota', 'nro_interno')->ignore($flota->id)],
            'dominio' => ['required', 'string', 'max:20', Rule::unique('flota', 'dominio')->ignore($flota->id)],
            'nro_motor' => ['required', 'string', 'max:50', Rule::unique('flota', 'nro_motor')->ignore($flota->id)],
            'nro_chasis' => ['required', 'string', 'max:50', Rule::unique('flota', 'nro_chasis')->ignore($flota->id)],
        ]));

        if ($validator->fails()) {
            return redirect()
                ->route('admin.flota.edit', $flota->id)
                ->withErrors($validator)
                ->withInput();
        }

        DB::transaction(function () use ($flota, $validator, $request) {
            $flota->update($validator->validated());
            $this->syncCubiertaEjes($flota, $request->input('cubierta_ejes', []));
        });

        return redirect()
            ->route('admin.flota.index')
            ->with('success', 'Registro de flota actualizado correctamente.');
    }

    public function updateServicioAsignado(Request $request, string $id)
    {
        $flota = Flota::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'servicio_asignado_actual_id' => ['nullable', 'integer', 'exists:servicios_asignados,id'],
            'observaciones' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('admin.flota.servicio-asignado.edit', $flota->id)
                ->withErrors($validator)
                ->withInput();
        }

        $data = $validator->validated();

        DB::transaction(function () use ($flota, $data) {
            $servicioAsignadoId = $data['servicio_asignado_actual_id'] ?? null;

            $flota->update([
                'servicio_asignado_actual_id' => $servicioAsignadoId,
            ]);

            $this->actualizarServicioAsignado(
                $flota,
                $servicioAsignadoId,
                $data['observaciones'] ?? null
            );
        });

        return redirect()
            ->route('admin.flota.servicio-asignado.edit', $flota->id)
            ->with('success', 'Servicio asignado actualizado correctamente.');
    }

    public function destroy(string $id)
    {
        $flota = Flota::findOrFail($id);
        $flota->delete();

        return redirect()
            ->route('admin.flota.index')
            ->with('success', 'Registro de flota eliminado correctamente.');
    }

    protected function normalizeRequest(Request $request): array
    {
        return [
            'tipo_motor_id' => $request->input('tipo_motor_id'),
            'modelo_motor_id' => $request->input('modelo_motor_id'),
            'cod_marca_carroceria_id' => $request->input('cod_marca_carroceria_id'),
            'cod_titular_id' => $request->input('cod_titular_id'),
            'cod_tipo_vehiculo_id' => $request->input('cod_tipo_vehiculo_id'),
            'cod_cia_seguro_id' => $request->input('cod_cia_seguro_id'),
            'modelo_caja_id' => $request->input('modelo_caja_id'),
            'tipo_caja_id' => $request->input('tipo_caja_id'),
            'marca_vehiculo_id' => $request->input('marca_vehiculo_id'),
            'tipo_aceite_motor' => trim((string) $request->input('tipo_aceite_motor')),
            'tipo_aceite_caja' => trim((string) $request->input('tipo_aceite_caja')),
            'nro_interno' => mb_strtoupper(trim((string) $request->input('nro_interno')), 'UTF-8'),
            'dominio' => mb_strtoupper(trim((string) $request->input('dominio')), 'UTF-8'),
            'estado' => $request->input('estado'),
            'tipo_medidor_servicio' => $request->input('tipo_medidor_servicio', 'km'),
            'horometro_actual' => $request->input('horometro_actual', 0),
            'nro_motor' => mb_strtoupper(trim((string) $request->input('nro_motor')), 'UTF-8'),
            'nro_chasis' => mb_strtoupper(trim((string) $request->input('nro_chasis')), 'UTF-8'),
            'cant_aceite_motor' => $request->input('cant_aceite_motor'),
            'cant_aceite_caja' => $request->input('cant_aceite_caja'),
            'med_cub_delanteras' => trim((string) $request->input('med_cub_delanteras')),
            'med_cub_traseras' => trim((string) $request->input('med_cub_traseras')),
            'cantidad_pasajeros' => $request->input('cantidad_pasajeros'),
            'anio_fabricacion' => $request->input('anio_fabricacion'),
            'nro_poliza' => trim((string) $request->input('nro_poliza')),
            'estado_seguro' => $request->input('estado_seguro'),
            'observaciones' => trim((string) $request->input('observaciones')),
            'foto_flota' => $request->input('foto_flota'),
            'foto_flota_2' => $request->input('foto_flota_2'),
            'foto_flota_3' => $request->input('foto_flota_3'),
            'foto_flota_4' => $request->input('foto_flota_4'),
        ];
    }

    protected function rules(): array
    {
        return [
            'tipo_motor_id' => ['required', 'integer', 'exists:tipo_motor,id'],
            'modelo_motor_id' => ['required', 'integer', 'exists:modelo_motor,id'],
            'cod_marca_carroceria_id' => ['required', 'integer', 'exists:marca_carroceria,id'],
            'cod_titular_id' => ['required', 'integer', 'exists:titular,id'],
            'cod_tipo_vehiculo_id' => ['required', 'integer', 'exists:tipo_vehiculo,id'],
            'cod_cia_seguro_id' => ['required', 'integer', 'exists:cia_seguro,id'],
            'marca_vehiculo_id' => ['required', 'integer', 'exists:marca_vehiculo,id'],
            'modelo_caja_id' => ['required', 'integer', 'exists:modelo_caja,id'],
            'tipo_caja_id' => ['required', 'integer', 'exists:tipo_caja,id'],
            'tipo_aceite_motor' => ['required', 'string', 'max:50'],
            'tipo_aceite_caja' => ['required', 'string', 'max:50'],
            'nro_interno' => ['required', 'string', 'max:50', 'unique:flota,nro_interno'],
            'dominio' => ['required', 'string', 'max:20', 'unique:flota,dominio'],
            'estado' => ['required', Rule::in(['activo', 'baja', 'mantenimiento'])],
            'tipo_medidor_servicio' => ['required', Rule::in(array_keys(Flota::TIPOS_MEDIDOR_SERVICIO))],
            'horometro_actual' => ['nullable', 'integer', 'min:0'],
            'nro_motor' => ['required', 'string', 'max:50', 'unique:flota,nro_motor'],
            'nro_chasis' => ['required', 'string', 'max:50', 'unique:flota,nro_chasis'],
            'cant_aceite_motor' => ['required', 'integer'],
            'cant_aceite_caja' => ['required', 'integer'],
            'med_cub_delanteras' => ['required', 'string', 'max:50'],
            'med_cub_traseras' => ['required', 'string', 'max:50'],
            'cantidad_pasajeros' => ['nullable', 'integer'],
            'anio_fabricacion' => ['nullable', 'integer'],
            'nro_poliza' => ['nullable', 'string', 'max:100'],
            'estado_seguro' => ['required', Rule::in(['Activo', 'Baja'])],
            'observaciones' => ['nullable', 'string'],
            'foto_flota' => ['nullable', 'string', 'max:255'],
            'foto_flota_2' => ['nullable', 'string', 'max:255'],
            'foto_flota_3' => ['nullable', 'string', 'max:255'],
            'foto_flota_4' => ['nullable', 'string', 'max:255'],
        ];
    }

    private function syncCubiertaEjes(Flota $flota, array $ejes): void
    {
        $validator = Validator::make(['cubierta_ejes' => $ejes], [
            'cubierta_ejes' => ['nullable', 'array'],
            'cubierta_ejes.*.numero_eje' => ['required', 'integer', 'min:1', 'max:20'],
            'cubierta_ejes.*.tipo_eje' => ['required', Rule::in(array_keys(FlotaCubiertaEje::TIPOS))],
            'cubierta_ejes.*.articulo_cubierta_id' => ['nullable', 'integer', 'exists:articulos,id'],
            'cubierta_ejes.*.cubiertas_izquierda' => ['required', 'integer', 'min:0', 'max:4'],
            'cubierta_ejes.*.cubiertas_derecha' => ['required', 'integer', 'min:0', 'max:4'],
        ]);

        $validator->after(function ($validator) use ($ejes) {
            $numeros = collect($ejes)
                ->pluck('numero_eje')
                ->filter(fn ($numero) => filled($numero))
                ->map(fn ($numero) => (int) $numero);

            if ($numeros->duplicates()->isNotEmpty()) {
                $validator->errors()->add('cubierta_ejes', 'No puede repetir el numero de eje en la configuracion de cubiertas.');
            }

            $totalCubiertas = collect($ejes)->sum(fn ($eje) => (int) ($eje['cubiertas_izquierda'] ?? 0) + (int) ($eje['cubiertas_derecha'] ?? 0));

            if ($totalCubiertas <= 0) {
                $validator->errors()->add('cubierta_ejes', 'Debe configurar al menos una cubierta para el vehiculo.');
            }
        });

        $validator->validate();

        $flota->cubiertaEjes()->delete();

        foreach (array_values($ejes) as $index => $eje) {
            $flota->cubiertaEjes()->create([
                'numero_eje' => (int) $eje['numero_eje'],
                'tipo_eje' => $eje['tipo_eje'],
                'articulo_cubierta_id' => $eje['articulo_cubierta_id'] ?? null,
                'cubiertas_izquierda' => (int) $eje['cubiertas_izquierda'],
                'cubiertas_derecha' => (int) $eje['cubiertas_derecha'],
                'orden' => $index + 1,
                'activo' => true,
            ]);
        }
    }

    private function actualizarServicioAsignado(Flota $flota, ?int $servicioAsignadoId, ?string $observaciones = null): void
    {
        $actual = FlotaServicioAsignadoHistorial::query()
            ->where('flota_id', $flota->id)
            ->whereNull('fecha_hasta')
            ->latest('fecha_desde')
            ->latest('id')
            ->first();

        if ($actual && (int) $actual->servicio_asignado_id === (int) $servicioAsignadoId) {
            return;
        }

        if ($actual) {
            $actual->update([
                'fecha_hasta' => now()->toDateString(),
            ]);
        }

        if (! $servicioAsignadoId) {
            return;
        }

        FlotaServicioAsignadoHistorial::create([
            'flota_id' => $flota->id,
            'servicio_asignado_id' => $servicioAsignadoId,
            'fecha_desde' => now()->toDateString(),
            'observaciones' => $observaciones ?: 'Asignacion registrada desde gestion de servicios.',
        ]);
    }
}
