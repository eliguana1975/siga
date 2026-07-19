@php
    $selectedFlotaId = old('flota_id', $solicitud->flota_id);
    $selectedOrdenId = old('orden_trabajo_id', $solicitud->orden_trabajo_id);
    $selectedFlota = $selectedFlotaId ? $flotas->firstWhere('id', (int) $selectedFlotaId) : null;
    $selectedOrden = $selectedOrdenId ? $ordenesTrabajo->firstWhere('id', (int) $selectedOrdenId) : null;
@endphp

<div class="row g-3 align-items-start">
    <div class="col-12 col-xl-8">
        <div class="row g-3">
            <div class="col-12 col-md-6">
                <label for="flota_id" class="form-label">Vehiculo / interno</label>
                @if ($selectedFlota)
                    <input type="hidden" name="flota_id" value="{{ $selectedFlota->id }}">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-truck"></i></span>
                        <input type="text" class="form-control" value="{{ $selectedFlota->nro_interno }} - {{ $selectedFlota->dominio }}" readonly>
                    </div>
                @else
                    <select name="flota_id" id="flota_id" class="form-select js-select2" data-placeholder="Seleccione vehiculo">
                        <option value="">Sin vehiculo</option>
                        @foreach ($flotas as $flota)
                            <option value="{{ $flota->id }}" data-chasis="{{ $flota->nro_chasis }}" @selected((string) $selectedFlotaId === (string) $flota->id)>
                                {{ $flota->nro_interno }} - {{ $flota->dominio }}
                            </option>
                        @endforeach
                    </select>
                @endif
                @error('flota_id')<small class="text-danger">{{ $message }}</small>@enderror
            </div>

            <div class="col-12 col-md-6">
                <label for="orden_trabajo_id" class="form-label">Orden de trabajo</label>
                @if ($selectedOrden)
                    <input type="hidden" name="orden_trabajo_id" value="{{ $selectedOrden->id }}">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-clipboard-check"></i></span>
                        <input type="text" class="form-control" value="OT #{{ $selectedOrden->id }} - {{ $selectedOrden->titulo }}" readonly>
                    </div>
                @else
                    <select name="orden_trabajo_id" id="orden_trabajo_id" class="form-select js-select2" data-placeholder="Seleccione orden">
                        <option value="">Sin orden asociada</option>
                        @foreach ($ordenesTrabajo as $orden)
                            <option value="{{ $orden->id }}" data-flota-id="{{ $orden->flota_id }}" @selected((string) $selectedOrdenId === (string) $orden->id)>
                                OT #{{ $orden->id }} - {{ $orden->titulo }}
                            </option>
                        @endforeach
                    </select>
                @endif
                @error('orden_trabajo_id')<small class="text-danger">{{ $message }}</small>@enderror
            </div>

            <div class="col-12 col-md-6">
                <label for="nro_chasis" class="form-label">Nro chasis</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-upc-scan"></i></span>
                    <input type="text" name="nro_chasis" id="nro_chasis" class="form-control"
                        value="{{ old('nro_chasis', $solicitud->nro_chasis) }}" placeholder="Chasis o referencia" readonly>
                </div>
                @error('nro_chasis')<small class="text-danger">{{ $message }}</small>@enderror
            </div>

            <div class="col-12 col-md-6">
                <label for="prioridad" class="form-label">Prioridad (*)</label>
                <select name="prioridad" id="prioridad" class="form-select" required>
                    @foreach (\App\Models\SolicitudRepuesto::PRIORIDADES as $value => $label)
                        <option value="{{ $value }}" @selected(old('prioridad', $solicitud->prioridad ?: 'normal') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('prioridad')<small class="text-danger">{{ $message }}</small>@enderror
            </div>

            <div class="col-12">
                <label for="descripcion_repuesto" class="form-label">Repuesto solicitado (*)</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-tools"></i></span>
                    <input type="text" name="descripcion_repuesto" id="descripcion_repuesto" class="form-control"
                        value="{{ old('descripcion_repuesto', $solicitud->descripcion_repuesto) }}"
                        placeholder="Ej: torreta selectora caja de cambios" required>
                </div>
                @error('descripcion_repuesto')<small class="text-danger">{{ $message }}</small>@enderror
            </div>

            <div class="col-12 col-md-7">
                <label for="codigo_repuesto" class="form-label">Codigo / referencia</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-barcode"></i></span>
                    <input type="text" name="codigo_repuesto" id="codigo_repuesto" class="form-control"
                        value="{{ old('codigo_repuesto', $solicitud->codigo_repuesto) }}">
                </div>
                @error('codigo_repuesto')<small class="text-danger">{{ $message }}</small>@enderror
            </div>

            <div class="col-12 col-md-5">
                <label for="cantidad" class="form-label">Cantidad (*)</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-123"></i></span>
                    <input type="number" name="cantidad" id="cantidad" class="form-control"
                        value="{{ old('cantidad', $solicitud->cantidad ?: 1) }}" min="1" required>
                </div>
                @error('cantidad')<small class="text-danger">{{ $message }}</small>@enderror
            </div>

            <div class="col-12">
                <label for="motivo" class="form-label">Motivo</label>
                <textarea name="motivo" id="motivo" class="form-control" rows="3" readonly>{{ old('motivo', $solicitud->motivo) }}</textarea>
                @error('motivo')<small class="text-danger">{{ $message }}</small>@enderror
            </div>

            <div class="col-12">
                <label for="observaciones_taller" class="form-label">Observaciones de taller</label>
                <textarea name="observaciones_taller" id="observaciones_taller" class="form-control" rows="3">{{ old('observaciones_taller', $solicitud->observaciones_taller) }}</textarea>
                @error('observaciones_taller')<small class="text-danger">{{ $message }}</small>@enderror
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-4">
        <div class="row g-3">
            <div class="col-12">
                <label for="foto_repuesto" class="form-label">Foto del repuesto</label>
                <input type="file" name="foto_repuesto" id="foto_repuesto" class="form-control js-image-preview-input" accept="image/*"
                    data-preview-target="foto_repuesto_preview">
                <div class="mt-2">
                    <img id="foto_repuesto_preview"
                        src="{{ $solicitud->foto_repuesto_path ? asset('storage/' . $solicitud->foto_repuesto_path) : '' }}"
                        alt="Previsualizacion foto del repuesto"
                        class="img-fluid rounded border w-100 js-image-preview {{ $solicitud->foto_repuesto_path ? '' : 'd-none' }}"
                        style="max-height: 220px; object-fit: contain; cursor: zoom-in;"
                        role="button"
                        data-bs-toggle="modal"
                        data-bs-target="#solicitudImagePreviewModal">
                </div>
                @error('foto_repuesto')<small class="text-danger">{{ $message }}</small>@enderror
            </div>

            <div class="col-12">
                <label for="foto_contexto" class="form-label">Foto de referencia</label>
                <input type="file" name="foto_contexto" id="foto_contexto" class="form-control js-image-preview-input" accept="image/*"
                    data-preview-target="foto_contexto_preview">
                <div class="mt-2">
                    <img id="foto_contexto_preview"
                        src="{{ $solicitud->foto_contexto_path ? asset('storage/' . $solicitud->foto_contexto_path) : '' }}"
                        alt="Previsualizacion foto de referencia"
                        class="img-fluid rounded border w-100 js-image-preview {{ $solicitud->foto_contexto_path ? '' : 'd-none' }}"
                        style="max-height: 220px; object-fit: contain; cursor: zoom-in;"
                        role="button"
                        data-bs-toggle="modal"
                        data-bs-target="#solicitudImagePreviewModal">
                </div>
                @error('foto_contexto')<small class="text-danger">{{ $message }}</small>@enderror
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="solicitudImagePreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" style="color: white">Vista de imagen</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex justify-content-end gap-2 mb-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="solicitudImageZoomOut" title="Alejar">
                        <i class="bi bi-zoom-out"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="solicitudImageZoomReset" title="Restablecer">
                        <i class="bi bi-arrows-fullscreen"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="solicitudImageZoomIn" title="Acercar">
                        <i class="bi bi-zoom-in"></i>
                    </button>
                </div>
                <div id="solicitudImagePreviewScroll" class="border rounded text-center"
                    style="height: 75vh; overflow: auto; background: #0f0f1f;">
                    <img id="solicitudImagePreviewModalImg" src="" alt="Vista ampliada" class="rounded"
                        style="max-width: 100%; max-height: 100%; object-fit: contain; transform-origin: top left;">
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const flotaSelect = document.getElementById('flota_id');
            const ordenSelect = document.getElementById('orden_trabajo_id');
            const chasisInput = document.getElementById('nro_chasis');

            function syncChasis() {
                if (!flotaSelect || !chasisInput) {
                    return;
                }

                const option = flotaSelect.selectedOptions && flotaSelect.selectedOptions[0];
                const chasis = option ? option.dataset.chasis : '';

                if (chasis && !chasisInput.value) {
                    chasisInput.value = chasis;
                }
            }

            if (flotaSelect) {
                flotaSelect.addEventListener('change', syncChasis);

                if (window.jQuery) {
                    window.jQuery(flotaSelect).on('select2:select change', syncChasis);
                }
            }

            function syncOrden() {
                if (!ordenSelect) {
                    return;
                }

                const option = ordenSelect.selectedOptions && ordenSelect.selectedOptions[0];
                const flotaId = option ? option.dataset.flotaId : '';

                if (flotaId && flotaSelect.value !== flotaId) {
                    flotaSelect.value = flotaId;

                    if (window.jQuery) {
                        window.jQuery(flotaSelect).trigger('change');
                    } else {
                        flotaSelect.dispatchEvent(new Event('change'));
                    }
                }
            }

            if (ordenSelect) {
                ordenSelect.addEventListener('change', syncOrden);

                if (window.jQuery) {
                    window.jQuery(ordenSelect).on('select2:select change', syncOrden);
                }

                syncOrden();
            }

            syncChasis();

            document.querySelectorAll('.js-image-preview-input').forEach(function (input) {
                input.addEventListener('change', function () {
                    const preview = document.getElementById(input.dataset.previewTarget);
                    const file = input.files && input.files[0];

                    if (!preview || !file) {
                        return;
                    }

                    preview.src = URL.createObjectURL(file);
                    preview.classList.remove('d-none');
                });
            });

            const modalImage = document.getElementById('solicitudImagePreviewModalImg');
            const modalScroll = document.getElementById('solicitudImagePreviewScroll');
            const zoomInButton = document.getElementById('solicitudImageZoomIn');
            const zoomOutButton = document.getElementById('solicitudImageZoomOut');
            const zoomResetButton = document.getElementById('solicitudImageZoomReset');
            let imageZoom = 1;

            function applyImageZoom() {
                if (!modalImage) {
                    return;
                }

                modalImage.style.maxWidth = imageZoom === 1 ? '100%' : 'none';
                modalImage.style.maxHeight = imageZoom === 1 ? '100%' : 'none';
                modalImage.style.width = imageZoom === 1 ? 'auto' : (100 * imageZoom) + '%';
                modalImage.style.height = 'auto';
            }

            function resetImageZoom() {
                imageZoom = 1;
                applyImageZoom();

                if (modalScroll) {
                    modalScroll.scrollTop = 0;
                    modalScroll.scrollLeft = 0;
                }
            }

            document.querySelectorAll('.js-image-preview').forEach(function (image) {
                image.addEventListener('click', function () {
                    if (modalImage && image.src) {
                        modalImage.src = image.src;
                        resetImageZoom();
                    }
                });
            });

            if (zoomInButton) {
                zoomInButton.addEventListener('click', function () {
                    imageZoom = Math.min(imageZoom + 0.25, 4);
                    applyImageZoom();
                });
            }

            if (zoomOutButton) {
                zoomOutButton.addEventListener('click', function () {
                    imageZoom = Math.max(imageZoom - 0.25, 0.5);
                    applyImageZoom();
                });
            }

            if (zoomResetButton) {
                zoomResetButton.addEventListener('click', resetImageZoom);
            }
        });
    </script>
@endpush
