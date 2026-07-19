@extends('layouts.admin')

@section('content')
    <div class="page-heading">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
            <div>
                <h3>Editar orden de trabajo #{{ $orden->id }}</h3>
                <p class="text-subtitle text-muted">Actualiza los datos generales de la orden seleccionada.</p>
            </div>
            <a href="{{ route('admin.ordenes-trabajo.index') }}" class="btn btn-light-secondary">
                <i class="bi bi-arrow-left"></i> Volver al listado
            </a>
        </div>
    </div>

    <div class="page-content">
        <section class="section">
            <div class="card">
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.ordenes-trabajo.update', $orden->id) }}">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            @include('admin.ordenes-trabajo.partials.form-fields', [
                                'orden' => $orden,
                                'modalId' => null,
                                'tipoTrabajoLabels' => $tipoTrabajoLabels,
                                'prioridadLabels' => $prioridadLabels,
                                'estadoLabels' => $estadoLabels,
                                'motivosOrdenTrabajo' => $motivosOrdenTrabajo,
                                'motivoVehiculoParadoLabels' => $motivoVehiculoParadoLabels,
                                'lockOrderFields' => true,
                            ])
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('admin.ordenes-trabajo.index') }}" class="btn btn-light-secondary">Cancelar</a>
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
