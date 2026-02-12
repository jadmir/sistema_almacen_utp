<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Elimina la restricción UNIQUE del campo 'codigo' en la tabla sections
     * para permitir que varias secciones puedan tener el mismo código (ej: "001")
     */
    public function up(): void
    {
        Schema::table('sections', function (Blueprint $table) {
            $table->dropUnique(['codigo']); // Eliminar índice unique del código
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sections', function (Blueprint $table) {
            $table->unique('codigo'); // Restaurar restricción unique
        });
    }
};
