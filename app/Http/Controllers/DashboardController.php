<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Movement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Obtener datos para el dashboard principal
     */
    public function index()
    {
        try {
            // 1. Productos a punto de vencer (próximos 30 días) - Optimizado
            $productosProximosVencer = Product::activo()
                ->proximosVencer(30)
                ->conRelaciones()
                ->orderBy('fecha_vencimiento', 'asc')
                ->limit(10)
                ->get(['id', 'codigo', 'nombre', 'stock_actual', 'unidad_medida', 'fecha_vencimiento', 'section_id'])
                ->map(function ($product) {
                    $diasRestantes = now()->diffInDays($product->fecha_vencimiento, false);
                    return [
                        'id' => $product->id,
                        'codigo' => $product->codigo,
                        'nombre' => $product->nombre,
                        'stock_actual' => $product->stock_actual,
                        'unidad_medida' => $product->unidad_medida,
                        'fecha_vencimiento' => $product->fecha_vencimiento->format('d/m/Y'),
                        'dias_restantes' => (int) $diasRestantes,
                        'seccion' => $product->section->nombre,
                        'urgente' => $diasRestantes <= 7
                    ];
                });

            // 2. Productos con stock mínimo o bajo - Optimizado
            $productosStockBajo = Product::activo()
                ->stockBajo()
                ->conRelaciones()
                ->orderByRaw('(stock_actual - stock_minimo) ASC')
                ->limit(10)
                ->get(['id', 'codigo', 'nombre', 'stock_actual', 'stock_minimo', 'unidad_medida', 'section_id'])
                ->map(function ($product) {
                    $porcentaje = $product->stock_minimo > 0
                        ? round(($product->stock_actual / $product->stock_minimo) * 100, 1)
                        : 0;
                    return [
                        'id' => $product->id,
                        'codigo' => $product->codigo,
                        'nombre' => $product->nombre,
                        'stock_actual' => $product->stock_actual,
                        'stock_minimo' => $product->stock_minimo,
                        'unidad_medida' => $product->unidad_medida,
                        'seccion' => $product->section->nombre,
                        'porcentaje_stock' => $porcentaje,
                        'critico' => $product->stock_actual == 0
                    ];
                });

            // 3. Últimas entradas (últimos 10 movimientos) - Optimizado
            $ultimasEntradas = Movement::entradas()
                ->conRelaciones()
                ->recientes(10)
                ->get(['id', 'product_id', 'user_id', 'area_id', 'cantidad', 'motivo', 'created_at'])
                ->map(function ($movement) {
                    return [
                        'id' => $movement->id,
                        'producto_codigo' => $movement->product->codigo,
                        'producto_nombre' => $movement->product->nombre,
                        'cantidad' => $movement->cantidad,
                        'unidad_medida' => $movement->product->unidad_medida,
                        'seccion' => $movement->product->section->nombre,
                        'usuario' => $movement->user->nombre,
                        'motivo' => $movement->motivo,
                        'fecha' => $movement->created_at->format('d/m/Y H:i'),
                        'fecha_relativa' => $movement->created_at->diffForHumans()
                    ];
                });

            // 4. Últimas salidas (últimos 10 movimientos) - Optimizado
            $ultimasSalidas = Movement::salidas()
                ->conRelaciones()
                ->recientes(10)
                ->get(['id', 'product_id', 'user_id', 'area_id', 'cantidad', 'motivo', 'created_at'])
                ->map(function ($movement) {
                    return [
                        'id' => $movement->id,
                        'producto_codigo' => $movement->product->codigo,
                        'producto_nombre' => $movement->product->nombre,
                        'cantidad' => $movement->cantidad,
                        'unidad_medida' => $movement->product->unidad_medida,
                        'seccion' => $movement->product->section->nombre,
                        'area_destino' => $movement->area ? $movement->area->nombre : null,
                        'area_codigo' => $movement->area ? $movement->area->codigo : null,
                        'usuario' => $movement->user->nombre,
                        'motivo' => $movement->motivo,
                        'fecha' => $movement->created_at->format('d/m/Y H:i'),
                        'fecha_relativa' => $movement->created_at->diffForHumans()
                    ];
                });

            // 5. Estadísticas generales - Optimizado con cache
            $estadisticas = Cache::remember('dashboard_stats', 300, function () {
                // Estadísticas de productos en una sola consulta
                $productsStats = DB::table('products')
                    ->selectRaw('
                        COUNT(*) as total_productos,
                        SUM(CASE WHEN stock_actual <= stock_minimo THEN 1 ELSE 0 END) as productos_stock_bajo,
                        SUM(CASE WHEN tiene_vencimiento = 1
                            AND fecha_vencimiento IS NOT NULL
                            AND fecha_vencimiento > CURDATE()
                            AND fecha_vencimiento <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
                            THEN 1 ELSE 0 END) as productos_por_vencer
                    ')
                    ->where('estado', true)
                    ->whereNull('deleted_at')
                    ->first();

                // Estadísticas de movimientos en una sola consulta
                $movementsStats = DB::table('movements')
                    ->selectRaw('
                        COUNT(*) as movimientos_hoy,
                        SUM(CASE WHEN tipo = "ENTRADA" THEN 1 ELSE 0 END) as entradas_hoy,
                        SUM(CASE WHEN tipo = "SALIDA" THEN 1 ELSE 0 END) as salidas_hoy
                    ')
                    ->whereDate('created_at', today())
                    ->first();

                return [
                    'total_productos' => $productsStats->total_productos ?? 0,
                    'productos_stock_bajo' => $productsStats->productos_stock_bajo ?? 0,
                    'productos_por_vencer' => $productsStats->productos_por_vencer ?? 0,
                    'movimientos_hoy' => $movementsStats->movimientos_hoy ?? 0,
                    'entradas_hoy' => $movementsStats->entradas_hoy ?? 0,
                    'salidas_hoy' => $movementsStats->salidas_hoy ?? 0,
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => [
                    'productos_proximos_vencer' => $productosProximosVencer,
                    'productos_stock_bajo' => $productosStockBajo,
                    'ultimas_entradas' => $ultimasEntradas,
                    'ultimas_salidas' => $ultimasSalidas,
                    'estadisticas' => $estadisticas
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener datos del dashboard',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener solo estadísticas generales - Optimizado
     */
    public function estadisticas()
    {
        try {
            // Cache por 5 minutos
            $estadisticas = Cache::remember('estadisticas_generales', 300, function () {
                // Una sola consulta para productos
                $productsStats = DB::table('products')
                    ->selectRaw('
                        COUNT(*) as total_productos,
                        COUNT(*) as productos_activos,
                        SUM(CASE WHEN stock_actual <= stock_minimo THEN 1 ELSE 0 END) as productos_stock_bajo,
                        SUM(CASE WHEN stock_actual = 0 THEN 1 ELSE 0 END) as productos_sin_stock,
                        SUM(CASE WHEN tiene_vencimiento = 1
                            AND fecha_vencimiento IS NOT NULL
                            AND fecha_vencimiento > CURDATE()
                            AND fecha_vencimiento <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
                            THEN 1 ELSE 0 END) as productos_por_vencer,
                        SUM(CASE WHEN tiene_vencimiento = 1
                            AND fecha_vencimiento IS NOT NULL
                            AND fecha_vencimiento < CURDATE()
                            THEN 1 ELSE 0 END) as productos_vencidos
                    ')
                    ->where('estado', true)
                    ->whereNull('deleted_at')
                    ->first();

                // Consultas separadas para movimientos (hoy y mes)
                $movementsTodayStats = DB::table('movements')
                    ->selectRaw('
                        COUNT(*) as movimientos_hoy,
                        SUM(CASE WHEN tipo = "ENTRADA" THEN 1 ELSE 0 END) as entradas_hoy,
                        SUM(CASE WHEN tipo = "SALIDA" THEN 1 ELSE 0 END) as salidas_hoy,
                        SUM(CASE WHEN tipo = "AJUSTE" THEN 1 ELSE 0 END) as ajustes_hoy
                    ')
                    ->whereDate('created_at', today())
                    ->first();

                $movimientosMes = Movement::mesActual()->count();

                return [
                    'total_productos' => $productsStats->total_productos ?? 0,
                    'productos_activos' => $productsStats->productos_activos ?? 0,
                    'productos_stock_bajo' => $productsStats->productos_stock_bajo ?? 0,
                    'productos_sin_stock' => $productsStats->productos_sin_stock ?? 0,
                    'productos_por_vencer' => $productsStats->productos_por_vencer ?? 0,
                    'productos_vencidos' => $productsStats->productos_vencidos ?? 0,
                    'movimientos_hoy' => $movementsTodayStats->movimientos_hoy ?? 0,
                    'entradas_hoy' => $movementsTodayStats->entradas_hoy ?? 0,
                    'salidas_hoy' => $movementsTodayStats->salidas_hoy ?? 0,
                    'ajustes_hoy' => $movementsTodayStats->ajustes_hoy ?? 0,
                    'movimientos_mes' => $movimientosMes,
                ];
            });

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
