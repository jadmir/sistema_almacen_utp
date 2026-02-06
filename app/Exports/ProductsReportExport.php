<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ProductsReportExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    protected $filters;
    
    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }
    
    public function collection()
    {
        $query = Product::with(['section.stockType']);
        
        // Aplicar filtros
        if (!empty($this->filters['section_id'])) {
            $query->where('section_id', $this->filters['section_id']);
        }
        
        if (!empty($this->filters['stock_type_id'])) {
            $query->whereHas('section', function ($q) {
                $q->where('stock_type_id', $this->filters['stock_type_id']);
            });
        }
        
        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('codigo', 'like', "%{$search}%");
            });
        }
        
        if (isset($this->filters['estado'])) {
            $query->where('estado', $this->filters['estado']);
        }
        
        return $query->orderBy('codigo', 'asc')->get()->map(function ($product) {
            return [
                $product->codigo,
                $product->nombre,
                $product->section->nombre,
                $product->section->stockType->nombre,
                $product->stock_actual,
                $product->stock_minimo,
                $product->stock_maximo ?? '-',
                $product->unidad_medida,
                $product->ubicacion ?? '-',
                $product->isLowStock() ? '⚠️ STOCK BAJO' : '✓ OK',
                $product->estado ? 'Activo' : 'Inactivo',
            ];
        });
    }
    
    public function headings(): array
    {
        return [
            'Código',
            'Nombre del Producto',
            'Sección',
            'Tipo de Stock',
            'Stock Actual',
            'Stock Mínimo',
            'Stock Máximo',
            'Unidad',
            'Ubicación',
            'Estado Stock',
            'Estado',
        ];
    }
    
    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }
    
    public function columnWidths(): array
    {
        return [
            'A' => 15, // Código
            'B' => 40, // Nombre
            'C' => 25, // Sección
            'D' => 25, // Tipo Stock
            'E' => 12, // Stock Actual
            'F' => 12, // Stock Mínimo
            'G' => 12, // Stock Máximo
            'H' => 12, // Unidad
            'I' => 20, // Ubicación
            'J' => 15, // Estado Stock
            'K' => 12, // Estado
        ];
    }
    
    public function title(): string
    {
        return 'Inventario de Productos';
    }
}
