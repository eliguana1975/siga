<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cronograma anual {{ $year }}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 8px; color: #222; }
        h1 { margin: 0 0 4px; font-size: 16px; }
        h2 { margin: 4px 0 3px; font-size: 12px; }
        .meta { margin-bottom: 6px; font-size: 9px; line-height: 1.25; }
        .month-block { margin-bottom: 6px; page-break-inside: avoid; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 6px; font-size: 8px; table-layout: fixed; }
        th, td { border: 1px solid #bbb; padding: 2px 1px; text-align: center; }
        th.emp, td.emp { text-align: left; width: 120px; min-width: 120px; max-width: 120px; word-break: break-word; }
        th.day, td.day { width: 18px; min-width: 18px; max-width: 18px; }
        td .code { display: inline-block; min-width: 14px; font-weight: 700; }
        .m { background: #d1e7dd; }
        .t { background: #d1e7dd; }
        .n { background: #d1e7dd; }
        .r { background: #cfe2ff; }
        .c { background: #cff4fc; }
        .fr { background: #e2e3e5; }
        .fc { background: #fff3cd; }
        .l { background: #cff4fc; }
        .fe { background: #cfe2ff; }
        .o { background: #d3d3d4; }
        .na { background: #f8d7da; }
        .legend { margin: 4px 0 6px; font-size: 8px; line-height: 1.3; }
        .legend span { display: inline-block; margin-right: 8px; white-space: nowrap; }
        .legend strong { display: inline-block; min-width: 18px; text-align: center; border: 1px solid #999; padding: 0 2px; margin-right: 2px; }
        .row-meta { display: block; font-size: 7px; color: #555; line-height: 1.15; }
        @media print {
            @page { size: A4 landscape; margin: 5mm; }
            .no-print { display: none; }
            body { margin: 0; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom:8px;">
        <button onclick="window.print()">Imprimir</button>
    </div>

    <h1>Cronograma laboral anual {{ $year }}</h1>
    <div class="meta">
        <strong>Mes:</strong> {{ $mes > 0 ? ($cronograma[array_key_first($cronograma)]['month_name'] ?? 'Mes seleccionado') : 'Todos' }}
        <br>
        <strong>Tipo de empleado:</strong> {{ $tipo !== '' ? $tipo : 'Todos' }}
        <br>
        <strong>Turno:</strong> {{ $turno !== '' ? ucfirst($turno) : 'Todos' }}
    </div>

    <div class="legend">
        <span><strong>M</strong> mañana</span>
        <span><strong>T</strong> tarde</span>
        <span><strong>N</strong> noche</span>
        <span><strong>FR</strong> franco</span>
        <span><strong>FC</strong> franco comp.</span>
        <span><strong>FE</strong> feriado</span>
        <span><strong>L</strong> licencia</span>
        <span><strong>-</strong> sin asignación</span>
    </div>

    @foreach($cronograma as $monthData)
        <div class="month-block">
            <h2>{{ $monthData['month_name'] }}</h2>
            @php
                $allRows = collect($monthData['turnos'])
                    ->flatMap(fn ($turnoData) => $turnoData['rows'])
                    ->values();
            @endphp
            <table>
                <thead>
                    <tr>
                        <th class="emp">Empleado</th>
                        @for($d = 1; $d <= $monthData['days']; $d++)
                            <th class="day">{{ $d }}</th>
                        @endfor
                    </tr>
                </thead>
                <tbody>
                    @foreach($allRows as $row)
                        <tr>
                            @php
                                $turnoLabel = match($row['turno_key'] ?? 'sin_turno') {
                                    'manana' => 'Turno manana',
                                    'tarde' => 'Turno tarde',
                                    'noche' => 'Turno noche',
                                    default => 'Sin turno',
                                };
                            @endphp
                            <td class="emp">
                                {{ $row['empleado'] }}
                                <span class="row-meta">
                                    @if($row['es_franquero']) Franquero @else Fijo @endif | {{ $turnoLabel }}
                                </span>
                            </td>
                            @for($d = 1; $d <= $monthData['days']; $d++)
                                @php
                                    $day = $row['days'][$d];
                                    $class = match($day['code']) {
                                        'M' => str_contains(strtolower($day['label'] ?? ''), 'reemplazo') ? 'r' : (str_contains(strtolower($day['label'] ?? ''), 'cobertura') ? 'c' : 'm'),
                                        'T' => str_contains(strtolower($day['label'] ?? ''), 'reemplazo') ? 'r' : (str_contains(strtolower($day['label'] ?? ''), 'cobertura') ? 'c' : 't'),
                                        'N' => str_contains(strtolower($day['label'] ?? ''), 'reemplazo') ? 'r' : (str_contains(strtolower($day['label'] ?? ''), 'cobertura') ? 'c' : 'n'),
                                        'FR' => 'fr',
                                        'FC' => 'fc',
                                        'L' => 'l',
                                        'FE' => 'fe',
                                        'O' => 'o',
                                        default => 'na',
                                    };
                                @endphp
                                <td class="day {{ $class }}"><span class="code">{{ $day['code'] }}</span></td>
                            @endfor
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endforeach

    <script>
        window.addEventListener('load', function() {
            setTimeout(function() { window.print(); }, 300);
        });
    </script>
</body>
</html>
