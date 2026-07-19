<?php

namespace App\Http\Controllers;

use App\Models\ControlUnidad;
use App\Models\Base;
use App\Models\Empleado;
use App\Models\Flota;
use App\Models\OrdenTrabajo;
use App\Models\OrdenTrabajoMotivo;
use App\Models\ServicioAsignado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ControlUnidadController extends Controller
{
    private const MOTIVOS_POR_PARTE = [
        'documentacion' => 'documentacion',
        'mecanica' => 'mecanica',
        'electricidad' => 'electricidad',
        'gomeria' => 'cubiertas',
        'carroceria' => 'carroceria',
        'accesorios' => 'accesorios',
    ];

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('check.permission:administrar-controles-unidad');
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));

        $query = ControlUnidad::with(['user', 'flota', 'conductorUser', 'servicioAsignado', 'ordenTrabajo.motivos'])
            ->orderByDesc('created_at')
            ->orderByDesc('id');

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query->where('interno', 'like', '%' . $search . '%')
                    ->orWhere('conductor', 'like', '%' . $search . '%')
                    ->orWhere('servicio_asignado', 'like', '%' . $search . '%')
                    ->orWhere('observaciones_generales', 'like', '%' . $search . '%')
                    ->orWhereHas('flota', function ($query) use ($search) {
                        $query->where('nro_interno', 'like', '%' . $search . '%')
                            ->orWhere('dominio', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('conductorUser', function ($query) use ($search) {
                        $query->where('name', 'like', '%' . $search . '%')
                            ->orWhere('email', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('servicioAsignado', function ($query) use ($search) {
                        $query->where('nombre', 'like', '%' . $search . '%');
                    });
            });
        }

        $controles = $query->paginate(10)->withQueryString();

        return view('admin.controles-unidad.index', compact('controles', 'search'));
    }

    public function create()
    {
        return view('admin.controles-unidad.create', [
            'partes' => ControlUnidad::PARTES,
            'controlUnidadItems' => ControlUnidad::CONTROL_UNIDAD,
            'flotas' => Flota::orderBy('nro_interno')->get(),
            'conductorActual' => Auth::user(),
            'serviciosAsignados' => ServicioAsignado::orderBy('nombre')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->merge([
            'conductor_user_id' => Auth::id(),
            'observaciones_generales' => trim((string) $request->input('observaciones_generales')),
        ]);

        $validator = Validator::make($request->all(), $this->rules());
        $this->validateKilometraje($validator, $request);
        $this->validateOrdenTrabajoResources($validator, $request);

        if ($validator->fails()) {
            return redirect()
                ->route('admin.controles-unidad.create')
                ->withErrors($validator)
                ->withInput();
        }

        $servicioAsignado = ServicioAsignado::findOrFail($request->input('servicio_asignado_id'));
        $flota = Flota::findOrFail($request->input('flota_id'));
        $conductor = Auth::user();

        $control = DB::transaction(function () use ($request, $servicioAsignado, $flota, $conductor) {
            $control = ControlUnidad::create([
                'user_id' => Auth::id(),
                'flota_id' => $flota->id,
                'conductor_user_id' => $conductor->id,
                'servicio_asignado_id' => $servicioAsignado->id,
                'interno' => $flota->nro_interno,
                'conductor' => $conductor->name,
                'kilometraje_actual' => $request->input('kilometraje_actual'),
                'servicio_asignado' => $servicioAsignado->nombre,
                'observaciones_generales' => $request->input('observaciones_generales'),
                'partes' => $this->normalizePartes($request),
                'control_unidad' => $this->normalizeControlUnidad($request),
            ]);

            if ($this->hasNoCumple($control->partes)) {
                $orden = $this->createOrdenTrabajoFromControl($control);
                $control->update(['orden_trabajo_id' => $orden->id]);
            }

            return $control;
        });

        if (! $request->user()?->can('controles-unidad.ver')) {
            return redirect()
                ->route('admin.controles-unidad.create')
                ->with('success', 'Check List Vehicular creado correctamente.');
        }

        return redirect()
            ->route('admin.controles-unidad.show', $control)
            ->with('success', 'Check List Vehicular creado correctamente.');
    }

    public function show(ControlUnidad $controlUnidad)
    {
        return view('admin.controles-unidad.show', [
            'control' => $controlUnidad->load(['user', 'flota', 'conductorUser', 'servicioAsignado', 'ordenTrabajo.motivos']),
            'partes' => ControlUnidad::PARTES,
            'controlUnidadItems' => ControlUnidad::CONTROL_UNIDAD,
        ]);
    }

    public function destroy(ControlUnidad $controlUnidad)
    {
        $controlUnidad->delete();

        return redirect()
            ->route('admin.controles-unidad.index')
            ->with('success', 'Check List Vehicular eliminado correctamente.');
    }

    private function rules(): array
    {
        $rules = [
            'flota_id' => ['required', 'integer', 'exists:flota,id'],
            'conductor_user_id' => ['required', 'integer', 'exists:users,id'],
            'kilometraje_actual' => ['required', 'integer', 'min:0'],
            'servicio_asignado_id' => ['required', 'integer', 'exists:servicios_asignados,id'],
            'observaciones_generales' => ['required', 'string'],
        ];

        foreach (ControlUnidad::PARTES as $parteKey => $parte) {
            foreach ($parte['items'] as $itemKey => $label) {
                $rules["partes.$parteKey.$itemKey"] = ['required', Rule::in(ControlUnidad::ESTADOS_PARTE)];
            }
        }

        foreach (ControlUnidad::CONTROL_UNIDAD as $itemKey => $label) {
            $rules["control_unidad.$itemKey"] = ['required', Rule::in(ControlUnidad::ESTADOS_CONTROL)];
        }

        return $rules;
    }

    private function normalizePartes(Request $request): array
    {
        $values = [];

        foreach (ControlUnidad::PARTES as $parteKey => $parte) {
            foreach ($parte['items'] as $itemKey => $label) {
                $values[$parteKey][$itemKey] = $request->input("partes.$parteKey.$itemKey");
            }
        }

        return $values;
    }

    private function normalizeControlUnidad(Request $request): array
    {
        $values = [];

        foreach (ControlUnidad::CONTROL_UNIDAD as $itemKey => $label) {
            $values[$itemKey] = $request->input("control_unidad.$itemKey");
        }

        return $values;
    }

    private function validateKilometraje($validator, Request $request): void
    {
        $validator->after(function ($validator) use ($request) {
            if (!$request->filled('flota_id') || !$request->filled('kilometraje_actual')) {
                return;
            }

            $ultimoChecklist = ControlUnidad::where('flota_id', $request->input('flota_id'))
                ->whereNotNull('kilometraje_actual')
                ->max('kilometraje_actual');

            $ultimaOrden = OrdenTrabajo::where('flota_id', $request->input('flota_id'))
                ->whereNotNull('kilometraje')
                ->max('kilometraje');

            $kilometrajesRegistrados = array_filter([
                $ultimoChecklist,
                $ultimaOrden,
            ], static fn ($value) => $value !== null);

            if ($kilometrajesRegistrados === []) {
                return;
            }

            $ultimoKilometraje = max($kilometrajesRegistrados);

            if ((int) $request->input('kilometraje_actual') < (int) $ultimoKilometraje) {
                $validator->errors()->add(
                    'kilometraje_actual',
                    'El kilometraje no puede ser menor al ultimo registrado para este vehiculo (' . number_format((int) $ultimoKilometraje, 0, ',', '.') . ' km).'
                );
            }
        });
    }

    private function validateOrdenTrabajoResources($validator, Request $request): void
    {
        $validator->after(function ($validator) use ($request) {
            if (!$this->requestHasNoCumple($request)) {
                return;
            }

            $empleado = $this->resolveEmpleadoForOrden((int) $request->input('conductor_user_id'));
            $base = $empleado ? $this->resolveBaseForOrden($empleado) : null;

            if (!$empleado) {
                $validator->errors()->add(
                    'conductor_user_id',
                    'Para generar la orden de trabajo, el conductor debe estar vinculado a un empleado activo.'
                );
            }

            if (!$base) {
                $validator->errors()->add(
                    'flota_id',
                    'Para generar la orden de trabajo, debe existir una base activa disponible.'
                );
            }
        });
    }

    private function requestHasNoCumple(Request $request): bool
    {
        foreach (ControlUnidad::PARTES as $parteKey => $parte) {
            foreach ($parte['items'] as $itemKey => $label) {
                if ($request->input("partes.$parteKey.$itemKey") === 'no_cumple') {
                    return true;
                }
            }
        }

        return false;
    }

    private function hasNoCumple(?array $partes): bool
    {
        foreach ($partes ?? [] as $items) {
            if (in_array('no_cumple', $items, true)) {
                return true;
            }
        }

        return false;
    }

    private function createOrdenTrabajoFromControl(ControlUnidad $control): OrdenTrabajo
    {
        $empleado = $this->resolveEmpleadoForOrden($control->conductor_user_id);
        $base = $this->resolveBaseForOrden($empleado);
        $itemsNoCumple = $this->noCumpleItems($control->partes);
        $motivoCodigos = $this->motivoCodigosFromControl($control->partes);
        $motivos = OrdenTrabajoMotivo::query()
            ->whereIn('codigo', $motivoCodigos)
            ->get()
            ->keyBy('codigo');
        $motivoIds = collect($motivoCodigos)
            ->map(fn ($codigo) => $motivos->get($codigo)?->id)
            ->filter()
            ->values()
            ->all();
        $motivoNombres = collect($motivoCodigos)
            ->map(fn ($codigo) => $motivos->get($codigo)?->nombre)
            ->filter()
            ->values()
            ->all();
        $descripcion = $this->descripcionConMotivos(
            "Check List Vehicular #" . $control->id . "\nItems no cumplen:\n- " . implode("\n- ", $itemsNoCumple),
            $motivoNombres
        );

        $orden = OrdenTrabajo::create([
            'empleado_id' => $empleado->id,
            'actualizado_por_user_id' => Auth::id(),
            'flota_id' => $control->flota_id,
            'base_id' => $base->id,
            'kilometraje' => $control->kilometraje_actual,
            'fecha_orden' => $control->created_at ?? now(),
            'tipo_trabajo' => 'inspeccion',
            'prioridad' => 'media',
            'estado' => 'pendiente',
            'titulo' => 'Check List Vehicular #' . $control->id . ' - Interno ' . $control->interno,
            'descripcion' => $descripcion,
            'observaciones' => $control->observaciones_generales,
            'fecha_cierre' => null,
        ]);

        if ($motivoIds !== []) {
            $orden->motivos()->sync($motivoIds);
        }

        return $orden;
    }

    private function resolveEmpleadoForOrden(?int $userId): ?Empleado
    {
        if (!$userId) {
            return null;
        }

        return Empleado::where('usuario_id', $userId)
            ->where('estado', 'activo')
            ->first();
    }

    private function resolveBaseForOrden(?Empleado $empleado): ?Base
    {
        if (!$empleado) {
            return null;
        }

        if ($empleado->base_id) {
            $base = Base::where('estado', 'activa')->find($empleado->base_id);

            if ($base) {
                return $base;
            }
        }

        return Base::where('estado', 'activa')->orderBy('nombre')->first();
    }

    private function noCumpleItems(?array $partes): array
    {
        $items = [];

        foreach (ControlUnidad::PARTES as $parteKey => $parte) {
            foreach ($parte['items'] as $itemKey => $label) {
                if (data_get($partes, "$parteKey.$itemKey") === 'no_cumple') {
                    $items[] = $parte['titulo'] . ': ' . $label;
                }
            }
        }

        return $items;
    }

    private function motivoCodigosFromControl(?array $partes): array
    {
        $codigos = [];

        foreach (ControlUnidad::PARTES as $parteKey => $parte) {
            $items = data_get($partes, $parteKey, []);

            if (in_array('no_cumple', $items, true)) {
                $codigos[] = self::MOTIVOS_POR_PARTE[$parteKey] ?? 'otro';
            }
        }

        return array_values(array_unique($codigos ?: ['otro']));
    }

    private function descripcionConMotivos(string $descripcion, array $motivoNombres): string
    {
        $motivoNombres = array_values(array_filter($motivoNombres));

        if ($motivoNombres === []) {
            return $descripcion;
        }

        $lineas = preg_split('/\r\n|\r|\n/', trim($descripcion));
        $lineaMotivos = 'Motivos: ' . implode(', ', $motivoNombres);

        if (count($lineas) <= 1) {
            return trim($descripcion) . "\n" . $lineaMotivos;
        }

        array_splice($lineas, 1, 0, [$lineaMotivos]);

        return implode("\n", $lineas);
    }
}
