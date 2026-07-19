@extends('layouts.admin')

@php
    $severityBadges = [
        'critica' => 'bg-light-danger',
        'alta' => 'bg-light-warning',
        'media' => 'bg-light-info',
        'baja' => 'bg-light-secondary',
    ];
@endphp

@section('content')
    <div class="page-heading">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3>Auditoria operativa</h3>
                <p class="text-subtitle text-muted">Control de consistencia de stock, entregas, reparaciones, solicitudes y OT.</p>
            </div>
            <a href="{{ route('admin.auditoria-operativa.json', ['dias_ot' => $diasOt]) }}" class="btn btn-info" target="_blank" rel="noopener">
                <i class="bi bi-braces"></i> Abrir JSON
            </a>
        </div>
    </div>

    <div class="page-content">
        <section class="section">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.auditoria-operativa.index') }}" class="row g-2 align-items-end">
                        <div class="col-12 col-md-4">
                            <label for="dias_ot" class="form-label mb-1">OT antigua desde dias</label>
                            <input type="number" name="dias_ot" id="dias_ot" class="form-control" min="1" value="{{ $diasOt }}">
                        </div>
                        <div class="col-12 col-md-3">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-arrow-repeat"></i> Ejecutar auditoria
                            </button>
                        </div>
                        <div class="col-12 col-md-5">
                            <small class="text-muted d-block">Ultimo control: {{ \Carbon\Carbon::parse($audit['checked_at'])->format('d/m/Y H:i') }}</small>
                            <span class="badge {{ $audit['status'] === 'ok' ? 'bg-light-success' : 'bg-light-warning' }}">
                                {{ $audit['status'] === 'ok' ? 'Sin inconsistencias' : 'Con observaciones' }}
                            </span>
                        </div>
                    </form>
                </div>
            </div>

            <div class="row g-3 mb-3">
                @foreach ($audit['metrics'] as $metric => $total)
                    <div class="col-12 col-md-6 col-xl-3">
                        <div class="card mb-0 h-100">
                            <div class="card-body">
                                <small class="text-muted d-block">{{ str_replace('_', ' ', $metric) }}</small>
                                <h4 class="mb-0">{{ number_format($total, 0, ',', '.') }}</h4>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Inconsistencias detectadas</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Codigo</th>
                                    <th>Severidad</th>
                                    <th>Total</th>
                                    <th>Detalle</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($audit['issues'] as $issue)
                                    <tr>
                                        <td><code>{{ $issue['codigo'] }}</code></td>
                                        <td><span class="badge {{ $severityBadges[$issue['severidad']] ?? 'bg-light-secondary' }}">{{ ucfirst($issue['severidad']) }}</span></td>
                                        <td>{{ number_format($issue['total'], 0, ',', '.') }}</td>
                                        <td>{{ $issue['detalle'] }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">No se detectaron inconsistencias operativas.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
