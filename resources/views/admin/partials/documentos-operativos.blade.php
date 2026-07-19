<div class="card">
    <div class="card-header">
        <h4 class="card-title mb-0">Documentos adjuntos</h4>
    </div>
    <div class="card-body">
        @can($editPermission)
            <form method="POST" action="{{ route('admin.documentos-operativos.store') }}" enctype="multipart/form-data" class="row g-3 mb-3">
                @csrf
                <input type="hidden" name="documentable_type" value="{{ $documentableType }}">
                <input type="hidden" name="documentable_id" value="{{ $documentableId }}">
                <div class="col-12 col-md-4">
                    <label class="form-label">Titulo (*)</label>
                    <input type="text" name="titulo" class="form-control" maxlength="160" required>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label">Archivo (*)</label>
                    <input type="file" name="archivo" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx,.xls,.xlsx" required>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label">Descripcion</label>
                    <input type="text" name="descripcion" class="form-control" maxlength="255">
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-paperclip"></i> Adjuntar
                    </button>
                </div>
            </form>
        @endcan

        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead>
                    <tr>
                        <th>Documento</th>
                        <th>Archivo</th>
                        <th>Usuario</th>
                        <th>Fecha</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($documentos as $documento)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $documento->titulo }}</div>
                                @if ($documento->descripcion)
                                    <small class="text-muted">{{ $documento->descripcion }}</small>
                                @endif
                            </td>
                            <td>
                                <div>{{ $documento->original_name }}</div>
                                <small class="text-muted">{{ $documento->sizeLabel() }}</small>
                            </td>
                            <td>{{ $documento->usuario?->name ?: 'Sistema' }}</td>
                            <td>{{ $documento->created_at?->format('d/m/Y H:i') }}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.documentos-operativos.download', $documento) }}" class="btn btn-sm btn-info">
                                    <i class="bi bi-download"></i>
                                </a>
                                @can($editPermission)
                                    <form method="POST" action="{{ route('admin.documentos-operativos.destroy', $documento) }}" class="d-inline" onsubmit="return confirm('Confirma eliminar este documento?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No hay documentos adjuntos.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
