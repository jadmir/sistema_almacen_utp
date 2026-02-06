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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained('sections')->onDelete('cascade');
            $table->string('codigo', 50)->unique(); // ASSOF-0149, ASSSP-0001, etc.
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->string('unidad_medida')->default('UNIDAD'); // UNIDAD, KG, LITRO, CAJA, etc.
            $table->integer('stock_actual')->default(0);
            $table->integer('stock_minimo')->default(0);
            $table->integer('stock_maximo')->nullable();
            $table->boolean('tiene_vencimiento')->default(false); // Si el producto tiene fecha de vencimiento
            $table->date('fecha_vencimiento')->nullable(); // Fecha de vencimiento (si aplica)
            $table->string('ubicacion')->nullable(); // Ubicación física en almacén
            $table->boolean('estado')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['section_id', 'codigo']);
            $table->index('stock_actual');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
