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

class StockBajoReportExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    public function collection()
    {
        return Product::with(['section.stockType'])
            ->whereRaw('stock_actual <= stock_minimo')
            ->where('estado', true)
            ->orderBy('stock_actual', 'asc')
            ->get()
            ->map(function ($product) {
                $diferencia = $product->stock_minimo - $product->stock_actual;
                $porcentaje = $product->stock_minimo > 0 
                    ? round(($product->stock_actual / $product->stock_minimo) * 100, 1) 
                    : 0;
                
                return [
                    $product->codigo,
                    $product->nombre,
                    $product->section->nombre,
                    $product->section->stockType->nombre,
                    $product->stock_actual,
                    $product->stock_minimo,
                    $diferencia,
                    $porcentaje . '%',
                    $product->unidad_medida,
                    $product->ubicacion ?? '-',
                    '⚠️ URGENTE',
                ];
            });
    }
    
    public function headings(): array
    {
        return [
            'Código',
            'Producto',
            'Sección',
            'Tipo de Stock',
            'Stock Actual',
            'Stock Mínimo',
            'Faltante',
            '% Disponible',
            'Unidad',
            'Ubicación',
            'Prioridad',
        ];
    }
    
    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'C00000']],
            ],
        ];
    }
    
    public function columnWidths(): array
    {
        return [
            'A' => 15,
            'B' => 40,
            'C' => 25,
            'D' => 25,
            'E' => 12,
            'F' => 12,
            'G' => 12,
            'H' => 12,
            'I' => 12,
            'J' => 20,
            'K' => 15,
        ];
    }
    
    public function title(): string
    {
        return 'Alertas - Stock Bajo';
    }
}
