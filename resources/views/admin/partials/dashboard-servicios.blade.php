<div class="table-responsive">
    <table class="table table-striped align-middle mb-0">
        <thead>
            <tr>
                <th>Interno</th>
                <th>Dominio</th>
                <th>Vehiculo</th>
                <th>Medidor actual</th>
                <th>Sistema</th>
                <th>Servicio</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($serviciosParaRealizar as $servicio)
                <tr>
                    <td class="fw-semibold">{{ $servicio['interno'] }}</td>
                    <td>{{ $servicio['dominio'] }}</td>
                    <td>{{ $servicio['vehiculo'] ?: '-' }}</td>
                    <td>{{ number_format((int) ($servicio['lectura_actual'] ?? 0), 0, ',', '.') }} {{ ($servicio['unidad'] ?? 'km') === 'horas' ? 'hs' : 'km' }}</td>
                    <td>{{ $servicio['sistema'] }}</td>
                    <td>{{ $servicio['servicio'] }}</td>
                    <td><span class="badge bg-light-danger">Realizar</span></td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">No hay internos con servicios vencidos.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
