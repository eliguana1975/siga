@php
    $flota = $flota ?? null;
    $photoFallback = asset('assets/static/images/samples/1.png');
    $photoSrc = fn ($field) => $flota && $flota->{$field} ? asset('storage/' . $flota->{$field}) : $photoFallback;
    $cubiertaEjesActuales = old('cubierta_ejes');

    if ($cubiertaEjesActuales === null) {
        $ejes = $flota?->cubiertaEjes;
        $cubiertaEjesActuales = $ejes && $ejes->isNotEmpty()
            ? $ejes->map(fn ($eje) => [
                'numero_eje' => $eje->numero_eje,
                'tipo_eje' => $eje->tipo_eje,
                'articulo_cubierta_id' => $eje->articulo_cubierta_id,
                'cubiertas_izquierda' => $eje->cubiertas_izquierda,
                'cubiertas_derecha' => $eje->cubiertas_derecha,
            ])->values()->all()
            : [
                ['numero_eje' => 1, 'tipo_eje' => 'delantero', 'articulo_cubierta_id' => null, 'cubiertas_izquierda' => 1, 'cubiertas_derecha' => 1],
                ['numero_eje' => 2, 'tipo_eje' => 'trasero', 'articulo_cubierta_id' => null, 'cubiertas_izquierda' => 2, 'cubiertas_derecha' => 2],
            ];
    }
@endphp

@if ($errors->any())
    <div class="col-12">
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

<div class="col-md-2 mb-3">
    <label for="nro_interno" class="form-label">Nro interno</label>
    <input type="text" name="nro_interno" id="nro_interno" class="form-control"
        value="{{ old('nro_interno', $flota?->nro_interno) }}" maxlength="50" required>
</div>

<div class="col-md-2 mb-3">
    <label for="dominio" class="form-label">Dominio</label>
    <input type="text" name="dominio" id="dominio" class="form-control"
        value="{{ old('dominio', $flota?->dominio) }}" maxlength="20" required>
</div>

<div class="col-md-2 mb-3">
    <label for="anio_fabricacion" class="form-label">Anio fabricacion</label>
    <input type="number" name="anio_fabricacion" id="anio_fabricacion" class="form-control"
        value="{{ old('anio_fabricacion', $flota?->anio_fabricacion) }}">
</div>

<div class="col-md-2 mb-3">
    <label for="estado" class="form-label">Estado</label>
    <select name="estado" id="estado" class="form-select" required>
        <option value="activo" @selected(old('estado', $flota?->estado ?? 'activo') === 'activo')>Activo</option>
        <option value="baja" @selected(old('estado', $flota?->estado) === 'baja')>Baja</option>
        <option value="mantenimiento" @selected(old('estado', $flota?->estado) === 'mantenimiento')>Mantenimiento</option>
    </select>
</div>

<div class="col-md-2 mb-3">
    <label for="tipo_medidor_servicio" class="form-label">Medidor service</label>
    <select name="tipo_medidor_servicio" id="tipo_medidor_servicio" class="form-select" required>
        @foreach ($tiposMedidorServicio as $value => $label)
            <option value="{{ $value }}" @selected(old('tipo_medidor_servicio', $flota?->tipo_medidor_servicio ?? 'km') === $value)>
                {{ $label }}
            </option>
        @endforeach
    </select>
</div>

<div class="col-md-2 mb-3">
    <label for="horometro_actual" class="form-label">Horometro actual</label>
    <input type="number" name="horometro_actual" id="horometro_actual" class="form-control"
        value="{{ old('horometro_actual', $flota?->horometro_actual ?? 0) }}" min="0" step="1">
</div>

<div class="col-md-4 mb-3">
    <label for="cod_titular_id" class="form-label">Titular</label>
    <select name="cod_titular_id" id="cod_titular_id" class="form-select" required>
        <option value="">Seleccione titular</option>
        @foreach ($titulares as $titular)
            <option value="{{ $titular->id }}" @selected(old('cod_titular_id', $flota?->cod_titular_id) == $titular->id)>
                {{ $titular->nombre }}
            </option>
        @endforeach
    </select>
</div>

