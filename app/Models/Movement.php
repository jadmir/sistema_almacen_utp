<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Movement extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'user_id',
        'area_id',
        'tipo',
        'cantidad',
        'stock_anterior',
        'stock_posterior',
        'motivo',
        'observaciones',
        'documento_referencia',
        'fecha_movimiento',
        // Campos para vale de cargo
        'numero_vale',
        'recibido_por',
        'dni_receptor',
        'cargo_receptor',
        'observaciones_receptor',
        'pdf_path',
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'stock_anterior' => 'integer',
        'stock_posterior' => 'integer',
        'fecha_movimiento' => 'datetime',
    ];

    /**
     * Relación con producto
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Relación con usuario
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con área
     */
    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    /**
     * Scope para obtener entradas
     */
    public function scopeEntradas($query)
    {
        return $query->where('tipo', 'ENTRADA');
    }

    /**
     * Scope para obtener salidas
     */
    public function scopeSalidas($query)
    {
        return $query->where('tipo', 'SALIDA');
    }

    /**
     * Scope para obtener ajustes
     */
    public function scopeAjustes($query)
    {
        return $query->where('tipo', 'AJUSTE');
    }

    /**
     * Scope para movimientos de hoy
     */
    public function scopeHoy($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Scope para movimientos del mes actual
     */
    public function scopeMesActual($query)
    {
        return $query->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year);
    }

    /**
     * Scope para filtrar por rango de fechas
     */
    public function scopeEntreFechas($query, $desde, $hasta)
    {
        return $query->whereBetween('created_at', [$desde, $hasta]);
    }

    /**
     * Scope para cargar relaciones básicas optimizadas
     */
    public function scopeConRelaciones($query)
    {
        return $query->with([
            'product:id,codigo,nombre,unidad_medida,section_id',
            'product.section:id,nombre,codigo,stock_type_id',
            'user:id,nombre,email',
            'area:id,nombre,codigo'
        ]);
    }

    /**
     * Scope para movimientos recientes
     */
    public function scopeRecientes($query, int $limit = 10)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }
}
