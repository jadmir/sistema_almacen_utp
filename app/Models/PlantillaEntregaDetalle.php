<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlantillaEntregaDetalle extends Model
{
    protected $table = 'plantillas_entregas_detalle';

    protected $fillable = [
        'plantilla_id',
        'product_id',
        'cantidad',
        'observaciones',
    ];

    /**
     * Relación con la plantilla
     */
    public function plantilla(): BelongsTo
    {
        return $this->belongsTo(PlantillaEntrega::class, 'plantilla_id');
    }

    /**
     * Relación con el producto
     */
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
