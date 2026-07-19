@extends('layouts.admin')

@section('content')
    <div class="page-heading">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3>Editar solicitud #{{ $solicitud->id }}</h3>
                <p class="text-subtitle text-muted">Actualiza datos cargados por taller.</p>
            </div>
            <a href="{{ route('admin.solicitudes-repuestos.show', $solicitud) }}" class="btn btn-light-secondary">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <div class="page-content">
        <section class="section">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.solicitudes-repuestos.update', $solicitud) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        @include('admin.solicitudes-repuestos.partials.form')
                        <div class="d-flex justify-content-end gap-2 mt-3">
                            <a href="{{ route('admin.solicitudes-repuestos.show', $solicitud) }}" class="btn btn-light-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-circle"></i> Actualizar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </div>
@endsection
