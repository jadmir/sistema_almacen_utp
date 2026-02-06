<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UsuarioController extends Controller
{
    /**
     * Listar todos los usuarios
     */
    public function index()
    {
        $usuarios = User::with('role')
            ->orderBy('nombre')
            ->get();
        
        return response()->json([
            'data' => $usuarios
        ]);
    }

    /**
     * Crear un nuevo usuario
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'rol_id' => 'required|exists:roles,id',
            'nombre' => 'required|string|min:3|max:255|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/',
            'email' => 'required|email|unique:usuarios,email|max:255',
            'dni' => 'required|string|size:8|unique:usuarios,dni|regex:/^[0-9]{8}$/',
        ], [
            'rol_id.required' => 'El rol es obligatorio',
            'rol_id.exists' => 'El rol seleccionado no existe',
            'nombre.required' => 'El nombre es obligatorio',
            'nombre.min' => 'El nombre debe tener al menos 3 caracteres',
            'nombre.max' => 'El nombre no puede exceder 255 caracteres',
            'nombre.regex' => 'El nombre solo puede contener letras y espacios',
            'email.required' => 'El correo electrónico es obligatorio',
            'email.email' => 'El correo electrónico debe ser válido',
            'email.unique' => 'El correo electrónico ya está registrado',
            'email.max' => 'El correo electrónico no puede exceder 255 caracteres',
            'dni.required' => 'El DNI es obligatorio',
            'dni.size' => 'El DNI debe tener exactamente 8 dígitos',
            'dni.unique' => 'El DNI ya está registrado',
            'dni.regex' => 'El DNI debe contener solo números',
        ]);

        // Generar contraseña automática: DNI + dos primeras letras del nombre en mayúscula
        $nombreLimpio = preg_replace('/\s+/', '', $validated['nombre']); // Quitar espacios
        $dosPrimerasLetras = strtoupper(substr($nombreLimpio, 0, 2));
        $passwordGenerada = $validated['dni'] . $dosPrimerasLetras;

        $validated['password'] = Hash::make($passwordGenerada);
        $validated['debe_cambiar_password'] = true; // Debe cambiar en primer login
        $validated['estado'] = true; // Estado activo por defecto

        $usuario = User::create($validated);
        $usuario->load('role');

        return response()->json([
            'message' => 'Usuario creado exitosamente',
            'data' => $usuario,
            'password_temporal' => $passwordGenerada // Enviar para mostrar al admin
        ], 201);
    }

    /**
     * Mostrar un usuario específico
     */
    public function show(User $usuario)
    {
        $usuario->load('role');
        
        return response()->json([
            'data' => $usuario
        ]);
    }

    /**
     * Actualizar un usuario
     */
    public function update(Request $request, User $usuario)
    {
        $validated = $request->validate([
            'rol_id' => 'sometimes|required|exists:roles,id',
            'nombre' => 'sometimes|required|string|min:3|max:255|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/',
            'email' => 'sometimes|required|email|unique:usuarios,email,' . $usuario->id . '|max:255',
            'password' => 'sometimes|nullable|string|min:8|max:50|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
            'estado' => 'boolean',
        ], [
            'rol_id.required' => 'El rol es obligatorio',
            'rol_id.exists' => 'El rol seleccionado no existe',
            'nombre.required' => 'El nombre es obligatorio',
            'nombre.min' => 'El nombre debe tener al menos 3 caracteres',
            'nombre.max' => 'El nombre no puede exceder 255 caracteres',
            'nombre.regex' => 'El nombre solo puede contener letras y espacios',
            'email.required' => 'El correo electrónico es obligatorio',
            'email.email' => 'El correo electrónico debe ser válido',
            'email.unique' => 'El correo electrónico ya está registrado',
            'email.max' => 'El correo electrónico no puede exceder 255 caracteres',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres',
            'password.max' => 'La contraseña no puede exceder 50 caracteres',
            'password.regex' => 'La contraseña debe contener al menos una mayúscula, una minúscula y un número',
            'estado.boolean' => 'El estado debe ser verdadero o falso',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $usuario->update($validated);
        $usuario->load('role');

        return response()->json([
            'message' => 'Usuario actualizado exitosamente',
            'data' => $usuario
        ]);
    }

    /**
     * Eliminar un usuario (soft delete)
     */
    public function destroy(User $usuario)
    {
        $usuario->delete();

        return response()->json([
            'message' => 'Usuario eliminado exitosamente'
        ]);
    }

    /**
     * Listar usuarios eliminados
     */
    public function trashed()
    {
        $usuarios = User::onlyTrashed()
            ->with('role')
            ->orderBy('nombre')
            ->get();
        
        return response()->json([
            'data' => $usuarios
        ]);
    }

    /**
     * Restaurar un usuario eliminado
     */
    public function restore($id)
    {
        $usuario = User::onlyTrashed()->findOrFail($id);
        $usuario->restore();
        $usuario->load('role');

        return response()->json([
            'message' => 'Usuario restaurado exitosamente',
            'data' => $usuario
        ]);
    }

    /**
     * Eliminar permanentemente un usuario
     */
    public function forceDestroy($id)
    {
        $usuario = User::onlyTrashed()->findOrFail($id);
        $usuario->forceDelete();

        return response()->json([
            'message' => 'Usuario eliminado permanentemente'
        ]);
    }

    /**
     * Asignar permisos a un usuario
     */
    public function assignPermissions(Request $request, $id)
    {
        $usuario = User::findOrFail($id);
        
        // Validar datos de entrada
        $validated = $request->validate([
            'permission_ids' => 'array',
            'revoked_permission_ids' => 'array',
            'remove_all' => 'boolean'
        ]);
        
        // Si viene el flag remove_all, remover todos los permisos y revocaciones
        if ($request->input('remove_all', false)) {
            $usuario->permissions()->sync([]);
            $usuario->revoked_permissions = [];
            $usuario->save();
            $usuario->load(['permissions', 'role.permissions']);
            
            return response()->json([
                'message' => 'Permisos personalizados y revocaciones removidos exitosamente',
                'data' => [
                    'usuario' => $usuario,
                    'permisos_totales' => $usuario->getAllPermissions()
                ]
            ]);
        }
        
        // Permisos adicionales (personalizados) - los que marcó ADEMÁS del rol
        $permisosAdicionales = $validated['permission_ids'] ?? [];
        
        // Permisos revocados (del rol que NO queremos para este usuario)
        $permisosRevocados = $validated['revoked_permission_ids'] ?? [];
        
        // Sincronizar permisos adicionales
        $usuario->permissions()->sync($permisosAdicionales);
        
        // Guardar permisos revocados
        $usuario->revoked_permissions = $permisosRevocados;
        $usuario->save();
        
        $usuario->load(['permissions', 'role.permissions']);

        return response()->json([
            'message' => 'Permisos asignados exitosamente',
            'data' => [
                'usuario' => $usuario,
                'permisos_totales' => $usuario->getAllPermissions()
            ]
        ]);
    }

    /**
     * Obtener permisos de un usuario con todos los permisos disponibles
     */
    public function getUserPermissions($id)
    {
        $usuario = User::with(['permissions', 'role.permissions'])->findOrFail($id);
        
        // Obtener TODOS los permisos del sistema
        $todosLosPermisos = \App\Models\Permission::orderBy('nombre')->get();
        
        // IDs de permisos individuales del usuario
        $permisosIndividualesIds = $usuario->permissions->pluck('id')->toArray();
        
        // IDs de permisos que vienen del rol
        $permisosDelRolIds = $usuario->role->permissions->pluck('id')->toArray();
        
        // IDs de permisos revocados (del rol que NO quiere este usuario)
        $permisosRevocadosIds = $usuario->revoked_permissions ?? [];
        
        // Mapear todos los permisos con flags
        $permisosConEstado = $todosLosPermisos->map(function($permiso) use ($permisosIndividualesIds, $permisosDelRolIds, $permisosRevocadosIds) {
            $tieneDelRol = in_array($permiso->id, $permisosDelRolIds);
            $estaRevocado = in_array($permiso->id, $permisosRevocadosIds);
            $tieneIndividual = in_array($permiso->id, $permisosIndividualesIds);
            
            return [
                'id' => $permiso->id,
                'nombre' => $permiso->nombre,
                'slug' => $permiso->slug,
                'descripcion' => $permiso->descripcion,
                'tiene_individual' => $tieneIndividual, // Permiso asignado directamente
                'tiene_por_rol' => $tieneDelRol, // Permiso que viene del rol
                'esta_revocado' => $estaRevocado, // Permiso del rol que fue revocado
                'tiene_permiso' => ($tieneDelRol && !$estaRevocado) || $tieneIndividual // Tiene el permiso (considerando revocaciones)
            ];
        });

        return response()->json([
            'data' => [
                'usuario' => [
                    'id' => $usuario->id,
                    'nombre' => $usuario->nombre,
                    'email' => $usuario->email,
                ],
                'rol' => [
                    'id' => $usuario->role->id,
                    'nombre' => $usuario->role->nombre,
                ],
                'permisos_disponibles' => $permisosConEstado,
                'permisos_del_rol' => $usuario->role->permissions,
                'permisos_individuales' => $usuario->permissions,
                'permisos_revocados' => $permisosRevocadosIds,
                'permisos_totales' => $usuario->getAllPermissions()
            ]
        ]);
    }

    /**
     * Remover permiso de un usuario
     */
    public function removePermission($userId, $permissionId)
    {
        $usuario = User::findOrFail($userId);
        $usuario->permissions()->detach($permissionId);

        return response()->json([
            'message' => 'Permiso removido del usuario exitosamente'
        ]);
    }
}
