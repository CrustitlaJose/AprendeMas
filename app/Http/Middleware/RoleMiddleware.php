<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, $role): Response
    {
        // Verifica si el usuario tiene sesión iniciada y si su rol coincide con el parámetro exigido
        if (!Auth::check() || Auth::user()->rol !== $role) {
            // Si falla, lanza un error 403 (Prohibido) o redirígelo a su panel correspondiente
            abort(403, 'Acceso denegado: No cuentas con los privilegios necesarios.');
        }

        return $next($request);
    }
}