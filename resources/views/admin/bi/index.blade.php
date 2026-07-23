@extends('layouts.admin')

@push('styles')
    <style>
        .bi-report-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 1rem;
        }

        .bi-report-card {
            height: 100%;
            border: 1px solid var(--bs-border-color);
            transition: transform .18s ease, border-color .18s ease;
        }

        .bi-report-card:hover {
            transform: translateY(-2px);
            border-color: var(--bs-primary);
        }

        .bi-report-icon {
            display: inline-grid;
            width: 2.75rem;
            height: 2.75rem;
            place-items: center;
            color: #fff;
            background: var(--bs-primary);
            border-radius: .45rem;
            font-size: 1.35rem;
        }

        .bi-technical-table code {
            display: inline-block;
            max-width: 420px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
    </style>
@endpush

@section('content')
    <div class="page-heading">
        <div>
            <h3>BI y estadisticas</h3>
            <p class="text-subtitle text-muted">Reportes visuales para analizar el funcionamiento del sistema.</p>
        </div>
    </div>

    <div class="page-content">
        <section class="section">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Reportes principales</h4>
                </div>
                <div class="card-body">
                    <div class="bi-report-grid">
                        @foreach ($reportes as $reporte)
                            <a href="{{ $reporte['url'] }}" class="card bi-report-card mb-0 text-decoration-none">
                                <div class="card-body">
                                    <div class="d-flex align-items-start gap-3">
                                        <span class="bi-report-icon">
                                            <i class="{{ $reporte['icono'] }}"></i>
                                        </span>
                                        <div>
                                            <h5 class="mb-1">{{ $reporte['nombre'] }}</h5>
                                            <p class="text-muted mb-3">{{ $reporte['descripcion'] }}</p>
                                            <span class="btn btn-primary btn-sm">
                                                Abrir reporte
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Datos tecnicos</h4>
                    <small class="text-muted">Estos enlaces son para integraciones, exportaciones o analisis externos.</small>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle mb-0 bi-technical-table">
                            <thead>
                                <tr>
                                    <th>Dataset</th>
                                    <th>Descripcion</th>
                                    <th class="text-end">Accion</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($datasets as $dataset)
                                    <tr>
                                        <td class="fw-semibold">{{ $dataset['nombre'] }}</td>
                                        <td>{{ $dataset['descripcion'] }}</td>
                                        <td class="text-end">
                                            <a href="{{ $dataset['url'] }}" class="btn btn-sm btn-light-secondary">
                                                <i class="bi bi-braces"></i> Ver datos
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
