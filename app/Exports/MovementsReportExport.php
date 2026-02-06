<?php

namespace App\Exports;

use App\Models\Movement;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class MovementsReportExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    protected $filters;
    
    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }
    
    public function collection()
    {
        $query = Movement::with(['product.section.stockType', 'user', 'area']);
        
        // Aplicar filtros
        if (!empty($this->filters['product_id'])) {
            $query->where('product_id', $this->filters['product_id']);
        }
        
        if (!empty($this->filters['tipo'])) {
            $query->where('tipo', $this->filters['tipo']);
        }
        
        if (!empty($this->filters['user_id'])) {
            $query->where('user_id', $this->filters['user_id']);
        }
        
        if (!empty($this->filters['fecha_desde'])) {
            $query->whereDate('created_at', '>=', $this->filters['fecha_desde']);
        }
        
        if (!empty($this->filters['fecha_hasta'])) {
            $query->whereDate('created_at', '<=', $this->filters['fecha_hasta']);
        }
        
        if (!empty($this->filters['section_id'])) {
            $query->whereHas('product', function ($q) {
                $q->where('section_id', $this->filters['section_id']);
            });
        }
        
        if (!empty($this->filters['stock_type_id'])) {
            $query->whereHas('product.section', function ($q) {
                $q->where('stock_type_id', $this->filters['stock_type_id']);
            });
        }
        
        return $query->orderBy('created_at', 'desc')->get()->map(function ($movement) {
            $tipoIcono = match($movement->tipo) {
                'ENTRADA' => '📥 ENTRADA',
                'SALIDA' => '📤 SALIDA',
                'AJUSTE' => '⚙️ AJUSTE',
                default => $movement->tipo
            };
            
            return [
                $movement->created_at->format('d/m/Y H:i'),
                $tipoIcono,
                $movement->product->codigo,
                $movement->product->nombre,
                $movement->product->section->nombre,
                $movement->product->section->stockType->nombre,
                $movement->cantidad,
                $movement->product->unidad_medida,
                $movement->stock_anterior,
                $movement->stock_posterior,
                $movement->motivo,
                $movement->observaciones ?? '-',
                $movement->area ? $movement->area->nombre . ' (' . $movement->area->codigo . ')' : '-',
                $movement->user->nombre,
            ];
        });
    }
    
    public function headings(): array
    {
        return [
            'Fecha y Hora',
            'Tipo Movimiento',
            'Código Producto',
            'Producto',
            'Sección',
            'Tipo de Stock',
            'Cantidad',
            'Unidad',
            'Stock Anterior',
            'Stock Posterior',
            'Motivo',
            'Observaciones',
            'Área Destino',
            'Usuario',
        ];
    }
    
    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2E75B6']],
            ],
        ];
    }
    
    public function columnWidths(): array
    {
        return [
            'A' => 18, // Fecha
            'B' => 15, // Tipo
            'C' => 15, // Código
            'D' => 35, // Producto
            'E' => 25, // Sección
            'F' => 25, // Tipo Stock
            'G' => 10, // Cantidad
            'H' => 10, // Unidad
            'I' => 12, // Stock Ant
            'J' => 12, // Stock Post
            'K' => 30, // Motivo
            'L' => 30, // Observaciones
            'M' => 25, // Área
            'N' => 20, // Usuario
        ];
    }
    
    public function title(): string
    {
        return 'Historial de Movimientos';
    }
}
