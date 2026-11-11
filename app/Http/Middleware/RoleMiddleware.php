<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = Auth::user();

        if ($user === null || $user->rol !== $role || $user->estado !== 'Activo') {
            abort(403, 'Acceso denegado: No cuentas con los privilegios necesarios.');
        }

        return $next($request);
    }
}
