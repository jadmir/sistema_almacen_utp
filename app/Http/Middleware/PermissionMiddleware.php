<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;

class PermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $permission  Slug del permiso requerido
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $jwtUser = $request->attributes->get('jwt_user');
        
        if (!$jwtUser) {
            return response()->json([
                'message' => 'No autenticado'
            ], 401);
        }

        // Cargar usuario con relaciones
        $user = User::with(['role.permissions', 'permissions'])->find($jwtUser->user_id);

        if (!$user || !$user->estado) {
            return response()->json([
                'message' => 'Usuario no encontrado o inactivo'
            ], 403);
        }

        // Admin tiene acceso a todo
        if ($user->role->nombre === 'Admin') {
            return $next($request);
        }

        // Verificar si tiene el permiso (del rol o individual)
        if ($user->hasPermission($permission)) {
            return $next($request);
        }

        return response()->json([
            'message' => 'No tienes permisos para acceder a este recurso'
        ], 403);
    }
}
