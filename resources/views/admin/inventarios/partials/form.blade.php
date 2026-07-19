@php
    $editing = $inventario !== null;
    $fieldId = fn ($field) => $editing ? $field . '-' . $inventario->id : $field;
    $fieldValue = function ($field, $default = null) use ($inventario, $modalId) {
        $current = $inventario?->{$field} ?? $default;

        return session('open_modal') === $modalId ? old($field, $current) : $current;
    };
@endphp

<div class="row">
    <div class="col-12 col-lg-6 mb-3">
        <label for="{{ $fieldId('articulo_id') }}" class="form-label">Articulo (*)</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-box-seam"></i></span>
            <select name="articulo_id" id="{{ $fieldId('articulo_id') }}" class="form-select" required>
                <option value="">Seleccione un articulo</option>
                @foreach ($articulos as $articulo)
                    <option value="{{ $articulo->id }}" @selected((string) $fieldValue('articulo_id') === (string) $articulo->id)>
                        {{ $articulo->nombre }}{{ $articulo->codigo_producto ? ' - ' . $articulo->codigo_producto : '' }}
                    </option>
                @endforeach
            </select>
        </div>
        @if (session('open_modal') === $modalId)
            @error('articulo_id')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        @endif
    </div>

    <div class="col-12 col-lg-6 mb-3">
        <label for="{{ $fieldId('deposito_id') }}" class="form-label">Deposito (*)</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-building"></i></span>
            <select name="deposito_id" id="{{ $fieldId('deposito_id') }}" class="form-select" required>
                <option value="">Seleccione un deposito</option>
                @foreach ($depositos as $deposito)
                    <option value="{{ $deposito->id }}" @selected((string) $fieldValue('deposito_id') === (string) $deposito->id)>
                        {{ $deposito->nombre }}
                    </option>
                @endforeach
            </select>
        </div>
        @if (session('open_modal') === $modalId)
            @error('deposito_id')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        @endif
    </div>

    <div class="col-12 col-md-6 col-xl-3 mb-3">
        <label for="{{ $fieldId('cantidad') }}" class="form-label">Cantidad (*)</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-123"></i></span>
            <input type="number" name="cantidad" id="{{ $fieldId('cantidad') }}" class="form-control"
                value="{{ $fieldValue('cantidad', 0) }}" min="0" step="1" required>
        </div>
        @if (session('open_modal') === $modalId)
            @error('cantidad')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        @endif
    </div>

    <div class="col-12 col-md-6 col-xl-3 mb-3">
        <label for="{{ $fieldId('precio_compra_unidad') }}" class="form-label">Precio unidad</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
            <input type="number" name="precio_compra_unidad" id="{{ $fieldId('precio_compra_unidad') }}"
                class="form-control" value="{{ $fieldValue('precio_compra_unidad') }}" min="0" step="0.01">
        </div>
        @if (session('open_modal') === $modalId)
            @error('precio_compra_unidad')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        @endif
    </div>

    <div class="col-12 col-md-6 mb-3">
        <label for="{{ $fieldId('estado') }}" class="form-label">Estado (*)</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-toggle-on"></i></span>
            <select name="estado" id="{{ $fieldId('estado') }}" class="form-select" required>
                @foreach ($estados as $estado)
                    <option value="{{ $estado }}" @selected($fieldValue('estado', 'compra') === $estado)>
                        {{ ucfirst($estado) }}
                    </option>
                @endforeach
            </select>
        </div>
        @if (session('open_modal') === $modalId)
            @error('estado')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        @endif
    </div>
</div>
