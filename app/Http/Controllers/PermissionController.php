<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    /**
     * Listar todos los permisos
     */
    public function index()
    {
        $permissions = Permission::orderBy('modulo')
            ->orderBy('nombre')
            ->get()
            ->groupBy('modulo');
        
        return response()->json([
            'data' => $permissions
        ]);
    }

    /**
     * Crear un nuevo permiso
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'nombre' => 'required|string|max:255|unique:permissions,nombre',
                'slug' => 'required|string|max:255|unique:permissions,slug|regex:/^[a-z0-9-_.]+$/',
                'descripcion' => 'nullable|string|max:500',
                'modulo' => 'nullable|string|max:100',
            ], [
                'nombre.required' => 'El nombre es obligatorio',
                'nombre.unique' => 'El nombre ya está registrado',
                'slug.required' => 'El slug es obligatorio',
                'slug.unique' => 'El slug ya está registrado',
                'slug.regex' => 'El slug solo puede contener letras minúsculas, números, guiones y puntos',
            ]);

            $validated['estado'] = true;
            $permission = Permission::create($validated);

            return response()->json([
                'message' => 'Permiso creado exitosamente',
                'data' => $permission
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * Mostrar un permiso específico
     */
    public function show(Permission $permission)
    {
        $permission->load('roles');
        
        return response()->json([
            'data' => $permission
        ]);
    }

    /**
     * Actualizar un permiso
     */
    public function update(Request $request, Permission $permission)
    {
        $validated = $request->validate([
            'nombre' => 'sometimes|required|string|max:255|unique:permissions,nombre,' . $permission->id,
            'slug' => 'sometimes|required|string|max:255|unique:permissions,slug,' . $permission->id . '|regex:/^[a-z0-9-_.]+$/',
            'descripcion' => 'nullable|string|max:500',
            'modulo' => 'nullable|string|max:100',
            'estado' => 'boolean',
        ]);

        $permission->update($validated);

        return response()->json([
            'message' => 'Permiso actualizado exitosamente',
            'data' => $permission
        ]);
    }

    /**
     * Eliminar un permiso
     */
    public function destroy(Permission $permission)
    {
        $permission->delete();

        return response()->json([
            'message' => 'Permiso eliminado exitosamente'
        ]);
    }

    /**
     * Asignar permisos a un rol
     */
    public function assignToRole(Request $request, $roleId)
    {
        $role = Role::findOrFail($roleId);
        
        $validated = $request->validate([
            'permission_ids' => 'required|array',
            'permission_ids.*' => 'exists:permissions,id',
        ], [
            'permission_ids.required' => 'Debe seleccionar al menos un permiso',
            'permission_ids.*.exists' => 'Uno o más permisos no existen',
        ]);

        $role->permissions()->sync($validated['permission_ids']);
        $role->load('permissions');

        return response()->json([
            'message' => 'Permisos asignados exitosamente',
            'data' => $role
        ]);
    }

    /**
     * Obtener permisos de un rol
     */
    public function getRolePermissions($roleId)
    {
        $role = Role::with('permissions')->findOrFail($roleId);

        return response()->json([
            'data' => [
                'role' => $role->nombre,
                'permissions' => $role->permissions
            ]
        ]);
    }

    /**
     * Remover permiso de un rol
     */
    public function removeFromRole($roleId, $permissionId)
    {
        $role = Role::findOrFail($roleId);
        $role->permissions()->detach($permissionId);

        return response()->json([
            'message' => 'Permiso removido del rol exitosamente'
        ]);
    }
}
