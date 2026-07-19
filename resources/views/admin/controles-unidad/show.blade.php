@extends('layouts.admin')

@php
    $parteLabels = [
        'cumple' => ['label' => 'Cumple', 'class' => 'bg-light-success'],
        'no_cumple' => ['label' => 'No cumple', 'class' => 'bg-light-danger'],
        'na' => ['label' => 'N/A', 'class' => 'bg-light-secondary'],
    ];

    $controlLabels = [
        'hecho' => ['label' => 'Hecho', 'class' => 'bg-light-success'],
        'sin_hacer' => ['label' => 'Sin hacer', 'class' => 'bg-light-warning'],
    ];
@endphp

@section('content')
    <style>
        .control-print-section {
            border: 1px solid var(--bs-border-color);
            border-radius: .45rem;
            overflow: hidden;
        }

        .control-print-section h4 {
            padding: 1rem 1.15rem;
            margin: 0;
            border-bottom: 1px solid var(--bs-border-color);
            font-size: 1rem;
        }

        @media print {
            body,
            #main {
                background: #fff !important;
                color: #000 !important;
            }

            #sidebar,
            .siga-main-toolbar,
            .page-heading .btn,
            .btn {
                display: none !important;
            }

            #main {
                margin-left: 0 !important;
                max-width: 100% !important;
                padding: 0 !important;
            }

            .card {
                border: 1px solid #ddd !important;
                box-shadow: none !important;
                break-inside: avoid;
            }
        }
    </style>

    <div class="page-heading">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
            <div>
                <h3>Check List Vehicular #{{ $control->id }}</h3>
                <p class="text-subtitle text-muted">Interno {{ $control->flota?->nro_interno ?? $control->interno }} - {{ optional($control->created_at)->format('d/m/Y H:i') }}</p>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
                    <i class="bi bi-printer"></i> Imprimir
                </button>
                <a href="{{ route('admin.controles-unidad.index') }}" class="btn btn-light-secondary">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>
            </div>
        </div>
    </div>

    <div class="page-content">
        <section class="section">
            <div id="controlPrintCompanyHeader"></div>

            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Datos principales</h4>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <small class="text-muted d-block">Interno</small>
                            <strong>{{ $control->flota?->nro_interno ?? $control->interno }}</strong>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block">Conductor</small>
                            <strong>{{ $control->conductorUser?->name ?? $control->conductor }}</strong>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted d-block">Kilometraje actual</small>
                            <strong>{{ number_format($control->kilometraje_actual, 0, ',', '.') }} km</strong>
                        </div>
                        <div class="col-md-2">
                            <small class="text-muted d-block">Usuario</small>
                            <strong>{{ $control->user?->name ?? 'Sistema' }}</strong>
                        </div>
                        <div class="col-md-5">
                            <small class="text-muted d-block">Servicio asignado</small>
                            <strong>{{ $control->servicioAsignado?->nombre ?? $control->servicio_asignado }}</strong>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted d-block">Orden de trabajo</small>
                            @if ($control->ordenTrabajo)
                                <span class="badge bg-light-warning">Pendiente</span>
                                <span class="ms-1">#{{ $control->ordenTrabajo->id }}</span>
                            @else
                                <span class="text-muted">No generada</span>
                            @endif
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block">Observaciones generales</small>
                            <span>{{ $control->observaciones_generales }}</span>
                        </div>
                    </div>
                </div>
            </div>

            @foreach ($partes as $parteKey => $parte)
                <div class="card control-print-section">
                    <h4>{{ $parte['titulo'] }}</h4>
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th class="text-end" style="width: 150px;">Resultado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($parte['items'] as $itemKey => $label)
                                    @php
                                        $value = data_get($control->partes, "$parteKey.$itemKey");
                                        $badge = $parteLabels[$value] ?? ['label' => '-', 'class' => 'bg-light-secondary'];
                                    @endphp
                                    <tr>
                                        <td>{{ $label }}</td>
                                        <td class="text-end">
                                            <span class="badge {{ $badge['class'] }}">{{ $badge['label'] }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach

            <div class="card control-print-section">
                <h4>Control vehicular</h4>
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th class="text-end" style="width: 150px;">Resultado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($controlUnidadItems as $itemKey => $label)
                                @php
                                    $value = data_get($control->control_unidad, $itemKey);
                                    $badge = $controlLabels[$value] ?? ['label' => '-', 'class' => 'bg-light-secondary'];
                                @endphp
                                <tr>
                                    <td>{{ $label }}</td>
                                    <td class="text-end">
                                        <span class="badge {{ $badge['class'] }}">{{ $badge['label'] }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            window.sigaMountPrintCompanyHeader?.('#controlPrintCompanyHeader');
        });
    </script>
@endpush
