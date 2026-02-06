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
}
