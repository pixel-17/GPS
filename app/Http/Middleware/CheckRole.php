<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // 1. Verificar si el usuario está logueado
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // 2. Verificar si el rol del usuario coincide con el requerido por la ruta
        // Accedemos a la relación 'role' que configuramos en el modelo User
        if (auth()->user()->role && auth()->user()->role->name === $role) {
            return $next($request);
        }

        // 3. Si no tiene el rol, abortamos con un error 403 (No autorizado)
        abort(403, 'No tienes permisos para acceder a esta sección.');
    }
}