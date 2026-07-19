@extends('layouts.admin')

@section('content')
    <div class="page-heading">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3>Editar ingreso #{{ $entrada->id }}</h3>
                <p class="text-subtitle text-muted">Actualiza los datos y articulos del ingreso.</p>
            </div>
            <a href="{{ route('admin.entradas.index') }}" class="btn btn-light-secondary">
                <i class="bi bi-arrow-left"></i> Volver al listado
            </a>
        </div>
    </div>

    <div class="page-content">
        <section class="section">
            <form method="POST" action="{{ route('admin.entradas.update', $entrada->id) }}">
                @csrf
                @method('PUT')
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Datos del ingreso</h4>
                    </div>
                    <div class="card-body">
                        @include('admin.entradas.partials.form')
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.entradas.index') }}" class="btn btn-light-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-success">Actualizar</button>
                </div>
            </form>
        </section>
    </div>
@endsection
