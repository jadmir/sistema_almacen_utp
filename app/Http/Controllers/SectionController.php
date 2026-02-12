<?php

namespace App\Http\Controllers;

use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SectionController extends Controller
{
    /**
     * Listar todas las secciones
     */
    public function index(Request $request)
    {
        try {
            $query = Section::with('stockType')->withCount('products');

            // Filtro por tipo de stock
            if ($request->has('stock_type_id')) {
                $query->where('stock_type_id', $request->stock_type_id);
            }

            // Filtro por estado
            if ($request->has('estado')) {
                $query->where('estado', $request->estado);
            }

            $sections = $query->orderBy('codigo', 'asc')->get();

            return response()->json([
                'status' => 'success',
                'data' => $sections
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener secciones',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear una nueva sección
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'stock_type_id' => 'required|exists:stock_types,id',
                'codigo' => 'required|string|max:50',
                'nombre' => 'required|string|max:100',
                'descripcion' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $section = Section::create($request->all());

            return response()->json([
                'status' => 'success',
                'message' => 'Sección creada exitosamente',
                'data' => $section->load('stockType')
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al crear sección',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener una sección específica
     */
    public function show($id)
    {
        try {
            $section = Section::with(['stockType', 'products'])
                ->findOrFail($id);

            return response()->json([
                'status' => 'success',
                'data' => $section
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Sección no encontrada',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Actualizar una sección
     */
    public function update(Request $request, $id)
    {
        try {
            $section = Section::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'stock_type_id' => 'sometimes|required|exists:stock_types,id',
                'codigo' => 'sometimes|required|string|max:50',
                'nombre' => 'sometimes|required|string|max:100',
                'descripcion' => 'nullable|string',
                'estado' => 'sometimes|required|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $section->update($request->all());

            return response()->json([
                'status' => 'success',
                'message' => 'Sección actualizada exitosamente',
                'data' => $section->load('stockType')
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al actualizar sección',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar una sección (soft delete)
     */
    public function destroy($id)
    {
        try {
            $section = Section::findOrFail($id);
            $section->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Sección eliminada exitosamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al eliminar sección',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
