<?php

namespace App\Http\Controllers;

use App\Models\Deposito;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DepositoController extends Controller
{
    /**
     * Listar todos los depósitos
     */
    public function index(Request $request)
    {
        try {
            $query = Deposito::withCount('productos');

            // Filtrar por estado activo/inactivo
            if ($request->filled('activo')) {
                $query->where('activo', filter_var($request->activo, FILTER_VALIDATE_BOOLEAN));
            }

            $depositos = $query->orderBy('nombre', 'asc')->get();

            return response()->json([
                'status' => 'success',
                'data' => $depositos
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener depósitos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear un nuevo depósito
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nombre' => 'required|string|max:255',
                'activo' => 'boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $deposito = Deposito::create([
                'nombre' => $request->nombre,
                'codigo' => strtoupper($request->codigo),
                'ubicacion' => $request->ubicacion,
                'descripcion' => $request->descripcion,
                'activo' => $request->activo ?? true,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Depósito creado exitosamente',
                'data' => $deposito
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al crear depósito',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar un depósito específico
     */
    public function show($id)
    {
        try {
            $deposito = Deposito::withCount('productos')->findOrFail($id);

            return response()->json([
                'status' => 'success',
                'data' => $deposito
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Depósito no encontrado',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Actualizar un depósito
     */
    public function update(Request $request, $id)
    {
        try {
            $deposito = Deposito::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'nombre' => 'sometimes|required|string|max:255',
                'activo' => 'boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $deposito->update([
                'nombre' => $request->nombre ?? $deposito->nombre,
                'codigo' => $request->codigo ? strtoupper($request->codigo) : $deposito->codigo,
                'ubicacion' => $request->ubicacion ?? $deposito->ubicacion,
                'descripcion' => $request->descripcion ?? $deposito->descripcion,
                'activo' => $request->activo ?? $deposito->activo,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Depósito actualizado exitosamente',
                'data' => $deposito
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al actualizar depósito',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar un depósito
     */
    public function destroy($id)
    {
        try {
            $deposito = Deposito::findOrFail($id);

            // Verificar si tiene productos asignados
            $cantidadProductos = $deposito->productos()->count();
            if ($cantidadProductos > 0) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No se puede eliminar el depósito porque tiene productos asignados',
                    'cantidad_productos' => $cantidadProductos
                ], 400);
            }

            $deposito->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Depósito eliminado exitosamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al eliminar depósito',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Listar depósitos activos (para selects)
     */
    public function activos()
    {
        try {
            $depositos = Deposito::where('activo', true)
                ->orderBy('nombre', 'asc')
                ->get(['id', 'nombre']);

            return response()->json([
                'status' => 'success',
                'data' => $depositos
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener depósitos activos',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
