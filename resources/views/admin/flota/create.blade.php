@extends('layouts.admin')

@section('content')
    <div class="page-heading">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3>Crear vehiculo</h3>
                <p class="text-subtitle text-muted">Registra un nuevo vehiculo en la flota.</p>
            </div>
            <a href="{{ route('admin.flota.index') }}" class="btn btn-light-secondary">
                <i class="bi bi-arrow-left"></i> Volver al listado
            </a>
        </div>
    </div>

    <div class="page-content">
        <section class="section">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.flota.store') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="row g-3">
                            @include('admin.flota.partials.form-fields')
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-3">
                            <a href="{{ route('admin.flota.index') }}" class="btn btn-light-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary">Guardar</button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </div>
@endsection
