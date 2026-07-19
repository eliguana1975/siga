<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordChanged
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->requiresPasswordChange()) {
            return $next($request);
        }

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => 'Debes cambiar tu contraseña antes de continuar.',
            ], 423);
        }

        if ($request->routeIs('password.change', 'password.change.update', 'logout', 'session.user')) {
            return $next($request);
        }

        return redirect()
            ->route('password.change')
            ->with('warning', 'Por seguridad debes cambiar tu contraseña antes de continuar.');
    }
}
