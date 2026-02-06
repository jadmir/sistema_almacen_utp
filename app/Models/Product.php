<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'section_id',
        'codigo',
        'nombre',
        'descripcion',
        'unidad_medida',
        'stock_actual',
        'stock_minimo',
        'stock_maximo',
        'tiene_vencimiento',
        'fecha_vencimiento',
        'ubicacion',
        'estado',
    ];

    protected $casts = [
        'stock_actual' => 'integer',
        'stock_minimo' => 'integer',
        'stock_maximo' => 'integer',
        'tiene_vencimiento' => 'boolean',
        'fecha_vencimiento' => 'date',
        'estado' => 'boolean',
    ];

    /**
     * Relación con sección
     */
    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    /**
     * Relación con movimientos
     */
    public function movements(): HasMany
    {
        return $this->hasMany(Movement::class);
    }

    /**
     * Verificar si el stock está bajo
     */
    public function isLowStock(): bool
    {
        return $this->stock_actual <= $this->stock_minimo;
    }

    /**
     * Verificar si hay stock disponible
     */
    public function hasStock(int $quantity = 1): bool
    {
        return $this->stock_actual >= $quantity;
    }
}
