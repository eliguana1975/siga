@extends('layouts.admin')

@section('content')
    <div class="page-heading">
        <div>
            <h3>BI y datos analiticos</h3>
            <p class="text-subtitle text-muted">Datasets JSON para reportes externos y tableros de analisis.</p>
        </div>
    </div>

    <div class="page-content">
        <section class="section">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Datasets disponibles</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Dataset</th>
                                    <th>Descripcion</th>
                                    <th>URL</th>
                                    <th class="text-end">Accion</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($datasets as $dataset)
                                    <tr>
                                        <td class="fw-semibold">{{ $dataset['nombre'] }}</td>
                                        <td>{{ $dataset['descripcion'] }}</td>
                                        <td><code>{{ $dataset['url'] }}</code></td>
                                        <td class="text-end">
                                            <a href="{{ $dataset['url'] }}" class="btn btn-sm btn-info" target="_blank" rel="noopener">
                                                <i class="bi bi-box-arrow-up-right"></i> Abrir JSON
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
