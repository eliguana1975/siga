@extends('layouts.admin')

@php
    $operarioId = $ordenTrabajo?->reparador_empleado_id ?: $ordenTrabajo?->empleado_id;
    $detalleByPos = $cambio?->detalles->keyBy('posicion') ?? collect();
    $estadosCubiertaSacada = \App\Models\DetalleCambioCubierta::ESTADOS_CUBIERTA_SACADA;
    $destinosCubiertaSacada = \App\Models\DetalleCambioCubierta::DESTINOS_CUBIERTA_SACADA;
    $numeroCubiertaVisible = function ($numero) {
        $numero = trim((string) $numero);

        return preg_replace('/^[^-]+-/', '', $numero);
    };
    $articulosCubiertasJson = $articulosCubiertas->map(function ($articulo) {
        return [
            'id' => $articulo->id,
            'nombre' => $articulo->nombre,
            'codigo_producto' => $articulo->codigo_producto,
        ];
    })->values();
    $detalleCambioCubiertasJson = $detalleByPos->map(function ($detalle) use ($numeroCubiertaVisible) {
        $articuloNombre = trim((string) ($detalle->articuloColocado?->nombre ?? ''));
        $nroColocada = $numeroCubiertaVisible($detalle->nro_cubierta_colocada ?? '');

        return [
            'articulo_colocado_id' => $detalle->articulo_colocado_id,
            'articulo_colocado_nombre' => $articuloNombre,
            'cubierta_colocada_id' => $detalle->cubierta_colocada_id,
            'nro_cubierta_colocada' => $nroColocada,
            'label_cubierta_colocada' => trim(($articuloNombre !== '' ? $articuloNombre . ' - ' : '') . ($nroColocada !== '' ? 'Nro ' . $nroColocada : '')),
        ];
    })->all();
    $selectedFlotaId = old('flota_id', $cambio?->flota_id ?? $ordenTrabajo?->flota_id);
    $selectedFlota = $flotas->firstWhere('id', $selectedFlotaId);
    $selectedOperarioId = old('operario_empleado_id', $cambio?->empleado_id ?? $operarioId);
    $selectedOperario = $empleados->firstWhere('id', $selectedOperarioId);
    $selectedFecha = old('fecha', optional($cambio?->fecha)->format('Y-m-d') ?? optional($ordenTrabajo?->fecha_orden)->format('Y-m-d') ?? now()->format('Y-m-d'));
    $selectedKm = old('km', $cambio?->kilometraje ?? $ordenTrabajo?->kilometraje);
@endphp

@php
    $tyreMovementScriptData = [
        'flotaLayouts' => $flotaCubiertaLayouts,
        'defaultLayout' => $cubiertaLayout,
        'articulosCubiertas' => $articulosCubiertasJson,
        'cubiertasPorArticulo' => $cubiertasDisponiblesPorArticulo,
        'cubiertasEnUsoPorFlotaPosicion' => $cubiertasEnUsoPorFlotaPosicion,
        'flotaMedidasPorId' => $flotaMedidasPorId ?? [],
        'detallesPorPosicion' => $detalleCambioCubiertasJson,
        'estadosSacada' => $estadosCubiertaSacada,
        'destinosSacada' => $destinosCubiertaSacada,
    ];
@endphp

