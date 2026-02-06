<?php

namespace App\Exports;

use App\Models\StockType;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class StockTypeReportExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    protected $stockTypeId;
    
    public function __construct($stockTypeId = null)
    {
        $this->stockTypeId = $stockTypeId;
    }
    
    public function collection()
    {
        $query = StockType::with(['sections.products']);
        
        if ($this->stockTypeId) {
            $query->where('id', $this->stockTypeId);
        }
        
        $stockTypes = $query->get();
        
        $data = collect();
        
        foreach ($stockTypes as $stockType) {
            // Encabezado del tipo de stock
            $data->push([
                'TIPO DE STOCK: ' . $stockType->nombre,
                '',
                '',
                '',
                '',
                '',
            ]);
            
            $totalProductos = 0;
            
            foreach ($stockType->sections as $section) {
                foreach ($section->products as $product) {
                    $totalProductos++;
                    
                    $data->push([
                        $product->codigo,
                        $product->nombre,
                        $section->nombre,
                        $product->stock_actual,
                        $product->unidad_medida,
                    ]);
                }
            }
            
            // Totales del tipo
            $data->push([
                '',
                '',
                'TOTAL ' . $stockType->nombre . ':',
                '',
                'Productos: ' . $totalProductos,
            ]);
            
            $data->push(['', '', '', '', '', '', '']); // Espacio
        }
        
        return $data;
    }
    
    public function headings(): array
    {
        return [
            'Código',
            'Producto',
            'Sección',
            'Stock Actual',
            'Unidad',
        ];
    }
    
    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '70AD47']],
            ],
        ];
    }
    
    public function columnWidths(): array
    {
        return [
            'A' => 15,
            'B' => 40,
            'C' => 25,
            'D' => 12,
            'E' => 12,
        ];
    }
    
    public function title(): string
    {
        return 'Reporte por Tipo de Stock';
    }
}
