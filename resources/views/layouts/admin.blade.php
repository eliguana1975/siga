<!DOCTYPE html>
<html lang="en" data-bs-theme="dark" class="theme-dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>{{ config('app.name', 'Sistema Integral de Gestion Automotriz') }}</title>



    <link rel="shortcut icon" href="{{ asset('assets/compiled/svg/favicon.svg') }}" type="image/x-icon">
    <link rel="shortcut icon" href="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACEAAAAiCAYAAADRcLDBAAAEs2lUWHRYTUw6Y29tLmFkb2JlLnhtcAAAAAAAPD94cGFja2V0IGJlZ2luPSLvu78iIGlkPSJXNU0wTXBDZWhpSHpyZVN6TlRjemtjOWQiPz4KPHg6eG1wbWV0YSB4bWxuczp4PSJhZG9iZTpuczptZXRhLyIgeDp4bXB0az0iWE1QIENvcmUgNS41LjAiPgogPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4KICA8cmRmOkRlc2NyaXB0aW9uIHJkZjphYm91dD0iIgogICAgeG1sbnM6ZXhpZj0iaHR0cDovL25zLmFkb2JlLmNvbS9leGlmLzEuMC8iCiAgICB4bWxuczp0aWZmPSJodHRwOi8vbnMuYWRvYmUuY29tL3RpZmYvMS4wLyIKICAgIHhtbG5zOnBob3Rvc2hvcD0iaHR0cDovL25zLmFkb2JlLmNvbS9waG90b3Nob3AvMS4wLyIKICAgIHhtbG5zOnhtcD0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wLyIKICAgIHhtbG5zOnhtcE1NPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvbW0vIgogICAgeG1sbnM6c3RFdnQ9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZUV2ZW50IyIKICAgZXhpZjpQaXhlbFhEaW1lbnNpb249IjMzIgogICBleGlmOlBpeGVsWURpbWVuc2lvbj0iMzQiCiAgIGV4aWY6Q29sb3JTcGFjZT0iMSIKICAgdGlmZjpJbWFnZVdpZHRoPSIzMyIKICAgdGlmZjpJbWFnZUxlbmd0aD0iMzQiCiAgIHRpZmY6UmVzb2x1dGlvblVuaXQ9IjIiCiAgIHRpZmY6WFJlc29sdXRpb249Ijk2LjAiCiAgIHRpZmY6WVJlc29sdXRpb249Ijk2LjAiCiAgIHBob3Rvc2hvcDpDb2xvck1vZGU9IjMiCiAgIHBob3Rvc2hvcDpJQ0NQcm9maWxlPSJzUkdCIElFQzYxOTY2LTIuMSIKICAgeG1wOk1vZGlmeURhdGU9IjIwMjItMDMtMzFUMTA6NTA6MjMrMDI6MDAiCiAgIHhtcDpNZXRhZGF0YURhdGU9IjIwMjItMDMtMzFUMTA6NTA6MjMrMDI6MDAiPgogICA8eG1wTU06SGlzdG9yeT4KICAgIDxyZGY6U2VxPgogICAgIDxyZGY6bGkKICAgICAgc3RFdnQ6YWN0aW9uPSJwcm9kdWNlZCIKICAgICAgc3RFdnQ6c29mdHdhcmVBZ2VudD0iQWZmaW5pdHkgRGVzaWduZXIgMS4xMC4xIgogICAgICBzdEV2dDp3aGVuPSIyMDIyLTAzLTMxVDEwOjUwOjIzKzAyOjAwIi8+CiAgICA8L3JkZjpTZXE+CiAgIDwveG1wTU06SGlzdG9yeT4KICA8L3JkZjpEZXNjcmlwdGlvbj4KIDwvcmRmOlJERj4KPC94OnhtcG1ldGE+Cjw/eHBhY2tldCBlbmQ9InIiPz5V57uAAAABgmlDQ1BzUkdCIElFQzYxOTY2LTIuMQAAKJF1kc8rRFEUxz9maORHo1hYKC9hISNGTWwsRn4VFmOUX5uZZ36oeTOv954kW2WrKLHxa8FfwFZZK0WkZClrYoOe87ypmWTO7dzzud97z+nec8ETzaiaWd4NWtYyIiNhZWZ2TvE946WZSjqoj6mmPjE1HKWkfdxR5sSbgFOr9Ll/rXoxYapQVik8oOqGJTwqPL5i6Q5vCzeo6dii8KlwpyEXFL519LjLLw6nXP5y2IhGBsFTJ6ykijhexGra0ITl5bRqmWU1fx/nJTWJ7PSUxBbxJkwijBBGYYwhBgnRQ7/MIQIE6ZIVJfK7f/MnyUmuKrPOKgZLpEhj0SnqslRPSEyKnpCRYdXp/9++msneoFu9JgwVT7b91ga+LfjetO3PQ9v+PgLvI1xkC/m5A+h7F32zoLXug38dzi4LWnwHzjeg8UGPGbFfySvuSSbh9QRqZ6H+Gqrm3Z7l9zm+h+iafNUV7O5Bu5z3L/wAdthn7QIme0YAAAAJcEhZcwAADsQAAA7EAZUrDhsAAAJTSURBVFiF7Zi9axRBGIefEw2IdxFBRQsLWUTBaywSK4ubdSGVIY1Y6HZql8ZKCGIqwX/AYLmCgVQKfiDn7jZeEQMWfsSAHAiKqPiB5mIgELWYOW5vzc3O7niHhT/YZvY37/swM/vOzJbIqVq9uQ04CYwCI8AhYAlYAB4Dc7HnrOSJWcoJcBS4ARzQ2F4BZ2LPmTeNuykHwEWgkQGAet9QfiMZjUSt3hwD7psGTWgs9pwH1hC1enMYeA7sKwDxBqjGnvNdZzKZjqmCAKh+U1kmEwi3IEBbIsugnY5avTkEtIAtFhBrQCX2nLVehqyRqFoCAAwBh3WGLAhbgCRIYYinwLolwLqKUwwi9pxV4KUlxKKKUwxC6ZElRCPLYAJxGfhSEOCz6m8HEXvOB2CyIMSk6m8HoXQTmMkJcA2YNTHm3congOvATo3tE3A29pxbpnFzQSiQPcB55IFmFNgFfEQeahaAGZMpsIJIAZWAHcDX2HN+2cT6r39GxmvC9aPNwH5gO1BOPFuBVWAZue0vA9+A12EgjPadnhCuH1WAE8ivYAQ4ohKaagV4gvxi5oG7YSA2vApsCOH60WngKrA3R9IsvQUuhIGY00K4flQG7gHH/mLytB4C42EgfrQb0mV7us8AAMeBS8mGNMR4nwHamtBB7B4QRNdaS0M8GxDEog7iyoAguvJ0QYSBuAOcAt71Kfl7wA8DcTvZ2KtOlJEr+ByyQtqqhTyHTIeB+ONeqi3brh+VgIN0fohUgWGggizZFTplu12yW8iy/YLOGWMpDMTPXnl+Az9vj2HERYqPAAAAAElFTkSuQmCC" type="image/png">



    <link rel="stylesheet" href="{{ asset('assets/compiled/css/app.css') }}">
    <script>
        window.SigaClearBrowserState = function() {
            try {
                [window.localStorage, window.sessionStorage].forEach(function(storage) {
                    if (!storage) {
                        return;
                    }

                    Object.keys(storage).forEach(function(key) {
                        if (key.indexOf('siga.') === 0 || key.indexOf('siga-') === 0 || key.indexOf('Siga') === 0) {
                            storage.removeItem(key);
                        }
                    });
                });
            } catch (error) {
                // El navegador puede bloquear storage en modo privado.
            }
        };

        (function() {
            document.documentElement.setAttribute('data-bs-theme', 'dark');
            document.documentElement.classList.add('theme-dark');
            document.documentElement.classList.remove('theme-light');
            localStorage.removeItem('theme');
        })();

        window.SigaSessionUserId = @json(auth()->id());
        window.SigaSessionCheckUrl = @json(route('session.user'));
        window.SigaCheckingSessionUser = false;
        window.SigaCheckSessionUser = function() {
            if (!window.SigaSessionCheckUrl || window.SigaCheckingSessionUser) {
                return;
            }

            window.SigaCheckingSessionUser = true;

            fetch(window.SigaSessionCheckUrl + '?_=' + Date.now(), {
                cache: 'no-store',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(function(response) {
                    return response.ok ? response.json() : null;
                })
                .then(function(payload) {
                    if (!payload) {
                        return;
                    }

                    const browserUserId = window.SigaSessionUserId === null ? null : Number(window.SigaSessionUserId);
                    const serverUserId = payload.user_id === null || payload.user_id === undefined ? null : Number(payload.user_id);

                    if (browserUserId !== serverUserId) {
                        window.SigaClearBrowserState?.();
                        window.location.replace(window.location.href.split('#')[0]);
                    }
                })
                .catch(function() {
                    // Si la consulta falla, dejamos la vista actual sin interrumpir al usuario.
                })
                .finally(function() {
                    window.SigaCheckingSessionUser = false;
                });
        };

        document.addEventListener('DOMContentLoaded', window.SigaCheckSessionUser);
        window.addEventListener('pageshow', function() {
            window.SigaCheckSessionUser();
        });
        window.addEventListener('focus', window.SigaCheckSessionUser);
        window.setInterval(window.SigaCheckSessionUser, 3000);
        document.addEventListener('visibilitychange', function() {
            if (!document.hidden) {
                window.SigaCheckSessionUser();
            }
        });
    </script>
    <link id="app-dark-css" rel="stylesheet" href="{{ asset('assets/compiled/css/app-dark.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/compiled/css/iconly.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/compiled/css/auth.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
    @stack('styles')
    <style>
        @media screen and (min-width: 1200px) {
            body:not(.auth-theme) {
                overflow-x: hidden;
            }

            #main {
                box-sizing: border-box;
                max-width: calc(100vw - 300px);
                transition: margin-left .7s cubic-bezier(.22, 1, .36, 1);
            }
        }

        .siga-main-toolbar {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            gap: .75rem;
            position: sticky;
            top: 0;
            z-index: 20;
            margin: -2rem -2rem 1.5rem;
            padding: .9rem 2rem;
            min-height: 74px;
            background-color: var(--bs-body-bg);
            border-bottom: 1px solid var(--bs-border-color);
            box-shadow: 0 .125rem .35rem rgba(0, 0, 0, .05);
        }

        .siga-toolbar-breadcrumb {
            display: flex;
            align-items: center;
            flex: 1 1 auto;
            min-height: 42px;
            min-width: 0;
            overflow: hidden;
        }

        .siga-toolbar-breadcrumb .breadcrumb {
            display: flex;
            align-items: center;
            flex-wrap: nowrap;
            min-height: 42px;
            min-width: 0;
            margin: 0;
            font-size: .92rem;
            line-height: 1;
            white-space: nowrap;
        }

        .siga-toolbar-breadcrumb .breadcrumb-item {
            display: inline-flex;
            align-items: center;
            max-width: 220px;
            min-width: 0;
        }

        .siga-toolbar-breadcrumb .breadcrumb-item a,
        .siga-toolbar-breadcrumb .breadcrumb-item.active {
            overflow: hidden;
            color: var(--bs-body-color);
            text-overflow: ellipsis;
        }

        .siga-toolbar-breadcrumb .breadcrumb-item a {
            text-decoration: none;
        }

        .siga-toolbar-breadcrumb .breadcrumb-item a:hover,
        .siga-toolbar-breadcrumb .breadcrumb-item a:focus {
            color: #435ebe;
        }

        .siga-mobile-menu-btn {
            display: none;
            align-items: center;
            justify-content: center;
            flex: 0 0 auto;
            width: 42px;
            height: 42px;
            padding: 0;
            color: #fff;
            background-color: #435ebe;
            border: 1px solid #435ebe;
            border-radius: .35rem;
        }

        .siga-mobile-menu-btn:hover,
        .siga-mobile-menu-btn:focus {
            color: #fff;
            background-color: #364b98;
            border-color: #364b98;
        }

        .siga-mobile-menu-btn i {
            font-size: 1.35rem;
            line-height: 1;
        }

        .siga-toolbar-actions {
            display: flex;
            align-items: center;
            gap: .75rem;
            margin-left: auto;
            min-width: 0;
        }

        .siga-user-badge {
            display: inline-flex;
            align-items: center;
            gap: .55rem;
            min-height: 42px;
            max-width: min(100%, 340px);
            padding: .45rem .75rem;
            color: var(--bs-body-color);
            background-color: transparent;
            border: 0;
            border-radius: .35rem;
            box-shadow: none;
        }

        .siga-user-badge i {
            color: #435ebe;
            font-size: 1.15rem;
            line-height: 1;
        }

        .siga-user-badge span {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .siga-user-menu {
            position: relative;
        }

        .siga-user-menu-toggle {
            display: inline-flex;
            align-items: center;
            gap: .55rem;
            min-height: 42px;
            max-width: 240px;
            padding: .45rem .75rem;
            color: var(--bs-body-color);
            background-color: transparent;
            border: 1px solid transparent;
            border-radius: .35rem;
            box-shadow: none;
        }

        .siga-user-menu-toggle:hover,
        .siga-user-menu-toggle:focus,
        .siga-user-menu-toggle.show {
            color: #fff;
            background-color: rgba(67, 94, 190, .16);
            border-color: rgba(67, 94, 190, .45);
        }

        .siga-user-menu-toggle i {
            color: #435ebe;
            font-size: 1.15rem;
            line-height: 1;
        }

        .siga-user-menu-toggle span {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .siga-user-menu .dropdown-menu {
            min-width: 220px;
            padding: .35rem;
        }

        .siga-user-menu .dropdown-header {
            max-width: 260px;
            overflow: hidden;
            color: var(--bs-body-color);
            font-weight: 700;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .siga-user-menu .dropdown-item {
            display: flex;
            align-items: center;
            gap: .55rem;
            width: 100%;
            border: 0;
            border-radius: .3rem;
            background: transparent;
            text-align: left;
        }

        .siga-user-menu .dropdown-item i {
            color: #6f8cff;
        }

        .siga-chat-toolbar-link {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 42px;
            min-height: 42px;
            color: var(--bs-body-color);
            background-color: transparent;
            border-radius: .35rem;
        }

        .siga-chat-toolbar-link i {
            color: #435ebe;
            font-size: 1.15rem;
            line-height: 1;
        }

        .siga-chat-toolbar-link .badge {
            position: absolute;
            top: .2rem;
            right: .15rem;
            min-width: 1.25rem;
            padding: .18rem .35rem;
        }

        .siga-notification-toolbar-link {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 42px;
            min-height: 42px;
            color: var(--bs-body-color);
            background-color: transparent;
            border-radius: .35rem;
        }

        .siga-notification-toolbar-link i {
            color: #f59e0b;
            font-size: 1.15rem;
            line-height: 1;
        }

        .siga-notification-toolbar-link .badge {
            position: absolute;
            top: .2rem;
            right: .15rem;
        }

        .siga-global-search {
            position: relative;
            flex: 0 1 360px;
            min-width: 220px;
        }

        .siga-global-search .input-group-text,
        .siga-global-search .form-control,
        .siga-global-search .btn {
            min-height: 38px;
        }

        .siga-global-search-results {
            position: absolute;
            top: calc(100% + .35rem);
            right: 0;
            left: 0;
            z-index: 1050;
            display: none;
            max-height: 420px;
            overflow: auto;
            background-color: var(--bs-body-bg);
            border: 1px solid var(--bs-border-color);
            border-radius: .35rem;
            box-shadow: 0 .5rem 1.25rem rgba(0, 0, 0, .25);
        }

        .siga-global-search-results.show {
            display: block;
        }

        .siga-global-search-results a {
            display: flex;
            gap: .65rem;
            padding: .65rem .75rem;
            color: var(--bs-body-color);
            text-decoration: none;
            border-bottom: 1px solid var(--bs-border-color);
        }

        .siga-global-search-results a:hover,
        .siga-global-search-results a:focus {
            background-color: rgba(67, 94, 190, .12);
        }

        .siga-logout-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .45rem;
            min-height: 42px;
            padding: .45rem .7rem;
            border-radius: .35rem;
            white-space: nowrap;
            border-color: #435ebe;
            background-color: #435ebe;
            color: #fff;
        }

        .siga-logout-btn:hover,
        .siga-logout-btn:focus {
            border-color: #364b98;
            background-color: #364b98;
            color: #fff;
        }

        @media screen and (max-width: 1199px) {
            .siga-mobile-menu-btn {
                display: inline-flex;
            }

            #main {
                margin-left: 0 !important;
                max-width: 100vw !important;
            }

            #sidebar {
                z-index: 1050;
            }

            #sidebar .sidebar-wrapper {
                left: -300px;
                width: min(300px, 86vw);
                z-index: 1051;
                transition: left .2s ease-in-out;
            }

            #sidebar.active .sidebar-wrapper {
                left: 0;
            }

            body.siga-sidebar-open {
                overflow: hidden;
            }

            .sidebar-backdrop {
                position: fixed;
                inset: 0;
                z-index: 1040;
                background-color: rgba(0, 0, 0, .45);
            }
        }

        @media screen and (max-width: 575px) {
            .siga-main-toolbar {
                align-items: center;
                flex-direction: row;
                flex-wrap: wrap;
                margin: -1rem -1rem 1rem;
                padding: .75rem 1rem;
            }

            .siga-toolbar-breadcrumb {
                flex: 1 1 0;
                width: auto;
            }

            .siga-toolbar-breadcrumb .breadcrumb-item {
                max-width: 34vw;
            }

            .siga-toolbar-actions {
                flex-wrap: wrap;
                width: 100%;
                gap: .45rem;
            }

            .siga-user-badge {
                flex: 1 1 0;
                min-width: 0;
                max-width: 46vw;
                padding-left: .35rem;
                padding-right: .35rem;
            }

            .siga-user-menu {
                flex: 1 1 auto;
                min-width: 0;
            }

            .siga-user-menu-toggle {
                width: 100%;
                max-width: 100%;
                justify-content: flex-start;
            }

            .siga-logout-btn {
                min-width: 42px;
                padding-left: .55rem;
                padding-right: .55rem;
            }

            .siga-logout-btn span {
                display: none;
            }
        }

        #main .table-responsive {
            max-width: 100%;
            overflow-x: auto;
        }

        #main .table-responsive > .table {
            width: max-content;
            min-width: 100%;
        }

        .siga-auto-input-icon .input-group-text {
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 44px;
        }

        .siga-auto-input-icon .input-group-text i {
            line-height: 1;
        }

        .siga-auto-input-icon .invalid-feedback {
            width: 100%;
        }

        @media screen and (min-width: 1200px) and (max-width: 1440px) {
            :root {
                --siga-sidebar-expanded-width: 240px;
            }

            body:not(.auth-theme) {
                font-size: .92rem;
            }

            #sidebar .sidebar-wrapper {
                width: 240px;
            }

            #main {
                margin-left: 240px !important;
                max-width: calc(100vw - 240px);
                padding: 1.15rem 1.15rem 1.15rem .9rem !important;
            }

            .siga-main-toolbar {
                margin: -1.15rem -1.15rem 1.25rem -.9rem;
                padding: .75rem 1.15rem .75rem .9rem;
            }

            .sidebar-wrapper .sidebar-header {
                padding: 1.1rem 1.25rem .7rem;
            }

            .sidebar-wrapper .menu {
                padding: 0 1rem;
            }

            .sidebar-wrapper .menu .sidebar-title {
                margin: .8rem 0 .55rem;
                font-size: .8rem;
            }

            .sidebar-wrapper .menu .sidebar-link {
                padding: .55rem .85rem;
                font-size: .92rem;
            }

            .sidebar-wrapper .menu .submenu .submenu-link {
                padding: .45rem 1.35rem;
                font-size: .9rem;
            }

            #main .page-heading {
                margin-bottom: .9rem;
            }

            #main .page-heading h3 {
                font-size: 1.35rem;
                margin-bottom: .25rem;
            }

            #main .page-heading .text-subtitle {
                margin-bottom: 0;
            }

            #main .page-content {
                margin-top: .65rem;
            }

            #main .card {
                margin-bottom: 1rem;
            }

            #main .card .card-header {
                padding: 1rem 1.15rem .45rem;
            }

            #main .card .card-body {
                padding: 1rem 1.15rem;
            }

            #main .card .card-body.px-4.py-4 {
                padding: .95rem 1.15rem !important;
            }

            #main .card-title {
                font-size: 1.05rem;
            }

            #main .stats-icon {
                height: 2.55rem;
                width: 2.55rem;
            }

            #main .stats-icon i {
                font-size: 1.25rem;
            }

            #main h4 {
                font-size: 1.15rem;
            }

            .table > :not(caption) > * > * {
                padding: .55rem .65rem;
            }

            .btn {
                padding: .45rem .75rem;
            }

            .btn-sm {
                padding: .25rem .45rem;
            }

            .modal-header,
            .modal-footer {
                padding: .8rem 1rem;
            }

            .modal-body {
                padding: 1rem;
            }
        }

        body:not(.auth-theme) .sidebar-wrapper .menu .sidebar-item.active > .sidebar-link {
            background-color: transparent;
            color: inherit;
            font-weight: inherit;
        }

        body:not(.auth-theme) .sidebar-wrapper .menu .sidebar-item.active > .sidebar-link i,
        body:not(.auth-theme) .sidebar-wrapper .menu .sidebar-item.active > .sidebar-link span {
            color: inherit;
            font-weight: inherit;
        }

        body:not(.auth-theme) .sidebar-wrapper .menu .sidebar-item.active > .sidebar-link:hover,
        body:not(.auth-theme) .sidebar-wrapper .menu .sidebar-link:hover {
            background-color: rgba(255, 255, 255, .055);
        }

        body:not(.auth-theme) .sidebar-wrapper .menu .submenu .submenu-item.active > .submenu-link {
            color: inherit;
            background-color: transparent;
            font-weight: inherit;
        }

        .select2-container {
            width: 100% !important;
        }

        .input-group > .select2-container {
            flex: 1 1 auto;
            width: 1% !important;
            min-width: 0;
        }

        .input-group > .select2-container .select2-selection {
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
        }

        .select2-container--bootstrap-5 .select2-selection {
            min-height: calc(1.5em + .75rem + 2px);
        }

        .select2-container--bootstrap-5 .select2-selection--single {
            align-items: center;
            display: flex;
        }

        .select2-container--bootstrap-5 .select2-selection,
        .select2-container--bootstrap-5 .select2-selection__rendered,
        .select2-container--bootstrap-5 .select2-selection__placeholder,
        .select2-container--bootstrap-5 .select2-dropdown,
        .select2-container--bootstrap-5 .select2-results__option,
        .select2-container--bootstrap-5 .select2-search__field {
            font-size: .78rem !important;
        }

        .select2-container--bootstrap-5 .select2-selection__rendered,
        .select2-container--bootstrap-5 .select2-selection__placeholder {
            align-items: center;
            display: flex;
            min-height: 100%;
            text-align: left;
            width: 100%;
        }

        .select2-container--bootstrap-5 .select2-results__option {
            text-align: left;
        }

        html[data-bs-theme="dark"] .select2-container--bootstrap-5 .select2-selection,
        html[data-bs-theme="dark"] .select2-dropdown,
        .theme-dark .select2-container--bootstrap-5 .select2-selection,
        .theme-dark .select2-dropdown {
            background-color: #1b1b29;
            border-color: #35354f;
            color: #cfd3e3;
        }

        html[data-bs-theme="dark"] .select2-container--bootstrap-5 .select2-selection__rendered,
        html[data-bs-theme="dark"] .select2-container--bootstrap-5 .select2-selection__placeholder,
        html[data-bs-theme="dark"] .select2-search__field,
        .theme-dark .select2-container--bootstrap-5 .select2-selection__rendered,
        .theme-dark .select2-container--bootstrap-5 .select2-selection__placeholder,
        .theme-dark .select2-search__field {
            color: #cfd3e3 !important;
        }

        html[data-bs-theme="dark"] .select2-search__field,
        .theme-dark .select2-search__field {
            background-color: #151521 !important;
            border-color: #35354f !important;
        }

        html[data-bs-theme="dark"] .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice,
        .theme-dark .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice {
            background-color: #2d2d44 !important;
            border-color: #72789f !important;
            color: #f7f8ff !important;
        }

        html[data-bs-theme="dark"] .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice__display,
        html[data-bs-theme="dark"] .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice__remove,
        .theme-dark .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice__display,
        .theme-dark .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice__remove {
            color: #f7f8ff !important;
        }

        html[data-bs-theme="dark"] .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice__remove:hover,
        .theme-dark .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice__remove:hover {
            color: #fff !important;
            background-color: rgba(255, 255, 255, .12) !important;
        }

        html[data-bs-theme="dark"] .select2-results__option--selected,
        .theme-dark .select2-results__option--selected {
            background-color: #2d2d44 !important;
            color: #fff !important;
        }

        html[data-bs-theme="dark"] .select2-results__option--highlighted,
        .theme-dark .select2-results__option--highlighted {
            background-color: #435ebe !important;
            color: #fff !important;
        }

        body:not(.auth-theme) {
            font-size: .94rem;
        }

        body:not(.auth-theme) .sidebar-wrapper .sidebar-header {
            padding: .2rem 1.25rem 0;
            min-height: 14px;
        }

        body:not(.auth-theme) .sidebar-wrapper .logo {
            display: none;
        }

        body:not(.auth-theme) .sidebar-wrapper .menu {
            padding-top: 0;
            padding-left: .35rem;
            padding-right: .75rem;
        }

        body:not(.auth-theme) .sidebar-wrapper .menu .sidebar-title {
            margin: .15rem 0 .45rem;
            font-size: 1.05rem;
            font-weight: 700;
        }

        body:not(.auth-theme) .sidebar-wrapper .menu .sidebar-link {
            padding: .52rem .55rem;
            font-size: .9rem;
        }

        body:not(.auth-theme) .sidebar-wrapper .menu .sidebar-link i {
            font-size: .95rem;
        }

        body:not(.auth-theme) .sidebar-wrapper .menu .submenu {
            margin-left: 0 !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
        }

        body:not(.auth-theme) .sidebar-wrapper .menu .submenu .submenu-item {
            margin-left: 0 !important;
            padding-left: 0 !important;
        }

        body:not(.auth-theme) .sidebar-wrapper .menu .submenu .submenu-link {
            margin-left: 0 !important;
            padding: .42rem .15rem .42rem 0 !important;
            font-size: .88rem;
        }

        body:not(.auth-theme) .sidebar-wrapper .menu > .sidebar-item > .submenu {
            margin-left: 0 !important;
            padding-left: 0 !important;
        }

        body:not(.auth-theme) .sidebar-wrapper .menu .submenu .submenu-item a:hover {
            margin-left: 0 !important;
        }

        body:not(.auth-theme) .sidebar-wrapper .menu .submenu .submenu-level-2 .submenu-link {
            padding-left: .25rem !important;
        }

        body:not(.auth-theme) .badge {
            font-weight: 700;
            letter-spacing: 0;
        }

        body:not(.auth-theme) .bg-light-primary {
            background-color: #435ebe !important;
            color: #fff !important;
        }

        body:not(.auth-theme) .bg-light-secondary {
            background-color: #6c757d !important;
            color: #fff !important;
        }

        body:not(.auth-theme) .bg-light-success {
            background-color: #198754 !important;
            color: #fff !important;
        }

        body:not(.auth-theme) .bg-light-danger {
            background-color: #dc3545 !important;
            color: #fff !important;
        }

        body:not(.auth-theme) .bg-light-warning {
            background-color: #ffc107 !important;
            color: #000 !important;
        }

        body:not(.auth-theme) .bg-light-info {
            background-color: #0dcaf0 !important;
            color: #000 !important;
        }

        body:not(.auth-theme) .bg-light-dark {
            background-color: #212529 !important;
            color: #fff !important;
        }

        body:not(.auth-theme) .form-control,
        body:not(.auth-theme) .form-select,
        body:not(.auth-theme) .select2-container--bootstrap-5 .select2-selection {
            min-height: 34px;
            padding-top: .3rem;
            padding-bottom: .3rem;
            font-size: .86rem;
        }

        body:not(.auth-theme) select.form-select,
        body:not(.auth-theme) select.form-control {
            padding-top: .25rem;
            padding-bottom: .25rem;
            text-align: left;
            text-align-last: left;
        }

        body:not(.auth-theme) select.form-select option,
        body:not(.auth-theme) select.form-control option {
            text-align: left;
        }

        body:not(.auth-theme) .select2-container--bootstrap-5 .select2-selection--single {
            align-items: center;
            display: flex;
            height: 34px;
            min-height: 34px;
            padding-top: .25rem;
            padding-bottom: .25rem;
        }

        body:not(.auth-theme) .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
            padding-left: .75rem;
            padding-right: 2rem;
            padding-top: 0;
            padding-bottom: 0;
            align-items: center;
            display: flex;
            height: 100%;
            line-height: 1.2;
            text-align: left;
            text-align-last: left;
        }

        body:not(.auth-theme) .select2-container--bootstrap-5 .select2-selection--single .select2-selection__arrow {
            height: 32px;
            top: 50%;
            transform: translateY(-50%);
        }

        body:not(.auth-theme) .input-group-text {
            min-height: 34px;
            padding: .3rem .55rem;
            font-size: .86rem;
        }

        body:not(.auth-theme) .btn {
            padding: .4rem .68rem;
            font-size: .9rem;
        }

        body:not(.auth-theme) .btn-sm {
            padding: .22rem .42rem;
            font-size: .82rem;
        }

        body:not(.auth-theme) .page-heading h3 {
            font-size: 1.3rem;
        }

        body:not(.auth-theme) .page-heading .text-subtitle {
            font-size: .92rem;
        }

        body:not(.auth-theme) .card-title,
        body:not(.auth-theme) h4 {
            font-size: 1.02rem;
        }

        body:not(.auth-theme) .table > :not(caption) > * > * {
            padding: .5rem .6rem;
            font-size: .88rem;
        }

        html[data-bs-theme="dark"] body:not(.auth-theme) {
            color-scheme: dark;
        }

        html[data-bs-theme="dark"] body:not(.auth-theme) .bg-white,
        html[data-bs-theme="dark"] body:not(.auth-theme) .bg-light {
            background-color: #1f1e2e !important;
            color: #dce0f5 !important;
        }

        html[data-bs-theme="dark"] body:not(.auth-theme) .text-dark,
        html[data-bs-theme="dark"] body:not(.auth-theme) .text-black {
            color: #f5f6ff !important;
        }

        html[data-bs-theme="dark"] body:not(.auth-theme) .text-muted,
        html[data-bs-theme="dark"] body:not(.auth-theme) .text-secondary {
            color: #aeb4d2 !important;
        }

        html[data-bs-theme="dark"] body:not(.auth-theme) .card,
        html[data-bs-theme="dark"] body:not(.auth-theme) .modal-content,
        html[data-bs-theme="dark"] body:not(.auth-theme) .dropdown-menu,
        html[data-bs-theme="dark"] body:not(.auth-theme) .list-group-item,
        html[data-bs-theme="dark"] body:not(.auth-theme) .accordion-item,
        html[data-bs-theme="dark"] body:not(.auth-theme) .offcanvas,
        html[data-bs-theme="dark"] body:not(.auth-theme) .toast,
        html[data-bs-theme="dark"] body:not(.auth-theme) .popover,
        html[data-bs-theme="dark"] body:not(.auth-theme) .swal2-popup {
            background-color: #1f1e2e !important;
            border-color: #3d3d58 !important;
            color: #dce0f5 !important;
        }

        html[data-bs-theme="dark"] body:not(.auth-theme) .modal-header,
        html[data-bs-theme="dark"] body:not(.auth-theme) .modal-footer,
        html[data-bs-theme="dark"] body:not(.auth-theme) .card-header,
        html[data-bs-theme="dark"] body:not(.auth-theme) .card-footer,
        html[data-bs-theme="dark"] body:not(.auth-theme) .dropdown-divider,
        html[data-bs-theme="dark"] body:not(.auth-theme) .list-group-item,
        html[data-bs-theme="dark"] body:not(.auth-theme) .accordion-button {
            border-color: #3d3d58 !important;
        }

        html[data-bs-theme="dark"] body:not(.auth-theme) .accordion-button,
        html[data-bs-theme="dark"] body:not(.auth-theme) .accordion-button:not(.collapsed) {
            background-color: #242438 !important;
            color: #f5f6ff !important;
            box-shadow: none;
        }

        html[data-bs-theme="dark"] body:not(.auth-theme) .dropdown-item {
            color: #dce0f5 !important;
        }

        html[data-bs-theme="dark"] body:not(.auth-theme) .dropdown-item:hover,
        html[data-bs-theme="dark"] body:not(.auth-theme) .dropdown-item:focus,
        html[data-bs-theme="dark"] body:not(.auth-theme) .list-group-item-action:hover,
        html[data-bs-theme="dark"] body:not(.auth-theme) .list-group-item-action:focus {
            background-color: #2d2d44 !important;
            color: #fff !important;
        }

        html[data-bs-theme="dark"] body:not(.auth-theme) .form-control,
        html[data-bs-theme="dark"] body:not(.auth-theme) .form-select,
        html[data-bs-theme="dark"] body:not(.auth-theme) .input-group-text {
            background-color: #151521 !important;
            border-color: #35354f !important;
            color: #f5f6ff !important;
        }

        html[data-bs-theme="dark"] body:not(.auth-theme) .form-control:disabled,
        html[data-bs-theme="dark"] body:not(.auth-theme) .form-control[readonly],
        html[data-bs-theme="dark"] body:not(.auth-theme) .form-select:disabled {
            background-color: #202034 !important;
            color: #c2c7df !important;
            opacity: 1;
        }

        html[data-bs-theme="dark"] body:not(.auth-theme) .form-control::placeholder {
            color: #8e94b2 !important;
        }

        html[data-bs-theme="dark"] body:not(.auth-theme) .form-check-input {
            background-color: #151521;
            border-color: #5a6080;
        }

        html[data-bs-theme="dark"] body:not(.auth-theme) .form-check-input:checked {
            background-color: #435ebe;
            border-color: #435ebe;
        }

        html[data-bs-theme="dark"] body:not(.auth-theme) .btn-light,
        html[data-bs-theme="dark"] body:not(.auth-theme) .btn-light-secondary {
            background-color: #9aa2ad !important;
            border-color: #9aa2ad !important;
            color: #111827 !important;
        }

        html[data-bs-theme="dark"] body:not(.auth-theme) .btn-outline-secondary {
            border-color: #72789f !important;
            color: #dce0f5 !important;
        }

        html[data-bs-theme="dark"] body:not(.auth-theme) .btn-outline-secondary:hover {
            background-color: #72789f !important;
            color: #111827 !important;
        }

        html[data-bs-theme="dark"] body:not(.auth-theme) .table {
            --bs-table-bg: transparent;
            --bs-table-color: #dce0f5;
            --bs-table-border-color: #3d3d58;
            color: #dce0f5;
        }

        html[data-bs-theme="dark"] body:not(.auth-theme) .table-striped > tbody > tr:nth-of-type(odd) > * {
            --bs-table-bg-type: rgba(255, 255, 255, .035);
            color: #dce0f5;
        }

        html[data-bs-theme="dark"] body:not(.auth-theme) .table-hover > tbody > tr:hover > * {
            --bs-table-bg-state: rgba(67, 94, 190, .16);
            color: #fff;
        }

        html[data-bs-theme="dark"] body:not(.auth-theme) .table-light,
        html[data-bs-theme="dark"] body:not(.auth-theme) .table-secondary {
            --bs-table-bg: #242438;
            --bs-table-color: #f5f6ff;
            --bs-table-border-color: #3d3d58;
        }

        html[data-bs-theme="dark"] body:not(.auth-theme) .page-link {
            background-color: #1f1e2e;
            border-color: #3d3d58;
            color: #dce0f5;
        }

        html[data-bs-theme="dark"] body:not(.auth-theme) .page-link:hover,
        html[data-bs-theme="dark"] body:not(.auth-theme) .page-item.active .page-link {
            background-color: #435ebe;
            border-color: #435ebe;
            color: #fff;
        }

        html[data-bs-theme="dark"] body:not(.auth-theme) .page-item.disabled .page-link {
            background-color: #202034;
            border-color: #35354f;
            color: #7f86a5;
        }

        html[data-bs-theme="dark"] body:not(.auth-theme) .alert-light,
        html[data-bs-theme="dark"] body:not(.auth-theme) .alert-light-secondary {
            background-color: #242438 !important;
            border-color: #3d3d58 !important;
            color: #dce0f5 !important;
        }

        html[data-bs-theme="dark"] body:not(.auth-theme) pre,
        html[data-bs-theme="dark"] body:not(.auth-theme) code {
            background-color: #151521;
            color: #f5f6ff;
        }
    </style>