<div class="col-md-3 mb-3">
    <label for="marca_vehiculo_id" class="form-label">Marca de vehiculo</label>
    <select name="marca_vehiculo_id" id="marca_vehiculo_id" class="form-select" required>
        <option value="">Seleccione marca</option>
        @foreach ($marcasVehiculos as $marcaVehiculo)
            <option value="{{ $marcaVehiculo->id }}" @selected(old('marca_vehiculo_id', $flota?->marca_vehiculo_id) == $marcaVehiculo->id)>
                {{ $marcaVehiculo->nombre }}
            </option>
        @endforeach
    </select>
</div>

<div class="col-md-3 mb-3">
    <label for="cod_tipo_vehiculo_id" class="form-label">Tipo de vehiculo</label>
    <select name="cod_tipo_vehiculo_id" id="cod_tipo_vehiculo_id" class="form-select" required>
        <option value="">Seleccione tipo</option>
        @foreach ($tipoVehiculos as $tipoVehiculo)
            <option value="{{ $tipoVehiculo->id }}" @selected(old('cod_tipo_vehiculo_id', $flota?->cod_tipo_vehiculo_id) == $tipoVehiculo->id)>
                {{ $tipoVehiculo->nombre }}
            </option>
        @endforeach
    </select>
</div>

<div class="col-md-3 mb-3">
    <label for="cod_cia_seguro_id" class="form-label">Compania de seguro</label>
    <select name="cod_cia_seguro_id" id="cod_cia_seguro_id" class="form-select" required>
        <option value="">Seleccione compania</option>
        @foreach ($ciasSeguros as $cia)
            <option value="{{ $cia->id }}" @selected(old('cod_cia_seguro_id', $flota?->cod_cia_seguro_id) == $cia->id)>
                {{ $cia->nombre }}
            </option>
        @endforeach
    </select>
</div>

<div class="col-md-3 mb-3">
    <label for="nro_poliza" class="form-label">Nro poliza</label>
    <input type="text" name="nro_poliza" id="nro_poliza" class="form-control"
        value="{{ old('nro_poliza', $flota?->nro_poliza) }}" maxlength="100">
</div>

<div class="col-md-4 mb-3">
    <label for="estado_seguro" class="form-label">Estado seguro</label>
    <select name="estado_seguro" id="estado_seguro" class="form-select" required>
        <option value="Activo" @selected(old('estado_seguro', $flota?->estado_seguro ?? 'Activo') === 'Activo')>Activo</option>
        <option value="Baja" @selected(old('estado_seguro', $flota?->estado_seguro) === 'Baja')>Baja</option>
    </select>
</div>

<div class="col-md-4 mb-3">
    <label for="cod_marca_carroceria_id" class="form-label">Marca carroceria</label>
    <select name="cod_marca_carroceria_id" id="cod_marca_carroceria_id" class="form-select" required>
        <option value="">Seleccione marca</option>
        @foreach ($marcaCarrocerias as $marca)
            <option value="{{ $marca->id }}" @selected(old('cod_marca_carroceria_id', $flota?->cod_marca_carroceria_id) == $marca->id)>
                {{ $marca->nombre }}
            </option>
        @endforeach
    </select>
</div>

<div class="col-md-4 mb-3">
    <label for="cantidad_pasajeros" class="form-label">Cantidad de pasajeros</label>
    <input type="number" name="cantidad_pasajeros" id="cantidad_pasajeros" class="form-control"
        value="{{ old('cantidad_pasajeros', $flota?->cantidad_pasajeros) }}">
</div>

<div class="col-md-4 mb-3">
    <label for="nro_motor" class="form-label">Nro motor</label>
    <input type="text" name="nro_motor" id="nro_motor" class="form-control"
        value="{{ old('nro_motor', $flota?->nro_motor) }}" maxlength="50" required>
</div>

<div class="col-md-4 mb-3">
    <label for="nro_chasis" class="form-label">Nro chasis</label>
    <input type="text" name="nro_chasis" id="nro_chasis" class="form-control"
        value="{{ old('nro_chasis', $flota?->nro_chasis) }}" maxlength="50" required>
</div>

<div class="col-md-4 mb-3">
    <label for="tipo_motor_id" class="form-label">Tipo motor</label>
    <select name="tipo_motor_id" id="tipo_motor_id" class="form-select" required>
        <option value="">Seleccione tipo</option>
        @foreach ($tipoMotores as $tipoMotor)
            <option value="{{ $tipoMotor->id }}" @selected(old('tipo_motor_id', $flota?->tipo_motor_id) == $tipoMotor->id)>
                {{ $tipoMotor->nombre }}
            </option>
        @endforeach
    </select>
