<?php

namespace App\Http\Controllers;

use App\Models\Movement;
use Illuminate\Http\Request;

class MovementController extends Controller
{
    /**
     * Listar todos los movimientos con filtros
     */
    public function index(Request $request)
    {
        try {
            $query = Movement::with(['product.section.stockType', 'user:id,nombre,email', 'area:id,nombre,codigo']);

            // Filtro por producto
            if ($request->filled('product_id')) {
                $query->where('product_id', $request->product_id);
            }

            // Filtro por tipo de movimiento
            if ($request->filled('tipo')) {
                $query->where('tipo', $request->tipo);
            }

            // Filtro por usuario
            if ($request->filled('user_id')) {
                $query->where('user_id', $request->user_id);
            }

            // Filtro por área
            if ($request->filled('area_id')) {
                $query->where('area_id', $request->area_id);
            }

            // Filtro por rango de fechas
            if ($request->filled('fecha_desde')) {
                $query->whereDate('created_at', '>=', $request->fecha_desde);
            }
            if ($request->filled('fecha_hasta')) {
                $query->whereDate('created_at', '<=', $request->fecha_hasta);
            }

            // Filtro por tipo de stock
            if ($request->filled('stock_type_id')) {
                $query->whereHas('product.section', function ($q) use ($request) {
                    $q->where('stock_type_id', $request->stock_type_id);
                });
            }

            // Filtro por sección
            if ($request->filled('section_id')) {
                $query->whereHas('product', function ($q) use ($request) {
                    $q->where('section_id', $request->section_id);
                });
            }

            $movimientos = $query->orderBy('created_at', 'desc')->paginate(50);

            return response()->json([
                'status' => 'success',
                'data' => $movimientos
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener movimientos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener un movimiento específico
     */
    public function show($id)
    {
        try {
            $movimiento = Movement::with(['product.section.stockType', 'user'])
                ->findOrFail($id);

            return response()->json([
                'status' => 'success',
                'data' => $movimiento
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Movimiento no encontrado',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Obtener estadísticas de movimientos
     */
    public function estadisticas(Request $request)
    {
        try {
            $query = Movement::query();

            // Filtros opcionales
            if ($request->has('fecha_desde')) {
                $query->whereDate('created_at', '>=', $request->fecha_desde);
            }
            if ($request->has('fecha_hasta')) {
                $query->whereDate('created_at', '<=', $request->fecha_hasta);
            }
            if ($request->has('stock_type_id')) {
                $query->whereHas('product.section', function ($q) use ($request) {
                    $q->where('stock_type_id', $request->stock_type_id);
                });
            }

            $estadisticas = [
                'total_movimientos' => $query->count(),
                'entradas' => (clone $query)->where('tipo', 'ENTRADA')->count(),
                'salidas' => (clone $query)->where('tipo', 'SALIDA')->count(),
                'ajustes' => (clone $query)->where('tipo', 'AJUSTE')->count(),
                'movimientos_recientes' => Movement::with(['product:id,codigo,nombre', 'user:id,nombre'])
                    ->orderBy('created_at', 'desc')
                    ->limit(10)
                    ->get(),
            ];

            return response()->json([
                'status' => 'success',
                'data' => $estadisticas
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener estadísticas',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
