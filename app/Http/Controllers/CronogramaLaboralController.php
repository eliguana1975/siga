<?php

namespace App\Http\Controllers;

use App\Models\CronogramaAsignacion;
use App\Models\CronogramaNovedad;
use App\Models\CronogramaPatron;
use App\Models\Empleado;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CronogramaLaboralController extends Controller
{
    private const TIPOS_EMPLEADO_PREDEFINIDOS = [
        'MECANICO',
        'SUPERVISOR DE TALLER',
        'PANOLERO',
        'CHOFER',
        'ELECTRICISTA',
        'ADMINISTRATIVO',
    ];

    private const TURNOS = [
        'manana' => 'Turno manana',
        'tarde' => 'Turno tarde',
        'noche' => 'Turno noche',
        'sin_turno' => 'Sin turno',
    ];

    private const MESES = [
        1 => 'Enero',
        2 => 'Febrero',
        3 => 'Marzo',
        4 => 'Abril',
        5 => 'Mayo',
        6 => 'Junio',
        7 => 'Julio',
        8 => 'Agosto',
        9 => 'Septiembre',
        10 => 'Octubre',
        11 => 'Noviembre',
        12 => 'Diciembre',
    ];

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('check.permission:administrar-cronogramas');
    }

    public function index(Request $request): View
    {
        $hasTipoEmpleadoColumn = Schema::hasColumn('empleados', 'tipo_empleado');
        $hasTurnoLaboralColumn = Schema::hasColumn('empleados', 'turno_laboral');
        $hasEsFranqueroColumn = Schema::hasColumn('empleados', 'es_franquero');
        $hasFranqueroTipoColumn = Schema::hasColumn('empleados', 'franquero_de_tipo_empleado');
        $hasFranqueroEmpleadoColumn = Schema::hasColumn('empleados', 'franquero_de_empleado_id');

        $year = (int) $request->input('year', (int) now()->year);
        $year = $year >= 2020 && $year <= 2100 ? $year : (int) now()->year;
        $mes = (int) $request->input('mes', 0);
        $mes = array_key_exists($mes, self::MESES) ? $mes : 0;

        $tipo = $hasTipoEmpleadoColumn
            ? trim(mb_strtoupper((string) $request->input('tipo_empleado', ''), 'UTF-8'))
            : '';
        $turno = $hasTurnoLaboralColumn
            ? trim((string) $request->input('turno', ''))
            : '';

        if ($turno !== '' && ! array_key_exists($turno, self::TURNOS)) {
            $turno = '';
        }

        $tiposEmpleado = $hasTipoEmpleadoColumn
            ? Empleado::query()
                ->selectRaw('TRIM(UPPER(tipo_empleado)) as tipo_empleado')
                ->whereNotNull('tipo_empleado')
                ->where('tipo_empleado', '!=', '')
                ->distinct()
                ->orderBy('tipo_empleado')
                ->pluck('tipo_empleado')
            : collect();

        if ($tiposEmpleado->isEmpty()) {
            $tiposEmpleado = collect(self::TIPOS_EMPLEADO_PREDEFINIDOS);
        }

        $turnos = collect(self::TURNOS)
            ->except('sin_turno')
            ->all();
        $meses = self::MESES;

        $selectColumns = ['id', 'nombres', 'apellidos', 'estado'];

        if ($hasTipoEmpleadoColumn) {
            $selectColumns[] = 'tipo_empleado';
        }

        if ($hasTurnoLaboralColumn) {
            $selectColumns[] = 'turno_laboral';
        }

        if ($hasEsFranqueroColumn) {
            $selectColumns[] = 'es_franquero';
        }

        if ($hasFranqueroTipoColumn) {
            $selectColumns[] = 'franquero_de_tipo_empleado';
        }

        if ($hasFranqueroEmpleadoColumn) {
            $selectColumns[] = 'franquero_de_empleado_id';
        }

        $empleados = Empleado::query()
            ->when($hasTipoEmpleadoColumn && $tipo !== '', function ($query) use ($tipo, $hasEsFranqueroColumn, $hasFranqueroTipoColumn) {
                $query->where(function ($subQuery) use ($tipo, $hasEsFranqueroColumn, $hasFranqueroTipoColumn) {
                    $subQuery->whereRaw('TRIM(UPPER(tipo_empleado)) = ?', [$tipo]);

                    if ($hasEsFranqueroColumn && $hasFranqueroTipoColumn) {
                        $subQuery->orWhere(function ($franqueroQuery) use ($tipo) {
                            $franqueroQuery->where('es_franquero', true)
                                ->whereRaw('TRIM(UPPER(franquero_de_tipo_empleado)) = ?', [$tipo]);
                        });
                    }
                });
            })
            ->when($hasTurnoLaboralColumn && $turno !== '', fn ($query) => $query->where('turno_laboral', $turno))
            ->where('estado', 'activo')
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->get($selectColumns);

        $empleadoIds = $empleados->pluck('id');
        $yearStart = Carbon::create($year, 1, 1)->startOfDay();
        $yearEnd = Carbon::create($year, 12, 31)->endOfDay();

        $asignaciones = CronogramaAsignacion::query()
            ->with('patron')
            ->whereIn('empleado_id', $empleadoIds)
            ->where('estado', 'activo')
            ->whereDate('fecha_inicio', '<=', $yearEnd->toDateString())
            ->where(function ($query) use ($yearStart) {
                $query->whereNull('fecha_fin')
                    ->orWhereDate('fecha_fin', '>=', $yearStart->toDateString());
            })
            ->orderBy('fecha_inicio')
            ->get()
            ->groupBy('empleado_id');

        $novedades = CronogramaNovedad::query()
            ->whereIn('empleado_id', $empleadoIds)
            ->whereBetween('fecha', [$yearStart->toDateString(), $yearEnd->toDateString()])
            ->orderBy('fecha')
            ->get()
            ->groupBy('empleado_id');

        $cronograma = $this->buildAnualCronograma($year, $empleados, $asignaciones, $novedades, $mes);

        $patrones = CronogramaPatron::query()->orderBy('nombre')->get();
        $ultimasAsignaciones = CronogramaAsignacion::query()
            ->with([
                'empleado:' . ($hasTipoEmpleadoColumn ? 'id,nombres,apellidos,tipo_empleado' : 'id,nombres,apellidos'),
                'patron:id,nombre,dias_trabajo,dias_descanso',
            ])
            ->latest('fecha_inicio')
            ->limit(20)
            ->get();

        $ultimasNovedades = CronogramaNovedad::query()
            ->with('empleado:' . ($hasTipoEmpleadoColumn ? 'id,nombres,apellidos,tipo_empleado' : 'id,nombres,apellidos'))
            ->latest('fecha')
            ->limit(20)
            ->get();

        return view('admin.cronogramas-laborales.index', compact(
            'year',
            'mes',
            'tipo',
            'turno',
            'tiposEmpleado',
            'turnos',
            'meses',
            'empleados',
            'cronograma',
            'patrones',
            'ultimasAsignaciones',
            'ultimasNovedades'
        ));
    }

    public function storePatron(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:120', 'unique:cronograma_patrones,nombre'],
            'dias_trabajo' => ['required', 'integer', 'min:1', 'max:365'],
            'dias_descanso' => ['required', 'integer', 'min:1', 'max:365'],
            'estado' => ['required', Rule::in(['activo', 'inactivo'])],
            'observaciones' => ['nullable', 'string'],
        ]);

        $data['nombre'] = mb_strtoupper(trim((string) $data['nombre']), 'UTF-8');

        CronogramaPatron::create($data);

        return redirect()->route('admin.cronogramas-laborales.index', $this->queryFromRequest($request))
            ->with('success', 'Patron de cronograma creado correctamente.');
    }

    public function updatePatron(Request $request, string $id): RedirectResponse
    {
        $patron = CronogramaPatron::findOrFail($id);

        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:120', Rule::unique('cronograma_patrones', 'nombre')->ignore($patron->id)],
            'dias_trabajo' => ['required', 'integer', 'min:1', 'max:365'],
            'dias_descanso' => ['required', 'integer', 'min:1', 'max:365'],
            'estado' => ['required', Rule::in(['activo', 'inactivo'])],
            'observaciones' => ['nullable', 'string'],
        ]);

        $data['nombre'] = mb_strtoupper(trim((string) $data['nombre']), 'UTF-8');

        $patron->update($data);

        return redirect()->route('admin.cronogramas-laborales.index', $this->queryFromRequest($request))
            ->with('success', 'Patron de cronograma actualizado correctamente.');
    }

    public function destroyPatron(Request $request, string $id): RedirectResponse
    {
        $patron = CronogramaPatron::findOrFail($id);
        $patron->delete();

        return redirect()->route('admin.cronogramas-laborales.index', $this->queryFromRequest($request))
            ->with('success', 'Patron de cronograma eliminado correctamente.');
    }

    public function storeAsignacion(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'empleado_id' => ['required', 'integer', 'exists:empleados,id'],
            'cronograma_patron_id' => ['required', 'integer', 'exists:cronograma_patrones,id'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'estado' => ['required', Rule::in(['activo', 'inactivo'])],
            'observaciones' => ['nullable', 'string'],
        ]);

        $this->ensureNoAsignacionOverlap($data['empleado_id'], $data['fecha_inicio'], $data['fecha_fin']);

        CronogramaAsignacion::create($data);

        return redirect()->route('admin.cronogramas-laborales.index', $this->queryFromRequest($request))
            ->with('success', 'Asignacion de cronograma registrada correctamente.');
    }

    public function updateAsignacion(Request $request, string $id): RedirectResponse
    {
        $asignacion = CronogramaAsignacion::findOrFail($id);

        $data = $request->validate([
            'cronograma_patron_id' => ['required', 'integer', 'exists:cronograma_patrones,id'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'estado' => ['required', Rule::in(['activo', 'inactivo'])],
            'observaciones' => ['nullable', 'string'],
        ]);

        $this->ensureNoAsignacionOverlap($asignacion->empleado_id, $data['fecha_inicio'], $data['fecha_fin'], $asignacion->id);

        $asignacion->update($data);

        return redirect()->route('admin.cronogramas-laborales.index', $this->queryFromRequest($request))
            ->with('success', 'Asignacion de cronograma actualizada correctamente.');
    }

    public function destroyAsignacion(Request $request, string $id): RedirectResponse
    {
        $asignacion = CronogramaAsignacion::findOrFail($id);
        $asignacion->delete();

        return redirect()->route('admin.cronogramas-laborales.index', $this->queryFromRequest($request))
            ->with('success', 'Asignacion de cronograma eliminada correctamente.');
    }

    public function storeNovedad(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'empleado_id' => ['required', 'integer', 'exists:empleados,id'],
            'fecha' => ['required', 'date'],
            'tipo' => ['required', Rule::in(array_keys(CronogramaNovedad::TIPOS))],
            'descripcion' => ['nullable', 'string', 'max:255'],
        ]);

        CronogramaNovedad::updateOrCreate(
            [
                'empleado_id' => $data['empleado_id'],
                'fecha' => $data['fecha'],
            ],
            [
                'tipo' => $data['tipo'],
                'descripcion' => $data['descripcion'] ?? null,
            ]
        );

        return redirect()->route('admin.cronogramas-laborales.index', $this->queryFromRequest($request))
            ->with('success', 'Novedad del cronograma guardada correctamente.');
    }

    public function destroyNovedad(Request $request, string $id): RedirectResponse
    {
        $novedad = CronogramaNovedad::findOrFail($id);
        $novedad->delete();

        return redirect()->route('admin.cronogramas-laborales.index', $this->queryFromRequest($request))
            ->with('success', 'Novedad eliminada correctamente.');
    }

    public function imprimir(Request $request): View
    {
        $hasTipoEmpleadoColumn = Schema::hasColumn('empleados', 'tipo_empleado');
        $hasTurnoLaboralColumn = Schema::hasColumn('empleados', 'turno_laboral');
        $hasEsFranqueroColumn = Schema::hasColumn('empleados', 'es_franquero');
        $hasFranqueroTipoColumn = Schema::hasColumn('empleados', 'franquero_de_tipo_empleado');
        $hasFranqueroEmpleadoColumn = Schema::hasColumn('empleados', 'franquero_de_empleado_id');

        $year = (int) $request->input('year', (int) now()->year);
        $year = $year >= 2020 && $year <= 2100 ? $year : (int) now()->year;
        $mes = (int) $request->input('mes', 0);
        $mes = array_key_exists($mes, self::MESES) ? $mes : 0;
        $tipo = $hasTipoEmpleadoColumn
            ? trim(mb_strtoupper((string) $request->input('tipo_empleado', ''), 'UTF-8'))
            : '';
        $turno = $hasTurnoLaboralColumn
            ? trim((string) $request->input('turno', ''))
            : '';

        if ($turno !== '' && ! array_key_exists($turno, self::TURNOS)) {
            $turno = '';
        }

        $selectColumns = ['id', 'nombres', 'apellidos', 'estado'];

        if ($hasTipoEmpleadoColumn) {
            $selectColumns[] = 'tipo_empleado';
        }

        if ($hasTurnoLaboralColumn) {
            $selectColumns[] = 'turno_laboral';
        }

        if ($hasEsFranqueroColumn) {
            $selectColumns[] = 'es_franquero';
        }

        if ($hasFranqueroTipoColumn) {
            $selectColumns[] = 'franquero_de_tipo_empleado';
        }

        if ($hasFranqueroEmpleadoColumn) {
            $selectColumns[] = 'franquero_de_empleado_id';
        }

        $empleados = Empleado::query()
            ->when($hasTipoEmpleadoColumn && $tipo !== '', function ($query) use ($tipo, $hasEsFranqueroColumn, $hasFranqueroTipoColumn) {
                $query->where(function ($subQuery) use ($tipo, $hasEsFranqueroColumn, $hasFranqueroTipoColumn) {
                    $subQuery->whereRaw('TRIM(UPPER(tipo_empleado)) = ?', [$tipo]);

                    if ($hasEsFranqueroColumn && $hasFranqueroTipoColumn) {
                        $subQuery->orWhere(function ($franqueroQuery) use ($tipo) {
                            $franqueroQuery->where('es_franquero', true)
                                ->whereRaw('TRIM(UPPER(franquero_de_tipo_empleado)) = ?', [$tipo]);
                        });
                    }
                });
            })
            ->when($hasTurnoLaboralColumn && $turno !== '', fn ($query) => $query->where('turno_laboral', $turno))
            ->where('estado', 'activo')
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->get($selectColumns);

        $empleadoIds = $empleados->pluck('id');
        $yearStart = Carbon::create($year, 1, 1)->startOfDay();
        $yearEnd = Carbon::create($year, 12, 31)->endOfDay();

        $asignaciones = CronogramaAsignacion::query()
            ->with('patron')
            ->whereIn('empleado_id', $empleadoIds)
            ->where('estado', 'activo')
            ->whereDate('fecha_inicio', '<=', $yearEnd->toDateString())
            ->where(function ($query) use ($yearStart) {
                $query->whereNull('fecha_fin')
                    ->orWhereDate('fecha_fin', '>=', $yearStart->toDateString());
            })
            ->orderBy('fecha_inicio')
            ->get()
            ->groupBy('empleado_id');

        $novedades = CronogramaNovedad::query()
            ->whereIn('empleado_id', $empleadoIds)
            ->whereBetween('fecha', [$yearStart->toDateString(), $yearEnd->toDateString()])
            ->orderBy('fecha')
            ->get()
            ->groupBy('empleado_id');

        $cronograma = $this->buildAnualCronograma($year, $empleados, $asignaciones, $novedades, $mes);

        return view('admin.cronogramas-laborales.print', compact('year', 'mes', 'tipo', 'turno', 'empleados', 'cronograma'));
    }

    private function buildAnualCronograma(int $year, $empleados, $asignacionesByEmpleado, $novedadesByEmpleado, int $mes = 0): array
    {
        $statusMap = [
            'descanso' => ['code' => 'FR', 'label' => 'Franco', 'class' => 'bg-light-secondary', 'working' => false],
            'franco_compensatorio' => ['code' => 'FC', 'label' => 'Franco compensatorio', 'class' => 'bg-light-warning'],
            'licencia' => ['code' => 'L', 'label' => 'Licencia', 'class' => 'bg-light-info'],
            'feriado' => ['code' => 'FE', 'label' => 'Feriado', 'class' => 'bg-light-primary'],
            'otro' => ['code' => 'O', 'label' => 'Otro', 'class' => 'bg-light-dark'],
            'sin_asignacion' => ['code' => '-', 'label' => 'Sin asignacion', 'class' => 'bg-light-danger'],
        ];

        $result = [];
        $coverageProfiles = $this->buildCoverageProfiles($empleados);

        $monthsToBuild = $mes > 0 ? [$mes] : array_keys(self::MESES);

        foreach ($monthsToBuild as $month) {
            $monthStart = Carbon::create($year, $month, 1)->startOfDay();
            $monthEnd = $monthStart->copy()->endOfMonth();
            $days = (int) $monthEnd->day;

            $rows = [];

            foreach ($empleados as $empleado) {
                $asignaciones = $asignacionesByEmpleado->get($empleado->id, collect());
                $novedades = $novedadesByEmpleado->get($empleado->id, collect())->keyBy(fn ($novedad) => $novedad->fecha->format('Y-m-d'));

                $dayStatuses = [];

                for ($day = 1; $day <= $days; $day++) {
                    $fecha = Carbon::create($year, $month, $day)->startOfDay();
                    $dateKey = $fecha->format('Y-m-d');

                    if ($novedades->has($dateKey)) {
                        $tipoNovedad = $novedades->get($dateKey)->tipo;
                        $dayStatuses[$day] = $tipoNovedad === 'trabaja'
                            ? $this->buildWorkStatus($empleado->turno_laboral ?? null)
                            : ($statusMap[$tipoNovedad] ?? $statusMap['otro']);
                        continue;
                    }

                    $asignacionActiva = $asignaciones->first(fn (CronogramaAsignacion $asignacion) => $asignacion->aplicaEnFecha($fecha));

                    if (! $asignacionActiva || ! $asignacionActiva->patron) {
                        if ((bool) ($empleado->es_franquero ?? false)) {
                            $dayStatuses[$day] = $this->buildFranqueroBaseStatus(
                                $empleado,
                                $fecha,
                                $empleados,
                                $asignacionesByEmpleado,
                                $statusMap,
                                $coverageProfiles
                            );
                        } else {
                            $dayStatuses[$day] = $statusMap['sin_asignacion'];
                        }
                        continue;
                    }

                    $inicio = $asignacionActiva->fecha_inicio->copy()->startOfDay();
                    $diasDesdeInicio = $inicio->diffInDays($fecha);
                    $diasTrabajo = (int) $asignacionActiva->patron->dias_trabajo;
                    $ciclo = max(1, (int) $asignacionActiva->patron->cicloDias());
                    $coverageProfile = $coverageProfiles[$empleado->id] ?? ['index' => 0, 'count' => 1];
                    $groupStep = max(1, intdiv($ciclo, max(1, (int) $coverageProfile['count'])));
                    $offset = ($coverageProfile['index'] ?? 0) * $groupStep;
                    $posicion = ($diasDesdeInicio + $offset) % $ciclo;

                    $dayStatuses[$day] = $posicion < $diasTrabajo
                        ? $this->buildWorkStatus($empleado->turno_laboral ?? null)
                        : $statusMap['descanso'];
                }

                $rows[] = [
                    'id' => $empleado->id,
                    'empleado' => trim($empleado->apellidos . ', ' . $empleado->nombres),
                    'tipo_empleado' => $empleado->tipo_empleado,
                    'turno_key' => $this->normalizeTurno($empleado->turno_laboral ?? null),
                    'es_franquero' => (bool) ($empleado->es_franquero ?? false),
                    'franquero_de_tipo_empleado' => $empleado->franquero_de_tipo_empleado,
                    'franquero_de_empleado_id' => $empleado->franquero_de_empleado_id,
                    'days' => $dayStatuses,
                ];
            }

            for ($day = 1; $day <= $days; $day++) {
                $this->applyCoverageForDay($rows, $day, $statusMap);
            }

            $rowsByTurno = collect($rows)
                ->groupBy('turno_key')
                ->map(fn ($groupRows) => $groupRows->values()->all())
                ->all();

            $turnoBlocks = [];

            foreach (self::TURNOS as $turnoKey => $turnoLabel) {
                $turnoBlocks[] = [
                    'key' => $turnoKey,
                    'label' => $turnoLabel,
                    'rows' => $rowsByTurno[$turnoKey] ?? [],
                ];
            }

            $result[$month] = [
                'month_name' => ucfirst($monthStart->translatedFormat('F')),
                'days' => $days,
                'turnos' => $turnoBlocks,
            ];
        }

        return $result;
    }

    private function ensureNoAsignacionOverlap(int $empleadoId, string $fechaInicio, ?string $fechaFin, ?int $ignoreId = null): void
    {
        $query = CronogramaAsignacion::query()
            ->where('empleado_id', $empleadoId)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->where(function ($query) use ($fechaInicio, $fechaFin) {
                $query->where(function ($sub) use ($fechaInicio) {
                    $sub->whereDate('fecha_inicio', '<=', $fechaInicio)
                        ->where(function ($end) use ($fechaInicio) {
                            $end->whereNull('fecha_fin')
                                ->orWhereDate('fecha_fin', '>=', $fechaInicio);
                        });
                });

                if ($fechaFin) {
                    $query->orWhere(function ($sub) use ($fechaFin) {
                        $sub->whereDate('fecha_inicio', '<=', $fechaFin)
                            ->where(function ($end) use ($fechaFin) {
                                $end->whereNull('fecha_fin')
                                    ->orWhereDate('fecha_fin', '>=', $fechaFin);
                            });
                    });
                }
            });

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'fecha_inicio' => 'El empleado ya tiene una asignacion que se superpone con el rango de fechas indicado.',
            ]);
        }
    }

    private function queryFromRequest(Request $request): array
    {
        return [
            'year' => $request->input('year', now()->year),
            'mes' => $request->input('mes', ''),
            'tipo_empleado' => $request->input('tipo_empleado', ''),
            'turno' => $request->input('turno', ''),
        ];
    }

    private function normalizeTurno(?string $turno): string
    {
        $turno = trim((string) $turno);

        if ($turno === '' || ! array_key_exists($turno, self::TURNOS)) {
            return 'sin_turno';
        }

        return $turno;
    }

    private function normalizeTipo(?string $tipo): string
    {
        return trim(mb_strtoupper((string) $tipo, 'UTF-8'));
    }

    private function isWorkingCode(string $code): bool
    {
        return in_array($code, ['M', 'T', 'N', 'S'], true);
    }

    private function applyCoverageForDay(array &$rows, int $day, array $statusMap): void
    {
        $indicesByGroup = [];

        foreach ($rows as $index => $row) {
            if ($row['turno_key'] === 'sin_turno' || ($row['es_franquero'] ?? false)) {
                continue;
            }

            $groupTipo = $this->normalizeTipo($row['tipo_empleado'] ?? null);
            $groupKey = $row['turno_key'] . '|' . $groupTipo;

            $indicesByGroup[$groupKey][] = $index;
        }

        foreach ($indicesByGroup as $groupKey => $fixedIndices) {
            [$turnoKey, $tipoKey] = explode('|', $groupKey, 2);

            // Regla principal: si un fijo esta FR, su estado debe mantenerse y el franquero lo reemplaza.
            $usedCandidateIndices = [];
            $requiredCoverageCode = $this->turnoCoverageCode($turnoKey);

            foreach ($fixedIndices as $fixedIndex) {
                $fixedDayStatus = $rows[$fixedIndex]['days'][$day] ?? null;
                $fixedCode = (string) ($fixedDayStatus['code'] ?? '');

                if ($fixedCode !== 'FR') {
                    continue;
                }

                $fixedEmployeeId = (int) ($rows[$fixedIndex]['id'] ?? 0);

                $directCandidateIndex = collect($rows)->keys()->first(function ($idx) use ($rows, $turnoKey, $fixedEmployeeId, $usedCandidateIndices, $day) {
                    $candidate = $rows[$idx];

                    if (in_array($idx, $usedCandidateIndices, true)) {
                        return false;
                    }

                    if (! ($candidate['es_franquero'] ?? false)) {
                        return false;
                    }

                    if ((int) ($candidate['franquero_de_empleado_id'] ?? 0) !== $fixedEmployeeId) {
                        return false;
                    }

                    return $this->canFranqueroReplace($candidate['days'][$day] ?? []);
                });

                $candidateIndex = $directCandidateIndex;

                if ($candidateIndex === null) {
                    $candidateIndex = collect($rows)->keys()->first(function ($idx) use ($rows, $turnoKey, $tipoKey, $usedCandidateIndices, $day) {
                        $candidate = $rows[$idx];

                        if (in_array($idx, $usedCandidateIndices, true)) {
                            return false;
                        }

                        if (! ($candidate['es_franquero'] ?? false)) {
                            return false;
                        }

                        $reemplazoPorTipo = $this->normalizeTipo($candidate['franquero_de_tipo_empleado'] ?? null) === $tipoKey;

                        if (! $reemplazoPorTipo) {
                            return false;
                        }

                        return $this->canFranqueroReplace($candidate['days'][$day] ?? []);
                    });
                }

                if ($candidateIndex !== null) {
                    $rows[$candidateIndex]['days'][$day] = $this->buildWorkStatus($turnoKey, 'reemplazo_franquero');
                    $usedCandidateIndices[] = $candidateIndex;
                }
            }

            $hasCoverage = collect($fixedIndices)
                ->contains(fn ($idx) => $this->isWorkingCode((string) ($rows[$idx]['days'][$day]['code'] ?? '')));

            if (! $hasCoverage) {
                // Solo cuenta como cobertura de franquero si ese dia quedo marcado como reemplazo.
                $hasCoverage = collect($rows)
                    ->contains(function ($row) use ($day, $tipoKey, $requiredCoverageCode) {
                        if (! ($row['es_franquero'] ?? false)) {
                            return false;
                        }

                        $matchesTipo = $this->normalizeTipo($row['franquero_de_tipo_empleado'] ?? null) === $tipoKey;
                        if (! $matchesTipo) {
                            return false;
                        }

                        $dayStatus = $row['days'][$day] ?? [];
                        $isReplacement = (($dayStatus['class'] ?? '') === 'bg-light-primary');

                        if (! $isReplacement) {
                            return false;
                        }

                        return (string) ($dayStatus['code'] ?? '') === $requiredCoverageCode;
                    });
            }

            if (! $hasCoverage) {
                $guardiaIndex = collect($fixedIndices)
                    ->first(function ($idx) use ($rows, $day) {
                        $currentCode = (string) ($rows[$idx]['days'][$day]['code'] ?? '');

                        return $currentCode !== 'FR';
                    });

                if ($guardiaIndex !== null) {
                    $rows[$guardiaIndex]['days'][$day] = $this->buildWorkStatus($turnoKey, 'trabaja_guardia');
                }
            }
        }
    }

    private function canFranqueroReplace(array $dayStatus): bool
    {
        $code = (string) ($dayStatus['code'] ?? '');

        if ($this->isWorkingCode($code)) {
            return true;
        }

        // Si el fijo esta FR, el reemplazo del franquero tiene prioridad operativa.
        return in_array($code, ['FR', '-'], true);
    }

    private function turnoCoverageCode(string $turnoKey): string
    {
        return match ($turnoKey) {
            'manana' => 'M',
            'tarde' => 'T',
            'noche' => 'N',
            default => 'S',
        };
    }

    private function buildFranqueroBaseStatus(
        $franquero,
        Carbon $fecha,
        $empleados,
        $asignacionesByEmpleado,
        array $statusMap,
        array $coverageProfiles
    ): array {
        $referenceAsignacion = null;
        $referenceEmpleadoId = null;

        $directEmpleadoId = (int) ($franquero->franquero_de_empleado_id ?? 0);

        if ($directEmpleadoId > 0) {
            $asignacionesDirectas = $asignacionesByEmpleado->get($directEmpleadoId, collect());
            $referenceAsignacion = $asignacionesDirectas->first(fn (CronogramaAsignacion $asignacion) => $asignacion->aplicaEnFecha($fecha) && $asignacion->patron);

            if ($referenceAsignacion) {
                $referenceEmpleadoId = $directEmpleadoId;
            }
        }

        if (! $referenceAsignacion) {
            $franqueroTipo = $this->normalizeTipo($franquero->franquero_de_tipo_empleado ?? null);

            if ($franqueroTipo !== '') {
                $referencia = collect($empleados)
                    ->first(function ($empleado) use ($franqueroTipo, $asignacionesByEmpleado, $fecha) {
                        if ((bool) ($empleado->es_franquero ?? false)) {
                            return false;
                        }

                        if ($this->normalizeTipo($empleado->tipo_empleado ?? null) !== $franqueroTipo) {
                            return false;
                        }

                        $asignaciones = $asignacionesByEmpleado->get($empleado->id, collect());

                        return $asignaciones->contains(fn (CronogramaAsignacion $asignacion) => $asignacion->aplicaEnFecha($fecha) && $asignacion->patron);
                    });

                if ($referencia) {
                    $referenceEmpleadoId = (int) $referencia->id;
                    $asignacionesReferencia = $asignacionesByEmpleado->get($referenceEmpleadoId, collect());
                    $referenceAsignacion = $asignacionesReferencia->first(fn (CronogramaAsignacion $asignacion) => $asignacion->aplicaEnFecha($fecha) && $asignacion->patron);
                }
            }
        }

        if (! $referenceAsignacion || ! $referenceAsignacion->patron) {
            return $statusMap['sin_asignacion'];
        }

        $inicio = $referenceAsignacion->fecha_inicio->copy()->startOfDay();
        $diasDesdeInicio = $inicio->diffInDays($fecha);
        $diasTrabajo = (int) $referenceAsignacion->patron->dias_trabajo;
        $diasDescanso = (int) $referenceAsignacion->patron->dias_descanso;
        $ciclo = max(1, (int) $referenceAsignacion->patron->cicloDias());

        $referenceProfile = $referenceEmpleadoId ? ($coverageProfiles[$referenceEmpleadoId] ?? ['index' => 0, 'count' => 1]) : ['index' => 0, 'count' => 1];
        $groupStep = max(1, intdiv($ciclo, max(1, (int) $referenceProfile['count'])));
        $offset = ($referenceProfile['index'] ?? 0) * $groupStep;

        // El franquero usa el mismo patron, pero desfasado para no descansar junto al fijo que cubre.
        $franqueroOffset = $offset + max(1, $diasDescanso);
        $posicion = ($diasDesdeInicio + $franqueroOffset) % $ciclo;

        if ($posicion < $diasTrabajo) {
            return $this->buildWorkStatus($franquero->turno_laboral ?? null, 'franquero_base');
        }

        return $statusMap['descanso'];
    }

    private function buildCoverageProfiles($empleados): array
    {
        $profiles = [];

        $groups = collect($empleados)
            ->filter(fn ($empleado) => ! ($empleado->es_franquero ?? false))
            ->groupBy(fn ($empleado) => $this->normalizeTipo($empleado->tipo_empleado ?? null));

        foreach ($groups as $groupKey => $groupEmpleados) {
            $sorted = $groupEmpleados
                ->sortBy([
                    fn ($empleado) => $this->normalizeTurno($empleado->turno_laboral ?? null),
                    fn ($empleado) => trim(($empleado->apellidos ?? '') . ' ' . ($empleado->nombres ?? '')),
                ])
                ->values();

            foreach ($sorted as $index => $empleado) {
                $profiles[$empleado->id] = [
                    'index' => $index,
                    'count' => max(1, $sorted->count()),
                    'group' => $groupKey,
                ];
            }
        }

        return $profiles;
    }

    private function buildWorkStatus(?string $turno, ?string $mode = null): array
    {
        $normalizedTurno = $this->normalizeTurno($turno);

        return match ($normalizedTurno) {
            'manana' => [
                'code' => 'M',
                'label' => $mode === 'reemplazo_franquero' ? 'Turno manana - reemplazo' : ($mode === 'trabaja_guardia' ? 'Turno manana - cobertura' : ($mode === 'franquero_base' ? 'Turno manana - franquero' : 'Turno manana')),
                'class' => $mode === 'reemplazo_franquero' ? 'bg-light-primary' : ($mode === 'trabaja_guardia' ? 'bg-light-info' : 'bg-light-success'),
                'working' => true,
            ],
            'tarde' => [
                'code' => 'T',
                'label' => $mode === 'reemplazo_franquero' ? 'Turno tarde - reemplazo' : ($mode === 'trabaja_guardia' ? 'Turno tarde - cobertura' : ($mode === 'franquero_base' ? 'Turno tarde - franquero' : 'Turno tarde')),
                'class' => $mode === 'reemplazo_franquero' ? 'bg-light-primary' : ($mode === 'trabaja_guardia' ? 'bg-light-info' : 'bg-light-success'),
                'working' => true,
            ],
            'noche' => [
                'code' => 'N',
                'label' => $mode === 'reemplazo_franquero' ? 'Turno noche - reemplazo' : ($mode === 'trabaja_guardia' ? 'Turno noche - cobertura' : ($mode === 'franquero_base' ? 'Turno noche - franquero' : 'Turno noche')),
                'class' => $mode === 'reemplazo_franquero' ? 'bg-light-primary' : ($mode === 'trabaja_guardia' ? 'bg-light-info' : 'bg-light-success'),
                'working' => true,
            ],
            default => [
                'code' => 'S',
                'label' => $mode === 'reemplazo_franquero' ? 'Sin turno - reemplazo' : ($mode === 'trabaja_guardia' ? 'Sin turno - cobertura' : ($mode === 'franquero_base' ? 'Sin turno - franquero' : 'Sin turno')),
                'class' => $mode === 'reemplazo_franquero' ? 'bg-light-primary' : ($mode === 'trabaja_guardia' ? 'bg-light-info' : 'bg-light-success'),
                'working' => true,
            ],
        };
    }
}
