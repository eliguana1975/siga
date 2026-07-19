<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="dark" class="theme-dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    <link rel="shortcut icon" href="{{ asset('assets/compiled/svg/favicon.svg') }}" type="image/x-icon">
    <link rel="stylesheet" href="{{ asset('assets/compiled/css/app.css') }}">
    <link id="app-dark-css" rel="stylesheet" href="{{ asset('assets/compiled/css/app-dark.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/compiled/css/auth.css') }}">
    <style>
        body {
            color-scheme: dark;
            background-color: #151521;
            color: #dce0f5;
        }

        .navbar,
        .dropdown-menu,
        .card,
        .modal-content {
            background-color: #1f1e2e !important;
            border-color: #3d3d58 !important;
            color: #dce0f5 !important;
        }

        .navbar .navbar-brand,
        .navbar .nav-link,
        .dropdown-item {
            color: #dce0f5 !important;
        }

        .dropdown-item:hover,
        .dropdown-item:focus {
            background-color: #2d2d44 !important;
            color: #fff !important;
        }

        .form-control,
        .form-select,
        .input-group-text {
            background-color: #151521 !important;
            border-color: #35354f !important;
            color: #f5f6ff !important;
        }

        .form-control::placeholder {
            color: #8e94b2 !important;
        }

        .bg-white,
        .bg-light {
            background-color: #1f1e2e !important;
            color: #dce0f5 !important;
        }

        .text-dark,
        .text-black {
            color: #f5f6ff !important;
        }
    </style>

    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body class="theme-dark dark">
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-dark shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/') }}">
                    {{ config('app.name', 'Laravel') }}
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav me-auto">

                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ms-auto">
                        <!-- Authentication Links -->
                        @guest
                            @if (Route::has('login'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                                </li>
                            @endif

                            @if (Route::has('register'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                                </li>
                            @endif
                        @else
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    {{ Auth::user()->name }}
                                </a>

                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        {{ __('Logout') }}
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        <main class="py-4">
            @yield('content')
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @php
        $flashMessages = [
            'success' => session('success'),
            'error' => session('error'),
            'status' => session('status'),
            'resent' => (bool) session('resent'),
        ];
    @endphp
    <script type="application/json" id="app-flash-messages">@json($flashMessages)</script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            let flashMessages = {
                success: null,
                error: null,
                status: null,
                resent: false,
            };

            try {
                flashMessages = JSON.parse(document.getElementById('app-flash-messages')?.textContent || '{}');
            } catch (error) {
                flashMessages = {
                    success: null,
                    error: null,
                    status: null,
                    resent: false,
                };
            }

            const showToast = function(icon, title, text) {
                Swal.fire({
                    icon: icon,
                    title: title,
                    text: text,
                    timer: 5000,
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timerProgressBar: true,
                });
            };

            if (flashMessages.success) {
                showToast('success', '¡Éxito!', flashMessages.success);
            }

            if (flashMessages.error) {
                showToast('error', 'Error', flashMessages.error);
            }

            if (flashMessages.status) {
                showToast('info', 'Información', flashMessages.status);
            }

            if (flashMessages.resent) {
                showToast('success', 'Enviado', 'Se ha enviado un nuevo enlace de verificación a tu correo.');
            }
        });
    </script>
    <script src="{{ asset('assets/static/js/initTheme.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const autoDismissMs = 5000;

            document.querySelectorAll('.alert.alert-dismissible').forEach(function (alertElement) {
                setTimeout(function () {
                    if (!alertElement.isConnected) {
                        return;
                    }

                    if (typeof bootstrap !== 'undefined' && bootstrap.Alert) {
                        bootstrap.Alert.getOrCreateInstance(alertElement).close();
                        return;
                    }

                    alertElement.classList.remove('show');
                    setTimeout(function () {
                        alertElement.remove();
                    }, 150);
                }, autoDismissMs);
            });
        });
    </script>
    @stack('scripts')</body>
</html>
