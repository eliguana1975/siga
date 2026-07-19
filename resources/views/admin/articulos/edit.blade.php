@extends('layouts.admin')

@push('styles')
    <style>
        .modal .input-group .input-group-text {
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 44px;
        }

        .modal .input-group .input-group-text i {
            line-height: 1;
        }

        .article-compact-switch {
            min-height: 38px;
        }

        .article-compact-switch .form-check-label {
            line-height: 1.2;
        }
    </style>
@endpush

@section('content')
    <div class="page-heading">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3>Editar Artículo</h3>
                <p class="text-subtitle text-muted">
                    Actualiza los datos del artículo seleccionado.
                </p>
            </div>
            <a href="{{ route('admin.articulos.index') }}" class="btn btn-light-secondary">
                <i class="bi bi-arrow-left"></i> Volver al listado
            </a>
        </div>
    </div>

    <div class="page-content">
        <section class="section">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.articulos.update', $articulo->id) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-12 col-md-6 mb-3">
                                <label for="nombre" class="form-label">Nombre (*)</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-box-seam"></i></span>
                                    <input type="text" name="nombre" id="nombre" class="form-control"
                                        value="{{ old('nombre', $articulo->nombre) }}" placeholder="Nombre del artículo" required>
                                </div>
                                @error('nombre')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="col-12 col-md-6 mb-3">
                                <label for="codigo_producto" class="form-label">Código</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-barcode"></i></span>
                                    <input type="text" name="codigo_producto" id="codigo_producto" class="form-control"
                                        value="{{ old('codigo_producto', $articulo->codigo_producto) }}" placeholder="Se genera automatico si queda vacio">
                                </div>
                                @error('codigo_producto')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="col-12 col-md-6 mb-3">
                                <label for="categoria_id" class="form-label">Categoría (*)</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-tag-fill"></i></span>
                                    <select name="categoria_id" id="categoria_id" class="form-select" required>
                                        <option value="">Seleccione una categoría</option>
                                        @foreach ($categorias as $categoria)
                                            <option value="{{ $categoria->id }}" @selected(old('categoria_id', $articulo->categoria_id) == $categoria->id)>
                                                {{ $categoria->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('categoria_id')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="col-12 col-md-6 mb-3">
                                <label for="unidad_medida_id" class="form-label">Unidad de Medida (*)</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-rulers"></i></span>
                                    <select name="unidad_medida_id" id="unidad_medida_id" class="form-select" required>
                                        <option value="">Seleccione una unidad</option>
                                        @foreach ($unidadesMedida as $unidad)
                                            <option value="{{ $unidad->id }}" @selected(old('unidad_medida_id', $articulo->unidad_medida_id) == $unidad->id)>
                                                {{ $unidad->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('unidad_medida_id')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="col-6 col-md-2 mb-2">
                                <label for="stock_minimo" class="form-label">Stock Mínimo</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-graph-down"></i></span>
                                    <input type="number" name="stock_minimo" id="stock_minimo" class="form-control"
                                        value="{{ old('stock_minimo', $articulo->stock_minimo) }}" min="0">
                                </div>
                                @error('stock_minimo')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="col-6 col-md-2 mb-2">
                                <label for="stock_maximo" class="form-label">Stock Máximo</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-graph-up"></i></span>
                                    <input type="number" name="stock_maximo" id="stock_maximo" class="form-control"
                                        value="{{ old('stock_maximo', $articulo->stock_maximo) }}" min="0">
                                </div>
                                @error('stock_maximo')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="col-6 col-md-2 mb-2">
                                <label for="stock_pedido" class="form-label">Stock de Pedido</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-box"></i></span>
                                    <input type="number" name="stock_pedido" id="stock_pedido" class="form-control"
                                        value="{{ old('stock_pedido', $articulo->stock_pedido) }}" min="0">
                                </div>
                                @error('stock_pedido')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-6 col-md-2 mb-2">
                                <label for="reposicion_modo" class="form-label">Reposicion</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-arrow-repeat"></i></span>
                                    <select name="reposicion_modo" id="reposicion_modo" class="form-select">
                                        <option value="manual" @selected(old('reposicion_modo', $articulo->reposicion_modo ?? 'manual') === 'manual')>No</option>
                                        <option value="automatico" @selected(old('reposicion_modo', $articulo->reposicion_modo ?? 'manual') === 'automatico')>Si</option>
                                    </select>
                                </div>
                                @error('reposicion_modo')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-6 col-md-2 mb-2">
                                <label class="form-label" for="es_herramienta">Herramienta</label>
                                <div class="border rounded px-2 py-1 article-compact-switch">
                                    <input type="hidden" name="es_herramienta" value="0">
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" role="switch" id="es_herramienta"
                                            name="es_herramienta" value="1" @checked(old('es_herramienta', $articulo->es_herramienta))>
                                        <label class="form-check-label" for="es_herramienta">Entrega empleados</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-md-2 mb-2">
                                <label class="form-label" for="es_ropa_epp">Ropa / EPP</label>
                                <div class="border rounded px-2 py-1 article-compact-switch">
                                    <input type="hidden" name="es_ropa_epp" value="0">
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" role="switch" id="es_ropa_epp"
                                            name="es_ropa_epp" value="1" @checked(old('es_ropa_epp', $articulo->es_ropa_epp))>
                                        <label class="form-check-label" for="es_ropa_epp">Entrega ropa/EPP</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-md-2 mb-2">
                                <label for="pasillo" class="form-label">Pasillo</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-signpost-split"></i></span>
                                    <input type="text" name="pasillo" id="pasillo" class="form-control"
                                        value="{{ old('pasillo', $articulo->pasillo) }}" maxlength="50" placeholder="Pasillo">
                                </div>
                                @error('pasillo')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="col-6 col-md-2 mb-2">
                                <label for="estanteria" class="form-label">Estanteria</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-bookshelf"></i></span>
                                    <input type="text" name="estanteria" id="estanteria" class="form-control"
                                        value="{{ old('estanteria', $articulo->estanteria) }}" maxlength="50" placeholder="Estanteria">
                                </div>
                                @error('estanteria')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="col-6 col-md-2 mb-2">
                                <label for="casillero" class="form-label">Casillero</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-grid-3x3-gap"></i></span>
                                    <input type="text" name="casillero" id="casillero" class="form-control"
                                        value="{{ old('casillero', $articulo->casillero) }}" maxlength="50" placeholder="Casillero">
                                </div>
                                @error('casillero')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="col-6 col-md-2 mb-2">
                                <label for="estado_item" class="form-label">Estado (*)</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-toggle-on"></i></span>
                                    <select name="estado_item" id="estado_item" class="form-select" required>
                                        <option value="activo" @selected(old('estado_item', $articulo->estado_item) === 'activo')>Activo</option>
                                        <option value="inactivo" @selected(old('estado_item', $articulo->estado_item) === 'inactivo')>Inactivo</option>
                                    </select>
                                </div>
                                @error('estado_item')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="col-12 mb-3">
                                <label for="observaciones" class="form-label">Observaciones</label>
                                <textarea name="observaciones" id="observaciones" class="form-control" rows="4" placeholder="Agregue cualquier observación sobre el artículo">{{ old('observaciones', $articulo->observaciones) }}</textarea>
                                @error('observaciones')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="col-12 mb-3">
                                <div class="card p-3">
                                    <h5 class="card-title">Fotos del artículo</h5>
                                    <div class="row">
                                        <div class="col-12 col-md-4 mb-3">
                                            <div class="text-center mb-2">
                                                <img id="fotoArticuloPreview1" src="{{ old('foto_articulo_1', $articulo->foto_articulo_1) ? asset('storage/' . old('foto_articulo_1', $articulo->foto_articulo_1)) : asset('assets/static/images/samples/1.png') }}" class="img-fluid rounded" style="max-height: 180px; width: 100%; object-fit: cover;">
                                            </div>
                                            <label for="foto_articulo_1" class="form-label">Foto 1</label>
                                            <input type="file" id="foto_articulo_1" name="foto_articulo_1" accept="image/*" class="form-control @error('foto_articulo_1') is-invalid @enderror">
                                            @error('foto_articulo_1')
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>
                                        <div class="col-12 col-md-4 mb-3">
                                            <div class="text-center mb-2">
                                                <img id="fotoArticuloPreview2" src="{{ old('foto_articulo_2', $articulo->foto_articulo_2) ? asset('storage/' . old('foto_articulo_2', $articulo->foto_articulo_2)) : asset('assets/static/images/samples/1.png') }}" class="img-fluid rounded" style="max-height: 180px; width: 100%; object-fit: cover;">
                                            </div>
                                            <label for="foto_articulo_2" class="form-label">Foto 2</label>
                                            <input type="file" id="foto_articulo_2" name="foto_articulo_2" accept="image/*" class="form-control @error('foto_articulo_2') is-invalid @enderror">
                                            @error('foto_articulo_2')
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>
                                        <div class="col-12 col-md-4 mb-3">
                                            <div class="text-center mb-2">
                                                <img id="fotoArticuloPreview3" src="{{ old('foto_articulo_3', $articulo->foto_articulo_3) ? asset('storage/' . old('foto_articulo_3', $articulo->foto_articulo_3)) : asset('assets/static/images/samples/1.png') }}" class="img-fluid rounded" style="max-height: 180px; width: 100%; object-fit: cover;">
                                            </div>
                                            <label for="foto_articulo_3" class="form-label">Foto 3</label>
                                            <input type="file" id="foto_articulo_3" name="foto_articulo_3" accept="image/*" class="form-control @error('foto_articulo_3') is-invalid @enderror">
                                            @error('foto_articulo_3')
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>
                                    </div>
                                    <p class="text-muted small">Selecciona hasta 3 imágenes del artículo para almacenar en el disco.</p>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-3">
                            <a href="{{ route('admin.articulos.index') }}" class="btn btn-light-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-success">Actualizar</button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            function attachPreview(inputId, previewId) {
                const input = document.getElementById(inputId);
                const preview = document.getElementById(previewId);

                if (!input || !preview) {
                    return;
                }

                input.addEventListener('change', function () {
                    const file = input.files[0];
                    if (file) {
                        preview.src = URL.createObjectURL(file);
                    }
                });
            }

            attachPreview('foto_articulo_1', 'fotoArticuloPreview1');
            attachPreview('foto_articulo_2', 'fotoArticuloPreview2');
            attachPreview('foto_articulo_3', 'fotoArticuloPreview3');
        });
    </script>
@endpush
