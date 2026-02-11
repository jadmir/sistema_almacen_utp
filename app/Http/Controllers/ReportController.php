<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
            'deposito_id' => $request->filled('deposito_id') ? $request->deposito_id : null,
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
            'deposito_id' => $request->filled('deposito_id') ? $request->deposito_id : null,
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
        $depositoId = $request->filled('deposito_id') ? $request->deposito_id : null;

        $fileName = 'proximos_vencer_' . Carbon::now()->format('Ymd_His') . '.xlsx';

        return Excel::download(new ProximosVencerReportExport($dias, $sectionId, $depositoId), $fileName);
    }

    /**
     * Reporte de productos vencidos
     */
    public function vencidos(Request $request)
    {
        $sectionId = $request->filled('section_id') ? $request->section_id : null;
        $depositoId = $request->filled('deposito_id') ? $request->deposito_id : null;

        $fileName = 'productos_vencidos_' . Carbon::now()->format('Ymd_His') . '.xlsx';

        return Excel::download(new ProductosVencidosReportExport($sectionId, $depositoId), $fileName);
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
        $query = Product::with(['section.stockType', 'deposito'])->where('estado', true);

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
        $query = Product::with(['section.stockType', 'deposito'])
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

        $query = Product::with(['section.stockType', 'deposito'])
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
        $query = Product::with(['section.stockType', 'deposito'])
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

    /**
     * Generar PDF de Vale de Cargo para una salida
     */
    public function valeCargoPdf($movementId)
    {
        try {
            $movimiento = Movement::with([
                'product.section',
                'user:id,nombre,email',
                'area:id,nombre,codigo'
            ])->findOrFail($movementId);

            // Validar que sea un movimiento de salida
            if ($movimiento->tipo !== 'SALIDA') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Solo se pueden generar vales de cargo para movimientos de salida'
                ], 400);
            }

            // Validar que tenga los datos del receptor
            if (!$movimiento->recibido_por) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Este movimiento no tiene datos de recepción registrados'
                ], 400);
            }

            $pdf = Pdf::loadView('reports.vale-cargo', compact('movimiento'));
            $pdf->setPaper('a4', 'portrait');

            $filename = 'vale_cargo_' . $movimiento->numero_vale . '.pdf';

            // Guardar PDF en el servidor como evidencia
            $pdfContent = $pdf->output();
            $path = 'vales_cargo/' . date('Y/m') . '/' . $filename;
            Storage::disk('public')->put($path, $pdfContent);

            // Guardar la ruta en la base de datos
            $movimiento->update(['pdf_path' => $path]);

            return $pdf->download($filename);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al generar vale de cargo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Listar vales de cargo generados
     */
    public function listarVales(Request $request)
    {
        try {
            $query = Movement::with([
                'product:id,codigo,nombre',
                'user:id,nombre',
                'area:id,nombre,codigo'
            ])
            ->where('tipo', 'SALIDA')
            ->whereNotNull('numero_vale')
            ->orderBy('created_at', 'desc');

            // Filtros opcionales
            if ($request->filled('fecha_desde')) {
                $query->whereDate('fecha_movimiento', '>=', $request->fecha_desde);
            }

            if ($request->filled('fecha_hasta')) {
                $query->whereDate('fecha_movimiento', '<=', $request->fecha_hasta);
            }

            if ($request->filled('numero_vale')) {
                $query->where('numero_vale', 'like', '%' . $request->numero_vale . '%');
            }

            if ($request->filled('recibido_por')) {
                $query->where('recibido_por', 'like', '%' . $request->recibido_por . '%');
            }

            if ($request->filled('area_id')) {
                $query->where('area_id', $request->area_id);
            }

            $perPage = $request->input('per_page', 15);
            $vales = $query->paginate($perPage);

            return response()->json([
                'status' => 'success',
                'data' => $vales
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al listar vales de cargo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Descargar PDF de vale previamente generado
     */
    public function descargarVale($movementId)
    {
        try {
            $movimiento = Movement::findOrFail($movementId);

            // Verificar que existe el PDF guardado
            if (!$movimiento->pdf_path || !Storage::disk('public')->exists($movimiento->pdf_path)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'El PDF de este vale no está disponible. Genere uno nuevo.'
                ], 404);
            }

            $filename = 'vale_cargo_' . $movimiento->numero_vale . '.pdf';
            $fullPath = Storage::disk('public')->path($movimiento->pdf_path);

            return response()->download($fullPath, $filename);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al descargar el vale',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
