<?php

namespace App\Http\Controllers;

use App\Models\ConfiguracionVencimientoVerificacion;
use App\Models\Flota;
use App\Models\RegistroVerificacionTecnica;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VerificacionTecnicaController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->get('search', ''));
        $configuraciones = ConfiguracionVencimientoVerificacion::query()
            ->where('estado', 'activo')
            ->orderByRaw("CASE WHEN UPPER(tipo) = 'CNRT' THEN 0 ELSE 1 END")
            ->orderBy('nombre')
            ->get();

        $ultimosRegistros = $this->ultimosRegistros();

        $flotas = Flota::query()
            ->with(['tipoVehiculo', 'marcaVehiculo'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('nro_interno', 'like', "%{$search}%")
                        ->orWhere('dominio', 'like', "%{$search}%")
                        ->orWhere('estado', 'like', "%{$search}%");
                });
            })
            ->orderBy('nro_interno')
            ->paginate(10)
            ->withQueryString();

        $flotas->getCollection()->transform(function (Flota $flota) use ($configuraciones, $ultimosRegistros) {
            $flota->verificaciones_tecnicas = $configuraciones->map(
                fn (ConfiguracionVencimientoVerificacion $configuracion) => $this->calcularVerificacion($flota, $configuracion, $ultimosRegistros)
            );

            return $flota;
        });

        return view('admin.verificaciones-tecnicas.index', compact('flotas', 'configuraciones', 'search'));
    }

    public function registrar(Request $request)
    {
        $validated = $request->validate([
            'flota_id' => ['required', 'exists:flota,id'],
            'configuracion_vencimiento_verificacion_id' => ['required', 'exists:configuracion_vencimientos_verificacion,id'],
            'fecha_emision' => ['nullable', 'date'],
            'fecha_vencimiento' => ['required', 'date'],
            'comprobante' => ['nullable', 'string', 'max:120'],
            'observaciones' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($validated) {
            RegistroVerificacionTecnica::query()
                ->where('flota_id', $validated['flota_id'])
                ->where('configuracion_vencimiento_verificacion_id', $validated['configuracion_vencimiento_verificacion_id'])
                ->where('estado', 'vigente')
                ->update(['estado' => 'renovada']);

            RegistroVerificacionTecnica::create([
                'flota_id' => $validated['flota_id'],
                'configuracion_vencimiento_verificacion_id' => $validated['configuracion_vencimiento_verificacion_id'],
                'user_id' => Auth::id(),
                'fecha_emision' => $validated['fecha_emision'] ?? null,
                'fecha_vencimiento' => $validated['fecha_vencimiento'],
                'comprobante' => $validated['comprobante'] ?? null,
                'estado' => 'vigente',
                'observaciones' => $validated['observaciones'] ?? null,
            ]);
        });

        return redirect()
            ->route('admin.verificaciones-tecnicas.index')
            ->with('success', 'Vencimiento de verificacion registrado correctamente.');
    }

    private function ultimosRegistros()
    {
        return RegistroVerificacionTecnica::query()
            ->with(['configuracion', 'usuario'])
            ->where('estado', '!=', 'cancelada')
            ->orderByDesc('fecha_vencimiento')
            ->orderByDesc('created_at')
            ->get()
            ->unique(fn (RegistroVerificacionTecnica $registro) => $registro->flota_id . '-' . $registro->configuracion_vencimiento_verificacion_id)
            ->keyBy(fn (RegistroVerificacionTecnica $registro) => $registro->flota_id . '-' . $registro->configuracion_vencimiento_verificacion_id);
    }

    private function calcularVerificacion(Flota $flota, ConfiguracionVencimientoVerificacion $configuracion, $ultimosRegistros): array
    {
        $registro = $ultimosRegistros->get($flota->id . '-' . $configuracion->id);
        $hoy = Carbon::today();
        $dias = $registro ? (int) $hoy->diffInDays($registro->fecha_vencimiento, false) : null;

        return [
            'id' => $configuracion->id,
            'tipo' => $configuracion->tipo,
            'nombre' => $configuracion->nombre,
            'dias_alerta' => (int) $configuracion->dias_alerta,
            'estado' => $this->estadoVerificacion($dias, (int) $configuracion->dias_alerta),
            'fecha_emision' => $registro?->fecha_emision,
            'fecha_vencimiento' => $registro?->fecha_vencimiento,
            'dias' => $dias,
            'comprobante' => $registro?->comprobante,
            'observaciones' => $registro?->observaciones,
            'ultimo_usuario' => $registro?->usuario?->name,
        ];
    }

    private function estadoVerificacion(?int $dias, int $diasAlerta): string
    {
        if ($dias === null) {
            return 'sin_cargar';
        }

        if ($dias < 0) {
            return 'vencido';
        }

        if ($dias <= $diasAlerta) {
            return 'proximo';
        }

        return 'ok';
    }
}