@section('content')
    <div class="page-heading no-print">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
            <div>
                <h3>Movimiento de cubiertas</h3>
                <p class="text-subtitle text-muted">Croquis para registrar ubicacion, cubiertas sacadas y cubiertas colocadas.</p>
                @if ($ordenTrabajo)
                    <span class="badge bg-light-info">Cargado desde OT #{{ $ordenTrabajo->id }}</span>
                @endif
            </div>
        </div>
    </div>

    <div class="page-content">
        <section class="section">
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible show fade">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible show fade">
                    <div class="fw-semibold mb-1">Revise los datos del cambio de cubiertas.</div>
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @php
                $formAction = $cambio ? route('admin.movimiento-cubiertas.update', $cambio->id) : route('admin.movimiento-cubiertas.store');
            @endphp

            <form id="tyreMovementForm" class="tyre-sheet" method="POST" action="{{ $formAction }}">
                <div id="tyreMovementData" data-default-fecha="{{ now()->format('Y-m-d') }}" data-auto-print="{{ request()->boolean('print') ? '1' : '0' }}" style="display:none;"></div>
                @csrf
                @if($cambio)
                    @method('PUT')
                @endif
                <input type="hidden" name="orden_trabajo_id" value="{{ old('orden_trabajo_id', $ordenTrabajo?->id) }}">
                <div class="tyre-sheet__header">
                    <div id="tyrePrintCompanyHeader"></div>
                    <h4>MOVIMIENTO CUBIERTAS</h4>
                </div>

                <div class="tyre-sheet__fields">
                    <div class="tyre-field">
                        <label for="orden_trabajo_display">Orden trabajo</label>
                        <input type="text" id="orden_trabajo_display"
                            value="{{ old('orden_trabajo_id', $ordenTrabajo?->id) ? 'OT #' . old('orden_trabajo_id', $ordenTrabajo?->id) : 'Sin OT' }}"
                            readonly>
                        <span class="tyre-print-value" id="orden_trabajo_print">{{ old('orden_trabajo_id', $ordenTrabajo?->id) ? 'OT #' . old('orden_trabajo_id', $ordenTrabajo?->id) : '' }}</span>
                    </div>
                    <div class="tyre-field">
                        <label for="fecha">Fecha</label>
                        <input type="date" id="fecha" name="fecha"
                            value="{{ $selectedFecha }}"
                            readonly>
                        <span class="tyre-print-value" id="fecha_print">{{ $selectedFecha ? \Illuminate\Support\Carbon::parse($selectedFecha)->format('d/m/Y') : '' }}</span>
                    </div>
                    <div class="tyre-field">
                        <label for="interno">Interno</label>
                        <input type="hidden" name="flota_id" value="{{ $selectedFlotaId }}">
                        <select id="interno" class="form-select js-select2" data-placeholder="Seleccione interno" disabled>
                            <option value=""></option>
                            @foreach ($flotas as $flota)
                                <option value="{{ $flota->id }}" @selected((string) $selectedFlotaId === (string) $flota->id)>{{ $flota->nro_interno }}</option>
                            @endforeach
                        </select>
                        <span class="tyre-print-value" id="interno_print">{{ $selectedFlota?->nro_interno ?? '' }}</span>
                    </div>
                    <div class="tyre-field">
                        <label for="km">KM</label>
                        <input type="number" min="0" step="1" id="km" name="km" value="{{ $selectedKm }}" readonly>
                        <span class="tyre-print-value" id="km_print">{{ $selectedKm }}</span>
                    </div>
                    <div class="tyre-field">
                        <label for="operario">Operario</label>
                        <select id="operario" name="operario_empleado_id" class="form-select js-select2" data-placeholder="Seleccione operario">
                            <option value=""></option>
                            @foreach ($empleados as $empleado)
                                <option value="{{ $empleado->id }}" @selected((string) $selectedOperarioId === (string) $empleado->id)>
                                    {{ $empleado->apellidos }}, {{ $empleado->nombres }}
                                </option>
                            @endforeach
                        </select>
                        <span class="tyre-print-value" id="operario_print">{{ $selectedOperario ? trim(($selectedOperario->apellidos ?? '') . ', ' . ($selectedOperario->nombres ?? '')) : '' }}</span>
                    </div>
                </div>

                <div class="tyre-layout">
                    <div id="tyreDiagram" class="tyre-diagram" aria-label="Croquis de ubicacion de cubiertas">
                        @foreach ($cubiertaLayout as $eje)
                            <div class="tyre-axle-row">
                                <div class="tyre-side tyre-side-left">
                                    @forelse ($eje['posiciones_izquierda'] as $posicion)
                                        <div class="tyre-position">{{ $posicion['etiqueta'] }}</div>
                                    @empty
                                        <span class="tyre-side-empty">Sin cub.</span>
                                    @endforelse
                                </div>
                                <div class="tyre-axle-line">
                                    <span>{{ $eje['tipo_label'] }} {{ $eje['numero_eje'] }}</span>
                                </div>
                                <div class="tyre-side tyre-side-right">
                                    @forelse ($eje['posiciones_derecha'] as $posicion)
                                        <div class="tyre-position">{{ $posicion['etiqueta'] }}</div>
                                    @empty
                                        <span class="tyre-side-empty">Sin cub.</span>
                                    @endforelse
                                </div>
                            </div>
                        @endforeach
                    </div>

               
                    <div class="tyre-tables">
                        <div class="tyre-table-wrap">
                            <div class="tyre-table-title">Cubiertas retiradas</div>
                            <table class="tyre-table tyre-table--retired">
                                <colgroup>
                                    <col class="tyre-col-position">
                                    <col class="tyre-col-sacada">
                                    <col class="tyre-col-sacada-status">
                                    <col class="tyre-col-sacada-dest">
                                    <col class="tyre-col-sacada-note">
                                </colgroup>
                                <thead>
                                    <tr>
                                        <th>Cubierta</th>
                                        <th>Sacada</th>
                                        <th>Estado</th>
                                        <th>Destino</th>
                                        <th>Motivo / obs.</th>
                                    </tr>
                                </thead>
                                <tbody id="tyreRetiredBody">
                                    @foreach ($posicionesCubiertas as $posicionItem)
                                        @php
                                            $posicion = $posicionItem['codigo'];
                                            $posicionLabel = $posicionItem['etiqueta'];
                                            $detallePos = $detalleByPos->get($posicion);
                                        @endphp
                                        <tr>
                                            <td>{{ $posicionLabel }}</td>
                                            <td><input type="text" name="sacada[{{ $posicion }}]" value="{{ old('sacada.' . $posicion, $numeroCubiertaVisible(optional($detallePos)->nro_cubierta_sacada ?? '')) }}" aria-label="Numero de control de cubierta sacada {{ $posicion }}"></td>
                                            <td>
                                                <select name="estado_sacada[{{ $posicion }}]" aria-label="Estado cubierta sacada {{ $posicion }}">
                                                    <option value="">Sin evaluar</option>
                                                    @foreach ($estadosCubiertaSacada as $value => $label)
                                                        <option value="{{ $value }}" @selected(old('estado_sacada.' . $posicion, optional($detallePos)->estado_cubierta_sacada) === $value)>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <select name="destino_sacada[{{ $posicion }}]" aria-label="Destino cubierta sacada {{ $posicion }}">
                                                    <option value="">Sin destino</option>
                                                    @foreach ($destinosCubiertaSacada as $value => $label)
                                                        <option value="{{ $value }}" @selected(old('destino_sacada.' . $posicion, optional($detallePos)->destino_cubierta_sacada) === $value)>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td class="tyre-note-cell">
                                                <input type="text" name="motivo_baja_sacada[{{ $posicion }}]"
                                                    value="{{ old('motivo_baja_sacada.' . $posicion, optional($detallePos)->motivo_baja_cubierta_sacada ?? '') }}"
                                                    placeholder="Motivo baja">
                                                <input type="text" name="observacion_sacada[{{ $posicion }}]"
                                                    value="{{ old('observacion_sacada.' . $posicion, optional($detallePos)->observacion_cubierta_sacada ?? '') }}"
                                                    placeholder="Observacion">
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            @if ($articulosCubiertas->isEmpty())
                                <p class="text-warning small mt-2 mb-0">No hay cubiertas disponibles en stock (nuevas o reutilizables).</p>
                            @endif
                        </div>

                        <div class="tyre-table-wrap">
                            <div class="tyre-table-title">Cubiertas colocadas</div>
                            @php
                                $totalCubiertasDisponibles = collect($cubiertasDisponiblesPorArticulo)->flatten(1)->count();
                            @endphp
                            <p class="text-muted small mt-1 mb-2">Cubiertas disponibles en stock: {{ $totalCubiertasDisponibles }}</p>
                            <table class="tyre-table tyre-table--placed">
                                <colgroup>
                                    <col class="tyre-col-position">
                                    <col class="tyre-col-colocada">
                                    <col class="tyre-col-nro">
                                </colgroup>
                                <thead>
                                    <tr>
                                        <th>Cubierta</th>
                                        <th>Colocada</th>
                                        <th>Nro</th>
                                    </tr>
                                </thead>
                                <tbody id="tyrePlacedBody">
                                    @php
                                        $todasLasCubiertasDisponibles = collect($cubiertasDisponiblesPorArticulo)->flatten(1)->values();
                                    @endphp
                                    @foreach ($posicionesCubiertas as $posicionItem)
                                        @php
                                            $posicion = $posicionItem['codigo'];
                                            $posicionLabel = $posicionItem['etiqueta'];
                                            $articuloCubiertaId = $posicionItem['articulo_cubierta_id'] ?? null;
                                            $cubiertasDisponibles = collect($cubiertasDisponiblesPorArticulo[$articuloCubiertaId] ?? []);
                                            if ($cubiertasDisponibles->isEmpty()) {
                                                $cubiertasDisponibles = $todasLasCubiertasDisponibles;
                                            }
                                            $detallePos = $detalleByPos->get($posicion);
                                            $selectedCubiertaId = old('cubierta_colocada_id.' . $posicion, optional($detallePos)->cubierta_colocada_id ?? '');
                                            $selectedCubiertaVisible = $selectedCubiertaId && $cubiertasDisponibles->contains(fn ($cubierta) => (string) $cubierta['id'] === (string) $selectedCubiertaId);
                                        @endphp
                                        <tr>
                                            <td>{{ $posicionLabel }}</td>
                                            <td>
                                                <select name="cubierta_colocada_id[{{ $posicion }}]" class="form-select js-select2 tyre-select"
                                                    data-placeholder="Seleccione cubierta"
                                                    aria-label="Cubierta colocada {{ $posicion }}">
                                                    <option value=""></option>
                                                    @foreach ($cubiertasDisponibles as $cubierta)
                                                        <option value="{{ $cubierta['id'] }}" @selected((string) $selectedCubiertaId === (string) $cubierta['id'])>
                                                            {{ $cubierta['label'] }}
                                                        </option>
                                                    @endforeach
                                                    @if ($selectedCubiertaId && ! $selectedCubiertaVisible)
                                                        <option value="{{ $selectedCubiertaId }}" data-numero="{{ $numeroCubiertaVisible(optional($detallePos)->nro_cubierta_colocada) }}" data-articulo-id="{{ optional($detallePos)->articulo_colocado_id }}" selected>
                                                            {{ trim(((optional($detallePos?->articuloColocado)->nombre ?? '') ? optional($detallePos?->articuloColocado)->nombre . ' - ' : '') . (optional($detallePos)->nro_cubierta_colocada ? 'Nro ' . $numeroCubiertaVisible(optional($detallePos)->nro_cubierta_colocada) : 'Cubierta seleccionada')) }}
                                                        </option>
                                                    @endif
                                                </select>
                                            </td>
                                            <td>
                                                <input type="text" name="nro_colocada[{{ $posicion }}]"
                                                    value="{{ old('nro_colocada.' . $posicion, $numeroCubiertaVisible(optional($detallePos)->nro_cubierta_colocada ?? '')) }}"
                                                    placeholder="Nro control"
                                                    aria-label="Numero de control de cubierta colocada {{ $posicion }}">
                                                <input type="hidden" name="colocada[{{ $posicion }}]" value="{{ old('colocada.' . $posicion, optional($detallePos)->articulo_colocado_id ?? '') }}">
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <p class="text-muted small mt-2 mb-0">Las cubiertas colocadas se filtran por la medida/articulo configurado en el eje del interno.</p>
                        </div>
                    </div>
                </div>

                <div class="tyre-observations">
                    <label for="observaciones">Observaciones</label>
                    <textarea id="observaciones" name="observaciones" rows="5">{{ old('observaciones', $cambio?->observaciones ?? '') }}</textarea>
                </div>

                <div class="tyre-actions no-print">
                    <a href="{{ route('admin.gestion-cubiertas.index') }}" class="btn btn-light-secondary">
                        <i class="bi bi-arrow-left"></i> Volver
                    </a>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-save"></i> Guardar cambio
                    </button>
                </div>
            </form>
        </section>
    </div>
