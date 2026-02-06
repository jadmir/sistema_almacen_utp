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
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_type_id')->constrained('stock_types')->onDelete('cascade');
            $table->string('codigo', 20)->unique(); // ASSAL, ASSSP, CAJA 01, etc.
            $table->string('nombre'); // Artículos de limpieza, Consumibles, etc.
            $table->string('descripcion')->nullable();
            $table->boolean('estado')->default(true);
            $table->timestamps();
            
            $table->index(['stock_type_id', 'codigo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sections');
    }
};
