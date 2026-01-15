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
            'nombre' => 'required|string|max:255',
            'email' => 'required|email|unique:usuarios,email',
            'password' => 'required|string|min:8',
            'estado' => 'boolean',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $usuario = User::create($validated);
        $usuario->load('role');

        return response()->json([
            'message' => 'Usuario creado exitosamente',
            'data' => $usuario
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
            'nombre' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:usuarios,email,' . $usuario->id,
            'password' => 'sometimes|nullable|string|min:8',
            'estado' => 'boolean',
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
     * Eliminar un usuario
     */
    public function destroy(User $usuario)
    {
        $usuario->delete();

        return response()->json([
            'message' => 'Usuario eliminado exitosamente'
        ]);
    }
}
