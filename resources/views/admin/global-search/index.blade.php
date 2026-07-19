@extends('layouts.admin')

@section('content')
    <div class="page-heading">
        <div>
            <h3>Busqueda global</h3>
            <p class="text-subtitle text-muted">Resultados disponibles segun tus permisos.</p>
        </div>
    </div>

    <div class="page-content">
        <section class="section">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.global-search.index') }}">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="search" name="q" class="form-control" value="{{ $query }}"
                                placeholder="Buscar unidad, articulo, compra, OT, solicitud, empleado...">
                            <button type="submit" class="btn btn-primary">Buscar</button>
                        </div>
                    </form>
                </div>
            </div>

            @if ($query !== '')
                @forelse ($results as $module => $items)
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">{{ $module }}</h4>
                        </div>
                        <div class="card-body">
                            <div class="list-group list-group-flush">
                                @foreach ($items as $item)
                                    <a href="{{ $item['url'] }}" class="list-group-item list-group-item-action px-0">
                                        <div class="d-flex gap-3 align-items-start">
                                            <span class="btn btn-sm btn-light-primary">
                                                <i class="bi {{ $item['icon'] }}"></i>
                                            </span>
                                            <div>
                                                <strong>{{ $item['title'] }}</strong>
                                                <small class="d-block text-muted">{{ $item['subtitle'] ?: $module }}</small>
                                            </div>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="card">
                        <div class="card-body text-center text-muted py-5">
                            No se encontraron resultados para "{{ $query }}".
                        </div>
                    </div>
                @endforelse
            @endif
        </section>
    </div>
@endsection
