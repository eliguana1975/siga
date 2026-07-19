@extends('layouts.admin')

@section('content')
<div class="page-heading">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h3>Detalle del Artículo</h3>
            <p class="text-subtitle text-muted">
                Información completa del artículo registrado.
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.articulos.index') }}" class="btn btn-light-secondary">
                <i class="bi bi-arrow-left"></i> Volver al listado
            </a>
        </div>
    </div>
</div>

<div class="page-content">
    <section class="section">
        <!-- El contenedor row principal envuelve ambas secciones -->
        <div class="row gy-4">
            
            <!-- COLUMNA IZQUIERDA: Datos del Artículo (7 columnas en pantallas grandes) -->
            <div class="col-12 col-xl-7">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-3 mb-4">
                            <div>
                                <h4 class="card-title">{{ $articulo->nombre }}</h4>
                                <p class="text-muted mb-2">Código del artículo: <strong>{{ $articulo->codigo_producto ?? 'Sin código' }}</strong></p>
                            </div>
                            <div>
                                @if ($articulo->estado_item === 'activo')
                                    <span class="badge bg-light-success text-success">Activo</span>
                                @else
                                    <span class="badge bg-light-secondary text-dark">Inactivo</span>
                                @endif
                            </div>
                        </div>

                        <h5 class="card-title">Datos del inventario</h5>
                        
                        <div class="row row-cols-1 row-cols-md-2 g-3 mt-1">
                            <div class="col">
                                <div class="border rounded p-3 h-100">
                                    <h6 class="mb-2 text-muted">Categoría</h6>
                                    <p class="mb-0 fw-semibold">{{ $articulo->categoria->nombre ?? 'N/A' }}</p>
                                </div>
                            </div>
                            <div class="col">
                                <div class="border rounded p-3 h-100">
                                    <h6 class="mb-2 text-muted">Unidad de medida</h6>
                                    <p class="mb-0 fw-semibold">{{ $articulo->unidadMedida->nombre ?? 'N/A' }}</p>
                                </div>
                            </div>
                            <div class="col">
                                <div class="border rounded p-3 h-100">
                                    <h6 class="mb-2 text-muted">Stock mínimo</h6>
                                    <p class="mb-0 fw-semibold">{{ $articulo->stock_minimo }}</p>
                                </div>
                            </div>
                            <div class="col">
                                <div class="border rounded p-3 h-100">
                                    <h6 class="mb-2 text-muted">Stock máximo</h6>
                                    <p class="mb-0 fw-semibold">{{ $articulo->stock_maximo }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mt-1">
                            <div class="col-12 col-md-6">
                                <div class="border rounded p-3 h-100">
                                    <h6 class="mb-2 text-muted">Stock de pedido</h6>
                                    <p class="mb-0 fw-semibold">{{ $articulo->stock_pedido }}</p>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="border rounded p-3 h-100">
                                    <h6 class="mb-2 text-muted">Reposicion</h6>
                                    <p class="mb-0 fw-semibold">{{ ($articulo->reposicion_modo ?? 'manual') === 'automatico' ? 'Si' : 'No' }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mt-1">
                            <div class="col-12">
                                <div class="border rounded p-3">
                                    <h6 class="mb-3 text-muted">Ubicacion fisica</h6>

                                    @php
                                        $ubicacion = collect([
                                            $articulo->pasillo ? 'Pasillo ' . $articulo->pasillo : null,
                                            $articulo->estanteria ? 'Estanteria ' . $articulo->estanteria : null,
                                            $articulo->casillero ? 'Casillero ' . $articulo->casillero : null,
                                        ])->filter()->implode(' / ');
                                    @endphp

                                    @if ($ubicacion)
                                        <p class="mb-0 fw-semibold">{{ $ubicacion }}</p>
                                    @else
                                        <p class="mb-0 text-muted">Sin ubicacion fisica registrada.</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        @if ($articulo->observaciones)
                            <div class="row g-3 mt-1">
                                <div class="col-12">
                                    <div class="border rounded p-3">
                                        <h6 class="mb-2 text-muted">Observaciones</h6>
                                        <p class="mb-0 text-muted">{{ $articulo->observaciones }}</p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- COLUMNA DERECHA: Imágenes (5 columnas en pantallas grandes) -->
            <div class="col-8 col-xl-5">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title">Imágenes del artículo</h5>
                        <div class="row g-3 mt-1">
                            @foreach (['foto_articulo_1', 'foto_articulo_2', 'foto_articulo_3'] as $imagen)
                                <div class="col-12">
                                    <div class="border rounded overflow-hidden bg-light text-center p-2">
                                        @php
                                            $path = $articulo->{$imagen};
                                        @endphp
                                        <img src="{{ $path ? asset('storage/' . $path) : asset('assets/static/images/samples/1.png') }}" 
                                             class="img-fluid rounded" 
                                             alt="Imagen del artículo" 
                                             style="max-height: 200px; object-fit: contain;">
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
</div>
@endsection
