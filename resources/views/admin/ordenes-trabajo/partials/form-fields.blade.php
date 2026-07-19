@php
    $isEditModal = $orden && session('open_modal') === $modalId;
    $lockOrderFields = (bool) ($lockOrderFields ?? false);

    $selectedEmpleadoId = $isEditModal ? old('empleado_id', $orden->empleado_id) : old('empleado_id', $orden?->empleado_id);
    $selectedReparadorId = $isEditModal ? old('reparador_empleado_id', $orden->reparador_empleado_id) : old('reparador_empleado_id', $orden?->reparador_empleado_id);
    $selectedFlotaId = $isEditModal ? old('flota_id', $orden->flota_id) : old('flota_id', $orden?->flota_id);
    $selectedBaseId = $isEditModal ? old('base_id', $orden->base_id) : old('base_id', $orden?->base_id);
    $kilometraje = $isEditModal ? old('kilometraje', $orden->kilometraje) : old('kilometraje', $orden?->kilometraje);
    $selectedTipo = $isEditModal ? old('tipo_trabajo', $orden->tipo_trabajo) : old('tipo_trabajo', $orden?->tipo_trabajo ?? 'correctivo');
    $selectedPrioridad = $isEditModal ? old('prioridad', $orden->prioridad) : old('prioridad', $orden?->prioridad ?? 'media');
    $selectedEstado = $isEditModal ? old('estado', $orden->estado) : old('estado', $orden?->estado ?? 'pendiente');
    $vehiculoParado = $isEditModal ? old('vehiculo_parado', (int) $orden->vehiculo_parado) : old('vehiculo_parado', (int) ($orden?->vehiculo_parado ?? false));
    $selectedMotivoParado = $isEditModal ? old('motivo_vehiculo_parado', $orden->motivo_vehiculo_parado) : old('motivo_vehiculo_parado', $orden?->motivo_vehiculo_parado);
    $fechaVehiculoParado = $isEditModal
        ? old('fecha_vehiculo_parado', optional($orden->fecha_vehiculo_parado)->format('Y-m-d'))
        : old('fecha_vehiculo_parado', optional($orden?->fecha_vehiculo_parado)->format('Y-m-d'));
    $fechaOrden = $isEditModal
        ? old('fecha_orden', optional($orden->fecha_orden)->format('Y-m-d'))
        : old('fecha_orden', $orden?->fecha_orden ? optional($orden->fecha_orden)->format('Y-m-d') : now()->format('Y-m-d'));
    $fechaOrdenHidden = old('fecha_orden', $orden?->fecha_orden ? optional($orden->fecha_orden)->format('Y-m-d') : now()->format('Y-m-d'));
    $fechaOrdenDisplay = $orden?->fecha_orden ? optional($orden->fecha_orden)->format('d/m/Y') : now()->format('d/m/Y');
    $fechaCierre = $isEditModal
        ? old('fecha_cierre', optional($orden->fecha_cierre)->format('Y-m-d'))
        : old('fecha_cierre', optional($orden?->fecha_cierre)->format('Y-m-d'));
    $selectedMotivos = collect($isEditModal
            ? old('motivos', $orden?->motivos?->pluck('id')->all() ?? [])
            : old('motivos', $orden?->motivos?->pluck('id')->all() ?? []))
        ->map(fn ($id) => (string) $id)
        ->all();
@endphp

<div class="col-md-5">
    <label class="form-label">Titulo / origen (*)</label>
    <input type="text" name="titulo" class="form-control"
        value="{{ $isEditModal ? old('titulo', $orden->titulo) : old('titulo', $orden?->titulo) }}"
        maxlength="150" @readonly($lockOrderFields) required>
</div>

<div class="col-md-7">
    <label class="form-label">Motivos (*)</label>
    <select name="motivos[]" class="form-select js-select2 js-orden-motivos" data-icon-decorated="true" data-placeholder="Seleccione uno o mas motivos" multiple required>
        @foreach (($motivosOrdenTrabajo ?? collect()) as $motivo)
            <option value="{{ $motivo->id }}" @selected(in_array((string) $motivo->id, $selectedMotivos, true))>
                {{ $motivo->nombre }}
            </option>
        @endforeach
    </select>