</head>

<body class="@if(View::hasSection('auth')) auth-theme @endif">
    @unless(View::hasSection('auth'))
    <script src="{{ asset('assets/static/js/initTheme.js') }}"></script>
    @endunless
    <div id="app">
        @hasSection('auth')
        @yield('content')
        @else
        <div id="sidebar">
            <div class="sidebar-wrapper active">
                <div class="sidebar-header position-relative">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="sidebar-toggler  x">
                            <a href="#" class="sidebar-hide d-xl-none d-block"><i class="bi bi-x bi-middle"></i></a>
                        </div>
                    </div>
                </div>
                <div class="sidebar-menu">
                    <ul class="menu">
                        @can('dashboard.ver')
                            <li class="sidebar-item {{ request()->routeIs('admin.index') || request()->routeIs('home') ? 'active' : '' }}">
                                <a href="{{ route('admin.index') }}" class='sidebar-link'>
                                    <i class="bi bi-grid-fill"></i>
                                    <span>Inicio</span>
                                </a>
                            </li>
                        @endcan
                        @can('bi.ver')
                            <li class="sidebar-item {{ request()->routeIs('admin.bi.*') ? 'active' : '' }}">
                                <a href="{{ route('admin.bi.index') }}" class='sidebar-link'>
                                    <i class="bi bi-bar-chart-line"></i>
                                    <span>BI</span>
                                </a>
                            </li>
                        @endcan
                        @can('notificaciones-operativas.ver')
                            <li class="sidebar-item {{ request()->routeIs('admin.notificaciones-operativas.*') ? 'active' : '' }}">
                                <a href="{{ route('admin.notificaciones-operativas.index') }}" class='sidebar-link'>
                                    <i class="bi bi-bell-fill"></i>
                                    <span>Notificaciones</span>
                                </a>
                            </li>
                        @endcan
                        @can('auditoria-operativa.ver')
                            <li class="sidebar-item {{ request()->routeIs('admin.auditoria-operativa.*') ? 'active' : '' }}">
                                <a href="{{ route('admin.auditoria-operativa.index') }}" class='sidebar-link'>
                                    <i class="bi bi-shield-check"></i>
                                    <span>Auditoria operativa</span>
                                </a>
                            </li>
                        @endcan
                        @can('chat.ver')
                            <li class="sidebar-item {{ request()->routeIs('admin.chat.*') ? 'active' : '' }}">
                                <a href="{{ route('admin.chat.index') }}" class='sidebar-link'>
                                    <i class="bi bi-chat-dots-fill"></i>
                                    <span>Chat interno</span>
                                </a>
                            </li>
                        @endcan
                        <li class="sidebar-item {{ request()->routeIs('admin.manual-usuario.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.manual-usuario.index') }}" class='sidebar-link'>
                                <i class="bi bi-journal-bookmark"></i>
                                <span>Manual de Usuario</span>
                            </a>
                        </li>
                        @can('bitacoras.ver')
                            <li class="sidebar-item {{ request()->routeIs('admin.bitacoras.*') ? 'active' : '' }}">
                                <a href="{{ route('admin.bitacoras.index') }}" class='sidebar-link'>
                                    <i class="bi bi-journal-text"></i>
                                    <span>Bitacora</span>
                                </a>
                            </li>
                        @endcan
                        @canany(['ordenes-trabajo.ver', 'ordenes-trabajo.crear', 'ordenes-trabajo-articulos.agregar', 'ordenes-trabajo-motivos.ver', 'gestion-cubiertas.ver', 'movimiento-cubiertas.ver', 'movimiento-cubiertas.crear', 'controles-unidad.ver', 'controles-unidad.crear'])
                        <li class="sidebar-item has-sub {{ request()->routeIs('admin.ordenes-trabajo.*') || request()->routeIs('admin.ordenes-trabajo-motivos.*') || request()->routeIs('admin.gestion-cubiertas.*') || request()->routeIs('admin.movimiento-cubiertas.*') || request()->routeIs('admin.controles-unidad.*') ? 'active' : '' }}">
                            <a href="#" class='sidebar-link'>
                                <i class="bi bi-wrench"></i>
                                <span>Ordenes de trabajo</span>
                            </a>

                            <ul class="submenu submenu-closed" style="--submenu-height: 129px;">
                                @can('ordenes-trabajo.ver')
                                    <li class="submenu-item {{ request()->routeIs('admin.ordenes-trabajo.*') ? 'active' : '' }}">
                                        <a href="{{ route('admin.ordenes-trabajo.index') }}" class="submenu-link">Ordenes de Trabajo</a>
                                    </li>
                                @endcan
                                @can('ordenes-trabajo-motivos.ver')
                                    <li class="submenu-item {{ request()->routeIs('admin.ordenes-trabajo-motivos.*') ? 'active' : '' }}">
                                        <a href="{{ route('admin.ordenes-trabajo-motivos.index') }}" class="submenu-link">Motivos</a>
                                    </li>
                                @endcan
                                @can('gestion-cubiertas.ver')
                                    <li class="submenu-item {{ request()->routeIs('admin.gestion-cubiertas.*') || request()->routeIs('admin.movimiento-cubiertas.*') ? 'active' : '' }}">
                                        <a href="{{ route('admin.gestion-cubiertas.index') }}" class="submenu-link">Gestion cubiertas</a>
                                    </li>
                                @endcan
                                @can('controles-unidad.ver')
                                    <li class="submenu-item {{ request()->routeIs('admin.controles-unidad.*') ? 'active' : '' }}">
                                        <a href="{{ route('admin.controles-unidad.index') }}" class="submenu-link">Check List Vehicular</a>
                                    </li>
                                @elsecan('controles-unidad.crear')
                                    <li class="submenu-item {{ request()->routeIs('admin.controles-unidad.*') ? 'active' : '' }}">
                                        <a href="{{ route('admin.controles-unidad.create') }}" class="submenu-link">Crear Check List</a>
                                    </li>
                                @endcan
                            </ul>
                        </li>
                        @endcanany
                        @canany(['flota.ver', 'servicios-kilometraje.ver', 'verificaciones-tecnicas.ver', 'historial-articulos-vehiculo.ver', 'configuracion-intervalos-servicio.ver', 'configuracion-vencimientos-verificacion.ver', 'titulares.ver', 'marca-vehiculo.ver', 'cia-seguro.ver', 'tipo-vehiculo.ver', 'marca-carroceria.ver', 'tipo-motor.ver', 'modelo-motor.ver', 'tipo-caja.ver', 'modelo-caja.ver'])
                        <li class="sidebar-item has-sub {{ request()->routeIs('admin.flota.*') || request()->routeIs('admin.servicios-kilometraje.*') || request()->routeIs('admin.verificaciones-tecnicas.*') || request()->routeIs('admin.configuracion-intervalos-servicio.*') || request()->routeIs('admin.configuracion-vencimientos-verificacion.*') || request()->routeIs('admin.historial-articulos-vehiculo.*') || request()->routeIs('admin.titulares.*') || request()->routeIs('admin.marca-vehiculo.*') || request()->routeIs('admin.cia-seguro.*') || request()->routeIs('admin.tipo-vehiculo.*') || request()->routeIs('admin.marca-carroceria.*') || request()->routeIs('admin.tipo-motor.*') || request()->routeIs('admin.modelo-motor.*') || request()->routeIs('admin.tipo-caja.*') || request()->routeIs('admin.modelo-caja.*') ? 'active' : '' }}">
                            <a href="#" class='sidebar-link'>
                                <i class="bi bi-car-front"></i>
                                <span>Flota</span>
                            </a>

                            <ul class="submenu submenu-closed" style="--submenu-height: 215px;">
                                @can('flota.ver')<li class="submenu-item {{ request()->routeIs('admin.flota.*') ? 'active' : '' }}"><a href="{{ route('admin.flota.index') }}" class="submenu-link">Flota</a></li>@endcan
                                @can('servicios-kilometraje.ver')<li class="submenu-item {{ request()->routeIs('admin.servicios-kilometraje.*') ? 'active' : '' }}"><a href="{{ route('admin.servicios-kilometraje.index') }}" class="submenu-link">Servicios por km / hs</a></li>@endcan
                                @can('verificaciones-tecnicas.ver')<li class="submenu-item {{ request()->routeIs('admin.verificaciones-tecnicas.*') ? 'active' : '' }}"><a href="{{ route('admin.verificaciones-tecnicas.index') }}" class="submenu-link">Verificaciones tecnicas</a></li>@endcan
                                @can('historial-articulos-vehiculo.ver')<li class="submenu-item {{ request()->routeIs('admin.historial-articulos-vehiculo.*') ? 'active' : '' }}"><a href="{{ route('admin.historial-articulos-vehiculo.index') }}" class="submenu-link">Historial vehiculos</a></li>@endcan
                                @can('historial-articulos-vehiculo.ver')<li class="submenu-item {{ request()->routeIs('admin.costeo-vehiculos.*') ? 'active' : '' }}"><a href="{{ route('admin.costeo-vehiculos.index') }}" class="submenu-link">Costeo vehiculos</a></li>@endcan
                                @canany(['configuracion-intervalos-servicio.ver', 'configuracion-vencimientos-verificacion.ver', 'titulares.ver', 'marca-vehiculo.ver', 'cia-seguro.ver', 'tipo-vehiculo.ver', 'marca-carroceria.ver', 'tipo-motor.ver', 'modelo-motor.ver', 'tipo-caja.ver', 'modelo-caja.ver'])
                                <li class="submenu-item has-sub {{ request()->routeIs('admin.configuracion-intervalos-servicio.*') || request()->routeIs('admin.configuracion-vencimientos-verificacion.*') || request()->routeIs('admin.titulares.*') || request()->routeIs('admin.marca-vehiculo.*') || request()->routeIs('admin.cia-seguro.*') || request()->routeIs('admin.tipo-vehiculo.*') || request()->routeIs('admin.marca-carroceria.*') || request()->routeIs('admin.tipo-motor.*') || request()->routeIs('admin.modelo-motor.*') || request()->routeIs('admin.tipo-caja.*') || request()->routeIs('admin.modelo-caja.*') ? 'active' : '' }}">
                                    <a href="#" class="submenu-link">Tablas auxiliares</a>
                                    <ul class="submenu submenu-level-2 ">
                                        @can('configuracion-intervalos-servicio.ver')
                                        <li class="submenu-item {{ request()->routeIs('admin.configuracion-intervalos-servicio.*') ? 'active' : '' }}">
                                            <a href="{{ route('admin.configuracion-intervalos-servicio.index') }}" class="submenu-link">Intervalos de servicios</a>
                                        </li>
                                        @endcan
                                        @can('configuracion-vencimientos-verificacion.ver')
                                        <li class="submenu-item {{ request()->routeIs('admin.configuracion-vencimientos-verificacion.*') ? 'active' : '' }}">
                                            <a href="{{ route('admin.configuracion-vencimientos-verificacion.index') }}" class="submenu-link">Tipos de verificaciones</a>
                                        </li>
                                        @endcan
                                        @can('titulares.ver')
                                        <li class="submenu-item {{ request()->routeIs('admin.titulares.*') ? 'active' : '' }}">
                                            <a href="{{ route('admin.titulares.index') }}" class="submenu-link">Titulares</a>
                                        </li>
                                        @endcan
                                        @can('marca-vehiculo.ver')
                                        <li class="submenu-item {{ request()->routeIs('admin.marca-vehiculo.*') ? 'active' : '' }}">
                                            <a href="{{ route('admin.marca-vehiculo.index') }}" class="submenu-link">Marca vehículo</a>
                                        </li>
                                        @endcan
                                        @can('cia-seguro.ver')
                                        <li class="submenu-item {{ request()->routeIs('admin.cia-seguro.*') ? 'active' : '' }}">
                                            <a href="{{ route('admin.cia-seguro.index') }}" class="submenu-link">Cía. seguro</a>
                                        </li>
                                        @endcan
                                        @can('tipo-vehiculo.ver')
                                        <li class="submenu-item {{ request()->routeIs('admin.tipo-vehiculo.*') ? 'active' : '' }}">
                                            <a href="{{ route('admin.tipo-vehiculo.index') }}" class="submenu-link">Tipo vehículo</a>
                                        </li>
                                        @endcan
                                        @can('marca-carroceria.ver')
                                        <li class="submenu-item {{ request()->routeIs('admin.marca-carroceria.*') ? 'active' : '' }}">
                                            <a href="{{ route('admin.marca-carroceria.index') }}" class="submenu-link">Marca carrocería</a>
                                        </li>
                                        @endcan
                                        @can('tipo-motor.ver')
                                        <li class="submenu-item {{ request()->routeIs('admin.tipo-motor.*') ? 'active' : '' }}">
                                            <a href="{{ route('admin.tipo-motor.index') }}" class="submenu-link">Tipo motor</a>
                                        </li>
                                        @endcan
                                        @can('modelo-motor.ver')
                                        <li class="submenu-item {{ request()->routeIs('admin.modelo-motor.*') ? 'active' : '' }}">
                                            <a href="{{ route('admin.modelo-motor.index') }}" class="submenu-link">Modelo motor</a>
                                        </li>
                                        @endcan
                                        @can('tipo-caja.ver')
                                        <li class="submenu-item {{ request()->routeIs('admin.tipo-caja.*') ? 'active' : '' }}">
                                            <a href="{{ route('admin.tipo-caja.index') }}" class="submenu-link">Tipo caja</a>
                                        </li>
                                        @endcan
                                        @can('modelo-caja.ver')
                                        <li class="submenu-item {{ request()->routeIs('admin.modelo-caja.*') ? 'active' : '' }}">
                                            <a href="{{ route('admin.modelo-caja.index') }}" class="submenu-link">Modelo caja</a>
                                        </li>
                                        @endcan
                                    </ul>
                                </li>
                                @endcanany
                            </ul>
                        </li>
                        @endcanany
                        @can('proveedores.ver')
                        <li class="sidebar-item has-sub {{ request()->routeIs('admin.proveedores.*') ? 'active' : '' }}">
                            <a href="#" class='sidebar-link'>
                                <i class="bi bi-person"></i>
                                <span>Proveedores</span>
                            </a>
                            <ul class="submenu submenu-closed" style="--submenu-height: 86px;">
                                <li class="submenu-item {{ request()->routeIs('admin.proveedores.*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.proveedores.index') }}" class="submenu-link">Proveedores</a>
                                </li>
                            </ul>
                        </li>
                        @endcan
                        @canany(['pedidos-articulos.ver', 'pedidos-articulos.crear', 'solicitudes-repuestos.ver', 'solicitudes-repuestos.crear', 'ordenes-compra.ver', 'ordenes-compra.crear', 'compras.ver', 'entradas.ver', 'entradas.crear'])
                        <li class="sidebar-item has-sub {{ request()->routeIs('admin.compras.*') || request()->routeIs('admin.entradas.*') || request()->routeIs('admin.ordenes-compra.*') || request()->routeIs('admin.pedidos-articulos.*') || request()->routeIs('admin.solicitudes-repuestos.*') ? 'active' : '' }}">
                            <a href="#" class='sidebar-link'>
                                <i class="bi bi-cart-plus"></i>
                                <span>Compras y pedidos</span>
                            </a>
                            <ul class="submenu submenu-closed" style="--submenu-height: 215px;">
                               @can('pedidos-articulos.ver')
                                   <li class="submenu-item {{ request()->routeIs('admin.pedidos-articulos.*') ? 'active' : '' }}">
                                        <a href="{{ route('admin.pedidos-articulos.index') }}" class="submenu-link">Pedido de articulos</a>
                                    </li>
                                @elsecan('pedidos-articulos.crear')
                                    <li class="submenu-item {{ request()->routeIs('admin.pedidos-articulos.*') ? 'active' : '' }}">
                                        <a href="{{ route('admin.pedidos-articulos.create') }}" class="submenu-link">Crear pedido</a>
                                    </li>
                                @endcan
                                @can('solicitudes-repuestos.ver')
                                    <li class="submenu-item {{ request()->routeIs('admin.solicitudes-repuestos.*') ? 'active' : '' }}">
                                        <a href="{{ route('admin.solicitudes-repuestos.index') }}" class="submenu-link">Solicitudes de repuestos</a>
                                    </li>
                                @elsecan('solicitudes-repuestos.crear')
                                    <li class="submenu-item {{ request()->routeIs('admin.solicitudes-repuestos.*') ? 'active' : '' }}">
                                        <a href="{{ route('admin.solicitudes-repuestos.create') }}" class="submenu-link">Crear solicitud repuesto</a>
                                    </li>
                                @endcan
                                @can('ordenes-compra.ver')
                                    <li class="submenu-item {{ request()->routeIs('admin.ordenes-compra.*') ? 'active' : '' }}">
                                       <a href="{{ route('admin.ordenes-compra.index') }}" class="submenu-link">Orden de compra</a>
                                    </li>
                                @endcan
                               
                                @can('compras.ver')
                                    <li class="submenu-item {{ request()->routeIs('admin.compras.*') ? 'active' : '' }}">
                                        <a href="{{ route('admin.compras.index') }}" class="submenu-link">Compras</a>
                                    </li>
                                @endcan
                                @can('entradas.ver')
                                    <li class="submenu-item {{ request()->routeIs('admin.entradas.pendientes') ? 'active' : '' }}">
                                        <a href="{{ route('admin.entradas.pendientes') }}" class="submenu-link">Pendientes de entrega</a>
                                    </li>
                                @endcan
                                @can('entradas.ver')
                                    <li class="submenu-item {{ request()->routeIs('admin.entradas.*') && ! request()->routeIs('admin.entradas.pendientes') ? 'active' : '' }}">
                                        <a href="{{ route('admin.entradas.index') }}" class="submenu-link">Ingresos de articulos</a>
                                    </li>
                                @elsecan('entradas.crear')
                                    <li class="submenu-item {{ request()->routeIs('admin.entradas.*') ? 'active' : '' }}">
                                        <a href="{{ route('admin.entradas.create') }}" class="submenu-link">Crear ingreso</a>
                                    </li>
                                @endcan
                            </ul>
                        </li>
                        @endcanany
                        @canany(['articulos.ver', 'categorias.ver', 'unidad-medidas.ver', 'inventarios.ver', 'inventario-transferencias.ver', 'entregas-herramientas.ver', 'entregas-ropa-epp.ver', 'reparaciones-articulos.ver'])
                        <li class="sidebar-item has-sub {{ request()->routeIs('admin.articulos.*') || request()->routeIs('admin.inventarios.*') || request()->routeIs('admin.entregas-herramientas.*') || request()->routeIs('admin.entregas-ropa-epp.*') || request()->routeIs('admin.reparaciones-articulos.*') || request()->routeIs('admin.categorias.*') || request()->routeIs('admin.unidad-medidas.*') ? 'active' : '' }}">
                            <a href="#" class='sidebar-link'>
                                <i class="bi bi-card-checklist"></i>
                                <span>Articulos</span>
                            </a>
                            <ul class="submenu submenu-closed" style="--submenu-height: 410px;">
                                @can('articulos.ver')
                                <li class="submenu-item {{ request()->routeIs('admin.articulos.index') || request()->routeIs('admin.articulos.create') || request()->routeIs('admin.articulos.edit') || request()->routeIs('admin.articulos.show') ? 'active' : '' }}">
                                    <a href="{{ route('admin.articulos.index') }}" class="submenu-link">Articulos</a>
                                </li>
                                <li class="submenu-item {{ request()->routeIs('admin.articulos.listado') ? 'active' : '' }}">
                                    <a href="{{ route('admin.articulos.listado') }}" class="submenu-link">Listados</a>
                                </li>
                                @endcan
                                @can('categorias.ver')
                                <li class="submenu-item {{ request()->routeIs('admin.categorias.*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.categorias.index') }}" class="submenu-link">Categorías</a>
                                </li>
                                @endcan
                                @can('unidad-medidas.ver')
                                <li class="submenu-item {{ request()->routeIs('admin.unidad-medidas.*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.unidad-medidas.index') }}" class="submenu-link">Unid de medida</a>
                                </li>
                                @endcan
                                @can('inventarios.ver')
                                <li class="submenu-item {{ request()->routeIs('admin.inventarios.index') || request()->routeIs('admin.inventarios.bajo-stock') || request()->routeIs('admin.inventarios.sin-stock') ? 'active' : '' }}">
                                    <a href="{{ route('admin.inventarios.index') }}" class="submenu-link">Inventarios</a>
                                </li>
                                @endcan
                                @can('inventario-transferencias.ver')
                                <li class="submenu-item {{ request()->routeIs('admin.inventarios.transferencias.*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.inventarios.transferencias.index') }}" class="submenu-link">Transferencias</a>
                                </li>
                                @endcan
                                @can('entregas-herramientas.ver')
                                <li class="submenu-item {{ request()->routeIs('admin.entregas-herramientas.*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.entregas-herramientas.index') }}" class="submenu-link">Entrega herramientas</a>
                                </li>
                                @endcan
                                @can('entregas-ropa-epp.ver')
                                <li class="submenu-item {{ request()->routeIs('admin.entregas-ropa-epp.*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.entregas-ropa-epp.index') }}" class="submenu-link">Entrega ropa y EPP</a>
                                </li>
                                @endcan
                                @can('reparaciones-articulos.ver')
                                <li class="submenu-item {{ request()->routeIs('admin.reparaciones-articulos.*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.reparaciones-articulos.index') }}" class="submenu-link">Reparaciones de articulos</a>
                                </li>
                                @elsecan('reparaciones-articulos.crear')
                                <li class="submenu-item {{ request()->routeIs('admin.reparaciones-articulos.*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.reparaciones-articulos.create') }}" class="submenu-link">Registrar reparacion</a>
                                </li>
                                @endcan
                            </ul>
                        </li>
                        @endcanany
                        @can('depositos.ver')
                        <li class="sidebar-item has-sub {{ request()->routeIs('admin.depositos.*') ? 'active' : '' }}">
                            <a href="#" class='sidebar-link'>
                                <i class="bi bi-card-checklist"></i>
                                <span>Depositos</span>
                            </a>
                            <ul class="submenu submenu-closed" style="--submenu-height: 86px;">
                                <li class="submenu-item {{ request()->routeIs('admin.depositos.*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.depositos.index') }}" class="submenu-link">Depósitos</a>
                                </li>
                            </ul>
                        </li>
                        @endcan

                        @canany(['ajustes.ver', 'dashboards.administrar', 'bases.ver', 'servicios-asignados.ver', 'roles.ver', 'users.ver', 'empleados.ver', 'cronogramas.ver', 'provincias.ver', 'ciudades.ver'])
                        <li class="sidebar-item has-sub {{ request()->routeIs('admin.ajustes.*') || request()->routeIs('admin.dashboards.*') || request()->routeIs('admin.bases.*') || request()->routeIs('admin.servicios-asignados.*') || request()->routeIs('admin.roles.*') || request()->routeIs('admin.users.*') || request()->routeIs('admin.empleados.*') || request()->routeIs('admin.cronogramas-laborales.*') || request()->routeIs('admin.provincias.*') || request()->routeIs('admin.ciudades.*') ? 'active' : '' }}">
                            <a href="#" class='sidebar-link'>
                                <i class="bi bi-gear"></i>
                                <span>Configuracion</span>
                            </a>
                            <ul class="submenu submenu-closed" style="--submenu-height: 470px;">
                                <li class="submenu-item has-sub {{ request()->routeIs('admin.ajustes.*') || request()->routeIs('admin.dashboards.*') || request()->routeIs('admin.configuracion-intervalos-servicio.*') || request()->routeIs('admin.bases.*') || request()->routeIs('admin.servicios-asignados.*') || request()->routeIs('admin.roles.*') || request()->routeIs('admin.users.*') || request()->routeIs('admin.empleados.*') || request()->routeIs('admin.cronogramas-laborales.*') || request()->routeIs('admin.provincias.*') || request()->routeIs('admin.ciudades.*') ? 'active' : '' }}">
                                    <a href="#" class="submenu-link">Ajustes</a>
                                    <ul class="submenu submenu-level-2 ">
                                        @can('ajustes.ver')
                                        <li class="submenu-item {{ request()->routeIs('admin.ajustes.*') ? 'active' : '' }}">
                                            <a href="{{ route('admin.ajustes.index') }}" class="submenu-link">Ajustes del sistema</a>
                                        </li>
                                        @endcan
                                        @can('dashboards.administrar')
                                        <li class="submenu-item {{ request()->routeIs('admin.dashboards.*') ? 'active' : '' }}">
                                            <a href="{{ route('admin.dashboards.index') }}" class="submenu-link">Dashboards</a>
                                        </li>
                                        @endcan
                                        @can('bases.ver')
                                        <li class="submenu-item {{ request()->routeIs('admin.bases.*') ? 'active' : '' }}">
                                            <a href="{{ route('admin.bases.index') }}" class="submenu-link">Bases</a>
                                        </li>
                                        @endcan
                                        @can('servicios-asignados.ver')
                                        <li class="submenu-item {{ request()->routeIs('admin.servicios-asignados.*') ? 'active' : '' }}">
                                            <a href="{{ route('admin.servicios-asignados.index') }}" class="submenu-link">Servicio asignado</a>
                                        </li>
                                        @endcan
                                        @can('roles.ver')
                                        <li class="submenu-item {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
                                            <a href="{{ route('admin.roles.index') }}" class="submenu-link">Roles</a>
                                        </li>
                                        @endcan
                                        @can('users.ver')
                                        <li class="submenu-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                                            <a href="{{ route('admin.users.index') }}" class="submenu-link">Usuarios</a>
                                        </li>
                                        @endcan
                                        @can('empleados.ver')
                                        <li class="submenu-item {{ request()->routeIs('admin.empleados.*') ? 'active' : '' }}">
                                            <a href="{{ route('admin.empleados.index') }}" class="submenu-link">Empleados</a>
                                        </li>
                                        @endcan
                                        @can('cronogramas.ver')
                                        <li class="submenu-item {{ request()->routeIs('admin.cronogramas-laborales.*') ? 'active' : '' }}">
                                            <a href="{{ route('admin.cronogramas-laborales.index') }}" class="submenu-link">Cronogramas laborales</a>
                                        </li>
                                        @endcan
                                        @can('provincias.ver')
                                        <li class="submenu-item {{ request()->routeIs('admin.provincias.*') ? 'active' : '' }}">
                                            <a href="{{ route('admin.provincias.index') }}" class="submenu-link">Provincias</a>
                                        </li>
                                        @endcan
                                        @can('ciudades.ver')
                                        <li class="submenu-item {{ request()->routeIs('admin.ciudades.*') ? 'active' : '' }}">
                                            <a href="{{ route('admin.ciudades.index') }}" class="submenu-link">Ciudades</a>
                                        </li>
                                        @endcan
                                    </ul>
                                </li>
                            </ul>
                        </li>
                        @endcanany
                    </ul>

                    <form id="sidebar-logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                </div>
            </div>
        </div>
        <div id="main">

            <div class="siga-main-toolbar">
                @php
                    $currentRouteName = Route::currentRouteName();
                    $breadcrumbDefinitions = [
                        ['patterns' => ['admin.chat.*'], 'group' => 'Chat interno', 'groupRoute' => 'admin.chat.index'],
                        ['patterns' => ['admin.manual-usuario.*'], 'group' => 'Manual de Usuario', 'groupRoute' => 'admin.manual-usuario.index'],
                        ['patterns' => ['admin.global-search.*'], 'group' => 'Busqueda global', 'groupRoute' => 'admin.global-search.index'],
                        ['patterns' => ['admin.notificaciones-operativas.*'], 'group' => 'Notificaciones operativas', 'groupRoute' => 'admin.notificaciones-operativas.index'],
                        ['patterns' => ['admin.auditoria-operativa.*'], 'group' => 'Auditoria operativa', 'groupRoute' => 'admin.auditoria-operativa.index'],
                        ['patterns' => ['admin.bitacoras.*'], 'group' => 'Bitacora', 'groupRoute' => 'admin.bitacoras.index'],
                        ['patterns' => ['admin.bi.*'], 'group' => 'BI', 'groupRoute' => 'admin.bi.index'],
                        ['patterns' => ['admin.ordenes-trabajo.*'], 'group' => 'Ordenes de trabajo', 'groupRoute' => 'admin.ordenes-trabajo.index', 'item' => 'Ordenes de Trabajo', 'itemRoute' => 'admin.ordenes-trabajo.index'],
                        ['patterns' => ['admin.gestion-cubiertas.*'], 'group' => 'Ordenes de trabajo', 'groupRoute' => 'admin.ordenes-trabajo.index', 'item' => 'Gestion cubiertas', 'itemRoute' => 'admin.gestion-cubiertas.index'],
                        ['patterns' => ['admin.controles-unidad.*'], 'group' => 'Ordenes de trabajo', 'groupRoute' => 'admin.ordenes-trabajo.index', 'item' => 'Check List Vehicular', 'itemRoute' => 'admin.controles-unidad.index'],
                        ['patterns' => ['admin.flota.*'], 'group' => 'Flota', 'groupRoute' => 'admin.flota.index', 'item' => 'Flota', 'itemRoute' => 'admin.flota.index'],
                        ['patterns' => ['admin.servicios-kilometraje.*'], 'group' => 'Flota', 'groupRoute' => 'admin.flota.index', 'item' => 'Servicios por km / hs', 'itemRoute' => 'admin.servicios-kilometraje.index'],
                        ['patterns' => ['admin.verificaciones-tecnicas.*'], 'group' => 'Flota', 'groupRoute' => 'admin.flota.index', 'item' => 'Verificaciones tecnicas', 'itemRoute' => 'admin.verificaciones-tecnicas.index'],
                        ['patterns' => ['admin.historial-articulos-vehiculo.*'], 'group' => 'Flota', 'groupRoute' => 'admin.flota.index', 'item' => 'Historial vehiculos', 'itemRoute' => 'admin.historial-articulos-vehiculo.index'],
                        ['patterns' => ['admin.costeo-vehiculos.*'], 'group' => 'Flota', 'groupRoute' => 'admin.flota.index', 'item' => 'Costeo vehiculos', 'itemRoute' => 'admin.costeo-vehiculos.index'],
                        ['patterns' => ['admin.configuracion-intervalos-servicio.*'], 'group' => 'Flota', 'groupRoute' => 'admin.flota.index', 'item' => 'Intervalos de servicios', 'itemRoute' => 'admin.configuracion-intervalos-servicio.index'],
                        ['patterns' => ['admin.configuracion-vencimientos-verificacion.*'], 'group' => 'Flota', 'groupRoute' => 'admin.flota.index', 'item' => 'Tipos de verificaciones', 'itemRoute' => 'admin.configuracion-vencimientos-verificacion.index'],
                        ['patterns' => ['admin.titulares.*'], 'group' => 'Flota', 'groupRoute' => 'admin.flota.index', 'item' => 'Titulares', 'itemRoute' => 'admin.titulares.index'],
                        ['patterns' => ['admin.marca-vehiculo.*'], 'group' => 'Flota', 'groupRoute' => 'admin.flota.index', 'item' => 'Marca vehiculo', 'itemRoute' => 'admin.marca-vehiculo.index'],
                        ['patterns' => ['admin.cia-seguro.*'], 'group' => 'Flota', 'groupRoute' => 'admin.flota.index', 'item' => 'Cia. seguro', 'itemRoute' => 'admin.cia-seguro.index'],
                        ['patterns' => ['admin.tipo-vehiculo.*'], 'group' => 'Flota', 'groupRoute' => 'admin.flota.index', 'item' => 'Tipo vehiculo', 'itemRoute' => 'admin.tipo-vehiculo.index'],
                        ['patterns' => ['admin.marca-carroceria.*'], 'group' => 'Flota', 'groupRoute' => 'admin.flota.index', 'item' => 'Marca carroceria', 'itemRoute' => 'admin.marca-carroceria.index'],
                        ['patterns' => ['admin.tipo-motor.*'], 'group' => 'Flota', 'groupRoute' => 'admin.flota.index', 'item' => 'Tipo motor', 'itemRoute' => 'admin.tipo-motor.index'],
                        ['patterns' => ['admin.modelo-motor.*'], 'group' => 'Flota', 'groupRoute' => 'admin.flota.index', 'item' => 'Modelo motor', 'itemRoute' => 'admin.modelo-motor.index'],
                        ['patterns' => ['admin.tipo-caja.*'], 'group' => 'Flota', 'groupRoute' => 'admin.flota.index', 'item' => 'Tipo caja', 'itemRoute' => 'admin.tipo-caja.index'],
                        ['patterns' => ['admin.modelo-caja.*'], 'group' => 'Flota', 'groupRoute' => 'admin.flota.index', 'item' => 'Modelo caja', 'itemRoute' => 'admin.modelo-caja.index'],
                        ['patterns' => ['admin.proveedores.*'], 'group' => 'Proveedores', 'groupRoute' => 'admin.proveedores.index'],
                        ['patterns' => ['admin.pedidos-articulos.*'], 'group' => 'Compras y pedidos', 'groupRoute' => 'admin.pedidos-articulos.index', 'item' => 'Pedido de articulos', 'itemRoute' => 'admin.pedidos-articulos.index'],
                        ['patterns' => ['admin.solicitudes-repuestos.*'], 'group' => 'Compras y pedidos', 'groupRoute' => 'admin.pedidos-articulos.index', 'item' => 'Solicitudes de repuestos', 'itemRoute' => 'admin.solicitudes-repuestos.index'],
                        ['patterns' => ['admin.ordenes-compra.*'], 'group' => 'Compras y pedidos', 'groupRoute' => 'admin.pedidos-articulos.index', 'item' => 'Orden de compra', 'itemRoute' => 'admin.ordenes-compra.index'],
                        ['patterns' => ['admin.compras.*'], 'group' => 'Compras y pedidos', 'groupRoute' => 'admin.pedidos-articulos.index', 'item' => 'Compras', 'itemRoute' => 'admin.compras.index'],
                        ['patterns' => ['admin.entradas.*'], 'group' => 'Compras y pedidos', 'groupRoute' => 'admin.pedidos-articulos.index', 'item' => 'Ingresos de articulos', 'itemRoute' => 'admin.entradas.index'],
                        ['patterns' => ['admin.articulos.listado'], 'group' => 'Articulos', 'groupRoute' => 'admin.articulos.index', 'item' => 'Listados', 'itemRoute' => 'admin.articulos.listado'],
                        ['patterns' => ['admin.articulos.*'], 'group' => 'Articulos', 'groupRoute' => 'admin.articulos.index', 'item' => 'Articulos', 'itemRoute' => 'admin.articulos.index'],
                        ['patterns' => ['admin.categorias.*'], 'group' => 'Articulos', 'groupRoute' => 'admin.articulos.index', 'item' => 'Categorias', 'itemRoute' => 'admin.categorias.index'],
                        ['patterns' => ['admin.unidad-medidas.*'], 'group' => 'Articulos', 'groupRoute' => 'admin.articulos.index', 'item' => 'Unid de medida', 'itemRoute' => 'admin.unidad-medidas.index'],
                        ['patterns' => ['admin.inventarios.transferencias.*'], 'group' => 'Articulos', 'groupRoute' => 'admin.articulos.index', 'item' => 'Transferencias', 'itemRoute' => 'admin.inventarios.transferencias.index'],
                        ['patterns' => ['admin.inventarios.*'], 'group' => 'Articulos', 'groupRoute' => 'admin.articulos.index', 'item' => 'Inventarios', 'itemRoute' => 'admin.inventarios.index'],
                        ['patterns' => ['admin.entregas-herramientas.*'], 'group' => 'Articulos', 'groupRoute' => 'admin.articulos.index', 'item' => 'Entrega herramientas', 'itemRoute' => 'admin.entregas-herramientas.index'],
                        ['patterns' => ['admin.entregas-ropa-epp.*'], 'group' => 'Articulos', 'groupRoute' => 'admin.articulos.index', 'item' => 'Entrega ropa y EPP', 'itemRoute' => 'admin.entregas-ropa-epp.index'],
                        ['patterns' => ['admin.reparaciones-articulos.*'], 'group' => 'Articulos', 'groupRoute' => 'admin.articulos.index', 'item' => 'Reparaciones de articulos', 'itemRoute' => 'admin.reparaciones-articulos.index'],
                        ['patterns' => ['admin.depositos.*'], 'group' => 'Depositos', 'groupRoute' => 'admin.depositos.index'],
                        ['patterns' => ['admin.ajustes.*'], 'group' => 'Configuracion', 'groupRoute' => 'admin.ajustes.index', 'item' => 'Ajustes del sistema', 'itemRoute' => 'admin.ajustes.index'],
                        ['patterns' => ['admin.bases.*'], 'group' => 'Configuracion', 'groupRoute' => 'admin.ajustes.index', 'item' => 'Bases', 'itemRoute' => 'admin.bases.index'],
                        ['patterns' => ['admin.servicios-asignados.*'], 'group' => 'Configuracion', 'groupRoute' => 'admin.ajustes.index', 'item' => 'Servicio asignado', 'itemRoute' => 'admin.servicios-asignados.index'],
                        ['patterns' => ['admin.roles.*'], 'group' => 'Configuracion', 'groupRoute' => 'admin.ajustes.index', 'item' => 'Roles', 'itemRoute' => 'admin.roles.index'],
                        ['patterns' => ['admin.users.*'], 'group' => 'Configuracion', 'groupRoute' => 'admin.ajustes.index', 'item' => 'Usuarios', 'itemRoute' => 'admin.users.index'],
                        ['patterns' => ['admin.empleados.*'], 'group' => 'Configuracion', 'groupRoute' => 'admin.ajustes.index', 'item' => 'Empleados', 'itemRoute' => 'admin.empleados.index'],
                        ['patterns' => ['admin.cronogramas-laborales.*'], 'group' => 'Configuracion', 'groupRoute' => 'admin.ajustes.index', 'item' => 'Cronogramas laborales', 'itemRoute' => 'admin.cronogramas-laborales.index'],
                        ['patterns' => ['admin.provincias.*'], 'group' => 'Configuracion', 'groupRoute' => 'admin.ajustes.index', 'item' => 'Provincias', 'itemRoute' => 'admin.provincias.index'],
                        ['patterns' => ['admin.ciudades.*'], 'group' => 'Configuracion', 'groupRoute' => 'admin.ajustes.index', 'item' => 'Ciudades', 'itemRoute' => 'admin.ciudades.index'],
                    ];

                    $currentBreadcrumb = collect($breadcrumbDefinitions)->first(function ($definition) {
                        return collect($definition['patterns'])->contains(fn ($pattern) => request()->routeIs($pattern));
                    });
                @endphp
                <button type="button" class="btn siga-mobile-menu-btn" title="Menu" aria-label="Abrir menu">
                    <i class="bi bi-list"></i>
                </button>
                <nav class="siga-toolbar-breadcrumb" aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.index') }}">Inicio</a>
                        </li>
                        @if ($currentBreadcrumb && ! request()->routeIs('admin.index') && ! request()->routeIs('home'))
                            @if (! empty($currentBreadcrumb['item']) && $currentBreadcrumb['item'] !== $currentBreadcrumb['group'])
                                <li class="breadcrumb-item">
                                    <a href="{{ route($currentBreadcrumb['groupRoute']) }}">{{ $currentBreadcrumb['group'] }}</a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">{{ $currentBreadcrumb['item'] }}</li>
                            @else
                                <li class="breadcrumb-item active" aria-current="page">{{ $currentBreadcrumb['group'] }}</li>
                            @endif
                        @endif
                    </ol>
                </nav>
                <div class="siga-toolbar-actions">
                    <form method="GET" action="{{ route('admin.global-search.index') }}" class="siga-global-search" role="search">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="search" name="q" id="sigaGlobalSearchInput" class="form-control"
                                value="{{ request()->routeIs('admin.global-search.*') ? request('q') : '' }}"
                                placeholder="Buscar en SIGA..." autocomplete="off">
                            <button type="submit" class="btn btn-primary" title="Buscar">
                                <i class="bi bi-arrow-return-left"></i>
                            </button>
                        </div>
                        <div id="sigaGlobalSearchResults" class="siga-global-search-results" aria-live="polite"></div>
                    </form>

                    @php
                        $operationalNotificationCount = \App\Models\NotificacionOperativa::query()
                            ->whereNull('resolved_at')
                            ->count();
                    @endphp
                    <a href="{{ route('admin.notificaciones-operativas.index') }}" class="siga-notification-toolbar-link" title="Notificaciones operativas" aria-label="Notificaciones operativas">
                        <i class="bi bi-bell"></i>
                        <span class="badge bg-light-danger {{ $operationalNotificationCount > 0 ? '' : 'd-none' }}">
                            {{ $operationalNotificationCount }}
                        </span>
                    </a>

                    @can('chat.ver')
                        <a href="{{ route('admin.chat.index') }}" class="siga-chat-toolbar-link" title="Chat interno" aria-label="Chat interno">
                            <i class="bi bi-chat-dots"></i>
                            <span id="chatUnreadBadge" class="badge bg-light-danger {{ $chatUnreadCount > 0 ? '' : 'd-none' }}">
                                {{ $chatUnreadCount }}
                            </span>
                        </a>
                    @endcan

                    <div class="dropdown siga-user-menu">
                        <button class="btn siga-user-menu-toggle dropdown-toggle" type="button"
                            id="sigaUserMenuButton" data-bs-toggle="dropdown" data-bs-display="static"
                            aria-expanded="false" title="{{ Auth::user()->name }}">
                            <i class="bi bi-person-circle"></i>
                            <span>{{ Auth::user()->name }}</span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="sigaUserMenuButton">
                            <div class="dropdown-header">
                                {{ Auth::user()->name }}
                            </div>
                            <div class="dropdown-divider"></div>
                            <a href="{{ route('password.change') }}" class="dropdown-item">
                                <i class="bi bi-key"></i>
                                <span>Cambiar contrasena</span>
                            </a>
                            <button type="submit" form="sidebar-logout-form" class="dropdown-item">
                                <i class="bi bi-box-arrow-right"></i>
                                <span>Cerrar sesion</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="page-content">
                @yield('content')
            </div>

        </div>
        @endif


        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        @php
        $flashData = [
        'success' => session('success'),
        'error' => session('error'),
        'status' => session('status'),
        'resent' => session('resent'),
        ];

        $errors_messages = [];
        if (session()->has('errors') && $errors->any()) {
        foreach ($errors->all() as $message) {
        $errors_messages[] = $message;
        }
        }
        @endphp

        <script type="application/json" id="siga-flash-data">@json($flashData)</script>
        <script type="application/json" id="siga-errors-data">@json($errors_messages)</script>

        <script>
            var __flash = {};
            var __errors = [];

            try {
                __flash = JSON.parse(document.getElementById('siga-flash-data')?.textContent || '{}');
            } catch (e) {
                __flash = {};
            }

            try {
                __errors = JSON.parse(document.getElementById('siga-errors-data')?.textContent || '[]');
            } catch (e) {
                __errors = [];
            }

            document.addEventListener('DOMContentLoaded', function() {
                // Mostrar errores de validación
                if (__errors && Array.isArray(__errors) && __errors.length > 0) {
                    const errorMessages = __errors.join('<br>');
                    Swal.fire({
                        icon: 'error',
                        title: 'Errores de Validación',
                        html: errorMessages,
                        confirmButtonText: 'Aceptar',
                        didClose: function() {
                            // Buscar el modal abierto y enviarlo al frente
                            const openModals = document.querySelectorAll('.modal.show');
                            if (openModals.length > 0) {
                                const lastModal = openModals[openModals.length - 1];
                                lastModal.style.zIndex = 10000;
                            }
                        }
                    });
                    return;
                }

                // Mostrar mensajes flash
                if (__flash.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Éxito!',
                        text: __flash.success,
                        timer: 5000,
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timerProgressBar: true,
                    });
                }

                if (__flash.error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: __flash.error,
                        timer: 5000,
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timerProgressBar: true,
                    });
                }

                if (__flash.status) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Información',
                        text: __flash.status,
                        timer: 5000,
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timerProgressBar: true,
                    });
                }

                if (__flash.resent) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Enviado',
                        text: 'Se ha enviado un nuevo enlace de verificación a tu correo.',
                        timer: 5000,
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timerProgressBar: true,
                    });
                }
            });

            window.confirmFormSubmit = function(form, message, title = 'Confirmar accion') {
                if (!window.Swal) {
                    return window.confirm(message);
                }

                Swal.fire({
                    icon: 'warning',
                    title: title,
                    text: message,
                    showCancelButton: true,
                    confirmButtonText: 'Si, continuar',
                    cancelButtonText: 'Cancelar',
                    reverseButtons: true,
                    focusCancel: true,
                    customClass: {
                        confirmButton: 'btn btn-danger',
                        cancelButton: 'btn btn-light-secondary me-2',
                    },
                    buttonsStyling: false,
                }).then(function(result) {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });

                return false;
            };
        </script>

        <script src="{{ asset('assets/compiled/js/app.js') }}"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const sidebar = document.getElementById('sidebar');
                const menuButtons = document.querySelectorAll('.siga-mobile-menu-btn');

                if (!sidebar || menuButtons.length === 0) {
                    return;
                }

                function isMobileSidebar() {
                    return window.matchMedia('(max-width: 1199px)').matches;
                }

                function removeBackdrop() {
                    document.querySelectorAll('.sidebar-backdrop').forEach(function(backdrop) {
                        backdrop.remove();
                    });
                }

                function closeSidebar() {
                    sidebar.classList.remove('active');
                    document.body.classList.remove('siga-sidebar-open');
                    removeBackdrop();
                }

                function openSidebar() {
                    sidebar.classList.add('active');
                    document.body.classList.add('siga-sidebar-open');

                    if (!document.querySelector('.sidebar-backdrop')) {
                        const backdrop = document.createElement('div');
                        backdrop.className = 'sidebar-backdrop';
                        backdrop.addEventListener('click', closeSidebar);
                        document.body.appendChild(backdrop);
                    }
                }

                function toggleSidebar(event) {
                    if (!isMobileSidebar()) {
                        return;
                    }

                    event.preventDefault();
                    event.stopPropagation();

                    if (sidebar.classList.contains('active')) {
                        closeSidebar();
                    } else {
                        openSidebar();
                    }
                }

                menuButtons.forEach(function(button) {
                    button.addEventListener('click', toggleSidebar);
                });

                sidebar.querySelectorAll('.sidebar-hide').forEach(function(button) {
                    button.addEventListener('click', function(event) {
                        if (!isMobileSidebar()) {
                            return;
                        }

                        event.preventDefault();
                        closeSidebar();
                    });
                });

                sidebar.querySelectorAll('a.sidebar-link[href], a.submenu-link[href]').forEach(function(link) {
                    link.addEventListener('click', function() {
                        if (isMobileSidebar() && link.getAttribute('href') !== '#') {
                            closeSidebar();
                        }
                    });
                });

                window.addEventListener('resize', function() {
                    if (!isMobileSidebar()) {
                        document.body.classList.remove('siga-sidebar-open');
                        removeBackdrop();
                    }
                });
            });
        </script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const sidebar = document.getElementById('sidebar');

                if (!sidebar) {
                    return;
                }

                function submenuHeight(submenu) {
                    let height = 0;

                    submenu.querySelectorAll(':scope > .submenu-item').forEach(function(item) {
                        const link = item.querySelector(':scope > .submenu-link');
                        if (link) {
                            height += link.offsetHeight;
                        }

                        const childSubmenu = item.querySelector(':scope > .submenu');
                        if (childSubmenu && childSubmenu.classList.contains('submenu-open')) {
                            height += submenuHeight(childSubmenu);
                        }
                    });

                    submenu.style.setProperty('--submenu-height', height + 'px');

                    return height;
                }

                function openSubmenu(submenu) {
                    submenu.classList.remove('submenu-closed');
                    submenu.classList.add('submenu-open');
                    submenuHeight(submenu);
                }

                function closeSubmenu(submenu) {
                    submenu.querySelectorAll('.submenu.submenu-open').forEach(closeSubmenu);
                    submenu.classList.remove('submenu-open');
                    submenu.classList.add('submenu-closed');
                    submenu.style.setProperty('--submenu-height', submenuHeight(submenu) + 'px');
                }

                function closeSiblingSubmenus(item) {
                    const parentList = item.parentElement;

                    if (!parentList) {
                        return;
                    }

                    parentList.querySelectorAll(':scope > .has-sub').forEach(function(sibling) {
                        if (sibling === item) {
                            return;
                        }

                        const siblingSubmenu = sibling.querySelector(':scope > .submenu');
                        if (siblingSubmenu) {
                            closeSubmenu(siblingSubmenu);
                        }
                    });
                }

                function toggleItem(item) {
                    const submenu = item.querySelector(':scope > .submenu');

                    if (!submenu) {
                        return;
                    }

                    const isOpen = submenu.classList.contains('submenu-open');
                    closeSiblingSubmenus(item);

                    if (isOpen) {
                        closeSubmenu(submenu);
                    } else {
                        openSubmenu(submenu);
                    }

                    const parentSubmenu = item.parentElement?.closest('.submenu');
                    if (parentSubmenu) {
                        submenuHeight(parentSubmenu);
                    }
                }

                sidebar.addEventListener('click', function(event) {
                    const trigger = event.target.closest('.sidebar-item.has-sub > .sidebar-link, .submenu-item.has-sub > .submenu-link');

                    if (!trigger || !sidebar.contains(trigger)) {
                        return;
                    }

                    event.preventDefault();
                    event.stopPropagation();
                    event.stopImmediatePropagation();
                    toggleItem(trigger.parentElement);
                }, true);

                sidebar.querySelectorAll('.submenu').forEach(function(submenu) {
                    submenuHeight(submenu);
                });

                sidebar.querySelectorAll('.sidebar-item.has-sub.active, .submenu-item.has-sub.active').forEach(function(item) {
                    const submenu = item.querySelector(':scope > .submenu');
                    if (submenu) {
                        closeSiblingSubmenus(item);
                        openSubmenu(submenu);
                    }
                });
            });
        </script>
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const sidebar = document.getElementById('sidebar');

                if (!sidebar) {
                    return;
                }

                sidebar.querySelectorAll('.sidebar-link, .submenu-link').forEach(function(link) {
                    const label = (link.querySelector('span')?.textContent || link.textContent || '').trim();

                    if (!label) {
                        return;
                    }

                    link.setAttribute('title', label);
                    link.setAttribute('aria-label', label);
                });
            });
        </script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const autoDismissMs = 5000;

                document.querySelectorAll('.alert.alert-dismissible').forEach(function(alertElement) {
                    setTimeout(function() {
                        if (!alertElement.isConnected) {
                            return;
                        }

                        if (typeof bootstrap !== 'undefined' && bootstrap.Alert) {
                            bootstrap.Alert.getOrCreateInstance(alertElement).close();
                            return;
                        }

                        alertElement.classList.remove('show');
                        setTimeout(function() {
                            alertElement.remove();
                        }, 150);
                    }, autoDismissMs);
                });
            });
        </script>
        <script>
            window.disableSigaAutocomplete = function(context) {
                if (document.body.classList.contains('auth-theme')) {
                    return;
                }

                const target = context || document;

                target.querySelectorAll('form').forEach(function(form) {
                    if (form.dataset.allowAutocomplete === 'true') {
                        return;
                    }

                    form.setAttribute('autocomplete', 'off');
                });

                target.querySelectorAll('input, textarea').forEach(function(control) {
                    if (control.dataset.allowAutocomplete === 'true') {
                        return;
                    }

                    const type = (control.getAttribute('type') || 'text').toLowerCase();
                    const blockedTypes = ['text', 'search', 'email', 'tel', 'url', 'password'];

                    if (control.matches('textarea') || blockedTypes.includes(type)) {
                        control.setAttribute('autocomplete', 'new-password');
                        control.setAttribute('autocorrect', 'off');
                        control.setAttribute('autocapitalize', 'off');
                        control.setAttribute('spellcheck', 'false');
                    }
                });
            };

            document.addEventListener('DOMContentLoaded', function() {
                window.disableSigaAutocomplete(document);

                const observer = new MutationObserver(function(mutations) {
                    mutations.forEach(function(mutation) {
                        mutation.addedNodes.forEach(function(node) {
                            if (node.nodeType === Node.ELEMENT_NODE) {
                                window.disableSigaAutocomplete(node);
                            }
                        });
                    });
                });

                observer.observe(document.body, {
                    childList: true,
                    subtree: true,
                });
            });
        </script>
        <script>
            (function() {
                const iconRules = [{
                        keywords: ['search', 'buscar'],
                        icon: 'bi-search'
                    },
                    {
                        keywords: ['email', 'correo', 'mail'],
                        icon: 'bi-envelope-fill'
                    },
                    {
                        keywords: ['telefono', 'teléfono', 'phone'],
                        icon: 'bi-telephone-fill'
                    },
                    {
                        keywords: ['direccion', 'dirección', 'address'],
                        icon: 'bi-geo-alt-fill'
                    },
                    {
                        keywords: ['nombre', 'name', 'contacto'],
                        icon: 'bi-person-fill'
                    },
                    {
                        keywords: ['empresa', 'compania', 'compañía', 'cia', 'seguro'],
                        icon: 'bi-building'
                    },
                    {
                        keywords: ['estado', 'status'],
                        icon: 'bi-toggle-on'
                    },
                    {
                        keywords: ['divisa', 'moneda', 'currency'],
                        icon: 'bi-currency-exchange'
                    },
                    {
                        keywords: ['provincia', 'ciudad'],
                        icon: 'bi-map-fill'
                    },
                    {
                        keywords: ['web', 'url', 'sitio'],
                        icon: 'bi-globe'
                    },
                    {
                        keywords: ['descripcion', 'descripción', 'observaciones', 'notas'],
                        icon: 'bi-card-text'
                    },
                    {
                        keywords: ['foto', 'logo', 'imagen', 'file'],
                        icon: 'bi-image-fill'
                    },
                    {
                        keywords: ['dominio', 'patente', 'poliza', 'póliza'],
                        icon: 'bi-credit-card-2-front-fill'
                    },
                    {
                        keywords: ['anio', 'año', 'fabricacion', 'fabricación'],
                        icon: 'bi-calendar-event'
                    },
                    {
                        keywords: ['pasajeros', 'cantidad', 'cant'],
                        icon: 'bi-123'
                    },
                    {
                        keywords: ['motor', 'caja', 'aceite', 'chasis', 'cub'],
                        icon: 'bi-tools'
                    },
                    {
                        keywords: ['marca', 'tipo', 'modelo', 'titular', 'categoria', 'categoría'],
                        icon: 'bi-tags-fill'
                    }
                ];

                function getControlText(control) {
                    const label = control.id ? document.querySelector('label[for="' + CSS.escape(control.id) + '"]') : null;
                    const nearbyLabel = control.closest('.mb-3, .col, [class*="col-"]')?.querySelector('.form-label');

                    return [
                        control.name,
                        control.id,
                        control.placeholder,
                        label?.textContent,
                        nearbyLabel?.textContent
                    ].filter(Boolean).join(' ').toLowerCase();
                }

                function resolveIcon(control) {
                    if (control.type === 'file') {
                        return 'bi-image-fill';
                    }

                    const text = getControlText(control).normalize('NFD').replace(/[\u0300-\u036f]/g, '');
                    const matchedRule = iconRules.find(function(rule) {
                        return rule.keywords.some(function(keyword) {
                            return text.includes(keyword.normalize('NFD').replace(/[\u0300-\u036f]/g, ''));
                        });
                    });

                    return matchedRule ? matchedRule.icon : 'bi-pencil-square';
                }

                window.initSigaInputIcons = function(context) {
                    if (document.body.classList.contains('auth-theme')) {
                        return;
                    }

                    const target = context || document;

                    target.querySelectorAll('input.form-control, textarea.form-control').forEach(function(control) {
                        if (control.closest('.input-group, .form-check, .form-control-icon') || control.dataset.iconDecorated === 'true') {
                            return;
                        }

                        if (['hidden', 'checkbox', 'radio', 'submit', 'button', 'reset'].includes(control.type)) {
                            return;
                        }

                        const group = document.createElement('div');
                        group.className = 'input-group siga-auto-input-icon';

                        const icon = document.createElement('span');
                        icon.className = 'input-group-text';
                        icon.innerHTML = '<i class="bi ' + resolveIcon(control) + '"></i>';

                        control.parentNode.insertBefore(group, control);
                        group.appendChild(icon);
                        group.appendChild(control);
                        control.dataset.iconDecorated = 'true';

                        if (group.nextElementSibling?.classList.contains('invalid-feedback')) {
                            group.appendChild(group.nextElementSibling);
                        }
                    });
                };

                document.addEventListener('DOMContentLoaded', function() {
                    window.initSigaInputIcons(document);
                });
            })();
        </script>
        <script>
            window.initSigaSelect2 = function(context) {
                if (typeof window.jQuery === 'undefined' || typeof window.jQuery.fn.select2 === 'undefined') {
                    return;
                }

                const $ = window.jQuery;
                const target = context || document;

                $(target).find('select.form-select, select.form-control').addBack('select.form-select, select.form-control').each(function() {
                    const $select = $(this);

                    if ($select.hasClass('select2-hidden-accessible') || $select.data('select2Disabled') === true) {
                        return;
                    }

                    const $modal = $select.closest('.modal');

                    $select.select2({
                        theme: 'bootstrap-5',
                        width: '100%',
                        dropdownParent: $modal.length ? $modal : $(document.body),
                        placeholder: $select.data('placeholder') || 'Seleccione una opcion',
                        allowClear: !$select.prop('required')
                    });
                });
            };

            document.addEventListener('DOMContentLoaded', function() {
                window.initSigaSelect2(document);

                document.querySelectorAll('.modal').forEach(function(modal) {
                    modal.addEventListener('shown.bs.modal', function() {
                        window.initSigaSelect2(modal);
                    });
                });
            });
        </script>
        @can('chat.ver')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const badge = document.getElementById('chatUnreadBadge');

                if (!badge) {
                    return;
                }

                let previousCount = Number.parseInt(badge.textContent, 10) || 0;

                function updateBadge(count) {
                    badge.textContent = count;
                    badge.classList.toggle('d-none', count <= 0);
                }

                function notifyLatest(latest) {
                    if (!latest || !window.Swal) {
                        return;
                    }

                    Swal.fire({
                        icon: 'info',
                        title: 'Nuevo mensaje',
                        text: latest.from ? latest.from + ': ' + latest.message : latest.message,
                        timer: 5000,
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: true,
                        confirmButtonText: 'Abrir',
                    }).then(function(result) {
                        if (result.isConfirmed && latest.url) {
                            window.location.href = latest.url;
                        }
                    });
                }

                function refreshChatUnread() {
                    if (typeof window.fetch !== 'function') {
                        return;
                    }

                    fetch("{{ route('admin.chat.unread') }}", {
                        headers: {
                            'Accept': 'application/json'
                        }
                    })
                        .then(function(response) {
                            if (response.status === 401 || response.status === 403) {
                                return null;
                            }

                            return response.ok ? response.json() : null;
                        })
                        .then(function(data) {
                            if (!data) {
                                return;
                            }

                            const nextCount = Number.parseInt(data.count, 10) || 0;

                            if (nextCount > previousCount) {
                                notifyLatest(data.latest);
                            }

                            previousCount = nextCount;
                            updateBadge(nextCount);
                        })
                        .catch(function() {});
                }

                setInterval(refreshChatUnread, 15000);
            });
        </script>
        @endcan

        @php
            $sigaPrintAjuste = \App\Models\Ajuste::query()->first();
            $sigaPrintProvincia = $sigaPrintAjuste?->provincia_id ? \App\Models\Provincia::find($sigaPrintAjuste->provincia_id) : null;
            $sigaPrintCiudad = $sigaPrintAjuste?->ciudad_id ? \App\Models\Ciudad::find($sigaPrintAjuste->ciudad_id) : null;
            $sigaPrintEmpresa = [
                'nombre' => $sigaPrintAjuste?->nombre ?: config('app.name', 'SIGA'),
                'descripcion' => $sigaPrintAjuste?->descripcion,
                'direccion' => $sigaPrintAjuste?->direccion,
                'telefono' => $sigaPrintAjuste?->telefono,
                'email' => $sigaPrintAjuste?->email,
                'web' => $sigaPrintAjuste?->web,
                'localidad' => collect([$sigaPrintCiudad?->nombre, $sigaPrintProvincia?->nombre])->filter()->implode(', '),
                'logo' => $sigaPrintAjuste?->logo ? asset('storage/' . $sigaPrintAjuste->logo) : null,
            ];
        @endphp
        <style>
            .siga-page-print-company {
                display: none;
            }

            @media print {
                .siga-page-print-company {
                    display: flex !important;
                    align-items: flex-start;
                    gap: 12px;
                    margin-bottom: 12px;
                    padding-bottom: 10px;
                    border-bottom: 1px solid #d1d5db;
                }

                .siga-page-print-company img {
                    width: 58px;
                    height: 58px;
                    object-fit: contain;
                }

                .siga-page-print-company h1 {
                    font-size: 17px;
                    margin: 0 0 3px;
                    color: #1f2937;
                    text-align: left;
                }

                .siga-page-print-company p {
                    font-size: 10px;
                    line-height: 1.35;
                    margin: 0 0 2px;
                    color: #4b5563;
                    text-align: left;
                }
            }
        </style>
        <script type="application/json" id="siga-print-company-data">@json($sigaPrintEmpresa)</script>
        <script>
            window.SigaPrintCompany = {};

            try {
                window.SigaPrintCompany = JSON.parse(document.getElementById('siga-print-company-data')?.textContent || '{}');
            } catch (e) {
                window.SigaPrintCompany = {};
            }

            window.sigaPrintCompanyStyles = function() {
                return `
                    .siga-print-company{display:flex;align-items:flex-start;gap:12px;margin-bottom:12px;padding-bottom:10px;border-bottom:1px solid #d1d5db;}
                    .siga-print-company img{width:58px;height:58px;object-fit:contain;}
                    .siga-print-company h1{font-size:17px;margin:0 0 3px;color:#1f2937;text-align:left;}
                    .siga-print-company p{font-size:10px;line-height:1.35;margin:0 0 2px;color:#4b5563;text-align:left;}
                `;
            };
            window.sigaPrintEscape = function(value) {
                return String(value || '').replace(/[&<>"']/g, function(character) {
                    return {
                        '&': '&amp;',
                        '<': '&lt;',
                        '>': '&gt;',
                        '"': '&quot;',
                        "'": '&#039;'
                    }[character];
                });
            };
            window.sigaPrintCompanyHeader = function(extraClass) {
                const empresa = window.SigaPrintCompany || {};
                const escape = window.sigaPrintEscape;
                const details = [
                    [empresa.direccion, empresa.localidad].filter(Boolean).join(' - '),
                    empresa.telefono ? 'Tel: ' + empresa.telefono : '',
                    empresa.email || '',
                    empresa.web || ''
                ].filter(Boolean).join(' | ');
                const className = ['siga-print-company', extraClass || ''].filter(Boolean).join(' ');

                return `
                    <div class="${className}">
                        ${empresa.logo ? '<img src="' + escape(empresa.logo) + '" alt="' + escape(empresa.nombre || '') + '">' : ''}
                        <div>
                            <h1>${escape(empresa.nombre || 'SIGA')}</h1>
                            ${empresa.descripcion ? '<p>' + escape(empresa.descripcion) + '</p>' : ''}
                            ${details ? '<p>' + escape(details) + '</p>' : ''}
                        </div>
                    </div>
                `;
            };
            window.sigaMountPrintCompanyHeader = function(target) {
                const element = typeof target === 'string' ? document.querySelector(target) : target;

                if (element) {
                    element.innerHTML = window.sigaPrintCompanyHeader('siga-page-print-company');
                }
            };

            window.downloadCSV = window.downloadCSV || function(csv, filename) {
                const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
                const link = document.createElement('a');
                const url = URL.createObjectURL(blob);
                link.setAttribute('href', url);
                link.setAttribute('download', filename + '.csv');
                link.style.visibility = 'hidden';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            };

            window.downloadCSVFromTable = window.downloadCSVFromTable || function(tableId, filename) {
                const table = document.getElementById(tableId);

                if (!table) {
                    return;
                }

                const csvRows = [];
                const headers = Array.from(table.querySelectorAll('thead th')).map(function(header) {
                    return '"' + header.innerText.trim().replace(/"/g, '""') + '"';
                });
                csvRows.push(headers.join(','));

                Array.from(table.querySelectorAll('tbody tr')).forEach(function(row) {
                    const cols = Array.from(row.querySelectorAll('td')).map(function(td) {
                        return '"' + td.innerText.trim().replace(/"/g, '""') + '"';
                    });
                    csvRows.push(cols.join(','));
                });

                window.downloadCSV(csvRows.join('\n'), filename);
            };

            window.exportTableToExcel = window.exportTableToExcel || function(tableId, filename) {
                const table = document.getElementById(tableId);

                if (!table) {
                    return;
                }

                const html = table.outerHTML.replace(/ /g, '%20');
                const link = document.createElement('a');
                link.href = 'data:application/vnd.ms-excel,' + html;
                link.download = filename + '.xls';
                link.click();
            };

            window.createPDF = window.createPDF || function(tableId, filename) {
                const table = document.getElementById(tableId);

                if (!table) {
                    return;
                }

                const style = '<style>table{width:100%;border-collapse:collapse;font-family:Arial,sans-serif;font-size:12px;}th,td{border:1px solid #ddd;padding:6px;text-align:left;}th{background:#f3f4f6;}h1{font-size:18px;margin:0 0 12px;text-align:center;}</style>';
                const printWindow = window.open('', '_blank');

                printWindow.document.write('<html><head><title>' + filename + '</title>' + style + '<style>' + window.sigaPrintCompanyStyles() + '</style></head><body>' + window.sigaPrintCompanyHeader());
                printWindow.document.write('<h1>' + filename.replace(/_/g, ' ') + '</h1>');
                printWindow.document.write(table.outerHTML);
                printWindow.document.write('</body></html>');
                printWindow.document.close();
                printWindow.focus();
                printWindow.print();
            };

            window.printTable = window.printTable || function(tableId) {
                const table = document.getElementById(tableId);

                if (!table) {
                    return;
                }

                const style = '<style>table{width:100%;border-collapse:collapse;font-family:Arial,sans-serif;font-size:12px;}th,td{border:1px solid #ddd;padding:6px;text-align:left;}th{background:#f3f4f6;}</style>';
                const printWindow = window.open('', '_blank');

                printWindow.document.write('<html><head><title>Imprimir</title>' + style + '<style>' + window.sigaPrintCompanyStyles() + '</style></head><body>' + window.sigaPrintCompanyHeader());
                printWindow.document.write(table.outerHTML);
                printWindow.document.write('</body></html>');
                printWindow.document.close();
                printWindow.focus();
                printWindow.print();
            };
        </script>

        <script type="application/json" id="siga-denied-actions-data">@json($deniedRouteActions ?? [])</script>
        <script type="application/json" id="siga-user-permissions-data">@json(auth()->user()?->getAllPermissions()->pluck('name')->values() ?? [])</script>
        <script>
            document.getElementById('sidebar-logout-form')?.addEventListener('submit', function() {
                window.SigaClearBrowserState?.();
            });

            window.SigaDeniedRouteActions = [];
            window.SigaUserPermissions = [];

            try {
                window.SigaDeniedRouteActions = JSON.parse(document.getElementById('siga-denied-actions-data')?.textContent || '[]');
            } catch (e) {
                window.SigaDeniedRouteActions = [];
            }

            try {
                window.SigaUserPermissions = JSON.parse(document.getElementById('siga-user-permissions-data')?.textContent || '[]');
            } catch (e) {
                window.SigaUserPermissions = [];
            }

            window.sigaCan = function(permission) {
                return Array.isArray(window.SigaUserPermissions) && window.SigaUserPermissions.includes(permission);
            };

            document.addEventListener('DOMContentLoaded', function() {
                const deniedActions = Array.isArray(window.SigaDeniedRouteActions)
                    ? window.SigaDeniedRouteActions
                    : [];

                if (deniedActions.length === 0) {
                    return;
                }

                const compiledActions = deniedActions.map(function(action) {
                    return {
                        name: action.name,
                        methods: (action.methods || []).map(function(method) {
                            return String(method).toUpperCase();
                        }),
                        pattern: new RegExp(action.pattern),
                    };
                });

                function pathFromUrl(url) {
                    try {
                        return new URL(url, window.location.href).pathname;
                    } catch (error) {
                        return '';
                    }
                }

                function formMethod(form) {
                    if (!form) {
                        return 'GET';
                    }

                    const spoofedMethod = form.querySelector('input[name="_method"]');

                    return (spoofedMethod?.value || form.getAttribute('method') || 'GET').toUpperCase();
                }

                function deniedByRoute(url, method) {
                    const path = pathFromUrl(url);
                    const normalizedMethod = String(method || 'GET').toUpperCase();

                    if (!path) {
                        return null;
                    }

                    return compiledActions.find(function(action) {
                        return action.methods.includes(normalizedMethod) && action.pattern.test(path);
                    }) || null;
                }

                function modalFormFor(control) {
                    const selector = control.getAttribute('data-bs-target') || control.getAttribute('href');

                    if (!selector || selector.charAt(0) !== '#') {
                        return null;
                    }

                    try {
                        return document.querySelector(selector)?.querySelector('form[action]') || null;
                    } catch (error) {
                        return null;
                    }
                }

                function deniedActionForControl(control) {
                    if (control.matches('form[action]')) {
                        return deniedByRoute(control.action, formMethod(control));
                    }

                    const closestForm = control.closest('form[action]');

                    if (closestForm) {
                        return deniedByRoute(closestForm.action, formMethod(closestForm));
                    }

                    if (control.matches('a[href]') && !control.getAttribute('href').startsWith('#')) {
                        return deniedByRoute(control.href, 'GET');
                    }

                    const modalForm = modalFormFor(control);

                    if (modalForm) {
                        return deniedByRoute(modalForm.action, formMethod(modalForm));
                    }

                    return null;
                }

                function disableControl(control, deniedAction) {
                    const title = 'No tienes permiso para realizar esta accion.';

                    control.classList.add('disabled');
                    control.setAttribute('aria-disabled', 'true');
                    control.setAttribute('title', title);
                    control.dataset.deniedRoute = deniedAction.name || '';

                    if (control.matches('a[href]')) {
                        control.dataset.originalHref = control.getAttribute('href');
                        control.removeAttribute('href');
                        control.setAttribute('tabindex', '-1');
                    }

                    if (control.matches('button, input, select, textarea')) {
                        control.disabled = true;
                    }
                }

                function protectForm(form, deniedAction) {
                    form.dataset.deniedRoute = deniedAction.name || '';
                    form.addEventListener('submit', function(event) {
                        event.preventDefault();
                    });

                    form.querySelectorAll('button[type="submit"], input[type="submit"]').forEach(function(submitControl) {
                        disableControl(submitControl, deniedAction);
                    });
                }

                function actionColumnIndexes(table) {
                    const headerRow = table.tHead?.rows[0];

                    if (!headerRow) {
                        return [];
                    }

                    return Array.from(headerRow.cells)
                        .map(function(cell, index) {
                            return cell.textContent.trim().toLowerCase() === 'acciones' ? index : -1;
                        })
                        .filter(function(index) {
                            return index >= 0;
                        });
                }

                document.querySelectorAll('table').forEach(function(table) {
                    const indexes = actionColumnIndexes(table);

                    if (indexes.length === 0 || !table.tBodies.length) {
                        return;
                    }

                    Array.from(table.tBodies[0].rows).forEach(function(row) {
                        indexes.forEach(function(index) {
                            const cell = row.cells[index];

                            if (!cell) {
                                return;
                            }

                            cell.querySelectorAll('form[action]').forEach(function(form) {
                                const deniedAction = deniedActionForControl(form);

                                if (deniedAction) {
                                    protectForm(form, deniedAction);
                                }
                            });

                            cell.querySelectorAll('a[href], button, input[type="submit"]').forEach(function(control) {
                                const deniedAction = deniedActionForControl(control);

                                if (deniedAction) {
                                    disableControl(control, deniedAction);
                                }
                            });
                        });
                    });
                });
            });
        </script>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const path = window.location.pathname.toLowerCase();
                const routeModuleMap = {
                    '/admin/inventarios/transferencias': 'inventario-transferencias',
                    '/admin/flota/repuestos': 'flota-repuestos',
                    '/admin/solicitudes-repuestos': 'solicitudes-repuestos',
                    '/admin/cronogramas-laborales': 'cronogramas',
                    '/admin/cias_seguro': 'cia-seguro',
                    '/admin/unidad-medidas': 'unidad-medidas',
                    '/admin/configuracion-intervalos-servicio': 'configuracion-intervalos-servicio',
                    '/admin/configuracion-vencimientos-verificacion': 'configuracion-vencimientos-verificacion',
                };

                function currentModule() {
                    const mapped = Object.keys(routeModuleMap)
                        .sort((a, b) => b.length - a.length)
                        .find(prefix => path.includes(prefix));

                    if (mapped) {
                        return routeModuleMap[mapped];
                    }

                    const match = path.match(/\/admin\/([^\/]+)/);
                    return match ? match[1] : null;
                }

                function hideControl(control) {
                    control.classList.add('d-none');
                    control.setAttribute('aria-hidden', 'true');
                    control.setAttribute('tabindex', '-1');
                }

                const module = currentModule();

                if (!module || typeof window.sigaCan !== 'function') {
                    return;
                }

                document.querySelectorAll('button, a').forEach(function(control) {
                    const text = (control.textContent || control.getAttribute('title') || '').trim().toLowerCase();

                    if (!text) {
                        return;
                    }

                    if ((text.includes('exportar') || text.includes('crear pdf')) && !window.sigaCan(module + '.exportar')) {
                        hideControl(control);
                    }

                    if (text.includes('imprimir') && !window.sigaCan(module + '.imprimir')) {
                        hideControl(control);
                    }
                });
            });
        </script>



        <script src="{{ asset('assets/extensions/apexcharts/apexcharts.min.js') }}"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                document.querySelectorAll('table#datatable').forEach(function(table) {
                    const container = table.closest('.table-responsive') || table.parentElement;
                    if (!container) {
                        return;
                    }

                    if (container.previousElementSibling?.classList.contains('datatable-search-wrapper') || container.querySelector('.datatable-search-wrapper')) {
                        return;
                    }

                    const card = table.closest('.card');
                    if (card) {
                        const searchForm = card.querySelector('form[method="GET"]');
                        if (searchForm) {
                            return;
                        }
                    }

                    const searchWrapper = document.createElement('div');
                    searchWrapper.className = 'datatable-search-wrapper mb-3';
                    searchWrapper.innerHTML = `
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="search" class="form-control form-control-sm datatable-search" placeholder="Buscar en la tabla...">
                        </div>
                    `;

                    container.parentNode.insertBefore(searchWrapper, container);

                    const input = searchWrapper.querySelector('.datatable-search');
                    const tbody = table.tBodies[0];
                    if (!tbody) {
                        return;
                    }

                    const rows = Array.from(tbody.rows);
                    const originalData = rows.map(function(row) {
                        return {
                            row: row,
                            text: row.textContent.trim().toLowerCase().replace(/\s+/g, ' '),
                        };
                    });

                    const noResultsRow = document.createElement('tr');
                    noResultsRow.className = 'datatable-no-results text-muted';
                    noResultsRow.innerHTML = `<td colspan="${table.tHead?.rows[0]?.cells.length || 1}" class="text-center py-4">No se encontraron registros.</td>`;

                    function toggleNoResults(show) {
                        if (show) {
                            if (!tbody.contains(noResultsRow)) {
                                tbody.appendChild(noResultsRow);
                            }
                        } else {
                            if (tbody.contains(noResultsRow)) {
                                tbody.removeChild(noResultsRow);
                            }
                        }
                    }

                    let searchTimeout = null;

                    input.addEventListener('input', function() {
                        if (searchTimeout) {
                            window.clearTimeout(searchTimeout);
                        }

                        searchTimeout = window.setTimeout(function() {
                            const query = input.value.trim().toLowerCase();
                            let visibleCount = 0;

                            if (query === '') {
                                originalData.forEach(function(item) {
                                    item.row.style.display = '';
                                });
                                visibleCount = originalData.length;
                            } else {
                                originalData.forEach(function(item) {
                                    const match = item.text.indexOf(query) !== -1;
                                    item.row.style.display = match ? '' : 'none';
                                    if (match) {
                                        visibleCount += 1;
                                    }
                                });
                            }

                            toggleNoResults(visibleCount === 0);
                        }, 120);
                    });
                });
            });
        </script>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const input = document.getElementById('sigaGlobalSearchInput');
                const resultsBox = document.getElementById('sigaGlobalSearchResults');

                if (!input || !resultsBox) {
                    return;
                }

                const suggestUrl = @json(route('admin.global-search.suggest'));
                let timeoutId = null;
                let controller = null;

                function hideResults() {
                    resultsBox.classList.remove('show');
                    resultsBox.innerHTML = '';
                }

                function renderResults(items, query) {
                    if (!items.length) {
                        resultsBox.innerHTML = `<div class="p-3 text-muted">Sin resultados para "${query}".</div>`;
                        resultsBox.classList.add('show');
                        return;
                    }

                    resultsBox.innerHTML = items.map(function(item) {
                        return `
                            <a href="${item.url}">
                                <span class="btn btn-sm btn-light-primary flex-shrink-0"><i class="bi ${item.icon}"></i></span>
                                <span class="min-width-0">
                                    <strong class="d-block">${escapeHtml(item.title)}</strong>
                                    <small class="text-muted">${escapeHtml(item.module)} · ${escapeHtml(item.subtitle || '')}</small>
                                </span>
                            </a>
                        `;
                    }).join('');
                    resultsBox.classList.add('show');
                }

                function escapeHtml(value) {
                    return String(value ?? '')
                        .replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;')
                        .replace(/"/g, '&quot;')
                        .replace(/'/g, '&#039;');
                }

                input.addEventListener('input', function() {
                    const query = input.value.trim();

                    if (timeoutId) {
                        window.clearTimeout(timeoutId);
                    }

                    if (controller) {
                        controller.abort();
                    }

                    if (query.length < 2) {
                        hideResults();
                        return;
                    }

                    timeoutId = window.setTimeout(function() {
                        controller = new AbortController();

                        fetch(`${suggestUrl}?q=${encodeURIComponent(query)}`, {
                            headers: {
                                'Accept': 'application/json',
                            },
                            signal: controller.signal,
                        })
                            .then(function(response) {
                                return response.ok ? response.json() : { results: [] };
                            })
                            .then(function(payload) {
                                renderResults(payload.results || [], query);
                            })
                            .catch(function(error) {
                                if (error.name !== 'AbortError') {
                                    hideResults();
                                }
                            });
                    }, 180);
                });

                document.addEventListener('click', function(event) {
                    if (!resultsBox.contains(event.target) && event.target !== input) {
                        hideResults();
                    }
                });

                input.addEventListener('keydown', function(event) {
                    if (event.key === 'Escape') {
                        hideResults();
                    }
                });
            });
        </script>

        @stack('scripts')
</body>

</html>
