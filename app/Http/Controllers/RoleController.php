<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    /**
     * Listar todos los roles
     */
    public function index()
    {
        $roles = Role::orderBy('nombre')->get();
        
        return response()->json([
            'data' => $roles
        ]);
    }

    /**
     * Crear un nuevo rol
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'estado' => 'boolean',
        ]);

        $role = Role::create($validated);

        return response()->json([
            'message' => 'Rol creado exitosamente',
            'data' => $role
        ], 201);
    }

    /**
     * Mostrar un rol específico
     */
    public function show(Role $role)
    {
        $role->load('usuarios');
        
        return response()->json([
            'data' => $role
        ]);
    }

    /**
     * Actualizar un rol
     */
    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'nombre' => 'sometimes|required|string|max:255',
            'descripcion' => 'nullable|string',
            'estado' => 'boolean',
        ]);

        $role->update($validated);

        return response()->json([
            'message' => 'Rol actualizado exitosamente',
            'data' => $role
        ]);
    }

    /**
     * Eliminar un rol
     */
    public function destroy(Role $role)
    {
        // Verificar si hay usuarios con este rol
        if ($role->usuarios()->count() > 0) {
            return response()->json([
                'message' => 'No se puede eliminar el rol porque tiene usuarios asociados'
            ], 422);
        }

        $role->delete();

        return response()->json([
            'message' => 'Rol eliminado exitosamente'
        ]);
    }
}
