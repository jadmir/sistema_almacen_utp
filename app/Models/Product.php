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
        'deposito_id',
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
     * Relación con depósito
     */
    public function deposito(): BelongsTo
    {
        return $this->belongsTo(Deposito::class);
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

    // ==================== SCOPES ====================

    /**
     * Scope para productos activos
     */
    public function scopeActivo($query)
    {
        return $query->where('estado', true);
    }

    /**
     * Scope para productos con stock bajo
     */
    public function scopeStockBajo($query)
    {
        return $query->whereRaw('stock_actual <= stock_minimo');
    }

    /**
     * Scope para productos sin stock
     */
    public function scopeSinStock($query)
    {
        return $query->where('stock_actual', 0);
    }

    /**
     * Scope para productos próximos a vencer
     */
    public function scopeProximosVencer($query, int $dias = 30)
    {
        return $query->where('tiene_vencimiento', true)
            ->whereNotNull('fecha_vencimiento')
            ->whereDate('fecha_vencimiento', '>', now())
            ->whereDate('fecha_vencimiento', '<=', now()->addDays($dias));
    }

    /**
     * Scope para productos vencidos
     */
    public function scopeVencidos($query)
    {
        return $query->where('tiene_vencimiento', true)
            ->whereNotNull('fecha_vencimiento')
            ->whereDate('fecha_vencimiento', '<', now());
    }

    /**
     * Scope para búsqueda por texto
     */
    public function scopeBuscar($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('nombre', 'like', "%{$search}%")
              ->orWhere('codigo', 'like', "%{$search}%")
              ->orWhere('descripcion', 'like', "%{$search}%");
        });
    }

    /**
     * Scope para cargar relaciones básicas optimizadas
     */
    public function scopeConRelaciones($query)
    {
        return $query->with([
            'section:id,nombre,codigo,stock_type_id', 
            'section.stockType:id,nombre,codigo_prefix',
            'deposito:id,nombre'
        ]);
    }
}
