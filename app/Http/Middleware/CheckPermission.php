<?php

namespace App\Http\Middleware;

use App\Support\SystemPermissions;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, ?string $permission = null): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        if ($user->isSuperUsuario()) {
            return $next($request);
        }

        $routePermission = SystemPermissions::routePermission($request->route()?->getName());
        $requiredPermission = $routePermission ?? $permission;

        if ($requiredPermission && ! $user->can($requiredPermission)) {
            abort(403, 'No tienes permiso para realizar esta accion.');
        }

        return $next($request);
    }
}
