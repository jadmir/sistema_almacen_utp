<?php

namespace App\Exports;

use App\Models\Section;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class SectionReportExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    protected $sectionId;
    
    public function __construct($sectionId = null)
    {
        $this->sectionId = $sectionId;
    }
    
    public function collection()
    {
        $query = Section::with(['stockType', 'products']);
        
        if ($this->sectionId) {
            $query->where('id', $this->sectionId);
        }
        
        $sections = $query->get();
        
        $data = collect();
        
        foreach ($sections as $section) {
            // Encabezado de la sección
            $data->push([
                'SECCIÓN: ' . $section->nombre . ' (' . $section->codigo . ')',
                '',
                '',
                '',
                '',
            ]);
            
            $data->push([
                'Tipo de Stock:',
                $section->stockType->nombre,
                '',
                '',
                '',
            ]);
            
            $data->push(['', '', '', '', '']); // Espacio
            
            $totalProductos = $section->products->count();
            $productosStockBajo = 0;
            
            foreach ($section->products as $product) {
                if ($product->isLowStock()) {
                    $productosStockBajo++;
                }
                
                $data->push([
                    $product->codigo,
                    $product->nombre,
                    $product->stock_actual,
                    $product->stock_minimo,
                    $product->unidad_medida,
                ]);
            }
            
            // Resumen de la sección
            $data->push(['', '', '', '', '']);
            $data->push([
                'RESUMEN:',
                '',
                '',
                '',
                '',
            ]);
            $data->push([
                'Total Productos:',
                $totalProductos,
                '',
                '',
                '',
            ]);
            $data->push([
                'Productos Stock Bajo:',
                $productosStockBajo,
                '',
                '',
                '',
            ]);
            
            $data->push(['', '', '', '', '']); // Espacio grande
        }
        
        return $data;
    }
    
    public function headings(): array
    {
        return [
            'Código',
            'Producto',
            'Stock Actual',
            'Stock Mínimo',
            'Unidad',
        ];
    }
    
    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFC000']],
            ],
        ];
    }
    
    public function columnWidths(): array
    {
        return [
            'A' => 15,
            'B' => 40,
            'C' => 12,
            'D' => 12,
            'E' => 12,
        ];
    }
    
    public function title(): string
    {
        return 'Reporte por Sección';
    }
}
