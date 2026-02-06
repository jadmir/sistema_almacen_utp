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
use Carbon\Carbon;

class ProximosVencerReportExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    protected $dias;
    
    public function __construct($dias = 30)
    {
        $this->dias = $dias;
    }
    
    public function collection()
    {
        return Product::with(['section.stockType'])
            ->where('tiene_vencimiento', true)
            ->whereNotNull('fecha_vencimiento')
            ->where('fecha_vencimiento', '<=', now()->addDays($this->dias))
            ->where('fecha_vencimiento', '>=', now())
            ->where('estado', true)
            ->orderBy('fecha_vencimiento', 'asc')
            ->get()
            ->map(function ($product) {
                $diasRestantes = now()->diffInDays($product->fecha_vencimiento, false);
                
                $urgencia = match(true) {
                    $diasRestantes <= 7 => '🔴 CRÍTICO',
                    $diasRestantes <= 15 => '🟠 URGENTE',
                    $diasRestantes <= 30 => '🟡 PRÓXIMO',
                    default => '🟢 NORMAL'
                };
                
                return [
                    $product->codigo,
                    $product->nombre,
                    $product->section->nombre,
                    $product->stock_actual,
                    $product->unidad_medida,
                    $product->fecha_vencimiento->format('d/m/Y'),
                    $diasRestantes . ' días',
                    $urgencia,
                    $product->ubicacion ?? '-',
                ];
            });
    }
    
    public function headings(): array
    {
        return [
            'Código',
            'Producto',
            'Sección',
            'Stock Actual',
            'Unidad',
            'Fecha Vencimiento',
            'Días Restantes',
            'Urgencia',
            'Ubicación',
        ];
    }
    
    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFA500']],
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
            'F' => 18,
            'G' => 15,
            'H' => 15,
            'I' => 20,
        ];
    }
    
    public function title(): string
    {
        return 'Próximos a Vencer - ' . $this->dias . ' días';
    }
}
