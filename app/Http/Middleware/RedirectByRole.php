<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectByRole
{
    public function handle(Request $request, Closure $next): Response
    {
        // Si el usuario está autenticado, decidimos su camino
        if (auth()->check()) {
            $user = auth()->user();

            // 🛡️ Si es Administrador (role_id = 1), continúa al dashboard original
            if ($user->role_id === 1) {
                return $next($request);
            }

            // 🚚 Si es Operador/Chofer (role_id = 2), lo desviamos a su pantalla móvil
            if ($user->role_id === 2) {
                return redirect()->route('operator.dashboard');
            }
        }

        return $next($request);
    }
}