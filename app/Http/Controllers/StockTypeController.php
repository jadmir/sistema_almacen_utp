<?php

namespace App\Http\Controllers;

use App\Models\StockType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StockTypeController extends Controller
{
    /**
     * Listar todos los tipos de stock
     */
    public function index()
    {
        try {
            $stockTypes = StockType::withCount('sections')
                ->orderBy('nombre', 'asc')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $stockTypes
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener tipos de stock',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear un nuevo tipo de stock
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nombre' => 'required|string|max:100|unique:stock_types,nombre',
                'descripcion' => 'nullable|string',
                'codigo_prefix' => 'required|string|max:20|unique:stock_types,codigo_prefix',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $stockType = StockType::create($request->all());

            return response()->json([
                'status' => 'success',
                'message' => 'Tipo de stock creado exitosamente',
                'data' => $stockType
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al crear tipo de stock',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener un tipo de stock específico
     */
    public function show($id)
    {
        try {
            $stockType = StockType::with(['sections' => function ($query) {
                $query->withCount('products');
            }])->findOrFail($id);

            return response()->json([
                'status' => 'success',
                'data' => $stockType
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tipo de stock no encontrado',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Actualizar un tipo de stock
     */
    public function update(Request $request, $id)
    {
        try {
            $stockType = StockType::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'nombre' => 'sometimes|required|string|max:100|unique:stock_types,nombre,' . $id,
                'descripcion' => 'nullable|string',
                'codigo_prefix' => 'sometimes|required|string|max:20|unique:stock_types,codigo_prefix,' . $id,
                'estado' => 'sometimes|required|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $stockType->update($request->all());

            return response()->json([
                'status' => 'success',
                'message' => 'Tipo de stock actualizado exitosamente',
                'data' => $stockType
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al actualizar tipo de stock',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar un tipo de stock (soft delete)
     */
    public function destroy($id)
    {
        try {
            $stockType = StockType::findOrFail($id);
            $stockType->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Tipo de stock eliminado exitosamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al eliminar tipo de stock',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
