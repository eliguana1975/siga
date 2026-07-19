@extends('layouts.admin')

@php
    $severityBadges = [
        'critica' => 'bg-light-danger',
        'alta' => 'bg-light-warning',
        'media' => 'bg-light-info',
        'baja' => 'bg-light-secondary',
    ];
    $summaryText = "Alertas operativas SIGA\n";
    $summaryText .= 'Generado: ' . now()->format('d/m/Y H:i') . "\n\n";
    if ($openSummary->isEmpty()) {
        $summaryText .= "No hay alertas abiertas.\n";
    } else {
        foreach ($openSummary as $item) {
            $summaryText .= '[' . strtoupper($item->severidad) . '] ' . $item->titulo . "\n";
            $summaryText .= trim((string) $item->mensaje) . "\n";
            if ($item->url) {
                $summaryText .= url($item->url) . "\n";
            }
            $summaryText .= "\n";
        }
    }
@endphp

@section('content')
    <div class="page-heading">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3>Notificaciones operativas</h3>
                <p class="text-subtitle text-muted">Alertas internas de stock, solicitudes demoradas y reparaciones vencidas.</p>
            </div>
            <a href="{{ route('admin.notificaciones-operativas.index', request()->query() + ['sync' => 1]) }}" class="btn btn-primary">
                <i class="bi bi-arrow-repeat"></i> Sincronizar
            </a>
        </div>
    </div>

    <div class="page-content">
        <section class="section">
            @foreach (['success' => 'success', 'error' => 'danger'] as $key => $type)
                @if (session($key))
                    <div class="alert alert-{{ $type }} alert-dismissible show fade">
                        {{ session($key) }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
            @endforeach

            <div class="row g-3 mb-3">
                <div class="col-12 col-md-4">
                    <div class="card mb-0"><div class="card-body"><small class="text-muted d-block">Abiertas</small><h3 class="mb-0">{{ $counts['abiertas'] }}</h3></div></div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="card mb-0"><div class="card-body"><small class="text-muted d-block">No leidas</small><h3 class="mb-0">{{ $counts['no_leidas'] }}</h3></div></div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="card mb-0"><div class="card-body"><small class="text-muted d-block">Criticas</small><h3 class="mb-0">{{ $counts['criticas'] }}</h3></div></div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Resumen para enviar</h4>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12 col-lg-8">
                            <textarea id="operationalNotificationSummary" class="form-control" rows="7" readonly>{{ $summaryText }}</textarea>
                        </div>
                        <div class="col-12 col-lg-4 d-flex flex-column gap-2">
                            <button type="button" class="btn btn-outline-secondary" id="copyOperationalNotificationSummary">
                                <i class="bi bi-clipboard"></i> Copiar resumen
                            </button>
                            <a class="btn btn-outline-primary" href="mailto:?subject={{ rawurlencode('Alertas operativas SIGA') }}&body={{ rawurlencode($summaryText) }}">
                                <i class="bi bi-envelope"></i> Enviar por email
                            </a>
                            <a class="btn btn-outline-success" target="_blank" rel="noopener" href="https://wa.me/?text={{ rawurlencode($summaryText) }}">
                                <i class="bi bi-whatsapp"></i> Enviar por WhatsApp
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.notificaciones-operativas.index') }}" class="mb-3">
                        <div class="row g-2 align-items-end">
                            <div class="col-12 col-md-3">
                                <label for="estado" class="form-label mb-1">Estado</label>
                                <select name="estado" id="estado" class="form-select">
                                    <option value="abiertas" @selected($estado === 'abiertas')>Abiertas</option>
                                    <option value="todas" @selected($estado === 'todas')>Todas</option>
                                    <option value="resueltas" @selected($estado === 'resueltas')>Resueltas</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-3">
                                <label for="tipo" class="form-label mb-1">Tipo</label>
                                <select name="tipo" id="tipo" class="form-select">
                                    <option value="">Todos</option>
                                    @foreach ($tipos as $tipoOption)
                                        <option value="{{ $tipoOption }}" @selected($tipo === $tipoOption)>{{ str_replace('_', ' ', $tipoOption) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-3">
                                <label for="severidad" class="form-label mb-1">Severidad</label>
                                <select name="severidad" id="severidad" class="form-select">
                                    <option value="">Todas</option>
                                    @foreach ($severidades as $severity)
                                        <option value="{{ $severity }}" @selected($severidad === $severity)>{{ ucfirst($severity) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-3 d-flex gap-2">
                                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel"></i> Filtrar</button>
                                <a href="{{ route('admin.notificaciones-operativas.index') }}" class="btn btn-light-secondary w-100">Limpiar</a>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-striped align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Severidad</th>
                                    <th>Notificacion</th>
                                    <th>Ultima deteccion</th>
                                    <th>Estado</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($notificaciones as $notificacion)
                                    <tr>
                                        <td><span class="badge {{ $severityBadges[$notificacion->severidad] ?? 'bg-light-secondary' }}">{{ ucfirst($notificacion->severidad) }}</span></td>
                                        <td>
                                            <div class="fw-semibold">{{ $notificacion->titulo }}</div>
                                            <small class="text-muted d-block">{{ $notificacion->mensaje }}</small>
                                            <small class="text-muted">{{ str_replace('_', ' ', $notificacion->tipo) }}</small>
                                        </td>
                                        <td>{{ $notificacion->last_seen_at?->format('d/m/Y H:i') }}</td>
                                        <td>
                                            @if ($notificacion->resolved_at)
                                                <span class="badge bg-light-success">Resuelta</span>
                                                <small class="d-block text-muted">{{ $notificacion->resolvedBy?->name }}</small>
                                            @elseif ($notificacion->read_at)
                                                <span class="badge bg-light-info">Leida</span>
                                            @else
                                                <span class="badge bg-light-warning">Nueva</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            @if ($notificacion->url)
                                                <a href="{{ $notificacion->url }}" class="btn btn-sm btn-info" title="Abrir">
                                                    <i class="bi bi-box-arrow-up-right"></i>
                                                </a>
                                            @endif
                                            @unless ($notificacion->read_at)
                                                <form method="POST" action="{{ route('admin.notificaciones-operativas.read', $notificacion) }}" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-primary" title="Marcar leida">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                </form>
                                            @endunless
                                            @unless ($notificacion->resolved_at)
                                                <form method="POST" action="{{ route('admin.notificaciones-operativas.resolve', $notificacion) }}" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success" title="Resolver">
                                                        <i class="bi bi-check2"></i>
                                                    </button>
                                                </form>
                                            @endunless
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted py-4">No hay notificaciones para los filtros seleccionados.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($notificaciones->count() > 0)
                        <div class="mt-3">{{ $notificaciones->links('vendor.pagination.bootstrap-5-no-summary') }}</div>
                    @endif
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const button = document.getElementById('copyOperationalNotificationSummary');
            const textarea = document.getElementById('operationalNotificationSummary');

            if (!button || !textarea) {
                return;
            }

            button.addEventListener('click', function() {
                textarea.select();
                textarea.setSelectionRange(0, textarea.value.length);
                document.execCommand('copy');
                button.innerHTML = '<i class="bi bi-check2"></i> Copiado';
                window.setTimeout(function() {
                    button.innerHTML = '<i class="bi bi-clipboard"></i> Copiar resumen';
                }, 1800);
            });
        });
    </script>
@endpush
