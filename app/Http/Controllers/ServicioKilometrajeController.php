<?php

namespace App\Http\Controllers;

use App\Models\ConfiguracionIntervaloServicio;
use App\Models\ControlUnidad;
use App\Models\Flota;
use App\Models\OrdenTrabajo;
use App\Models\RegistroServicioKilometraje;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ServicioKilometrajeController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->get('search', ''));
        $medidor = trim((string) $request->get('medidor', 'todos'));
        $medidor = in_array($medidor, ['todos', 'km', 'horas'], true) ? $medidor : 'todos';

        $intervalos = ConfiguracionIntervaloServicio::query()
            ->where('estado', 'activo')
            ->orderByRaw("CASE WHEN LOWER(sistema) = 'motor' OR LOWER(nombre) LIKE '%motor%' THEN 0 ELSE 1 END")
            ->orderBy('sistema')
            ->orderBy('unidad_intervalo')
            ->orderBy('kilometros_intervalo')
            ->orderBy('nombre')
            ->get();

        $ordenesKm = OrdenTrabajo::query()
            ->selectRaw('flota_id, MAX(kilometraje) as kilometraje')
            ->whereNotNull('flota_id')
            ->whereNotNull('kilometraje')
            ->groupBy('flota_id')
            ->pluck('kilometraje', 'flota_id');

        $controlesKm = ControlUnidad::query()
            ->selectRaw('flota_id, MAX(kilometraje_actual) as kilometraje')
            ->whereNotNull('flota_id')
            ->whereNotNull('kilometraje_actual')
            ->groupBy('flota_id')
            ->pluck('kilometraje', 'flota_id');

        $ultimosServicios = $this->ultimosServiciosRegistrados();

        $flotas = Flota::query()
            ->with(['tipoVehiculo', 'marcaVehiculo', 'tipoCaja', 'modeloCaja'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('nro_interno', 'like', "%{$search}%")
                        ->orWhere('dominio', 'like', "%{$search}%")
                        ->orWhere('estado', 'like', "%{$search}%");
                });
            })
            ->when($medidor !== 'todos', fn ($query) => $query->where('tipo_medidor_servicio', $medidor))
            ->orderBy('nro_interno')
            ->paginate(10)
            ->withQueryString();

        $flotas->getCollection()->transform(function (Flota $flota) use ($intervalos, $ordenesKm, $controlesKm, $ultimosServicios) {
            $tipoMedidor = $this->tipoMedidorParaFlota($flota);
            $kilometrajeActual = max(
                (int) ($ordenesKm[$flota->id] ?? 0),
                (int) ($controlesKm[$flota->id] ?? 0)
            );
            $horometroActual = max((int) ($flota->horometro_actual ?? 0), 0);
            $lecturaActual = $tipoMedidor === 'horas' ? $horometroActual : $kilometrajeActual;

            $intervalosCompatibles = $this->intervalosCompatiblesConCaja($intervalos, $flota)
                ->filter(function (ConfiguracionIntervaloServicio $intervalo) use ($tipoMedidor) {
                    return $this->normalizeMedidor($intervalo->unidad_intervalo ?? 'km') === $tipoMedidor;
                })
                ->values();

            $flota->tipo_medidor_calculado = $tipoMedidor;
            $flota->lectura_actual_calculada = $lecturaActual;
            $flota->servicios_kilometraje = $intervalosCompatibles->map(
                fn (ConfiguracionIntervaloServicio $intervalo) => $this->calcularServicio($flota, $intervalo, $lecturaActual, $ultimosServicios)
            );

            return $flota;
        });

        return view('admin.servicios-kilometraje.index', compact('flotas', 'intervalos', 'search', 'medidor'));
    }

    public function registrarServicio(Request $request)
    {
        $validated = $request->validate([
            'flota_id' => ['required', 'exists:flota,id'],
            'configuracion_intervalo_servicio_id' => ['required', 'exists:configuracion_intervalos_servicio,id'],
            'kilometraje_servicio' => ['nullable', 'integer', 'min:0'],
            'horometro_servicio' => ['nullable', 'integer', 'min:0'],
            'observaciones' => ['nullable', 'string'],
        ]);

        $flota = Flota::query()->with('tipoCaja')->findOrFail($validated['flota_id']);
        $intervalo = ConfiguracionIntervaloServicio::findOrFail($validated['configuracion_intervalo_servicio_id']);

        if (! $this->servicioCompatibleConCaja($flota, $intervalo)) {
            return redirect()
                ->route('admin.servicios-kilometraje.index')
                ->with('error', 'El servicio seleccionado no corresponde al tipo de caja del vehiculo.');
        }

        $tipoMedidorFlota = $this->tipoMedidorParaFlota($flota);
        $tipoMedidorIntervalo = $this->normalizeMedidor($intervalo->unidad_intervalo ?? 'km');

        if ($tipoMedidorFlota !== $tipoMedidorIntervalo) {
            return redirect()
                ->route('admin.servicios-kilometraje.index')
                ->with('error', 'La unidad del intervalo no coincide con el medidor configurado para esta unidad.');
        }

        if ($tipoMedidorFlota === 'horas' && ! array_key_exists('horometro_servicio', $validated)) {
            return redirect()
                ->route('admin.servicios-kilometraje.index')
                ->with('error', 'Debe informar horometro para registrar el servicio de esta unidad.');
        }

        if ($tipoMedidorFlota === 'km' && ! array_key_exists('kilometraje_servicio', $validated)) {
            return redirect()
                ->route('admin.servicios-kilometraje.index')
                ->with('error', 'Debe informar kilometraje para registrar el servicio de esta unidad.');
        }

        $kilometrajeServicio = $tipoMedidorFlota === 'km'
            ? (int) ($validated['kilometraje_servicio'] ?? 0)
            : 0;

        $horometroServicio = $tipoMedidorFlota === 'horas'
            ? (int) ($validated['horometro_servicio'] ?? 0)
            : null;

        RegistroServicioKilometraje::create([
            'flota_id' => $validated['flota_id'],
            'configuracion_intervalo_servicio_id' => $validated['configuracion_intervalo_servicio_id'],
            'user_id' => Auth::id(),
            'kilometraje_servicio' => $kilometrajeServicio,
            'horometro_servicio' => $horometroServicio,
            'fecha_servicio' => now(),
            'observaciones' => $validated['observaciones'] ?? null,
        ]);

        return redirect()
            ->route('admin.servicios-kilometraje.index')
            ->with('success', 'Servicio registrado correctamente. El indicador fue reiniciado.');
    }

    private function ultimosServiciosRegistrados()
    {
        return RegistroServicioKilometraje::query()
            ->with('intervalo')
            ->orderByDesc('fecha_servicio')
            ->orderByDesc('id')
            ->get()
            ->unique(fn (RegistroServicioKilometraje $registro) => $registro->flota_id . '-' . $registro->configuracion_intervalo_servicio_id)
            ->keyBy(fn (RegistroServicioKilometraje $registro) => $registro->flota_id . '-' . $registro->configuracion_intervalo_servicio_id);
    }

    private function calcularServicio(Flota $flota, ConfiguracionIntervaloServicio $intervalo, int $lecturaActual, $ultimosServicios): array
    {
        $unidad = $this->normalizeMedidor($intervalo->unidad_intervalo ?? 'km');
        $intervaloValor = max(1, (int) $intervalo->kilometros_intervalo);
        $ultimoServicio = $ultimosServicios->get($flota->id . '-' . $intervalo->id);
        $ultimoServicioValor = $unidad === 'horas'
            ? (int) ($ultimoServicio?->horometro_servicio ?? 0)
            : (int) ($ultimoServicio?->kilometraje_servicio ?? 0);

        $valorBase = $ultimoServicio
            ? min($ultimoServicioValor, $lecturaActual)
            : 0;

        $recorridos = max(0, $lecturaActual - $valorBase);
        $faltan = $recorridos >= $intervaloValor ? 0 : $intervaloValor - $recorridos;
        $proximo = $valorBase + $intervaloValor;

        return [
            'id' => $intervalo->id,
            'sistema' => $intervalo->sistema,
            'sistema_label' => $intervalo->sistemaLabel(),
            'nombre' => $intervalo->nombre,
            'etiqueta' => $intervalo->etiqueta(),
            'unidad' => $unidad,
            'intervalo' => $intervaloValor,
            'proximo' => $proximo,
            'faltan' => $faltan,
            'estado' => $this->estadoServicio($faltan, $intervaloValor),
            'ultimo_servicio_valor' => $ultimoServicioValor > 0 ? $ultimoServicioValor : null,
            'ultimo_servicio_fecha' => $ultimoServicio?->fecha_servicio,
        ];
    }

    private function normalizeMedidor(?string $medidor): string
    {
        $medidor = trim((string) $medidor);

        return $medidor === 'horas' ? 'horas' : 'km';
    }

    private function tipoMedidorParaFlota(Flota $flota): string
    {
        $configured = $this->normalizeMedidor($flota->tipo_medidor_servicio ?? 'km');

        if ($configured === 'horas') {
            return 'horas';
        }

        $descriptor = $this->normalizarTexto(collect([
            $flota->tipoVehiculo?->nombre,
            $flota->modeloCaja?->nombre,
            $flota->observaciones,
        ])->filter()->implode(' '));

        $keywords = [
            'pala cargadora',
            'motoniveladora',
            'grupo electrogeno',
            'torre de luz',
            'compresor',
        ];

        return collect($keywords)->contains(fn (string $keyword) => str_contains($descriptor, $keyword))
            ? 'horas'
            : 'km';
    }

    private function intervalosCompatiblesConCaja($intervalos, Flota $flota)
    {
        return $intervalos
            ->filter(fn (ConfiguracionIntervaloServicio $intervalo) => $this->servicioCompatibleConCaja($flota, $intervalo))
            ->values();
    }

    private function servicioCompatibleConCaja(Flota $flota, ConfiguracionIntervaloServicio $intervalo): bool
    {
        $servicio = $this->normalizarTexto($intervalo->sistema . ' ' . $intervalo->nombre);

        if ($this->esServicioCajaTransferencia($servicio)) {
            return $this->vehiculoEs4x4($flota);
        }

        if (! str_contains($servicio, 'caja')) {
            return true;
        }

        $esManual = str_contains($servicio, 'manual');
        $esAutomatica = str_contains($servicio, 'automatica') || str_contains($servicio, 'automatico');

        if (! $esManual && ! $esAutomatica) {
            return true;
        }

        $tipoCaja = $this->normalizarTexto($flota->tipoCaja?->nombre ?? '');

        if ($tipoCaja === '') {
            return true;
        }

        if (str_contains($tipoCaja, 'manual')) {
            return $esManual;
        }

        if (str_contains($tipoCaja, 'automatica') || str_contains($tipoCaja, 'automatico')) {
            return $esAutomatica;
        }

        return true;
    }

    private function esServicioCajaTransferencia(string $servicio): bool
    {
        return str_contains($servicio, 'transferencia') || str_contains($servicio, '4x4') || str_contains($servicio, '4 x 4');
    }

    private function vehiculoEs4x4(Flota $flota): bool
    {
        $descripcionVehiculo = $this->normalizarTexto(collect([
            $flota->tipoVehiculo?->nombre,
            $flota->modeloCaja?->nombre,
            $flota->observaciones,
        ])->filter()->implode(' '));

        return str_contains($descripcionVehiculo, '4x4')
            || str_contains($descripcionVehiculo, '4 x 4')
            || str_contains($descripcionVehiculo, '4wd')
            || str_contains($descripcionVehiculo, 'awd')
            || str_contains($descripcionVehiculo, 'doble traccion');
    }

    private function normalizarTexto(string $texto): string
    {
        return mb_strtolower(Str::ascii($texto), 'UTF-8');
    }

    private function estadoServicio(int $faltan, int $intervalo): string
    {
        if ($faltan <= 0) {
            return 'vencido';
        }

        if ($faltan <= max(1000, (int) floor($intervalo * 0.1))) {
            return 'proximo';
        }

        return 'ok';
    }
}
