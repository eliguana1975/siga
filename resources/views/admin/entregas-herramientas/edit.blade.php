@extends('layouts.admin')

@php
    $herramientasOptions = $herramientas->map(fn ($inventario) => [
        'id' => $inventario->articulo_id,
        'label' => trim(($inventario->articulo?->nombre ?? 'Herramienta') . ($inventario->articulo?->codigo_producto ? ' - ' . $inventario->articulo->codigo_producto : '')),
        'stock' => (int) ($inventario->stock_para_entrega ?? $inventario->cantidad),
        'unidad' => $inventario->articulo?->unidadMedida?->nombre ?? '',
    ])->values();

    $initialRows = collect(old('detalles', $entrega->detalles->map(fn ($detalle) => [
        'articulo_id' => $detalle->articulo_id,
        'cantidad' => $detalle->cantidad_entregada,
        'condicion_entrega' => $detalle->condicion_entrega,
    ])->values()->all()))->values();
@endphp

@section('content')
    <div class="page-heading">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3>Editar entrega de herramientas #{{ $entrega->id }}</h3>
                <p class="text-subtitle text-muted">Actualiza los datos y herramientas entregadas al empleado.</p>
            </div>
            <a href="{{ route('admin.entregas-herramientas.index') }}" class="btn btn-light-secondary">
                <i class="bi bi-arrow-left"></i> Volver
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

                    <form method="POST" action="{{ route('admin.entregas-herramientas.update', $entrega->id) }}">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-12 col-md-4">
                                <label class="form-label">Empleado (*)</label>
                                <select id="empleado_id" name="empleado_id" class="form-select" required>
                                    <option value="">Seleccione empleado</option>
                                    @foreach ($empleados as $empleado)
                                        <option value="{{ $empleado->id }}" @selected((string) old('empleado_id', request('empleado_id', $entrega->empleado_id)) === (string) $empleado->id)>
                                            {{ trim($empleado->apellidos . ' ' . $empleado->nombres) }} - {{ $empleado->numero_doc }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label">Deposito (*)</label>
                                <select id="deposito_id" name="deposito_id" class="form-select" required data-tools-url="{{ route('admin.entregas-herramientas.edit', $entrega->id) }}">
                                    <option value="">Seleccione deposito</option>
                                    @foreach ($depositos as $deposito)
                                        <option value="{{ $deposito->id }}" @selected((string) old('deposito_id', $selectedDepositoId) === (string) $deposito->id)>
                                            {{ $deposito->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label">Fecha entrega (*)</label>
                                <input id="fecha_entrega" type="date" name="fecha_entrega" class="form-control" value="{{ old('fecha_entrega', request('fecha_entrega', $entrega->fecha_entrega?->format('Y-m-d'))) }}" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Observaciones</label>
                                <textarea id="observaciones" name="observaciones" class="form-control" rows="2">{{ old('observaciones', request('observaciones', $entrega->observaciones)) }}</textarea>
                            </div>
                        </div>

                        <div class="border rounded p-3 mt-4">
                            <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
                                <h5 class="mb-0">Detalle de herramientas</h5>
                                <button type="button" id="addToolRow" class="btn btn-sm btn-primary">
                                    <i class="bi bi-plus-circle"></i> Agregar herramienta
                                </button>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm align-middle">
                                    <thead>
                                        <tr>
                                            <th>Herramienta</th>
                                            <th style="width: 130px;">Cantidad</th>
                                            <th>Condicion</th>
                                            <th style="width: 55px;"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="toolRows"></tbody>
                                </table>
                            </div>
                            @if ($herramientas->isEmpty())
                                <p class="text-muted mb-0">No hay herramientas con stock disponible para el deposito seleccionado.</p>
                            @endif
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-3">
                            <a href="{{ route('admin.entregas-herramientas.index') }}" class="btn btn-light-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary">Actualizar entrega</button>
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
            const rows = document.getElementById('toolRows');
            const addButton = document.getElementById('addToolRow');
            const empleadoSelect = document.getElementById('empleado_id');
            const depositoSelect = document.getElementById('deposito_id');
            const fechaEntregaInput = document.getElementById('fecha_entrega');
            const observacionesInput = document.getElementById('observaciones');
            const options = @json($herramientasOptions);
            const initialRows = @json($initialRows);

            function escapeHtml(value) {
                return String(value ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
            }

            function optionHtml(selected) {
                return ['<option value="">Seleccione herramienta</option>'].concat(options.map(option => {
                    const isSelected = String(selected ?? '') === String(option.id) ? 'selected' : '';
                    return `<option value="${escapeHtml(option.id)}" ${isSelected}>${escapeHtml(option.label)} - Stock ${escapeHtml(option.stock)} ${escapeHtml(option.unidad)}</option>`;
                })).join('');
            }

            function rowTemplate(index, data = {}) {
                return `
                    <tr data-tool-row>
                        <td><select name="detalles[${index}][articulo_id]" class="form-select form-select-sm" required>${optionHtml(data.articulo_id)}</select></td>
                        <td><input type="number" name="detalles[${index}][cantidad]" class="form-control form-control-sm" min="1" value="${escapeHtml(data.cantidad || 1)}" required></td>
                        <td><input type="text" name="detalles[${index}][condicion_entrega]" class="form-control form-control-sm" value="${escapeHtml(data.condicion_entrega || '')}" placeholder="Buena / usada"></td>
                        <td><button type="button" class="btn btn-sm btn-light-danger" data-remove-row><i class="bi bi-trash"></i></button></td>
                    </tr>
                `;
            }

            function reindex() {
                rows.querySelectorAll('[data-tool-row]').forEach((row, index) => {
                    row.querySelectorAll('[name^="detalles["]').forEach(input => {
                        input.name = input.name.replace(/detalles\[\d+\]/, `detalles[${index}]`);
                    });
                });
            }

            addButton?.addEventListener('click', function () {
                rows.insertAdjacentHTML('beforeend', rowTemplate(rows.querySelectorAll('[data-tool-row]').length));
            });

            rows?.addEventListener('click', function (event) {
                const button = event.target.closest('[data-remove-row]');
                if (!button) return;
                button.closest('[data-tool-row]')?.remove();
                reindex();
            });

            depositoSelect?.addEventListener('change', function () {
                const url = new URL(this.dataset.toolsUrl, window.location.origin);

                if (this.value) {
                    url.searchParams.set('deposito_id', this.value);
                }

                if (empleadoSelect?.value) {
                    url.searchParams.set('empleado_id', empleadoSelect.value);
                }

                if (fechaEntregaInput?.value) {
                    url.searchParams.set('fecha_entrega', fechaEntregaInput.value);
                }

                if (observacionesInput?.value.trim()) {
                    url.searchParams.set('observaciones', observacionesInput.value.trim());
                }

                window.location.href = url.toString();
            });

            if (initialRows.length > 0) {
                initialRows.forEach((row, index) => rows.insertAdjacentHTML('beforeend', rowTemplate(index, row)));
            } else if (options.length > 0) {
                addButton?.click();
            }
        });
    </script>
@endpush
