@php
    $repuesto = $repuesto ?? null;
    $modalId = $modalId ?? '';
    $isOpenModal = session('open_modal') === $modalId;
    $selectedArticuloId = $isOpenModal ? old('articulo_id', $repuesto?->articulo_id) : $repuesto?->articulo_id;
    $selectedServicioId = $isOpenModal ? old('configuracion_intervalo_servicio_id', $repuesto?->configuracion_intervalo_servicio_id) : $repuesto?->configuracion_intervalo_servicio_id;
    $selectedModoCarga = $isOpenModal ? old('modo_carga_servicio', $repuesto?->modo_carga_servicio ?? 'manual') : ($repuesto?->modo_carga_servicio ?? 'manual');
    $selectedEstado = $isOpenModal ? old('estado', $repuesto?->estado ?? 'activo') : ($repuesto?->estado ?? 'activo');
    $cantidadServicio = $isOpenModal ? old('cantidad_servicio', $repuesto?->cantidad_servicio ?? 1) : ($repuesto?->cantidad_servicio ?? 1);
    $obligatorioServicio = $isOpenModal ? old('obligatorio_servicio', $repuesto?->obligatorio_servicio ?? false) : ($repuesto?->obligatorio_servicio ?? false);
@endphp

<div class="row">
    <div class="col-12 mb-3">
        <label class="form-label">Articulo existente</label>
        <select name="articulo_id" class="form-select">
            <option value="">No vincular articulo</option>
            @foreach ($articulos as $articulo)
                <option value="{{ $articulo->id }}" @selected((string) $selectedArticuloId === (string) $articulo->id)>
                    {{ $articulo->nombre }} - {{ $articulo->codigo_producto ?? 'Sin codigo' }}
                </option>
            @endforeach
        </select>
        @if ($isOpenModal)
            @error('articulo_id')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        @endif
    </div>

    <div class="col-12 col-md-6 mb-3">
        <label class="form-label">Repuesto manual</label>
        <input type="text" name="nombre_repuesto" class="form-control"
            value="{{ $isOpenModal ? old('nombre_repuesto', $repuesto?->nombre_repuesto) : $repuesto?->nombre_repuesto }}"
            maxlength="180" placeholder="Ej: Correa alternador">
        @if ($isOpenModal)
            @error('nombre_repuesto')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        @endif
    </div>

    <div class="col-12 col-md-6 mb-3">
        <label class="form-label">Codigo / referencia</label>
        <input type="text" name="codigo_referencia" class="form-control"
            value="{{ $isOpenModal ? old('codigo_referencia', $repuesto?->codigo_referencia) : $repuesto?->codigo_referencia }}"
            maxlength="100" placeholder="Ej: 6PK1230">
        @if ($isOpenModal)
            @error('codigo_referencia')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        @endif
    </div>

    <div class="col-12 col-md-6 mb-3">
        <label class="form-label">Marca</label>
        <input type="text" name="marca" class="form-control"
            value="{{ $isOpenModal ? old('marca', $repuesto?->marca) : $repuesto?->marca }}"
            maxlength="100" placeholder="Ej: Gates / INA / Bosch">
        @if ($isOpenModal)
            @error('marca')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        @endif
    </div>

    <div class="col-12">
        <div class="border rounded p-3 mb-3">
            <div class="d-flex flex-column flex-md-row justify-content-between gap-2 mb-3">
                <div>
                    <h6 class="mb-1">Kit de servicio</h6>
                    <small class="text-muted">
                        Vincula este repuesto con un servicio para cargarlo automaticamente en la orden de trabajo.
                    </small>
                </div>
                <span class="badge bg-light-info align-self-start">Opcional</span>
            </div>

            <div class="row g-3">
                <div class="col-12 col-md-5">
                    <label class="form-label">Servicio</label>
                    <select name="configuracion_intervalo_servicio_id" class="form-select">
                        <option value="">No usar en kit</option>
                        @foreach ($intervalosServicio as $intervalo)
                            <option value="{{ $intervalo->id }}" @selected((string) $selectedServicioId === (string) $intervalo->id)>
                                {{ $intervalo->etiqueta() }}
                            </option>
                        @endforeach
                    </select>
                    @if ($isOpenModal)
                        @error('configuracion_intervalo_servicio_id')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    @endif
                </div>

                <div class="col-6 col-md-2">
                    <label class="form-label">Cantidad</label>
                    <input type="number" name="cantidad_servicio" class="form-control" value="{{ $cantidadServicio }}" min="1">
                    @if ($isOpenModal)
                        @error('cantidad_servicio')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    @endif
                </div>

                <div class="col-6 col-md-3">
                    <label class="form-label">Carga</label>
                    <select name="modo_carga_servicio" class="form-select">
                        <option value="automatico" @selected($selectedModoCarga === 'automatico')>Automatica</option>
                        <option value="manual" @selected($selectedModoCarga === 'manual')>Manual</option>
                    </select>
                    @if ($isOpenModal)
                        @error('modo_carga_servicio')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    @endif
                </div>

                <div class="col-12 col-md-2 d-flex align-items-end">
                    <div class="form-check mb-2">
                        <input type="hidden" name="obligatorio_servicio" value="0">
                        <input class="form-check-input" type="checkbox" name="obligatorio_servicio" value="1"
                            id="obligatorio-servicio-{{ $modalId }}" @checked($obligatorioServicio)>
                        <label class="form-check-label" for="obligatorio-servicio-{{ $modalId }}">
                            Obligatorio
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-6 mb-3">
        <label class="form-label">Estado (*)</label>
        <select name="estado" class="form-select" required>
            <option value="activo" @selected($selectedEstado === 'activo')>Activo</option>
            <option value="inactivo" @selected($selectedEstado === 'inactivo')>Inactivo</option>
        </select>
        @if ($isOpenModal)
            @error('estado')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        @endif
    </div>

    <div class="col-12 mb-3">
        <label class="form-label">Observaciones</label>
        <textarea name="observaciones" class="form-control" rows="3"
            placeholder="Ej: Aplica para motor Cummins, llevar de repuesto en viaje">{{ $isOpenModal ? old('observaciones', $repuesto?->observaciones) : $repuesto?->observaciones }}</textarea>
        @if ($isOpenModal)
            @error('observaciones')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        @endif
    </div>
</div>
