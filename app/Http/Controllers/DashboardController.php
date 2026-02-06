<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Movement;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Obtener datos para el dashboard principal
     */
    public function index()
    {
        try {
            // 1. Productos a punto de vencer (próximos 30 días)
            $productosProximosVencer = Product::where('tiene_vencimiento', true)
                ->where('estado', true)
                ->whereNotNull('fecha_vencimiento')
                ->whereDate('fecha_vencimiento', '>', now())
                ->whereDate('fecha_vencimiento', '<=', now()->addDays(30))
                ->with(['section.stockType'])
                ->orderBy('fecha_vencimiento', 'asc')
                ->limit(10)
                ->get()
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

            // 2. Productos con stock mínimo o bajo
            $productosStockBajo = Product::where('estado', true)
                ->whereRaw('stock_actual <= stock_minimo')
                ->with(['section.stockType'])
                ->orderByRaw('(stock_actual - stock_minimo) ASC')
                ->limit(10)
                ->get()
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

            // 3. Últimas entradas (últimos 10 movimientos)
            $ultimasEntradas = Movement::where('tipo', 'ENTRADA')
                ->with(['product.section', 'user', 'area'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
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

            // 4. Últimas salidas (últimos 10 movimientos)
            $ultimasSalidas = Movement::where('tipo', 'SALIDA')
                ->with(['product.section', 'user', 'area'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
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

            // 5. Estadísticas generales
            $estadisticas = [
                'total_productos' => Product::where('estado', true)->count(),
                'productos_stock_bajo' => Product::where('estado', true)
                    ->whereRaw('stock_actual <= stock_minimo')
                    ->count(),
                'productos_por_vencer' => Product::where('tiene_vencimiento', true)
                    ->where('estado', true)
                    ->whereNotNull('fecha_vencimiento')
                    ->whereDate('fecha_vencimiento', '>', now())
                    ->whereDate('fecha_vencimiento', '<=', now()->addDays(30))
                    ->count(),
                'movimientos_hoy' => Movement::whereDate('created_at', today())->count(),
                'entradas_hoy' => Movement::where('tipo', 'ENTRADA')
                    ->whereDate('created_at', today())
                    ->count(),
                'salidas_hoy' => Movement::where('tipo', 'SALIDA')
                    ->whereDate('created_at', today())
                    ->count(),
            ];

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
     * Obtener solo estadísticas generales
     */
    public function estadisticas()
    {
        try {
            $estadisticas = [
                'total_productos' => Product::where('estado', true)->count(),
                'productos_activos' => Product::where('estado', true)->count(),
                'productos_stock_bajo' => Product::where('estado', true)
                    ->whereRaw('stock_actual <= stock_minimo')
                    ->count(),
                'productos_sin_stock' => Product::where('estado', true)
                    ->where('stock_actual', 0)
                    ->count(),
                'productos_por_vencer' => Product::where('tiene_vencimiento', true)
                    ->where('estado', true)
                    ->whereNotNull('fecha_vencimiento')
                    ->whereDate('fecha_vencimiento', '>', now())
                    ->whereDate('fecha_vencimiento', '<=', now()->addDays(30))
                    ->count(),
                'productos_vencidos' => Product::where('tiene_vencimiento', true)
                    ->where('estado', true)
                    ->whereNotNull('fecha_vencimiento')
                    ->whereDate('fecha_vencimiento', '<', now())
                    ->count(),
                'movimientos_hoy' => Movement::whereDate('created_at', today())->count(),
                'entradas_hoy' => Movement::where('tipo', 'ENTRADA')
                    ->whereDate('created_at', today())
                    ->count(),
                'salidas_hoy' => Movement::where('tipo', 'SALIDA')
                    ->whereDate('created_at', today())
                    ->count(),
                'ajustes_hoy' => Movement::where('tipo', 'AJUSTE')
                    ->whereDate('created_at', today())
                    ->count(),
                'movimientos_mes' => Movement::whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->count(),
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