</div>

<div class="col-md-4 mb-3">
    <label for="modelo_motor_id" class="form-label">Modelo motor</label>
    <select name="modelo_motor_id" id="modelo_motor_id" class="form-select" required>
        <option value="">Seleccione modelo</option>
        @foreach ($modeloMotores as $modeloMotor)
            <option value="{{ $modeloMotor->id }}" @selected(old('modelo_motor_id', $flota?->modelo_motor_id) == $modeloMotor->id)>
                {{ $modeloMotor->nombre }}
            </option>
        @endforeach
    </select>
</div>

<div class="col-md-4 mb-3">
    <label for="modelo_caja_id" class="form-label">Modelo caja</label>
    <select name="modelo_caja_id" id="modelo_caja_id" class="form-select" required>
        <option value="">Seleccione modelo</option>
        @foreach ($modeloCajas as $modeloCaja)
            <option value="{{ $modeloCaja->id }}" @selected(old('modelo_caja_id', $flota?->modelo_caja_id) == $modeloCaja->id)>
                {{ $modeloCaja->nombre }}
            </option>
        @endforeach
    </select>
</div>

<div class="col-md-4 mb-3">
    <label for="tipo_caja_id" class="form-label">Tipo caja</label>
    <select name="tipo_caja_id" id="tipo_caja_id" class="form-select" required>
        <option value="">Seleccione tipo</option>
        @foreach ($tipoCajas as $tipoCaja)
            <option value="{{ $tipoCaja->id }}" @selected(old('tipo_caja_id', $flota?->tipo_caja_id) == $tipoCaja->id)>
                {{ $tipoCaja->nombre }}
            </option>
        @endforeach
    </select>
</div>

<div class="col-md-4 mb-3">
    <label for="tipo_aceite_motor" class="form-label">Tipo aceite motor</label>
    <input type="text" name="tipo_aceite_motor" id="tipo_aceite_motor" class="form-control"
        value="{{ old('tipo_aceite_motor', $flota?->tipo_aceite_motor) }}" maxlength="50" required>
</div>

<div class="col-md-4 mb-3">
    <label for="tipo_aceite_caja" class="form-label">Tipo aceite caja</label>
    <input type="text" name="tipo_aceite_caja" id="tipo_aceite_caja" class="form-control"
        value="{{ old('tipo_aceite_caja', $flota?->tipo_aceite_caja) }}" maxlength="50" required>
</div>

<div class="col-md-4 mb-3">
    <label for="cant_aceite_motor" class="form-label">Cant. aceite motor</label>
    <input type="number" name="cant_aceite_motor" id="cant_aceite_motor" class="form-control"
        value="{{ old('cant_aceite_motor', $flota?->cant_aceite_motor) }}" required>
</div>

<div class="col-md-4 mb-3">
    <label for="cant_aceite_caja" class="form-label">Cant. aceite caja</label>
    <input type="number" name="cant_aceite_caja" id="cant_aceite_caja" class="form-control"
        value="{{ old('cant_aceite_caja', $flota?->cant_aceite_caja) }}" required>
</div>

<div class="col-md-4 mb-3">
    <label for="med_cub_delanteras" class="form-label">Med. cub delanteras</label>
    <input type="text" name="med_cub_delanteras" id="med_cub_delanteras" class="form-control"
        value="{{ old('med_cub_delanteras', $flota?->med_cub_delanteras) }}" maxlength="50" required>
</div>

<div class="col-md-4 mb-3">
    <label for="med_cub_traseras" class="form-label">Med. cub traseras</label>
    <input type="text" name="med_cub_traseras" id="med_cub_traseras" class="form-control"
        value="{{ old('med_cub_traseras', $flota?->med_cub_traseras) }}" maxlength="50" required>
</div>

<div class="col-12">
    <div class="border rounded p-3 mb-3">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
            <div>
                <h5 class="mb-1">Configuracion de cubiertas</h5>
                <p class="text-muted small mb-0">Defina los ejes y la cantidad de cubiertas por lado para generar el croquis automaticamente.</p>
            </div>
            <button type="button" id="addCubiertaEje" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-plus-circle"></i> Agregar eje
            </button>
        </div>

        <div class="table-responsive">
            <table class="table table-striped align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 120px;">Eje</th>
                        <th>Tipo</th>
                        <th>Medida / articulo</th>
                        <th style="width: 170px;">Cub. izquierda</th>
                        <th style="width: 170px;">Cub. derecha</th>
                        <th style="width: 90px;">Accion</th>
                    </tr>
                </thead>
                <tbody id="cubiertaEjesBody"></tbody>
            </table>
        </div>

        <div id="cubiertaCroquisPreview" class="flota-tyre-preview mt-3" aria-label="Vista previa de configuracion de cubiertas"></div>
    </div>
