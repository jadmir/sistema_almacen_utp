<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\JWTService;
use Symfony\Component\HttpFoundation\Response;

class JWTAuth
{
    protected JWTService $jwtService;

    public function __construct(JWTService $jwtService)
    {
        $this->jwtService = $jwtService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $this->jwtService->getTokenFromRequest();

        if (!$token) {
            return response()->json([
                'message' => 'Token no proporcionado'
            ], 401);
        }

        try {
            $decoded = $this->jwtService->verifyToken($token);
            
            // Agregar los datos decodificados a la request
            $request->attributes->add(['jwt_user' => $decoded]);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Token inválido o expirado',
                'error' => $e->getMessage()
            ], 401);
        }

        return $next($request);
    }
}
