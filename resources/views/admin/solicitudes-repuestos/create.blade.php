@extends('layouts.admin')

@section('content')
    <div class="page-heading">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3>Nueva solicitud de repuesto</h3>
                <p class="text-subtitle text-muted">Registra repuestos no catalogados para que compras los procese.</p>
            </div>
            <a href="{{ route('admin.solicitudes-repuestos.index') }}" class="btn btn-light-secondary">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <div class="page-content">
        <section class="section">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.solicitudes-repuestos.store') }}" enctype="multipart/form-data">
                        @csrf
                        @include('admin.solicitudes-repuestos.partials.form')
                        <div class="d-flex justify-content-end gap-2 mt-3">
                            <a href="{{ route('admin.solicitudes-repuestos.index') }}" class="btn btn-light-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Guardar solicitud
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </div>
@endsection
