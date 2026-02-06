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

class ProductosVencidosReportExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    public function collection()
    {
        return Product::with(['section.stockType'])
            ->where('tiene_vencimiento', true)
            ->whereNotNull('fecha_vencimiento')
            ->where('fecha_vencimiento', '<', now())
            ->where('estado', true)
            ->orderBy('fecha_vencimiento', 'desc')
            ->get()
            ->map(function ($product) {
                $diasVencidos = now()->diffInDays($product->fecha_vencimiento, false);
                
                return [
                    $product->codigo,
                    $product->nombre,
                    $product->section->nombre,
                    $product->section->stockType->nombre,
                    $product->stock_actual,
                    $product->unidad_medida,
                    $product->fecha_vencimiento->format('d/m/Y'),
                    abs($diasVencidos) . ' días',
                    $product->ubicacion ?? '-',
                    '❌ RETIRAR',
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
            'Unidad',
            'Fecha Vencimiento',
            'Días Vencido',
            'Ubicación',
            'Acción',
        ];
    }
    
    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '8B0000']],
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
            'G' => 18,
            'H' => 15,
            'I' => 20,
            'J' => 15,
        ];
    }
    
    public function title(): string
    {
        return 'Productos Vencidos';
    }
}
