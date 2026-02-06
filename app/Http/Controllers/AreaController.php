<?php

namespace App\Http\Controllers;

use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AreaController extends Controller
{
    /**
     * Listar todas las áreas
     */
    public function index(Request $request)
    {
        $query = Area::query();
        
        // Filtros
        if ($request->filled('nombre')) {
            $query->where('nombre', 'like', '%' . $request->nombre . '%');
        }
        
        if ($request->filled('codigo')) {
            $query->where('codigo', 'like', '%' . $request->codigo . '%');
        }
        
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }
        
        // Paginación
        $perPage = min($request->input('per_page', 10), 100);
        $areas = $query->orderBy('nombre', 'asc')->paginate($perPage);
        
        return response()->json($areas);
    }
    
    /**
     * Crear nueva área
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255|unique:areas,nombre',
            'codigo' => 'required|string|max:20|unique:areas,codigo',
            'descripcion' => 'nullable|string',
            'responsable' => 'nullable|string|max:255',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $area = Area::create($request->all());
        
        return response()->json([
            'status' => 'success',
            'message' => 'Área creada exitosamente',
            'data' => $area
        ], 201);
    }
    
    /**
     * Mostrar área específica
     */
    public function show($id)
    {
        $area = Area::with(['movements' => function($query) {
            $query->latest()->take(10);
        }])->findOrFail($id);
        
        return response()->json($area);
    }
    
    /**
     * Actualizar área
     */
    public function update(Request $request, $id)
    {
        $area = Area::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255|unique:areas,nombre,' . $id,
            'codigo' => 'required|string|max:20|unique:areas,codigo,' . $id,
            'descripcion' => 'nullable|string',
            'responsable' => 'nullable|string|max:255',
            'estado' => 'boolean',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $area->update($request->all());
        
        return response()->json([
            'status' => 'success',
            'message' => 'Área actualizada exitosamente',
            'data' => $area
        ]);
    }
    
    /**
     * Eliminar área (soft delete)
     */
    public function destroy($id)
    {
        $area = Area::findOrFail($id);
        $area->delete();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Área eliminada exitosamente'
        ]);
    }
    
    /**
     * Listar áreas activas (para selects)
     */
    public function activas()
    {
        $areas = Area::where('estado', true)
            ->orderBy('nombre', 'asc')
            ->get(['id', 'nombre', 'codigo', 'responsable']);
        
        return response()->json($areas);
    }
}