</div>

<div class="col-12 mb-3">
    <label for="observaciones" class="form-label">Observaciones</label>
    <textarea name="observaciones" id="observaciones" class="form-control" rows="3">{{ old('observaciones', $flota?->observaciones) }}</textarea>
</div>

<div class="col-12">
    <h5 class="mb-3">Fotos de flota</h5>
</div>

@foreach ([
    'foto_flota' => ['label' => 'Foto flota 1', 'preview' => 'fotoPreview1'],
    'foto_flota_2' => ['label' => 'Foto flota 2', 'preview' => 'fotoPreview2'],
    'foto_flota_3' => ['label' => 'Foto flota 3', 'preview' => 'fotoPreview3'],
    'foto_flota_4' => ['label' => 'Foto flota 4', 'preview' => 'fotoPreview4'],
] as $field => $photo)
    <div class="col-md-3 mb-3">
        <label for="{{ $field }}" class="form-label">{{ $photo['label'] }}</label>
        <div class="mb-2 text-center">
            <img id="{{ $photo['preview'] }}" src="{{ $photoSrc($field) }}" class="img-fluid rounded" style="max-height:160px; width:100%; object-fit:cover;">
        </div>
        <input type="file" id="{{ $field }}" name="{{ $field }}" accept="image/*" class="form-control">
    </div>
@endforeach

@php
    $cubiertaFormData = [
        'tiposEjeCubierta' => $tiposEjeCubierta ?? [],
        'articulosCubiertas' => ($articulosCubiertas ?? collect())->map(fn ($articulo) => [
            'id' => $articulo->id,
            'label' => trim($articulo->nombre . ($articulo->codigo_producto ? ' - ' . $articulo->codigo_producto : '')),
        ])->values(),
        'cubiertaEjes' => array_values($cubiertaEjesActuales),
    ];
@endphp

