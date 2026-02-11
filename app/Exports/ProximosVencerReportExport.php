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
    protected $sectionId;
    protected $depositoId;

    public function __construct($dias = 30, $sectionId = null, $depositoId = null)
    {
        $this->dias = $dias;
        $this->sectionId = $sectionId;
        $this->depositoId = $depositoId;
    }

    public function collection()
    {
        $query = Product::with(['section.stockType', 'deposito'])
            ->where('tiene_vencimiento', true)
            ->whereNotNull('fecha_vencimiento')
            ->where('fecha_vencimiento', '<=', now()->addDays($this->dias))
            ->where('fecha_vencimiento', '>=', now())
            ->where('estado', true);

        // Aplicar filtros
        if ($this->sectionId) {
            $query->where('section_id', $this->sectionId);
        }

        if ($this->depositoId) {
            $query->where('deposito_id', $this->depositoId);
        }

        return $query->orderBy('fecha_vencimiento', 'asc')
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
                    $product->deposito ? $product->deposito->nombre : 'Sin depósito',
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
            'Depósito',
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
            'D' => 35,
            'E' => 12,
            'F' => 12,
            'G' => 18,
            'H' => 15,
            'I' => 15,
            'J' => 20,
        ];
    }

    public function title(): string
    {
        return 'Próximos a Vencer - ' . $this->dias . ' días';
    }
}
