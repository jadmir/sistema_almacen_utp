<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProductsReportExport;
use App\Exports\StockBajoReportExport;
use App\Exports\ProximosVencerReportExport;
use App\Exports\ProductosVencidosReportExport;
use App\Exports\MovementsReportExport;
use App\Exports\KardexReportExport;
use App\Exports\StockTypeReportExport;
use App\Exports\SectionReportExport;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Product;
use App\Models\Movement;

class ReportController extends Controller
{
    /**
     * Reporte general de productos
     */
    public function productos(Request $request)
    {
        $filters = [
            'section_id' => $request->filled('section_id') ? $request->section_id : null,
            'stock_type_id' => $request->filled('stock_type_id') ? $request->stock_type_id : null,
            'codigo' => $request->filled('codigo') ? $request->codigo : null,
            'nombre' => $request->filled('nombre') ? $request->nombre : null,
        ];
        
        $fileName = 'inventario_productos_' . Carbon::now()->format('Ymd_His') . '.xlsx';
        
        return Excel::download(new ProductsReportExport($filters), $fileName);
    }
    
    /**
     * Reporte de productos con stock bajo
     */
    public function stockBajo(Request $request)
    {
        $filters = [
            'section_id' => $request->filled('section_id') ? $request->section_id : null,
            'stock_type_id' => $request->filled('stock_type_id') ? $request->stock_type_id : null,
        ];
        
        $fileName = 'stock_bajo_' . Carbon::now()->format('Ymd_His') . '.xlsx';
        
        return Excel::download(new StockBajoReportExport($filters), $fileName);
    }
    
    /**
     * Reporte de productos próximos a vencer
     */
    public function proximosVencer(Request $request)
    {
        $dias = $request->filled('dias') ? (int)$request->dias : 30;
        $sectionId = $request->filled('section_id') ? $request->section_id : null;
        
        $fileName = 'proximos_vencer_' . Carbon::now()->format('Ymd_His') . '.xlsx';
        
        return Excel::download(new ProximosVencerReportExport($dias, $sectionId), $fileName);
    }
    
    /**
     * Reporte de productos vencidos
     */
    public function vencidos(Request $request)
    {
        $sectionId = $request->filled('section_id') ? $request->section_id : null;
        
        $fileName = 'productos_vencidos_' . Carbon::now()->format('Ymd_His') . '.xlsx';
        
        return Excel::download(new ProductosVencidosReportExport($sectionId), $fileName);
    }
    
    /**
     * Reporte de movimientos
     */
    public function movimientos(Request $request)
    {
        $filters = [
            'product_id' => $request->filled('product_id') ? $request->product_id : null,
            'tipo' => $request->filled('tipo') ? $request->tipo : null,
            'user_id' => $request->filled('user_id') ? $request->user_id : null,
            'fecha_desde' => $request->filled('fecha_desde') ? $request->fecha_desde : null,
            'fecha_hasta' => $request->filled('fecha_hasta') ? $request->fecha_hasta : null,
            'section_id' => $request->filled('section_id') ? $request->section_id : null,
            'stock_type_id' => $request->filled('stock_type_id') ? $request->stock_type_id : null,
        ];
        
        $fileName = 'movimientos_' . Carbon::now()->format('Ymd_His') . '.xlsx';
        
        return Excel::download(new MovementsReportExport($filters), $fileName);
    }
    
    /**
     * Kardex de un producto específico
     */
    public function kardex(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);
        
        $productId = $request->product_id;
        $fechaDesde = $request->filled('fecha_desde') ? $request->fecha_desde : null;
        $fechaHasta = $request->filled('fecha_hasta') ? $request->fecha_hasta : null;
        
        $fileName = 'kardex_producto_' . $productId . '_' . Carbon::now()->format('Ymd_His') . '.xlsx';
        
