<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Crear índices solo si no existen (usar try-catch para evitar errores)
        $this->createIndexIfNotExists('products', 'codigo');
        $this->createIndexIfNotExists('products', 'estado');
        $this->createIndexIfNotExists('products', ['estado', 'stock_actual'], 'products_estado_stock_actual_index');
        $this->createIndexIfNotExists('products', ['tiene_vencimiento', 'fecha_vencimiento'], 'products_tiene_vencimiento_fecha_vencimiento_index');
        $this->createIndexIfNotExists('products', 'created_at');

        $this->createIndexIfNotExists('movements', 'tipo');
        $this->createIndexIfNotExists('movements', 'created_at');
        $this->createIndexIfNotExists('movements', 'fecha_movimiento');
        $this->createIndexIfNotExists('movements', ['tipo', 'created_at'], 'movements_tipo_created_at_index');

        $this->createIndexIfNotExists('sections', 'codigo');

        $this->createIndexIfNotExists('usuarios', 'estado');
    }

    /**
     * Crear índice solo si no existe
     */
    private function createIndexIfNotExists(string $table, $column, ?string $indexName = null): void
    {
        try {
            Schema::table($table, function (Blueprint $table) use ($column, $indexName) {
                if ($indexName) {
                    $table->index($column, $indexName);
                } else {
                    $table->index($column);
                }
            });
        } catch (\Exception $e) {
            // Si el índice ya existe, ignorar el error
            if (strpos($e->getMessage(), 'Duplicate key name') === false) {
                throw $e;
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['codigo']);
            $table->dropIndex(['estado']);
            $table->dropIndex(['estado', 'stock_actual']);
            $table->dropIndex(['tiene_vencimiento', 'fecha_vencimiento']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('movements', function (Blueprint $table) {
            $table->dropIndex(['tipo']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['fecha_movimiento']);
            $table->dropIndex(['tipo', 'created_at']);
        });

        Schema::table('sections', function (Blueprint $table) {
            $table->dropIndex(['codigo']);
        });

        Schema::table('usuarios', function (Blueprint $table) {
            $table->dropIndex(['estado']);
        });
    }
};
