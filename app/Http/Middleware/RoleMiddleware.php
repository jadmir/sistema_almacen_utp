<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     * 
     * @param array|string $roles Rol o roles permitidos
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $jwtUser = $request->attributes->get('jwt_user');

        if (!$jwtUser) {
            return response()->json([
                'message' => 'No autenticado'
            ], 401);
        }

        // Verificar si el rol del usuario está en los roles permitidos
        if (!in_array($jwtUser->rol_nombre, $roles)) {
            return response()->json([
                'message' => 'No tienes permisos para acceder a este recurso',
                'required_roles' => $roles,
                'your_role' => $jwtUser->rol_nombre
            ], 403);
        }

        return $next($request);
    }
}
