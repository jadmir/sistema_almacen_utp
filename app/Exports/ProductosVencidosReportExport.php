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
    protected $sectionId;
    protected $depositoId;

    public function __construct($sectionId = null, $depositoId = null)
    {
        $this->sectionId = $sectionId;
        $this->depositoId = $depositoId;
    }

    public function collection()
    {
        $query = Product::with(['section.stockType', 'deposito'])
            ->where('tiene_vencimiento', true)
            ->whereNotNull('fecha_vencimiento')
            ->where('fecha_vencimiento', '<', now())
            ->where('estado', true);

        // Aplicar filtros
        if ($this->sectionId) {
            $query->where('section_id', $this->sectionId);
        }

        if ($this->depositoId) {
            $query->where('deposito_id', $this->depositoId);
        }

        return $query->orderBy('fecha_vencimiento', 'desc')
            ->get()
            ->map(function ($product) {
                $diasVencidos = now()->diffInDays($product->fecha_vencimiento, false);

                return [
                    $product->codigo,
                    $product->nombre,
                    $product->section->nombre,
                    $product->section->stockType->nombre,
                    $product->deposito ? $product->deposito->nombre : 'Sin depósito',
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
            'Depósito',
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
            'E' => 35,
            'F' => 12,
            'G' => 12,
            'H' => 18,
            'I' => 15,
            'J' => 20,
            'K' => 15,
        ];
    }

    public function title(): string
    {
        return 'Productos Vencidos';
    }
}