@endsection

@push('styles')
    <style>
        .tyre-sheet {
            max-width: 1180px;
            margin: 0 auto;
            padding: 1.25rem;
            color: #d7dcff;
            background: #1f1e2e;
            border: 1px solid rgba(180, 185, 220, .28);
            border-radius: 8px;
        }

        .tyre-sheet__header {
            display: grid;
            grid-template-columns: 1fr;
            align-items: center;
            border: 1px solid rgba(222, 226, 255, .55);
            min-height: 92px;
        }

        .tyre-sheet__header h4 {
            margin: 0;
            text-align: center;
            font-size: 1.35rem;
            font-weight: 800;
            letter-spacing: 0;
        }

        .tyre-sheet__fields {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            border-inline: 1px solid rgba(222, 226, 255, .55);
            border-bottom: 1px solid rgba(222, 226, 255, .55);
        }

        .tyre-field {
            min-height: 58px;
            border-right: 1px solid rgba(222, 226, 255, .35);
            padding: .45rem .55rem;
        }

        .tyre-field:last-child {
            border-right: 0;
        }

        .tyre-field label,
        .tyre-observations label {
            display: block;
            margin-bottom: .25rem;
            color: #f2f4ff;
            font-weight: 700;
            text-transform: uppercase;
            font-size: .78rem;
        }

        .tyre-field input,
        .tyre-field select,
        .tyre-table input,
        .tyre-table select,
        .tyre-observations textarea {
            width: 100%;
            min-height: 34px;
            color: #f7f8ff;
            background: #151522;
            border: 1px solid rgba(222, 226, 255, .25);
            border-radius: 4px;
            padding: .35rem .45rem;
            text-align: left;
            text-align-last: left;
        }

        .tyre-print-value {
            display: none;
        }

        .tyre-layout {
            border-inline: 1px solid rgba(222, 226, 255, .55);
            border-bottom: 1px solid rgba(222, 226, 255, .55);
            padding: 2rem 1.25rem 1.5rem;
        }

        .tyre-diagram {
            display: grid;
            gap: 1rem;
            width: min(680px, 100%);
            margin: 0 auto 1.75rem;
        }

        .tyre-axle-row {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 130px minmax(0, 1fr);
            gap: 1rem;
            align-items: center;
        }

        .tyre-side {
            display: flex;
            gap: .45rem;
            align-items: center;
        }

        .tyre-side-left {
            justify-content: flex-end;
        }

        .tyre-side-right {
            justify-content: flex-start;
        }

        .tyre-axle-line {
            position: relative;
            min-height: 54px;
            display: grid;
            place-items: center;
            color: #f2f4ff;
            font-size: .75rem;
            font-weight: 800;
            text-align: center;
            text-transform: uppercase;
        }

        .tyre-axle-line::before {
            content: "";
            position: absolute;
            left: -1rem;
            right: -1rem;
            top: 50%;
            height: 6px;
            transform: translateY(-50%);
            background: #e9ecff;
            border-radius: 4px;
            z-index: 0;
        }

        .tyre-axle-line span {
            position: relative;
            z-index: 1;
            padding: .2rem .45rem;
            background: #1f1e2e;
        }

        .tyre-position {
            width: 74px;
            min-height: 54px;
            display: grid;
            place-items: center;
            color: #f7f8ff;
            background: #151522;
            border: 2px solid #e9ecff;
            border-radius: 6px;
            font-size: .78rem;
            font-weight: 800;
            letter-spacing: 0;
            text-align: center;
            box-shadow: inset 0 0 0 1px rgba(255,255,255,.08);
        }

        .tyre-side-empty {
            color: rgba(215, 220, 255, .55);
            font-size: .75rem;
        }

        .tyre-tables {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
            align-items: start;
            min-width: 0;
        }

        .tyre-table-wrap {
            min-width: 0;
            overflow-x: auto;
        }

        .tyre-table-title {
            margin-bottom: .45rem;
            color: #f2f4ff;
            font-size: .78rem;
            font-weight: 800;
            letter-spacing: 0;
            text-transform: uppercase;
        }

        .tyre-table {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
        }

        .tyre-table--retired {
            min-width: 760px;
        }

        .tyre-table--placed {
            min-width: 520px;
        }

        .tyre-table th,
        .tyre-table td {
            height: 46px;
            border: 1px solid rgba(222, 226, 255, .55);
            padding: .35rem .45rem;
            text-align: center;
            vertical-align: middle;
        }

        .tyre-table--retired th,
        .tyre-table--retired td {
            height: auto;
            min-height: 46px;
        }

        .tyre-col-position {
            width: 86px;
        }

        .tyre-col-sacada {
            width: 145px;
        }

        .tyre-col-sacada-status,
        .tyre-col-sacada-dest {
            width: 150px;
        }

        .tyre-col-sacada-note {
            width: 210px;
        }

        .tyre-col-colocada {
            width: auto;
        }

        .tyre-col-nro {
            width: 112px;
        }

        .tyre-table th,
        .tyre-table td:first-child {
            color: #f2f4ff;
            font-weight: 800;
            text-transform: uppercase;
        }

        .tyre-table input,
        .tyre-table select {
            display: block;
            height: 34px;
            border: 0;
            text-align: left;
            text-align-last: left;
        }

        .tyre-note-cell {
            vertical-align: middle;
        }

        .tyre-note-cell input {
            height: 30px;
        }

        .tyre-note-cell input + input {
            margin-top: .35rem;
        }

        .tyre-table .select2-container {
            width: 100% !important;
            min-width: 0;
            text-align: left;
        }

        .tyre-table .select2-container--bootstrap-5 .select2-selection--single {
            height: 34px;
            min-height: 34px;
            padding-block: .2rem;
            border: 0;
            background: #151522;
        }

        .tyre-table .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
            align-items: center;
            display: flex;
            height: 100%;
            line-height: 1.2;
            text-align: left;
            text-align-last: left;
        }

        .tyre-observations {
            border-inline: 1px solid rgba(222, 226, 255, .55);
            border-bottom: 1px solid rgba(222, 226, 255, .55);
            padding: 1rem;
        }

        .tyre-observations textarea {
            min-height: 130px;
            resize: vertical;
        }

        .tyre-actions {
            display: flex;
            justify-content: flex-end;
            gap: .75rem;
            padding-top: 1rem;
        }

        @media (max-width: 768px) {
            .tyre-sheet__header h4 {
                padding: 1rem;
            }

            .tyre-sheet__fields,
            .tyre-tables {
                grid-template-columns: 1fr;
            }

            .tyre-layout {
                padding-inline: .75rem;
            }

            .tyre-table .select2-container {
                min-width: 0;
            }

            .tyre-field {
                border-right: 0;
                border-bottom: 1px solid rgba(222, 226, 255, .35);
            }

            .tyre-axle-row {
                grid-template-columns: minmax(0, 1fr) 88px minmax(0, 1fr);
                gap: .5rem;
            }

            .tyre-position {
                width: 58px;
                min-height: 46px;
                font-size: .68rem;
            }

            .tyre-axle-line {
                font-size: .65rem;
            }
        }

        @media print {
            @page {
                size: A4 portrait;
                margin: 7mm;
            }

            html, body {
                width: 100%;
                height: auto;
                margin: 0;
                padding: 0;
                background: #fff;
            }

            #main,
            .page-content,
            .section {
                margin: 0 !important;
                padding: 0 !important;
                max-width: 100% !important;
                background: #fff !important;
            }

            body * {
                visibility: hidden;
            }

            #tyrePrintCompanyHeader,
            #tyrePrintCompanyHeader *,
            .tyre-sheet,
            .tyre-sheet * {
                visibility: visible;
            }

            .no-print,
            .no-print * {
                display: none !important;
            }

            #tyrePrintCompanyHeader {
                display: block !important;
                align-self: stretch;
                width: 42mm;
                height: 28mm;
                margin: 0;
                color: #000;
                background: #e5e5e5;
                border-right: 1px solid #000;
                overflow: hidden;
            }

            #tyrePrintCompanyHeader .siga-page-print-company {
                display: grid !important;
                place-items: center;
                height: 100%;
                margin: 0;
                padding: 2mm;
                border: 0;
                text-align: center;
                gap: 1mm;
            }

            #tyrePrintCompanyHeader .siga-page-print-company img {
                width: 18mm;
                height: 12mm;
            }

            #tyrePrintCompanyHeader .siga-page-print-company h1 {
                font-size: 8px;
                margin: 0;
                text-align: center;
            }

            #tyrePrintCompanyHeader .siga-page-print-company p {
                display: none;
            }

            .tyre-layout .text-muted,
            .tyre-layout .text-warning {
                display: none !important;
            }

            .tyre-sheet {
                position: relative;
                inset: auto;
                max-width: 100%;
                min-height: auto;
                width: 100%;
                margin: 0;
                padding: 0;
                color: #000;
                background: #fff;
                border: 2px solid #000;
                border-radius: 0;
                page-break-inside: avoid;
                page-break-after: avoid;
                break-inside: avoid;
                break-after: avoid;
                font-size: 10px;
            }

            .tyre-sheet__header {
                display: grid;
                grid-template-columns: 42mm minmax(0, 1fr);
                min-height: 28mm;
                padding: 0;
                border: 1px solid #000;
            }

            .tyre-sheet__header::before {
                content: none;
            }

            .tyre-sheet__header h4 {
                display: grid;
                place-items: center;
                margin: 0;
                font-size: 16px;
                font-weight: 700;
                line-height: 1;
            }

            .tyre-sheet__fields {
                display: grid;
                grid-template-columns: 1fr;
                border: 1px solid #000;
            }

            .tyre-field {
                display: grid;
                grid-template-columns: 34mm minmax(0, 1fr);
                align-items: center;
                min-height: auto;
                border: 1px solid #000;
                padding: 0;
            }

            .tyre-field label {
                margin: 0;
                padding: 1.2mm 1.5mm;
                color: #000;
                font-size: 10px;
                line-height: 1;
                text-transform: uppercase;
            }

            .tyre-field input,
            .tyre-field select {
                min-height: 6mm;
                font-size: 10px;
                line-height: 1.1;
                padding: .8mm 1.2mm;
                color: #000;
                background: #fff;
                border: 0;
            }

            .tyre-field > input:not([type="hidden"]),
            .tyre-field > select {
                display: none !important;
            }

            .tyre-print-value {
                display: block;
                min-height: 6mm;
                padding: .8mm 1.2mm;
                color: #000;
                font-size: 10px;
                line-height: 1.1;
            }

            .tyre-layout {
                display: grid;
                grid-template-columns: 1fr;
                gap: 5mm;
                align-items: start;
                border: 1px solid #000;
                padding: 9mm 24mm 6mm;
            }

            .tyre-diagram {
                position: relative;
                width: 105mm;
                max-width: 100%;
                gap: 22mm;
                margin: 0 auto;
                padding: 2mm 0;
            }

            .tyre-axle-row {
                grid-template-columns: minmax(0, 1fr) 32mm minmax(0, 1fr);
                gap: 0;
            }

            .tyre-diagram::after {
                content: "";
                position: absolute;
                left: 50%;
                top: 18mm;
                width: 2.8mm;
                height: 49mm;
                transform: translateX(-50%);
                background: #000;
                z-index: 0;
            }

            .tyre-side {
                gap: 1.5mm;
            }

            .tyre-axle-line {
                min-height: 24mm;
                color: #000;
                font-size: 0;
            }

            .tyre-axle-line::before {
                left: -9mm;
                right: -9mm;
                height: 1.5mm;
                background: #000;
                z-index: 0;
            }

            .tyre-axle-line span {
                display: none;
            }

            .tyre-position {
                width: 15mm;
                min-height: 25mm;
                font-size: 10px;
                border: 1px solid #000;
                border-radius: 0;
                background: #fff;
                z-index: 1;
            }

            .tyre-side-empty {
                color: #000;
                font-size: 5px;
            }

            .tyre-tables {
                grid-template-columns: 1fr 1fr;
                gap: 30mm;
                margin-bottom: 0;
            }

            .tyre-table-wrap {
                overflow: visible;
            }

            .tyre-table-title {
                display: none;
            }

            .tyre-table--retired col:nth-child(n+3),
            .tyre-table--retired th:nth-child(n+3),
            .tyre-table--retired td:nth-child(n+3),
            .tyre-table--placed col:nth-child(2),
            .tyre-table--placed th:nth-child(2),
            .tyre-table--placed td:nth-child(2) {
                display: none;
            }

            .tyre-table--retired th:nth-child(2)::after {
                content: "";
            }

            .tyre-table--placed th:nth-child(3) {
                font-size: 0;
            }

            .tyre-table--placed th:nth-child(3)::before {
                content: "Colocada";
                font-size: 10px;
                color: #000;
            }

            .tyre-table--retired,
            .tyre-table--placed {
                min-width: 0;
            }

            .tyre-table th,
            .tyre-table td {
                height: auto;
                padding: 1.4mm 2mm;
                border: 1px solid #000;
                font-size: 10px;
                line-height: 1.1;
            }

            .tyre-table input,
            .tyre-table select {
                height: auto;
                min-height: 5mm;
                font-size: 10px;
                line-height: 1.1;
                padding: .4mm;
                text-align: left;
                text-align-last: left;
            }

            .tyre-note-cell input {
                height: auto;
                min-height: 11px;
            }

            .tyre-note-cell input + input {
                margin-top: .4mm;
            }

            .tyre-observations {
                border: 0;
                margin: 0 24mm 8mm;
                padding: 1.5mm 2mm 2mm;
            }

            .tyre-observations label {
                display: block;
                font-size: 12px;
                margin: 0 0 1mm;
            }

            .tyre-observations textarea {
                min-height: 27mm;
                font-size: 10px;
                line-height: 1.2;
                padding: 0;
                color: #000;
                background: #fff;
                border: 0;
                resize: none;
            }

            .select2-container {
                display: none !important;
            }

            .tyre-table select {
                display: block !important;
                appearance: none;
                -webkit-appearance: none;
            }

            .tyre-field input,
            .tyre-field select,
            .tyre-table input,
            .tyre-table select,
            .tyre-observations textarea,
            .tyre-position {
                color: #000;
                background: #fff;
            }

            .tyre-sheet__header,
            .tyre-sheet__fields,
            .tyre-layout,
            .tyre-observations,
            .tyre-field,
            .tyre-table th,
            .tyre-table td,
            .tyre-position,
            .tyre-field input,
            .tyre-field select,
            .tyre-table input,
            .tyre-table select,
            .tyre-observations textarea,
            .tyre-table .select2-container,
            .tyre-table .select2-container--bootstrap-5 .select2-selection--single,
            .tyre-print-summary {
                border-color: #000 !important;
            }
        }
    </style>
