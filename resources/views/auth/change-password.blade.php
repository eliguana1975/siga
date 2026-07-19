@extends('layouts.admin')

@section('content')
    <div class="page-heading">
        <h3>Cambiar contraseña</h3>
        <p class="text-subtitle text-muted">
            Actualiza tu clave de acceso al sistema.
        </p>
    </div>

    <div class="page-content">
        <section class="section">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Nueva contraseña</h4>
                </div>
                <div class="card-body">
                    @if (session('warning') || auth()->user()?->requiresPasswordChange())
                        <div class="alert alert-warning">
                            {{ session('warning') ?? 'Es tu primer ingreso o tu contraseña fue reiniciada. Debes cambiarla para continuar.' }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('password.change.update') }}">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-12 col-md-6 mb-3">
                                <label for="current_password" class="form-label">Contraseña actual (*)</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-shield-lock"></i></span>
                                    <input type="password" name="current_password" id="current_password"
                                        class="form-control @error('current_password') is-invalid @enderror" required>
                                </div>
                                @error('current_password')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="col-12 col-md-6 mb-3">
                                <label for="password" class="form-label">Nueva contraseña (*)</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                    <input type="password" name="password" id="password"
                                        class="form-control @error('password') is-invalid @enderror"
                                        placeholder="Mínimo 8 caracteres" required>
                                </div>
                                @error('password')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="col-12 col-md-6 mb-3">
                                <label for="password_confirmation" class="form-label">Repetir nueva contraseña (*)</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                    <input type="password" name="password_confirmation" id="password_confirmation"
                                        class="form-control" required>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            @unless (auth()->user()?->requiresPasswordChange())
                                <a href="{{ route('home') }}" class="btn btn-light-secondary">Cancelar</a>
                            @endunless
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Guardar contraseña
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </div>
@endsection
