<div id="printReparacionRotulos-{{ $reparacion->id }}" class="d-none">
    <div class="repair-sheet">
        <div class="rotulos-grid">
            <div class="rotulo-card">
                <div class="rotulo-head">
                    <span class="rotulo-title">REMITENTE</span>
                </div>
                <div class="rotulo-item"><strong>Nombre:</strong> {{ $empresaRemite['nombre'] ?? '-' }}</div>
                <div class="rotulo-item"><strong>Domicilio:</strong> {{ $empresaRemite['direccion'] ?? '-' }}</div>
                <div class="rotulo-item"><strong>Telefono:</strong> {{ $empresaRemite['telefono'] ?? '-' }}</div>
                <div class="rotulo-item"><strong>Provincia:</strong> {{ $empresaRemite['provincia_nombre'] ?? '-' }}</div>
                <div class="rotulo-item"><strong>Codigo postal:</strong> {{ $empresaRemite['codigo_postal'] ?? '-' }}</div>
            </div>

            <div class="rotulo-card">
                <div class="rotulo-head">
                    <span class="rotulo-title">DESTINATARIO</span>
                </div>
                <div class="rotulo-item"><strong>Nombre:</strong> {{ $reparacion->proveedor?->nombre ?? '-' }}</div>
                <div class="rotulo-item"><strong>Domicilio:</strong> {{ $reparacion->domicilio ?: '-' }}</div>
                <div class="rotulo-item"><strong>Telefono:</strong> {{ $reparacion->telefono ?: '-' }}</div>
                <div class="rotulo-item"><strong>Provincia:</strong> {{ $reparacion->provincia?->nombre ?? '-' }}</div>
                <div class="rotulo-item"><strong>Codigo postal:</strong> {{ $reparacion->codigo_postal ?: '-' }}</div>
            </div>
        </div>
    </div>
</div>
