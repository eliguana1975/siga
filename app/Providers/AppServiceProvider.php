<?php

namespace App\Providers;

use App\Models\ChatMensaje;
use App\Services\BitacoraService;
use App\Support\SystemPermissions;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route as RouteFacade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        Gate::before(function ($user): ?bool {
            return $user->isSuperUsuario() ? true : null;
        });

        Event::listen('eloquent.updating: *', function (string $eventName, array $data): void {
            BitacoraService::recordarOriginal($data[0]);
        });

        Event::listen('eloquent.created: *', function (string $eventName, array $data): void {
            BitacoraService::registrarModelo('crear', $data[0]);
        });

        Event::listen('eloquent.updated: *', function (string $eventName, array $data): void {
            BitacoraService::registrarModelo('editar', $data[0]);
        });

        Event::listen('eloquent.deleted: *', function (string $eventName, array $data): void {
            BitacoraService::registrarModelo('eliminar', $data[0]);
        });

        Event::listen(Login::class, function (Login $event): void {
            BitacoraService::registrar(
                accion: 'login',
                descripcion: $event->user->name . ' inicio sesion',
                metadata: ['user_id' => $event->user->id, 'email' => $event->user->email],
                modulo: 'autenticacion',
            );
        });

        Event::listen(Logout::class, function (Logout $event): void {
            BitacoraService::registrar(
                accion: 'logout',
                descripcion: ($event->user?->name ?? 'Usuario') . ' cerro sesion',
                metadata: ['user_id' => $event->user?->id, 'email' => $event->user?->email],
                modulo: 'autenticacion',
            );
        });

        Event::listen(Failed::class, function (Failed $event): void {
            BitacoraService::registrar(
                accion: 'login_fallido',
                descripcion: 'Intento de inicio de sesion fallido para ' . ($event->credentials['email'] ?? 'usuario desconocido'),
                metadata: ['email' => $event->credentials['email'] ?? null],
                modulo: 'autenticacion',
            );
        });

        View::composer('layouts.admin', function ($view) {
            $chatUnreadCount = Auth::check()
                ? ChatMensaje::where('receptor_id', Auth::id())->whereNull('leido_at')->count()
                : 0;

            $deniedRouteActions = [];
            $user = Auth::user();

            if ($user && ! $user->isSuperUsuario()) {
                foreach (RouteFacade::getRoutes() as $route) {
                    $routeName = $route->getName();
                    $permission = SystemPermissions::routePermission($routeName);

                    if (! $routeName || ! $permission || $user->can($permission)) {
                        continue;
                    }

                    $methods = array_values(array_filter(
                        $route->methods(),
                        fn (string $method): bool => $method !== 'HEAD'
                    ));

                    $deniedRouteActions[] = [
                        'name' => $routeName,
                        'methods' => $methods,
                        'pattern' => $this->routeUriPattern($route->uri()),
                    ];
                }
            }

            $view->with([
                'chatUnreadCount' => $chatUnreadCount,
                'deniedRouteActions' => $deniedRouteActions,
            ]);
        });
    }

    private function routeUriPattern(string $uri): string
    {
        $uri = trim($uri, '/');

        if ($uri === '') {
            return '^/$';
        }

        $quoted = preg_quote($uri, '#');
        $quoted = preg_replace('#\\\\\{[^/]+\\\\\}#', '[^/]+', $quoted);

        return '(?:^|/)' . $quoted . '/?$';
    }
}
