<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlantillaEntrega extends Model
{
    protected $table = 'plantillas_entregas';

    protected $fillable = [
        'nombre',
        'descripcion',
        'area_id',
        'motivo',
        'activa',
        'created_by',
    ];

    protected $casts = [
        'activa' => 'boolean',
    ];

    /**
     * Relación con el área
     */
    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class, 'area_id');
    }

    /**
     * Relación con el usuario creador
     */
    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relación con los detalles (productos)
     */
    public function detalles(): HasMany
    {
        return $this->hasMany(PlantillaEntregaDetalle::class, 'plantilla_id');
    }
}
