@extends('layouts.admin')

@section('auth', true)

@push('styles')
    <link rel="manifest" href="{{ asset('mobile-app/manifest.webmanifest') }}">
    <meta name="theme-color" content="#151521">
    <style>
        #auth {
            min-height: 100vh;
            overflow: hidden;
            background: #151521;
        }

        #auth .row {
            min-height: 100vh;
        }

        #auth .siga-login-side {
            position: relative;
            z-index: 2;
            background: #151521;
        }

        #auth .siga-login-side::after {
            content: "";
            position: absolute;
            top: 0;
            right: -8.5vw;
            z-index: -1;
            width: 17vw;
            height: 100%;
            background: #151521;
            transform: skewX(-12deg);
            transform-origin: top;
        }

        #auth #auth-left {
            display: flex;
            align-items: center;
            min-height: 100vh;
            padding: 3rem 3.25rem;
        }

        #auth .siga-login-form {
            width: 100%;
            max-width: 390px;
        }

        #auth .siga-mobile-install {
            width: 100%;
            max-width: 390px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1.25rem;
            margin-top: 1.15rem;
            padding: 1rem 1.1rem;
            color: #dfe4ff;
            background: rgba(31, 30, 46, .82);
            border: 1px solid #3d3d58;
            border-radius: .45rem;
        }

        #auth .siga-mobile-install-copy {
            flex: 1 1 auto;
            min-width: 0;
        }

        #auth .siga-mobile-install-title {
            display: flex;
            align-items: center;
            gap: .55rem;
            color: #ffffff;
            font-size: .9rem;
            font-weight: 700;
        }

        #auth .siga-mobile-install-title i {
            flex: 0 0 auto;
            color: #8ea2ff;
            font-size: 1.15rem;
            line-height: 1;
        }

        #auth .siga-mobile-install p {
            margin: .3rem 0 0;
            color: #b8bdd8;
            font-size: .78rem;
            line-height: 1.4;
            overflow-wrap: anywhere;
        }

        #auth .siga-mobile-install .btn {
            flex: 0 0 auto;
            min-width: 6.25rem;
            padding: .42rem .9rem;
            border-radius: 999px;
            font-size: .82rem;
            font-weight: 600;
        }

        #auth .siga-mobile-install .btn:disabled {
            opacity: .72;
        }

        #auth .form-group {
            margin-bottom: 1rem !important;
        }

        #auth .form-control.form-control-xl {
            min-height: 50px;
            padding: .7rem 1.15rem .7rem 3.75rem;
            color: #f7f8ff;
            background: #1f1e2e;
            border: 1px solid #3d3d58;
            border-radius: 999px;
            font-size: .98rem;
        }

        #auth .form-control.form-control-xl::placeholder {
            color: #c8cce0;
        }

        #auth .form-control.form-control-xl:focus {
            color: #ffffff;
            background: #1f1e2e;
            border-color: #435ebe;
            box-shadow: 0 0 0 .2rem rgba(67, 94, 190, .22);
        }

        #auth .form-group.has-password-toggle .form-control.form-control-xl {
            padding-right: 3.25rem;
        }

        #auth .form-control-icon {
            left: 1.05rem;
            top: 50%;
            width: 1.35rem;
            height: auto;
            transform: translateY(-50%);
            font-size: 1.05rem;
            text-align: center;
            pointer-events: none;
        }

        #auth .form-control-icon i {
            line-height: 1;
        }

        #auth .password-toggle {
            position: absolute;
            right: .95rem;
            top: 50%;
            z-index: 3;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2rem;
            height: 2rem;
            padding: 0;
            color: #b9bdd6;
            background: transparent;
            border: 0;
            transform: translateY(-50%);
        }

        #auth .password-toggle:hover,
        #auth .password-toggle:focus {
            color: #ffffff;
            outline: none;
        }

        #auth .password-toggle i {
            font-size: 1.1rem;
            line-height: 1;
        }

        #auth .form-check-lg {
            min-height: auto;
            margin-top: 1.15rem;
        }

        #auth .form-check-lg .form-check-input {
            width: 1.05rem;
            height: 1.05rem;
            margin-top: 0;
        }

        #auth .form-check-label {
            font-size: .88rem;
        }

        #auth .btn-lg {
            min-height: 44px;
            padding: .55rem 1.75rem;
            border-radius: 999px;
            font-size: .98rem;
            font-weight: 600;
        }

        #auth .siga-login-actions {
            display: flex;
            align-items: center;
            gap: 2rem;
            margin-top: 2.75rem;
        }

        #auth .siga-login-actions .btn {
            flex: 0 0 150px;
        }

        #auth .siga-login-actions a {
            color: #dfe4ff;
            font-weight: 600;
            text-decoration: none;
        }

        #auth .siga-login-actions a:hover,
        #auth .siga-login-actions a:focus {
            color: #ffffff;
            text-decoration: underline;
        }

        #auth #auth-right {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 4rem;
            overflow: hidden;
            background: #435ebe;
        }

        #auth .siga-login-brand {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            min-height: 100%;
        }

        #auth .siga-login-brand img {
            display: block;
            width: 100%;
            height: 100%;
            max-width: none;
            max-height: none;
            object-fit: cover;
            object-position: center;
            filter: none;
        }

        #auth .siga-login-brand-placeholder {
            display: none;
        }

        @media screen and (max-width: 1399.9px) {
            #auth #auth-left {
                padding: 2.5rem 3rem;
            }

        }

        @media screen and (max-width: 767px) {
            #auth .siga-login-side::after {
                display: none;
            }

            #auth #auth-left {
                min-height: 100vh;
                padding: 2.25rem 1.5rem;
            }

            #auth .siga-login-form {
                max-width: none;
            }

            #auth .siga-mobile-install {
                max-width: none;
                align-items: stretch;
                flex-direction: column;
                gap: .85rem;
            }

            #auth .siga-mobile-install .btn {
                width: 100%;
            }

            #auth .siga-login-actions {
                align-items: stretch;
                flex-direction: column;
                gap: 1rem;
            }

            #auth .siga-login-actions .btn {
                flex-basis: auto;
            }
        }
    </style>
