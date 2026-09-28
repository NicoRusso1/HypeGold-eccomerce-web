<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Permite continuar solo si el usuario autenticado tiene rol de administrador.
     * Se registra bajo el alias "admin" en bootstrap/app.php.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->isAdmin()) {
            abort(403, 'No tenés permisos de administrador para realizar esta acción.');
        }

        return $next($request);
    }
}