@endpush

<script type="application/json" id="tyre-movement-script-data">@json($tyreMovementScriptData)</script>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            window.sigaMountPrintCompanyHeader?.('#tyrePrintCompanyHeader');

            let scriptData = {
                flotaLayouts: {},
                defaultLayout: [],
                articulosCubiertas: [],
                cubiertasPorArticulo: {},
                cubiertasEnUsoPorFlotaPosicion: {},
                flotaMedidasPorId: {},
                detallesPorPosicion: {},
                estadosSacada: {},
                destinosSacada: {},
            };

            try {
                scriptData = JSON.parse(document.getElementById('tyre-movement-script-data')?.textContent || '{}');
            } catch (error) {
                scriptData = {
                    flotaLayouts: {},
                    defaultLayout: [],
                    articulosCubiertas: [],
                    cubiertasPorArticulo: {},
                    cubiertasEnUsoPorFlotaPosicion: {},
                    flotaMedidasPorId: {},
                    detallesPorPosicion: {},
                    estadosSacada: {},
                    destinosSacada: {},
                };
            }

            const flotaLayouts = scriptData.flotaLayouts || {};
            const defaultLayout = scriptData.defaultLayout || [];
            const articulosCubiertas = Array.isArray(scriptData.articulosCubiertas) ? scriptData.articulosCubiertas : [];
            const cubiertasPorArticulo = scriptData.cubiertasPorArticulo || {};
            const cubiertasEnUsoPorFlotaPosicion = scriptData.cubiertasEnUsoPorFlotaPosicion || {};
            const flotaMedidasPorId = scriptData.flotaMedidasPorId || {};
            const detallesPorPosicion = scriptData.detallesPorPosicion || {};
            const estadosSacada = scriptData.estadosSacada || {};
            const destinosSacada = scriptData.destinosSacada || {};
            const internoSelect = document.getElementById('interno');
            const operarioSelect = document.getElementById('operario');
            const fechaInput = document.getElementById('fecha');
            const kmInput = document.getElementById('km');
            const tyreDiagram = document.getElementById('tyreDiagram');
            const retiredBody = document.getElementById('tyreRetiredBody');
            const placedBody = document.getElementById('tyrePlacedBody');

            function selectedOptionText(select) {
                return (select?.options?.[select.selectedIndex]?.textContent || '').replace(/\s+/g, ' ').trim();
            }

            function formatDateForPrint(value) {
                if (!value || !/^\d{4}-\d{2}-\d{2}$/.test(value)) {
                    return value || '';
                }

                const [year, month, day] = value.split('-');

                return `${day}/${month}/${year}`;
            }

            function setPrintText(id, value) {
                const element = document.getElementById(id);

                if (element) {
                    element.textContent = value || '';
                }
            }

            function syncPrintHeaderValues() {
                setPrintText('fecha_print', formatDateForPrint(fechaInput?.value || ''));
                setPrintText('interno_print', selectedOptionText(internoSelect));
                setPrintText('km_print', kmInput?.value || '');
                setPrintText('operario_print', selectedOptionText(operarioSelect));
            }

            function escapeHtml(value) {
                return String(value ?? '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            function flattenPositions(layout) {
                return (layout || []).flatMap(eje => {
                    const enrich = (pos) => ({
                        ...pos,
                        tipo_eje: eje.tipo_eje || null,
                        numero_eje: pos?.numero_eje ?? eje.numero_eje ?? null,
                    });

                    return [
                        ...(eje.posiciones_izquierda || []).map(enrich),
                        ...(eje.posiciones_derecha || []).map(enrich),
                    ];
                });
            }

            function normalizeText(value) {
                return String(value || '')
                    .toLowerCase()
                    .normalize('NFD')
                    .replace(/[\u0300-\u036f]/g, '')
                    .trim();
            }

            function collectPositionValues() {
                const values = {};

                document.querySelectorAll('[name^="sacada["], [name^="estado_sacada["], [name^="destino_sacada["], [name^="motivo_baja_sacada["], [name^="observacion_sacada["], [name^="cubierta_colocada_id["], [name^="colocada["], [name^="nro_colocada["]').forEach(input => {
                    const match = input.name.match(/^([^\[]+)\[([^\]]+)\]$/);

                    if (!match) {
                        return;
                    }

                    const [, group, position] = match;
                    values[position] = values[position] || {};
                    values[position][group] = input.value;
                });

                return values;
            }

            function renderTyreDiagram(layout) {
                if (!tyreDiagram) {
                    return;
                }

                tyreDiagram.innerHTML = (layout || []).map(eje => {
                    const left = renderTyreSide(eje.posiciones_izquierda || []);
                    const right = renderTyreSide(eje.posiciones_derecha || []);

                    return `
                        <div class="tyre-axle-row">
                            <div class="tyre-side tyre-side-left">${left}</div>
                            <div class="tyre-axle-line"><span>${escapeHtml(eje.tipo_label || 'Eje')} ${escapeHtml(eje.numero_eje || '')}</span></div>
                            <div class="tyre-side tyre-side-right">${right}</div>
                        </div>
                    `;
                }).join('');
            }

            function renderTyreSide(positions) {
                if (!positions.length) {
                    return '<span class="tyre-side-empty">Sin cub.</span>';
                }

                return positions.map(position => `<div class="tyre-position">${escapeHtml(position.etiqueta || position.codigo)}</div>`).join('');
            }

            function renderOptions(options, selected, emptyLabel) {
                const items = [`<option value="">${escapeHtml(emptyLabel)}</option>`];

                Object.entries(options || {}).forEach(([value, label]) => {
                    items.push(`<option value="${escapeHtml(value)}" ${String(selected ?? '') === String(value) ? 'selected' : ''}>${escapeHtml(label)}</option>`);
                });

                return items.join('');
            }

            function renderCubiertaOptions(cubiertas, selected) {
                const items = ['<option value=""></option>'];

                cubiertas.forEach(cubierta => {
                    items.push(`<option value="${escapeHtml(cubierta.id)}" data-numero="${escapeHtml(cubierta.numero || '')}" data-articulo-id="${escapeHtml(cubierta.articulo_id || '')}" ${String(selected ?? '') === String(cubierta.id) ? 'selected' : ''}>${escapeHtml(cubierta.label)}</option>`);
                });

                return items.join('');
            }

            function findCubierta(cubiertas, cubiertaId) {
                return (cubiertas || []).find(cubierta => String(cubierta.id) === String(cubiertaId));
            }

            function articuloIdsByMedida(medida) {
                const needle = normalizeText(medida);

                if (!needle) {
                    return [];
                }

                return articulosCubiertas
                    .filter(articulo => {
                        const haystack = normalizeText(`${articulo.nombre || ''} ${articulo.codigo_producto || ''}`);

                        return haystack.includes(needle);
                    })
                    .map(articulo => Number(articulo.id))
                    .filter(id => Number.isInteger(id));
            }

            function allCubiertasDisponibles() {
                return Object.values(cubiertasPorArticulo || {}).flatMap(cubiertas => cubiertas || []);
            }

            function cubiertasParaPosicion(position) {
                const articuloCubiertaId = position.articulo_cubierta_id || '';

                if (articuloCubiertaId) {
                    const porArticulo = (cubiertasPorArticulo[String(articuloCubiertaId)] || []).slice();

                    return porArticulo.length ? porArticulo : allCubiertasDisponibles();
                }

                const internoId = String(internoSelect?.value || '');
                const medidas = flotaMedidasPorId[internoId] || {};
                const esDelantero = String(position.tipo_eje || '') === 'delantero' || Number(position.numero_eje || 0) === 1;
                const medidaObjetivo = esDelantero ? medidas.delanteras : medidas.traseras;
                const articuloIds = articuloIdsByMedida(medidaObjetivo);
                const porMedida = articuloIds.flatMap(id => cubiertasPorArticulo[String(id)] || []);

                return porMedida.length ? porMedida : allCubiertasDisponibles();
            }

            function renderTyreTables(layout) {
                if (!retiredBody || !placedBody) {
                    return;
                }

                const currentValues = collectPositionValues();
                const positions = flattenPositions(layout);

                retiredBody.innerHTML = positions.map(position => {
                    const code = position.codigo;
                    const label = position.etiqueta || code;
                    const value = currentValues[code] || {};
                    const sacadaValue = value.sacada || '';

                    return `
                        <tr>
                            <td>${escapeHtml(label)}</td>
                            <td><input type="text" name="sacada[${escapeHtml(code)}]" value="${escapeHtml(sacadaValue)}" aria-label="Numero de control de cubierta sacada ${escapeHtml(label)}"></td>
                            <td>
                                <select name="estado_sacada[${escapeHtml(code)}]" aria-label="Estado cubierta sacada ${escapeHtml(label)}">
                                    ${renderOptions(estadosSacada, value.estado_sacada, 'Sin evaluar')}
                                </select>
                            </td>
                            <td>
                                <select name="destino_sacada[${escapeHtml(code)}]" aria-label="Destino cubierta sacada ${escapeHtml(label)}">
                                    ${renderOptions(destinosSacada, value.destino_sacada, 'Sin destino')}
                                </select>
                            </td>
                            <td class="tyre-note-cell">
                                <input type="text" name="motivo_baja_sacada[${escapeHtml(code)}]" value="${escapeHtml(value.motivo_baja_sacada || '')}" placeholder="Motivo baja">
                                <input type="text" name="observacion_sacada[${escapeHtml(code)}]" value="${escapeHtml(value.observacion_sacada || '')}" placeholder="Observacion">
                            </td>
                        </tr>
                    `;
                }).join('');

                placedBody.innerHTML = positions.map(position => {
                    const code = position.codigo;
                    const label = position.etiqueta || code;
                    const value = currentValues[code] || {};
                    const detalleGuardado = detallesPorPosicion[code] || {};
                    const articuloCubiertaId = position.articulo_cubierta_id || '';
                    const cubiertasDisponibles = cubiertasParaPosicion(position);
                    const cubiertasOpciones = cubiertasDisponibles.slice();

                    let selectedCubiertaId = value.cubierta_colocada_id || detalleGuardado.cubierta_colocada_id || '';
                    let selectedCubierta = selectedCubiertaId ? findCubierta(cubiertasDisponibles, selectedCubiertaId) : null;
                    const nroGuardado = value.nro_colocada || detalleGuardado.nro_cubierta_colocada || '';
                    const articuloGuardadoId = value.colocada || detalleGuardado.articulo_colocado_id || articuloCubiertaId;
                    const labelGuardado = detalleGuardado.label_cubierta_colocada || '';

                    if (selectedCubiertaId && !selectedCubierta) {
                        selectedCubierta = {
                            id: selectedCubiertaId,
                            numero: nroGuardado,
                            articulo_id: articuloGuardadoId,
                            label: labelGuardado ? `${labelGuardado} (actual)` : (nroGuardado ? `Nro ${nroGuardado} (actual)` : 'Cubierta seleccionada'),
                        };
                        cubiertasOpciones.unshift(selectedCubierta);
                    }

                    const nroColocada = (value.nro_colocada || '').trim() !== ''
                        ? value.nro_colocada
                        : (selectedCubierta?.numero || nroGuardado);

                    return `
                        <tr>
                            <td>${escapeHtml(label)}</td>
                            <td>
                                <select name="cubierta_colocada_id[${escapeHtml(code)}]" class="form-select js-select2 tyre-select" data-placeholder="Seleccione cubierta" aria-label="Cubierta colocada ${escapeHtml(label)}">
                                    ${renderCubiertaOptions(cubiertasOpciones, selectedCubiertaId)}
                                </select>
                            </td>
                            <td>
                                <input type="text" name="nro_colocada[${escapeHtml(code)}]" value="${escapeHtml(nroColocada)}" placeholder="Nro control" aria-label="Numero de control de cubierta colocada ${escapeHtml(label)}">
                                <input type="hidden" name="colocada[${escapeHtml(code)}]" value="${escapeHtml(value.colocada || selectedCubierta?.articulo_id || articuloGuardadoId)}">
                            </td>
                        </tr>
                    `;
                }).join('');

                window.initSigaSelect2?.(placedBody);

                const syncPlacedRow = (select, optionElement = null) => {
                    const row = select.closest('tr');
                    const selectedOption = optionElement || select.options[select.selectedIndex] || null;
                    const nroInput = row?.querySelector('input[name^="nro_colocada["]');
                    const articuloInput = row?.querySelector('input[name^="colocada["]');

                    if (nroInput) {
                        nroInput.value = selectedOption?.dataset?.numero || '';
                    }

                    if (articuloInput) {
                        articuloInput.value = selectedOption?.dataset?.articuloId || '';
                    }
                };

                placedBody.querySelectorAll('select[name^="cubierta_colocada_id["]').forEach(select => {
                    select.addEventListener('change', function () {
                        syncPlacedRow(select);
                    });

                    if (window.jQuery) {
                        window.jQuery(select)
                            .off('select2:select.tyreMovement select2:clear.tyreMovement')
                            .on('select2:select.tyreMovement', function (event) {
                                syncPlacedRow(select, event.params?.data?.element || null);
                            })
                            .on('select2:clear.tyreMovement', function () {
                                syncPlacedRow(select, null);
                            });
                    }

                    if (select.value) {
                        syncPlacedRow(select);
                    }
                });
            }

            function updateTyreLayoutFromInterno() {
                const layout = flotaLayouts[String(internoSelect?.value || '')] || defaultLayout;

                renderTyreDiagram(layout);
                renderTyreTables(layout);
            }

            internoSelect?.addEventListener('change', updateTyreLayoutFromInterno);
            operarioSelect?.addEventListener('change', syncPrintHeaderValues);
            fechaInput?.addEventListener('change', syncPrintHeaderValues);
            kmInput?.addEventListener('input', syncPrintHeaderValues);

            if (window.jQuery && internoSelect) {
                window.jQuery(internoSelect).on('select2:select select2:clear', function () {
                    updateTyreLayoutFromInterno();
                    syncPrintHeaderValues();
                });
            }

            if (window.jQuery && operarioSelect) {
                window.jQuery(operarioSelect).on('select2:select select2:clear', syncPrintHeaderValues);
            }

            updateTyreLayoutFromInterno();
            syncPrintHeaderValues();
            window.addEventListener('beforeprint', syncPrintHeaderValues);
        });

        document.getElementById('clearTyreMovement')?.addEventListener('click', function () {
            const form = document.getElementById('tyreMovementForm');
            const dataEl = document.getElementById('tyreMovementData');

            form?.reset();

            const fecha = document.getElementById('fecha');
            const defaultFecha = dataEl?.dataset.defaultFecha ?? '';

            if (fecha && defaultFecha) {
                fecha.value = defaultFecha;
            }

            if (window.jQuery) {
                window.jQuery(form).find('select').val(null).trigger('change');
            }
        });

        const dataEl = document.getElementById('tyreMovementData');
        const autoPrint = dataEl?.dataset.autoPrint === '1';
        if (autoPrint) {
            window.addEventListener('load', function () {
                setTimeout(function () {
                    window.print();
                }, 450);
            });
        }
    </script>
@endpush