</div>

<div class="col-md-3">
    <label class="form-label">Kilometraje (*)</label>
    <input type="number" name="kilometraje" class="form-control" value="{{ $kilometraje }}" min="0" @readonly($lockOrderFields) required>
</div>

<div class="col-md-4">
    <label class="form-label">Fecha orden (*)</label>
    @if ($lockOrderFields)
        <input type="hidden" name="fecha_orden" value="{{ $fechaOrdenHidden }}">
        <input type="text" class="form-control" value="{{ $fechaOrdenDisplay }}" readonly>
    @else
        <input type="date" name="fecha_orden" class="form-control" value="{{ $fechaOrden }}" required>
    @endif
</div>

<div class="col-md-4">
    <label class="form-label">Empleado (*)</label>
    <select name="empleado_id" class="form-select js-select2" data-icon-decorated="true" required>
        <option value="">Seleccione empleado</option>
        @foreach ($empleados as $empleado)
            <option value="{{ $empleado->id }}" @selected((string) $selectedEmpleadoId === (string) $empleado->id)>
                {{ $empleado->apellidos }}, {{ $empleado->nombres }} - {{ $empleado->numero_doc }}
            </option>
        @endforeach
    </select>
</div>

<div class="col-md-4">
    <label class="form-label">Vehiculo de flota (*)</label>
    @if ($lockOrderFields)
        <input type="hidden" name="flota_id" value="{{ $selectedFlotaId }}">
    @endif
    <select name="{{ $lockOrderFields ? 'flota_id_display' : 'flota_id' }}" class="form-select js-select2" data-icon-decorated="true" @disabled($lockOrderFields) required>
        <option value="">Seleccione vehiculo</option>
        @foreach ($flotas as $flota)
            <option value="{{ $flota->id }}" @selected((string) $selectedFlotaId === (string) $flota->id)>
                {{ $flota->nro_interno }} - {{ $flota->dominio }}
            </option>
        @endforeach
    </select>
</div>

<div class="col-md-4">
    <label class="form-label">Base de trabajo (*)</label>
    @if ($lockOrderFields)
        <input type="hidden" name="base_id" value="{{ $selectedBaseId }}">
    @endif
    <select name="{{ $lockOrderFields ? 'base_id_display' : 'base_id' }}" class="form-select js-select2" data-icon-decorated="true" @disabled($lockOrderFields) required>
        <option value="">Seleccione base</option>
        @foreach ($bases as $base)
            <option value="{{ $base->id }}" @selected((string) $selectedBaseId === (string) $base->id)>
                {{ $base->nombre }}
            </option>
        @endforeach
    </select>
</div>

<div class="col-md-4">
    <label class="form-label">Empleado reparador</label>
    <select name="reparador_empleado_id" class="form-select js-select2" data-icon-decorated="true">
        <option value="">Seleccione empleado</option>
        @foreach ($empleados as $empleado)
            <option value="{{ $empleado->id }}" @selected((string) $selectedReparadorId === (string) $empleado->id)>
                {{ $empleado->apellidos }}, {{ $empleado->nombres }} - {{ $empleado->numero_doc }}
            </option>
        @endforeach
    </select>
</div>

<div class="col-md-3">
    <label class="form-label">Tipo (*)</label>
    <select name="tipo_trabajo" class="form-select js-select2" data-icon-decorated="true" required>
        @foreach ($tipoTrabajoLabels as $value => $label)
            <option value="{{ $value }}" @selected($selectedTipo === $value)>{{ $label }}</option>
        @endforeach
    </select>
</div>

<div class="col-md-3">
    <label class="form-label">Prioridad (*)</label>
    <select name="prioridad" class="form-select js-select2" data-icon-decorated="true" required>
        @foreach ($prioridadLabels as $value => $label)
            <option value="{{ $value }}" @selected($selectedPrioridad === $value)>{{ $label }}</option>
        @endforeach
    </select>
</div>

<div class="col-md-3">
    <label class="form-label">Estado (*)</label>
    <select name="estado" class="form-select js-select2" data-icon-decorated="true" required>
        @foreach ($estadoLabels as $value => $label)
            <option value="{{ $value }}" @selected($selectedEstado === $value)>{{ $label }}</option>
        @endforeach
    </select>