@endpush

@section('content')
@php
    $loginAjuste = \App\Models\Ajuste::query()->first();
    $loginImageUrl = $loginAjuste?->imagen_login
        ? asset('storage/' . $loginAjuste->imagen_login)
        : null;
@endphp

<div id="auth">
    <div class="row h-100">
        <div class="col-lg-5 col-12 siga-login-side">
            <div id="auth-left">
                <div>
                    <form method="POST" action="{{ route('login') }}" class="siga-login-form">
                        @csrf

                        <div class="form-group position-relative has-icon-left mb-4">
                            <input id="email" type="email" class="form-control form-control-xl @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus placeholder="Correo electronico">
                            <div class="form-control-icon">
                                <i class="bi bi-person"></i>
                            </div>
                            @error('email')
                                <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <div class="form-group position-relative has-icon-left has-password-toggle mb-4">
                            <input id="password" type="password" class="form-control form-control-xl @error('password') is-invalid @enderror" name="password" required autocomplete="current-password" placeholder="Contrasena">
                            <div class="form-control-icon">
                                <i class="bi bi-shield-lock"></i>
                            </div>
                            <button type="button" class="password-toggle" id="togglePassword" aria-label="Mostrar contrasena" aria-pressed="false">
                                <i class="bi bi-eye"></i>
                            </button>
                            @error('password')
                                <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <div class="form-check form-check-lg d-flex align-items-end">
                            <input class="form-check-input me-2" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                            <label class="form-check-label text-gray-600" for="remember">Recuerdame</label>
                        </div>

                        <div class="siga-login-actions">
                            <button class="btn btn-primary btn-lg shadow-lg" type="submit">Iniciar sesion</button>
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}">Recuperar contrasena</a>
                            @endif
                        </div>
                    </form>

                    <div class="siga-mobile-install">
                        <div class="siga-mobile-install-copy">
                            <div class="siga-mobile-install-title">
                                <i class="bi bi-phone"></i>
                                <span>App movil</span>
                            </div>
                            <p id="webInstallHelp">Instalar en el telefono</p>
                        </div>
                        <button type="button" id="webInstallAppButton" class="btn btn-outline-primary">
                            Instalar
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-7 d-none d-lg-block">
            <div id="auth-right">
                @if ($loginImageUrl)
                    <div class="siga-login-brand">
                        <img src="{{ $loginImageUrl }}" alt="{{ $loginAjuste->nombre ?? config('app.name', 'SIGA') }}">
                    </div>
                @else
                    <div class="siga-login-brand-placeholder" aria-hidden="true"></div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const passwordInput = document.getElementById('password');
            const toggleButton = document.getElementById('togglePassword');
            const toggleIcon = toggleButton?.querySelector('i');
            const installButton = document.getElementById('webInstallAppButton');
            const installHelp = document.getElementById('webInstallHelp');
            let deferredInstallPrompt = null;

            function isStandaloneMode() {
                return window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
            }

            function updateInstallButton() {
                if (!installButton || !installHelp) {
                    return;
                }

                if (isStandaloneMode()) {
                    installButton.textContent = 'Instalada';
                    installButton.disabled = true;
                    installHelp.textContent = 'La app ya esta instalada.';
                    return;
                }

                installButton.disabled = false;
                installButton.textContent = deferredInstallPrompt ? 'Instalar' : 'Instalar';
                installHelp.textContent = deferredInstallPrompt
                    ? 'Toca instalar para agregar SIGA al telefono.'
                    : 'Si el navegador no abre el instalador, entra a la app movil.';
            }

            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.register('{{ asset('mobile-sw.js') }}').catch(function() {});
            }

            window.addEventListener('beforeinstallprompt', function(event) {
                event.preventDefault();
                deferredInstallPrompt = event;
                updateInstallButton();
            });

            window.addEventListener('appinstalled', function() {
                deferredInstallPrompt = null;
                updateInstallButton();
            });

            installButton?.addEventListener('click', async function() {
                if (!deferredInstallPrompt) {
                    window.location.href = '{{ route('mobile.app') }}';
                    return;
                }

                deferredInstallPrompt.prompt();
                await deferredInstallPrompt.userChoice.catch(function() {});
                deferredInstallPrompt = null;
                updateInstallButton();
            });

            updateInstallButton();

            if (passwordInput && toggleButton && toggleIcon) {
                toggleButton.addEventListener('click', function() {
                    const isHidden = passwordInput.type === 'password';

                    passwordInput.type = isHidden ? 'text' : 'password';
                    toggleButton.setAttribute('aria-label', isHidden ? 'Ocultar contrasena' : 'Mostrar contrasena');
                    toggleButton.setAttribute('aria-pressed', isHidden ? 'true' : 'false');
                    toggleIcon.classList.toggle('bi-eye', !isHidden);
                    toggleIcon.classList.toggle('bi-eye-slash', isHidden);
                    passwordInput.focus();
                });
            }
        });
    </script>
@endpush
