<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Services\JWTService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Exception;

class AuthController extends Controller
{
    protected JWTService $jwtService;

    public function __construct(JWTService $jwtService)
    {
        $this->jwtService = $jwtService;
    }

    /**
     * Login de usuario
     */
    public function login(Request $request)
    {
        try {
            // Validar datos de entrada
            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            // Buscar usuario por email
            $user = User::where('email', $request->email)->first();

            // Verificar si el usuario existe
            if (!$user) {
                return response()->json([
                    'message' => 'El correo electrónico no está registrado',
                    'errors' => [
                        'email' => ['El correo electrónico no existe en el sistema']
                    ]
                ], 401);
            }

            // Verificar si el usuario está activo
            if (!$user->estado) {
                return response()->json([
                    'message' => 'Usuario inactivo',
                    'errors' => [
                        'email' => ['Tu cuenta está desactivada. Contacta al administrador']
                    ]
                ], 403);
            }

            // Verificar la contraseña
            if (!Hash::check($request->password, $user->password)) {
                return response()->json([
                    'message' => 'Contraseña incorrecta',
                    'errors' => [
                        'password' => ['La contraseña proporcionada es incorrecta']
                    ]
                ], 401);
            }

            // Cargar relación con role
            $user->load('role');

            // Generar token JWT
            $token = $this->jwtService->generateToken([
                'user_id' => $user->id,
                'email' => $user->email,
                'rol_id' => $user->rol_id,
                'rol_nombre' => $user->role->nombre,
            ]);

            return response()->json([
                'message' => 'Login exitoso',
                'token' => $token,
                'token_type' => 'Bearer',
                'user' => [
                    'id' => $user->id,
                    'nombre' => $user->nombre,
                    'email' => $user->email,
                    'rol' => $user->role->nombre,
                ],
            ], 200);

        } catch (ValidationException $e) {
            // Errores de validación
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);

        } catch (Exception $e) {
            // Error general
            return response()->json([
                'message' => 'Error en el servidor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener usuario autenticado
     */
    public function me(Request $request)
    {
        try {
            $jwtUser = $request->attributes->get('jwt_user');
            
            $user = User::with('role')->find($jwtUser->user_id);

            if (!$user || !$user->estado) {
                return response()->json([
                    'message' => 'Usuario no encontrado o inactivo'
                ], 404);
            }

            return response()->json([
                'user' => [
                    'id' => $user->id,
                    'nombre' => $user->nombre,
                    'email' => $user->email,
                    'estado' => $user->estado,
                    'rol' => [
                        'id' => $user->role->id,
                        'nombre' => $user->role->nombre,
                        'descripcion' => $user->role->descripcion,
                    ],
                ],
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al obtener información del usuario',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Logout (invalidar token - en este caso solo responder OK)
     */
    public function logout()
    {
        try {
            return response()->json([
                'message' => 'Logout exitoso'
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al cerrar sesión',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