</div>

<div class="col-md-3">
    <label class="form-label">Fecha cierre</label>
    <input type="date" name="fecha_cierre" class="form-control" value="{{ $fechaCierre }}">
</div>

<div class="col-md-3">
    <label class="form-label">Vehiculo parado</label>
    <select name="vehiculo_parado" class="form-select js-select2" data-icon-decorated="true">
        <option value="0" @selected((string) $vehiculoParado === '0')>No</option>
        <option value="1" @selected((string) $vehiculoParado === '1')>Si</option>
    </select>
</div>

<div class="col-md-4">
    <label class="form-label">Motivo de parada</label>
    <select name="motivo_vehiculo_parado" class="form-select js-select2" data-icon-decorated="true">
        <option value="">Sin motivo</option>
        @foreach (($motivoVehiculoParadoLabels ?? []) as $value => $label)
            <option value="{{ $value }}" @selected($selectedMotivoParado === $value)>{{ $label }}</option>
        @endforeach
    </select>
</div>

<div class="col-md-5">
    <label class="form-label">Fecha desde que esta parado</label>
    <input type="date" name="fecha_vehiculo_parado" class="form-control" value="{{ $fechaVehiculoParado }}">
</div>

<div class="col-12">
    <label class="form-label">Observacion de parada</label>
    <textarea name="observacion_vehiculo_parado" class="form-control" rows="2">{{ $isEditModal ? old('observacion_vehiculo_parado', $orden->observacion_vehiculo_parado) : old('observacion_vehiculo_parado', $orden?->observacion_vehiculo_parado) }}</textarea>
</div>

<div class="col-12">
    <label class="form-label">Descripcion</label>
    <textarea name="descripcion" class="form-control js-orden-descripcion" rows="3" @readonly($lockOrderFields)>{{ $isEditModal ? old('descripcion', $orden->descripcion) : old('descripcion', $orden?->descripcion) }}</textarea>
</div>

<div class="col-12">
    <label class="form-label">Observaciones</label>
    <textarea name="observaciones" class="form-control" rows="2">{{ $isEditModal ? old('observaciones', $orden->observaciones) : old('observaciones', $orden?->observaciones) }}</textarea>
</div>

@once
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                function cleanDescription(value) {
                    return String(value || '')
                        .split(/\r\n|\r|\n/)
                        .filter(line => !/^\s*Motivos:\s*/i.test(line))
                        .join('\n')
                        .trim();
                }

                function selectedMotivoTexts(select) {
                    return Array.from(select?.selectedOptions || [])
                        .map(option => option.textContent.replace(/\s+/g, ' ').trim())
                        .filter(Boolean);
                }

                function descriptionWithMotivos(description, motivos) {
                    const clean = cleanDescription(description);

                    if (!motivos.length) {
                        return clean;
                    }

                    const motivoLine = `Motivos: ${motivos.join(', ')}`;

                    if (!clean) {
                        return motivoLine;
                    }

                    const lines = clean.split(/\r\n|\r|\n/);

                    if (lines.length === 1) {
                        return `${lines[0]}\n${motivoLine}`;
                    }

                    return [lines[0], motivoLine, ...lines.slice(1)].join('\n');
                }

                function syncOrdenDescripcionMotivos(select) {
                    const form = select.closest('form');
                    const descripcion = form?.querySelector('.js-orden-descripcion');

                    if (!descripcion) {
                        return;
                    }

                    descripcion.value = descriptionWithMotivos(descripcion.value, selectedMotivoTexts(select));
                }

                document.querySelectorAll('.js-orden-motivos').forEach(select => {
                    syncOrdenDescripcionMotivos(select);

                    select.addEventListener('change', function () {
                        syncOrdenDescripcionMotivos(select);
                    });

                    if (window.jQuery) {
                        window.jQuery(select).on('select2:select select2:unselect select2:clear', function () {
                            syncOrdenDescripcionMotivos(select);
                        });
                    }
                });
            });
        </script>
    @endpush
@endonce