<script type="application/json" id="flota-cubiertas-form-data">@json($cubiertaFormData)</script>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const dataElement = document.getElementById('flota-cubiertas-form-data');
            let initialData = {
                tiposEjeCubierta: {},
                articulosCubiertas: [],
                cubiertaEjes: [],
            };

            try {
                initialData = JSON.parse(dataElement?.textContent || '{}');
            } catch (error) {
                initialData = {
                    tiposEjeCubierta: {},
                    articulosCubiertas: [],
                    cubiertaEjes: [],
                };
            }

            const tiposEjeCubierta = initialData.tiposEjeCubierta || {};
            const articulosCubiertas = Array.isArray(initialData.articulosCubiertas) ? initialData.articulosCubiertas : [];
            let cubiertaEjes = Array.isArray(initialData.cubiertaEjes) ? initialData.cubiertaEjes : [];
            const cubiertaEjesBody = document.getElementById('cubiertaEjesBody');
            const addCubiertaEje = document.getElementById('addCubiertaEje');
            const cubiertaCroquisPreview = document.getElementById('cubiertaCroquisPreview');

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

            attachPreview('foto_flota', 'fotoPreview1');
            attachPreview('foto_flota_2', 'fotoPreview2');
            attachPreview('foto_flota_3', 'fotoPreview3');
            attachPreview('foto_flota_4', 'fotoPreview4');

            function escapeHtml(value) {
                return String(value ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
            }

            function normalizeEjes() {
                cubiertaEjes = cubiertaEjes
                    .map((eje, index) => ({
                        numero_eje: Number(eje.numero_eje || index + 1),
                        tipo_eje: eje.tipo_eje || (index === 0 ? 'delantero' : 'trasero'),
                        articulo_cubierta_id: eje.articulo_cubierta_id || '',
                        cubiertas_izquierda: Number(eje.cubiertas_izquierda ?? 1),
                        cubiertas_derecha: Number(eje.cubiertas_derecha ?? 1),
                    }))
                    .sort((a, b) => a.numero_eje - b.numero_eje);
            }

            function renderCubiertaEjes() {
                if (!cubiertaEjesBody || !cubiertaCroquisPreview) {
                    return;
                }

                normalizeEjes();
                cubiertaEjesBody.innerHTML = '';

                cubiertaEjes.forEach((eje, index) => {
                    const row = document.createElement('tr');
                    const tipoOptions = Object.entries(tiposEjeCubierta)
                        .map(([value, label]) => `<option value="${escapeHtml(value)}" ${value === eje.tipo_eje ? 'selected' : ''}>${escapeHtml(label)}</option>`)
                        .join('');
                    const articuloOptions = ['<option value="">Sin medida definida</option>'].concat(articulosCubiertas.map(articulo => {
                        const value = String(articulo.id);
                        const selected = value === String(eje.articulo_cubierta_id || '') ? 'selected' : '';
                        return `<option value="${escapeHtml(value)}" ${selected}>${escapeHtml(articulo.label)}</option>`;
                    })).join('');

                    row.innerHTML = `
                        <td>
                            <input type="number" name="cubierta_ejes[${index}][numero_eje]" class="form-control form-control-sm eje-field" data-index="${index}" data-field="numero_eje" min="1" max="20" value="${eje.numero_eje}" required>
                        </td>
                        <td>
                            <select name="cubierta_ejes[${index}][tipo_eje]" class="form-select form-select-sm eje-field" data-index="${index}" data-field="tipo_eje" required>
                                ${tipoOptions}
                            </select>
                        </td>
                        <td>
                            <select name="cubierta_ejes[${index}][articulo_cubierta_id]" class="form-select form-select-sm eje-field" data-index="${index}" data-field="articulo_cubierta_id">
                                ${articuloOptions}
                            </select>
                        </td>
                        <td>
                            <input type="number" name="cubierta_ejes[${index}][cubiertas_izquierda]" class="form-control form-control-sm eje-field" data-index="${index}" data-field="cubiertas_izquierda" min="0" max="4" value="${eje.cubiertas_izquierda}" required>
                        </td>
                        <td>
                            <input type="number" name="cubierta_ejes[${index}][cubiertas_derecha]" class="form-control form-control-sm eje-field" data-index="${index}" data-field="cubiertas_derecha" min="0" max="4" value="${eje.cubiertas_derecha}" required>
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-danger eje-remove" data-index="${index}" ${cubiertaEjes.length <= 1 ? 'disabled' : ''}>
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    `;

                    cubiertaEjesBody.appendChild(row);
                });

                renderCubiertaPreview();
            }

            function renderCubiertaPreview() {
                cubiertaCroquisPreview.innerHTML = cubiertaEjes.map(eje => {
                    const izquierda = Array.from({ length: eje.cubiertas_izquierda }, (_, index) => `<span>${escapeHtml(getCubiertaCodigo(eje.numero_eje, eje.tipo_eje, 'I', index + 1, eje.cubiertas_izquierda))}</span>`).join('');
                    const derecha = Array.from({ length: eje.cubiertas_derecha }, (_, index) => `<span>${escapeHtml(getCubiertaCodigo(eje.numero_eje, eje.tipo_eje, 'D', index + 1, eje.cubiertas_derecha))}</span>`).join('');

                    return `
                        <div class="flota-tyre-preview__axle">
                            <div class="flota-tyre-preview__side">${izquierda || '<em>Sin cub.</em>'}</div>
                            <div class="flota-tyre-preview__center">Eje ${escapeHtml(eje.numero_eje)}</div>
                            <div class="flota-tyre-preview__side">${derecha || '<em>Sin cub.</em>'}</div>
                        </div>
                    `;
                }).join('');
            }

            function getCubiertaCodigo(numeroEje, tipoEje, lado, orden, cantidad) {
                numeroEje = Number(numeroEje);
                cantidad = Number(cantidad);

                if (tipoEje === 'auxiliar') {
                    const suffix = numeroEje > 1 ? String(numeroEje) : '';

                    if (cantidad === 1) {
                        return lado === 'I' ? `AUXI${suffix}` : `AUXD${suffix}`;
                    }

                    return lado === 'I'
                        ? `AUXI${suffix}${orden > 1 ? `-${orden}` : ''}`
                        : `AUXD${suffix}${orden > 1 ? `-${orden}` : ''}`;
                }

                if (tipoEje === 'delantero') {
                    if (cantidad === 1) {
                        return lado === 'I' ? 'DI' : 'DD';
                    }

                    return lado === 'I'
                        ? (orden === 1 ? 'DIE' : (orden === 2 ? 'DII' : `DI${orden}`))
                        : (orden === 1 ? 'DDI' : (orden === 2 ? 'DDE' : `DD${orden}`));
                }

                if (tipoEje === 'trasero' || tipoEje === 'acoplado') {
                    const suffix = numeroEje > 2 ? String(numeroEje) : '';

                    if (cantidad === 1) {
                        return lado === 'I' ? `TIE${suffix}` : `TDI${suffix}`;
                    }

                    return lado === 'I'
                        ? (orden === 1 ? `TIE${suffix}` : (orden === 2 ? `TII${suffix}` : `TI${suffix}-${orden}`))
                        : (orden === 1 ? `TDI${suffix}` : (orden === 2 ? `TDE${suffix}` : `TD${suffix}-${orden}`));
                }

                if (cantidad === 1) {
                    return `E${numeroEje}${lado}`;
                }

                return lado === 'I'
                    ? `E${numeroEje}${orden === 1 ? 'IE' : (orden === 2 ? 'II' : `I${orden}`)}`
                    : `E${numeroEje}${orden === 1 ? 'DI' : (orden === 2 ? 'DE' : `D${orden}`)}`;
            }

            cubiertaEjesBody?.addEventListener('input', function (event) {
                const field = event.target.closest('.eje-field');

                if (!field) {
                    return;
                }

                const index = Number(field.dataset.index);
                const key = field.dataset.field;
                cubiertaEjes[index][key] = ['numero_eje', 'cubiertas_izquierda', 'cubiertas_derecha'].includes(key)
                    ? Number(field.value)
                    : field.value;
                renderCubiertaPreview();
            });

            cubiertaEjesBody?.addEventListener('change', function (event) {
                const field = event.target.closest('.eje-field');

                if (!field) {
                    return;
                }

                const index = Number(field.dataset.index);
                cubiertaEjes[index][field.dataset.field] = ['numero_eje', 'cubiertas_izquierda', 'cubiertas_derecha'].includes(field.dataset.field)
                    ? Number(field.value)
                    : field.value;
                renderCubiertaEjes();
            });

            cubiertaEjesBody?.addEventListener('click', function (event) {
                const button = event.target.closest('.eje-remove');

                if (!button || cubiertaEjes.length <= 1) {
                    return;
                }

                cubiertaEjes.splice(Number(button.dataset.index), 1);
                renderCubiertaEjes();
            });

            addCubiertaEje?.addEventListener('click', function () {
                const nextNumber = Math.max(0, ...cubiertaEjes.map(eje => Number(eje.numero_eje || 0))) + 1;
                cubiertaEjes.push({
                    numero_eje: nextNumber,
                    tipo_eje: nextNumber === 1 ? 'delantero' : 'trasero',
                    cubiertas_izquierda: 1,
                    cubiertas_derecha: 1,
                    articulo_cubierta_id: '',
                });
                renderCubiertaEjes();
            });

            renderCubiertaEjes();
        });
    </script>
@endpush

@push('styles')
    <style>
        .flota-tyre-preview {
            display: grid;
            gap: .75rem;
            padding: 1rem;
            background: rgba(21, 21, 34, .45);
            border: 1px solid rgba(222, 226, 255, .16);
            border-radius: 8px;
        }

        .flota-tyre-preview__axle {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 90px minmax(0, 1fr);
            gap: 1rem;
            align-items: center;
        }

        .flota-tyre-preview__center {
            color: var(--bs-body-color);
            text-align: center;
            font-weight: 700;
            font-size: .85rem;
        }

        .flota-tyre-preview__side {
            display: flex;
            gap: .35rem;
            align-items: center;
        }

        .flota-tyre-preview__side:last-child {
            justify-content: flex-end;
        }

        .flota-tyre-preview__side span {
            display: inline-grid;
            place-items: center;
            min-width: 58px;
            min-height: 38px;
            color: #f7f8ff;
            background: #151522;
            border: 1px solid rgba(222, 226, 255, .55);
            border-radius: 6px;
            font-size: .75rem;
            font-weight: 800;
        }

        .flota-tyre-preview__side em {
            color: var(--bs-secondary-color);
            font-style: normal;
            font-size: .8rem;
        }
    </style>
@endpush
