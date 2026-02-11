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
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Product::with(['section.stockType', 'deposito'])
            ->whereRaw('stock_actual <= stock_minimo')
            ->where('estado', true);

        // Aplicar filtros
        if (!empty($this->filters['section_id'])) {
            $query->where('section_id', $this->filters['section_id']);
        }

        if (!empty($this->filters['stock_type_id'])) {
            $query->whereHas('section', function ($q) {
                $q->where('stock_type_id', $this->filters['stock_type_id']);
            });
        }

        if (!empty($this->filters['deposito_id'])) {
            $query->where('deposito_id', $this->filters['deposito_id']);
        }

        return $query->orderBy('stock_actual', 'asc')
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
                    $product->deposito ? $product->deposito->nombre : 'Sin depósito',
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
            'Depósito',
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
            'E' => 35,
            'F' => 12,
            'G' => 12,
            'H' => 12,
            'I' => 12,
            'J' => 12,
            'K' => 20,
            'L' => 15,
        ];
    }

    public function title(): string
    {
        return 'Alertas - Stock Bajo';
    }
}
