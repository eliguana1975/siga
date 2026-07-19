@extends('layouts.admin')

@section('content')
    <style>
        .control-form-card {
            border: 1px solid var(--bs-border-color);
            border-radius: .45rem;
            overflow: hidden;
        }

        .control-section-title {
            display: flex;
            align-items: center;
            gap: .5rem;
            padding: 1rem 1.15rem;
            margin: 0;
            border-bottom: 1px solid var(--bs-border-color);
            font-size: 1rem;
        }

        .control-check-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 .35rem;
            padding: .75rem 1rem 1rem;
        }

        .control-check-table th {
            color: var(--bs-heading-color);
            font-size: .78rem;
            font-weight: 700;
            text-align: center;
            text-transform: uppercase;
        }

        .control-check-table th:first-child {
            width: 52%;
        }

        .control-check-table th:not(:first-child) {
            width: 16%;
        }

        .control-check-table td {
            background-color: rgba(127, 127, 127, .07);
            padding: .7rem .6rem;
            vertical-align: middle;
        }

        .control-check-table td:first-child {
            border-radius: .35rem 0 0 .35rem;
            width: 52%;
        }

        .control-check-table td:last-child {
            border-radius: 0 .35rem .35rem 0;
        }

        .control-radio-cell {
            display: table-cell;
            width: 16%;
            text-align: center;
        }

        .control-radio-cell .form-check-input {
            display: block;
            float: none;
            margin: 0 auto;
            width: 1.15rem;
            height: 1.15rem;
            cursor: pointer;
        }

        @media (max-width: 575px) {
            .control-check-table {
                padding: .5rem;
            }

            .control-check-table td:first-child {
                width: 46%;
            }

            .control-radio-cell {
                width: 18%;
            }

            .control-check-table th:first-child {
                width: 46%;
            }

            .control-check-table th:not(:first-child) {
                width: 18%;
            }
        }
    </style>

    <div class="page-heading">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
            <div>
                <h3>Nuevo Check List Vehicular</h3>
                <p class="text-subtitle text-muted">Registra el estado general de documentacion, mecanica, electricidad y accesorios.</p>
            </div>
            <a href="{{ route('admin.controles-unidad.index') }}" class="btn btn-light-secondary">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <div class="page-content">
        <section class="section">
            <form method="POST" action="{{ route('admin.controles-unidad.store') }}">
                @csrf

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Datos principales</h4>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="flota_id" class="form-label">Interno (*)</label>
                                <select id="flota_id" name="flota_id"
                                    class="form-select js-select2 @error('flota_id') is-invalid @enderror"
                                    data-icon-decorated="true" required>
                                    <option value="">Seleccione interno</option>
                                    @foreach ($flotas as $flota)
                                        <option value="{{ $flota->id }}" @selected((string) old('flota_id') === (string) $flota->id)>
                                            {{ $flota->nro_interno }}{{ $flota->dominio ? ' - ' . $flota->dominio : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('flota_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-3">
                                <label for="servicio_asignado_id" class="form-label">Servicio asignado (*)</label>
                                <select id="servicio_asignado_id" name="servicio_asignado_id"
                                    class="form-select js-select2 @error('servicio_asignado_id') is-invalid @enderror"
                                    data-icon-decorated="true" required>
                                    <option value="">Seleccione servicio</option>
                                    @foreach ($serviciosAsignados as $servicioAsignado)
                                        <option value="{{ $servicioAsignado->id }}" @selected((string) old('servicio_asignado_id') === (string) $servicioAsignado->id)>
                                            {{ $servicioAsignado->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('servicio_asignado_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-3">
                                <label for="conductor_user_id" class="form-label">Conductor (*)</label>
                                <input type="hidden" id="conductor_user_id" name="conductor_user_id" value="{{ $conductorActual?->id }}">
                                <input type="text" class="form-control @error('conductor_user_id') is-invalid @enderror"
                                    value="{{ $conductorActual?->name }}" readonly>
                                @error('conductor_user_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-3">
                                <label for="kilometraje_actual" class="form-label">Kilometraje actual (*)</label>
                                <input type="number" id="kilometraje_actual" name="kilometraje_actual"
                                    class="form-control @error('kilometraje_actual') is-invalid @enderror"
                                    value="{{ old('kilometraje_actual') }}" min="0" required>
                                @error('kilometraje_actual') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                @foreach ($partes as $parteKey => $parte)
                    <div class="card control-form-card">
                        <h4 class="control-section-title">
                            <i class="bi bi-clipboard-check"></i>
                            {{ $parte['titulo'] }} <span class="text-danger">*</span>
                        </h4>
                        <div class="table-responsive">
                            <table class="control-check-table">
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th>Cumple</th>
                                        <th>No cumple</th>
                                        <th>N/A</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($parte['items'] as $itemKey => $label)
                                        @php $field = "partes.$parteKey.$itemKey"; @endphp
                                        <tr>
                                            <td>{{ $label }}</td>
                                            @foreach (['cumple' => 'Cumple', 'no_cumple' => 'No cumple', 'na' => 'N/A'] as $value => $title)
                                                <td class="control-radio-cell">
                                                    <input class="form-check-input @error($field) is-invalid @enderror"
                                                        type="radio"
                                                        name="partes[{{ $parteKey }}][{{ $itemKey }}]"
                                                        value="{{ $value }}"
                                                        title="{{ $title }}"
                                                        @checked(old($field) === $value)
                                                        required>
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach

                <div class="card control-form-card">
                    <h4 class="control-section-title">
                        <i class="bi bi-ui-checks"></i>
                        Control vehicular
                    </h4>
                    <div class="table-responsive">
                        <table class="control-check-table">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>Hecho</th>
                                    <th>Sin hacer</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($controlUnidadItems as $itemKey => $label)
                                    @php $field = "control_unidad.$itemKey"; @endphp
                                    <tr>
                                        <td>{{ $label }}</td>
                                        @foreach (['hecho' => 'Hecho', 'sin_hacer' => 'Sin hacer'] as $value => $title)
                                            <td class="control-radio-cell">
                                                <input class="form-check-input @error($field) is-invalid @enderror"
                                                    type="radio"
                                                    name="control_unidad[{{ $itemKey }}]"
                                                    value="{{ $value }}"
                                                    title="{{ $title }}"
                                                    @checked(old($field) === $value)
                                                    required>
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Cierre del checklist</h4>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="observaciones_generales" class="form-label">Observaciones generales (*)</label>
                                <textarea id="observaciones_generales" name="observaciones_generales"
                                    class="form-control @error('observaciones_generales') is-invalid @enderror"
                                    rows="3" required>{{ old('observaciones_generales', 'S/N') }}</textarea>
                                @error('observaciones_generales') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mb-4">
                    <a href="{{ route('admin.controles-unidad.index') }}" class="btn btn-light-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Guardar checklist
                    </button>
                </div>
            </form>
        </section>
    </div>
@endsection
