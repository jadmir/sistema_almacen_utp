<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stock_types', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique(); // Productos, Letreros, Tópico
            $table->string('descripcion')->nullable();
            $table->string('codigo_prefix', 10)->unique(); // ASSOF, ASSSP, CAJA
            $table->boolean('estado')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_types');
    }
};
