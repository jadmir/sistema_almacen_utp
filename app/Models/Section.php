<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Section extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_type_id',
        'codigo',
        'nombre',
        'descripcion',
        'estado',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];

    /**
     * Relación con tipo de stock
     */
    public function stockType(): BelongsTo
    {
        return $this->belongsTo(StockType::class);
    }

    /**
     * Relación con productos
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
