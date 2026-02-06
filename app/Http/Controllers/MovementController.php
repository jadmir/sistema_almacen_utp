<?php

namespace App\Http\Controllers;

use App\Models\Movement;
use Illuminate\Http\Request;

class MovementController extends Controller
{
    /**
     * Listar todos los movimientos con filtros - Optimizado
     */
    public function index(Request $request)
    {
        try {
            // Query optimizada con eager loading selectivo
            $query = Movement::conRelaciones();

            // Filtro por producto
            if ($request->filled('product_id')) {
                $query->where('product_id', $request->product_id);
            }

            // Filtro por tipo de movimiento usando scopes
            if ($request->filled('tipo')) {
                switch ($request->tipo) {
                    case 'ENTRADA':
                        $query->entradas();
                        break;
                    case 'SALIDA':
                        $query->salidas();
                        break;
                    case 'AJUSTE':
                        $query->ajustes();
                        break;
                }
            }

            // Filtro por usuario
            if ($request->filled('user_id')) {
                $query->where('user_id', $request->user_id);
            }

            // Filtro por área
            if ($request->filled('area_id')) {
                $query->where('area_id', $request->area_id);
            }

            // Filtro por rango de fechas optimizado
            if ($request->filled('fecha_desde') && $request->filled('fecha_hasta')) {
                $query->entreFechas($request->fecha_desde, $request->fecha_hasta);
            } elseif ($request->filled('fecha_desde')) {
                $query->whereDate('created_at', '>=', $request->fecha_desde);
            } elseif ($request->filled('fecha_hasta')) {
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

            // Select campos necesarios
            $movimientos = $query->select([
                'id', 'product_id', 'user_id', 'area_id', 'tipo',
                'cantidad', 'stock_anterior', 'stock_posterior',
                'motivo', 'observaciones', 'documento_referencia',
                'fecha_movimiento', 'created_at', 'updated_at'
            ])
            ->orderBy('created_at', 'desc')
            ->paginate(50);

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
     * Obtener un movimiento específico - Optimizado
     */
    public function show($id)
    {
        try {
            $movimiento = Movement::with([
                'product:id,codigo,nombre,unidad_medida,section_id',
                'product.section:id,nombre,codigo,stock_type_id',
                'product.section.stockType:id,nombre,codigo',
                'user:id,nombre,email',
                'area:id,nombre,codigo'
            ])
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
     * Obtener estadísticas de movimientos - Optimizado
     */
    public function estadisticas(Request $request)
    {
        try {
            $query = Movement::query();

            // Filtros opcionales
            if ($request->filled('fecha_desde') && $request->filled('fecha_hasta')) {
                $query->entreFechas($request->fecha_desde, $request->fecha_hasta);
            } elseif ($request->filled('fecha_desde')) {
                $query->whereDate('created_at', '>=', $request->fecha_desde);
            } elseif ($request->filled('fecha_hasta')) {
                $query->whereDate('created_at', '<=', $request->fecha_hasta);
            }

            if ($request->filled('stock_type_id')) {
                $query->whereHas('product.section', function ($q) use ($request) {
                    $q->where('stock_type_id', $request->stock_type_id);
                });
            }

            // Estadísticas agrupadas en una sola consulta
            $stats = (clone $query)
                ->selectRaw('
                    COUNT(*) as total_movimientos,
                    SUM(CASE WHEN tipo = "ENTRADA" THEN 1 ELSE 0 END) as entradas,
                    SUM(CASE WHEN tipo = "SALIDA" THEN 1 ELSE 0 END) as salidas,
                    SUM(CASE WHEN tipo = "AJUSTE" THEN 1 ELSE 0 END) as ajustes
                ')
                ->first();

            $estadisticas = [
                'total_movimientos' => $stats->total_movimientos ?? 0,
                'entradas' => $stats->entradas ?? 0,
                'salidas' => $stats->salidas ?? 0,
                'ajustes' => $stats->ajustes ?? 0,
                'movimientos_recientes' => Movement::conRelaciones()
                    ->recientes(10)
                    ->get(['id', 'product_id', 'user_id', 'tipo', 'cantidad', 'motivo', 'created_at'])
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
