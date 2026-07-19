@extends('layouts.admin')

@section('content')
    <div class="page-heading">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3>Chat interno</h3>
                <p class="text-subtitle text-muted">Mensajes entre usuarios del sistema.</p>
                <div class="siga-chat-retention text-muted">
                    <i class="bi bi-info-circle"></i>
                    Los mensajes se eliminan automaticamente despues de 90 dias.
                </div>
            </div>
            @if ($unreadCount > 0)
                <span class="badge bg-light-primary">{{ $unreadCount }} sin leer</span>
            @endif
        </div>
    </div>

    <div class="page-content">
        <section class="section">
            <div class="row g-3">
                <div class="col-12 col-lg-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <h4 class="card-title mb-0">Conversaciones</h4>
                        </div>
                        <div class="card-body">
                            <div class="list-group mb-4">
                                @forelse ($conversaciones as $item)
                                    @php
                                        $otroUsuario = $item->otro_usuario;
                                    @endphp
                                    <a href="{{ route('admin.chat.index', ['user' => $otroUsuario?->id]) }}"
                                        class="list-group-item list-group-item-action {{ $selectedUser?->id === $otroUsuario?->id ? 'active' : '' }}">
                                        <div class="d-flex justify-content-between gap-2">
                                            <strong>{{ $otroUsuario?->name ?? 'Usuario eliminado' }}</strong>
                                            @if ($item->unread_count > 0)
                                                <span class="badge bg-light-primary">{{ $item->unread_count }}</span>
                                            @endif
                                        </div>
                                        <small class="d-block text-truncate {{ $selectedUser?->id === $otroUsuario?->id ? 'text-white-50' : 'text-muted' }}">
                                            @if ($item->ultimoMensaje)
                                                {{ $item->ultimoMensaje->emisor_id === auth()->id() ? 'Tu: ' : '' }}{{ $item->ultimoMensaje->mensaje }}
                                            @else
                                                Sin mensajes todavia.
                                            @endif
                                        </small>
                                    </a>
                                @empty
                                    <div class="text-muted py-2">Todavia no hay conversaciones.</div>
                                @endforelse
                            </div>

                            <h4 class="card-title">Nuevo mensaje</h4>
                            <div class="list-group">
                                @forelse ($usuarios as $usuario)
                                    <a href="{{ route('admin.chat.index', ['user' => $usuario->id]) }}"
                                        class="list-group-item list-group-item-action {{ $selectedUser?->id === $usuario->id ? 'active' : '' }}">
                                        <i class="bi bi-person-circle me-1"></i>{{ $usuario->name }}
                                        <small class="d-block {{ $selectedUser?->id === $usuario->id ? 'text-white-50' : 'text-muted' }}">{{ $usuario->email }}</small>
                                    </a>
                                @empty
                                    <div class="text-muted py-2">No hay otros usuarios registrados.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-8">
                    <div class="card h-100">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4 class="card-title mb-0">
                                @if ($selectedUser)
                                    {{ $selectedUser->name }}
                                @else
                                    Selecciona un usuario
                                @endif
                            </h4>
                            @if ($selectedUser)
                                <span class="badge bg-light-secondary">{{ $selectedUser->email }}</span>
                            @endif
                        </div>
                        <div class="card-body d-flex flex-column">
                            @if ($selectedUser)
                                <div class="siga-chat-thread mb-3" id="chatThread">
                                    @forelse ($mensajes as $mensaje)
                                        @php
                                            $ownMessage = $mensaje->emisor_id === auth()->id();
                                        @endphp
                                        <div class="siga-chat-row {{ $ownMessage ? 'is-own' : '' }}">
                                            <div class="siga-chat-bubble">
                                                <div class="siga-chat-meta">
                                                    {{ $ownMessage ? 'Tu' : $mensaje->emisor?->name }}
                                                    <span>{{ $mensaje->created_at?->format('d/m/Y H:i') }}</span>
                                                </div>
                                                <div>{{ $mensaje->mensaje }}</div>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="text-center text-muted py-5">No hay mensajes en esta conversacion.</div>
                                    @endforelse
                                </div>

                                <form method="POST" action="{{ route('admin.chat.store') }}">
                                    @csrf
                                    <input type="hidden" name="receptor_id" value="{{ $selectedUser->id }}">
                                    <label for="mensaje" class="form-label">Mensaje (*)</label>
                                    <div class="input-group">
                                        <textarea name="mensaje" id="mensaje" class="form-control" rows="2" maxlength="2000" required>{{ old('mensaje') }}</textarea>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-send"></i> Enviar
                                        </button>
                                    </div>
                                    @error('mensaje')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </form>
                            @else
                                <div class="text-center text-muted py-5">
                                    Elige un usuario para iniciar una conversacion.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('styles')
    <style>
        .siga-chat-retention {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            margin-top: .25rem;
            font-size: .88rem;
            line-height: 1.3;
        }

        .siga-chat-retention i {
            color: #6c8cff;
            line-height: 1;
        }

        .siga-chat-thread {
            display: flex;
            flex-direction: column;
            gap: .7rem;
            min-height: 420px;
            max-height: 58vh;
            overflow-y: auto;
            padding: .35rem .25rem;
        }

        .siga-chat-row {
            display: flex;
            justify-content: flex-start;
        }

        .siga-chat-row.is-own {
            justify-content: flex-end;
        }

        .siga-chat-bubble {
            max-width: min(78%, 680px);
            padding: .75rem .9rem;
            border-radius: .5rem;
            background-color: var(--bs-tertiary-bg);
            border: 1px solid var(--bs-border-color);
            overflow-wrap: anywhere;
        }

        .siga-chat-row.is-own .siga-chat-bubble {
            color: #fff;
            background-color: #435ebe;
            border-color: #435ebe;
        }

        .siga-chat-meta {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: .35rem;
            font-size: .78rem;
            font-weight: 700;
            opacity: .85;
        }

        @media screen and (max-width: 767px) {
            .siga-chat-bubble {
                max-width: 92%;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const thread = document.getElementById('chatThread');

            if (thread) {
                thread.scrollTop = thread.scrollHeight;
            }
        });
    </script>
@endpush
