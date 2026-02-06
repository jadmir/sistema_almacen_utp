<?php

namespace App\Exports;

use App\Models\Movement;
use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class KardexReportExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    protected $productId;
    protected $fechaDesde;
    protected $fechaHasta;
    
    public function __construct($productId, $fechaDesde = null, $fechaHasta = null)
    {
        $this->productId = $productId;
        $this->fechaDesde = $fechaDesde;
        $this->fechaHasta = $fechaHasta;
    }
    
    public function collection()
    {
        $product = Product::with('section.stockType')->findOrFail($this->productId);
        
        $query = Movement::where('product_id', $this->productId)
            ->with(['user', 'area']);
        
        if ($this->fechaDesde) {
            $query->whereDate('created_at', '>=', $this->fechaDesde);
        }
        
        if ($this->fechaHasta) {
            $query->whereDate('created_at', '<=', $this->fechaHasta);
        }
        
        $movements = $query->orderBy('created_at', 'asc')->get();
        
        // Encabezado con información del producto
        $data = collect([
            ['KARDEX DE PRODUCTO', '', '', '', '', '', '', '', '', ''],
            ['Código:', $product->codigo, '', '', '', '', '', '', '', ''],
            ['Producto:', $product->nombre, '', '', '', '', '', '', '', ''],
            ['Sección:', $product->section->nombre, '', '', '', '', '', '', '', ''],
            ['Tipo Stock:', $product->section->stockType->nombre, '', '', '', '', '', '', '', ''],
            ['Unidad:', $product->unidad_medida, '', '', '', '', '', '', '', ''],
            ['Tiene Vencimiento:', $product->tiene_vencimiento ? 'SÍ' : 'NO', '', '', '', '', '', '', '', ''],
            ['Fecha Vencimiento:', $product->fecha_vencimiento ? $product->fecha_vencimiento->format('d/m/Y') : 'N/A', '', '', '', '', '', '', '', ''],
            ['', '', '', '', '', '', '', '', '', ''],
        ]);
        
        // Agregar movimientos
        foreach ($movements as $movement) {
            $data->push([
                $movement->created_at->format('d/m/Y H:i'),
                $movement->tipo,
                $movement->tipo === 'ENTRADA' ? $movement->cantidad : '',
                $movement->tipo === 'SALIDA' ? $movement->cantidad : '',
                $movement->tipo === 'AJUSTE' ? $movement->cantidad : '',
                $movement->stock_anterior,
                $movement->stock_posterior,
                $movement->motivo,
                $movement->area ? $movement->area->nombre : '-',
                $movement->user->nombre,
            ]);
        }
        
        return $data;
    }
    
    public function headings(): array
    {
        return [
            'Fecha y Hora',
            'Tipo',
            'Entrada',
            'Salida',
            'Ajuste',
            'Stock Anterior',
            'Stock Posterior',
            'Motivo',
            'Área',
            'Usuario',
        ];
    }
    
    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 16],
                'alignment' => ['horizontal' => 'center'],
            ],
            10 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
            ],
        ];
    }
    
    public function columnWidths(): array
    {
        return [
            'A' => 18,
            'B' => 12,
            'C' => 10,
            'D' => 10,
            'E' => 10,
            'F' => 12,
            'G' => 12,
            'H' => 35,
            'I' => 25,
            'J' => 20,
        ];
    }
    
    public function title(): string
    {
        return 'Kardex de Producto';
    }
}