        return Excel::download(new KardexReportExport($productId, $fechaDesde, $fechaHasta), $fileName);
    }
    
    /**
     * Reporte por tipo de stock
     */
    public function tipoStock(Request $request)
    {
        $stockTypeId = $request->filled('stock_type_id') ? $request->stock_type_id : null;
        
        $fileName = 'reporte_tipo_stock_' . Carbon::now()->format('Ymd_His') . '.xlsx';
        
        return Excel::download(new StockTypeReportExport($stockTypeId), $fileName);
    }
    
    /**
     * Reporte por sección
     */
    public function seccion(Request $request)
    {
        $sectionId = $request->filled('section_id') ? $request->section_id : null;
        
        $fileName = 'reporte_seccion_' . Carbon::now()->format('Ymd_His') . '.xlsx';
        
        return Excel::download(new SectionReportExport($sectionId), $fileName);
    }

    // ========================================
    // REPORTES EN PDF
    // ========================================

    /**
     * Reporte general de productos en PDF
     */
    public function productosPdf(Request $request)
    {
        $query = Product::with(['section.stockType'])->where('estado', true);

        if ($request->filled('section_id')) {
            $query->where('section_id', $request->section_id);
        }

        if ($request->filled('stock_type_id')) {
            $query->whereHas('section', function ($q) use ($request) {
                $q->where('stock_type_id', $request->stock_type_id);
            });
        }

        $productos = $query->orderBy('codigo', 'asc')->get();

        $pdf = Pdf::loadView('reports.productos', compact('productos'));
        $pdf->setPaper('a4', 'landscape');
        
        return $pdf->download('inventario_productos_' . Carbon::now()->format('Ymd_His') . '.pdf');
    }

    /**
     * Reporte de productos con stock bajo en PDF
     */
    public function stockBajoPdf(Request $request)
    {
        $query = Product::with(['section.stockType'])
            ->where('estado', true)
            ->whereRaw('stock_actual <= stock_minimo');

        if ($request->filled('section_id')) {
            $query->where('section_id', $request->section_id);
        }

        if ($request->filled('stock_type_id')) {
            $query->whereHas('section', function ($q) use ($request) {
                $q->where('stock_type_id', $request->stock_type_id);
            });
        }

        $productos = $query->orderByRaw('(stock_actual - stock_minimo) ASC')->get();

        $pdf = Pdf::loadView('reports.stock-bajo', compact('productos'));
        $pdf->setPaper('a4', 'landscape');
        
        return $pdf->download('stock_bajo_' . Carbon::now()->format('Ymd_His') . '.pdf');
    }

    /**
     * Reporte de productos próximos a vencer en PDF
     */
    public function proximosVencerPdf(Request $request)
    {
        $dias = $request->filled('dias') ? (int)$request->dias : 30;
        
        $query = Product::with(['section.stockType'])
            ->where('tiene_vencimiento', true)
            ->where('estado', true)
            ->whereNotNull('fecha_vencimiento')
            ->whereDate('fecha_vencimiento', '>', now())
            ->whereDate('fecha_vencimiento', '<=', now()->addDays($dias));

        if ($request->filled('section_id')) {
            $query->where('section_id', $request->section_id);
        }

        $productos = $query->orderBy('fecha_vencimiento', 'asc')->get();

        $pdf = Pdf::loadView('reports.proximos-vencer', compact('productos', 'dias'));
        $pdf->setPaper('a4', 'landscape');
        
        return $pdf->download('proximos_vencer_' . Carbon::now()->format('Ymd_His') . '.pdf');
    }

    /**
     * Reporte de movimientos en PDF
     */
    public function movimientosPdf(Request $request)
    {
        $query = Movement::with(['product.section.stockType', 'user', 'area']);

        $filtros = [];

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
            $filtros['product_id'] = $request->product_id;
        }

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
            $filtros['tipo'] = $request->tipo;
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
            $filtros['user_id'] = $request->user_id;
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('created_at', '>=', $request->fecha_desde);
            $filtros['fecha_desde'] = $request->fecha_desde;
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('created_at', '<=', $request->fecha_hasta);
            $filtros['fecha_hasta'] = $request->fecha_hasta;
        }

        if ($request->filled('section_id')) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('section_id', $request->section_id);
            });
            $filtros['section_id'] = $request->section_id;
        }

        if ($request->filled('area_id')) {
            $query->where('area_id', $request->area_id);
            $filtros['area_id'] = $request->area_id;
        }

        $movimientos = $query->orderBy('created_at', 'desc')->limit(200)->get();

        $pdf = Pdf::loadView('reports.movimientos', compact('movimientos', 'filtros'));
        $pdf->setPaper('a4', 'landscape');
        
        return $pdf->download('movimientos_' . Carbon::now()->format('Ymd_His') . '.pdf');
    }

    /**
     * Kardex de un producto en PDF
     */
    public function kardexPdf(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $producto = Product::with('section.stockType')->findOrFail($request->product_id);
        
        $query = Movement::where('product_id', $request->product_id)
            ->with(['user', 'area']);

        $fechaDesde = null;
        $fechaHasta = null;

        if ($request->filled('fecha_desde')) {
            $query->whereDate('created_at', '>=', $request->fecha_desde);
            $fechaDesde = $request->fecha_desde;
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('created_at', '<=', $request->fecha_hasta);
            $fechaHasta = $request->fecha_hasta;
        }

        $movimientos = $query->orderBy('created_at', 'asc')->get();

        $pdf = Pdf::loadView('reports.kardex', compact('producto', 'movimientos', 'fechaDesde', 'fechaHasta'));
        $pdf->setPaper('a4', 'landscape');
        
        return $pdf->download('kardex_producto_' . $producto->codigo . '_' . Carbon::now()->format('Ymd_His') . '.pdf');
    }

    /**
     * Reporte de productos vencidos en PDF
     */
    public function vencidosPdf(Request $request)
    {
        $query = Product::with(['section.stockType'])
            ->where('tiene_vencimiento', true)
            ->where('estado', true)
            ->whereNotNull('fecha_vencimiento')
            ->whereDate('fecha_vencimiento', '<', now());

        if ($request->filled('section_id')) {
            $query->where('section_id', $request->section_id);
        }

        $productos = $query->orderBy('fecha_vencimiento', 'desc')->get();

        $pdf = Pdf::loadView('reports.productos-vencidos', compact('productos'));
        $pdf->setPaper('a4', 'landscape');
        
        return $pdf->download('productos_vencidos_' . Carbon::now()->format('Ymd_His') . '.pdf');
    }

    /**
     * Reporte por sección en PDF
     */
    public function seccionPdf(Request $request)
    {
        $query = Product::with(['section.stockType'])->where('estado', true);

        if ($request->filled('section_id')) {
            $query->where('section_id', $request->section_id);
        }

        $productos = $query->orderBy('section_id')->orderBy('codigo')->get();

        $pdf = Pdf::loadView('reports.seccion', compact('productos'));
        $pdf->setPaper('a4', 'landscape');
        
        return $pdf->download('reporte_seccion_' . Carbon::now()->format('Ymd_His') . '.pdf');
    }
}
