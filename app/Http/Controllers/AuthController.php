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

            // Cargar relaciones
            $user->load(['role.permissions', 'permissions']);

            // Verificar si debe cambiar contraseña
            if ($user->debe_cambiar_password) {
                // Generar token temporal para cambio de contraseña
                $token = $this->jwtService->generateToken([
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'rol_id' => $user->rol_id,
                    'rol_nombre' => $user->role->nombre,
                ]);

                return response()->json([
                    'message' => 'Debe cambiar su contraseña',
                    'debe_cambiar_password' => true,
                    'token' => $token,
                    'token_type' => 'Bearer',
                    'user' => [
                        'id' => $user->id,
                        'nombre' => $user->nombre,
                        'email' => $user->email,
                    ],
                ], 200);
            }

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
                    'permissions' => $user->permissions, // Permisos individuales
                    'role' => [
                        'id' => $user->role->id,
                        'nombre' => $user->role->nombre,
                        'permissions' => $user->role->permissions, // Permisos del rol
                    ],
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
            
            $user = User::with(['role.permissions', 'permissions'])->find($jwtUser->user_id);

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
                    'rol' => $user->role->nombre,
                    'permissions' => $user->permissions, // Permisos individuales
                    'role' => [
                        'id' => $user->role->id,
                        'nombre' => $user->role->nombre,
                        'descripcion' => $user->role->descripcion,
                        'permissions' => $user->role->permissions, // Permisos del rol
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

    /**
     * Refrescar permisos del usuario actual (sin relogin)
     */
    public function refreshPermissions(Request $request)
    {
        try {
            $jwtUser = $request->attributes->get('jwt_user');
            
            $user = User::with(['role.permissions', 'permissions'])->find($jwtUser->user_id);

            if (!$user) {
                return response()->json([
                    'message' => 'Usuario no encontrado'
                ], 404);
            }

            // Verificar si el usuario fue desactivado
            if (!$user->estado) {
                return response()->json([
                    'message' => 'Usuario desactivado',
                    'logout_required' => true
                ], 403);
            }

            return response()->json([
                'message' => 'Permisos actualizados',
                'user' => [
                    'id' => $user->id,
                    'nombre' => $user->nombre,
                    'email' => $user->email,
                    'estado' => $user->estado,
                    'rol' => $user->role->nombre,
                    'permissions' => $user->permissions, // Permisos individuales actualizados
                    'role' => [
                        'id' => $user->role->id,
                        'nombre' => $user->role->nombre,
                        'descripcion' => $user->role->descripcion,
                        'permissions' => $user->role->permissions, // Permisos del rol actualizados
                    ],
                ],
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al refrescar permisos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cambiar contraseña (obligatorio en primer login)
     */
    public function cambiarPassword(Request $request)
    {
        try {
            $request->validate([
                'password_actual' => 'required|string',
                'password_nueva' => 'required|string|min:8|max:50|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
                'password_confirmacion' => 'required|same:password_nueva',
            ], [
                'password_actual.required' => 'La contraseña actual es obligatoria',
                'password_nueva.required' => 'La nueva contraseña es obligatoria',
                'password_nueva.min' => 'La nueva contraseña debe tener al menos 8 caracteres',
                'password_nueva.max' => 'La nueva contraseña no puede exceder 50 caracteres',
                'password_nueva.regex' => 'La nueva contraseña debe contener al menos una mayúscula, una minúscula y un número',
                'password_confirmacion.required' => 'La confirmación de contraseña es obligatoria',
                'password_confirmacion.same' => 'Las contraseñas no coinciden',
            ]);

            $jwtUser = $request->attributes->get('jwt_user');
            $user = User::findOrFail($jwtUser->user_id);

            // Verificar contraseña actual
            if (!Hash::check($request->password_actual, $user->password)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'La contraseña actual es incorrecta',
                    'errors' => [
                        'password_actual' => ['La contraseña actual no coincide']
                    ]
                ], 422);
            }

            // Actualizar contraseña
            $user->password = Hash::make($request->password_nueva);
            $user->debe_cambiar_password = false; // Ya cambió su contraseña
            $user->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Contraseña cambiada exitosamente. Por favor, inicia sesión nuevamente con tu nueva contraseña.'
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al cambiar contraseña',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
