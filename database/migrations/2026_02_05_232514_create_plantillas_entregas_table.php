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
        // Tabla principal de plantillas
        Schema::create('plantillas_entregas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre'); // Ej: "Entrega mensual área administrativa"
            $table->string('descripcion')->nullable();
            $table->foreignId('area_id')->constrained('areas')->onDelete('cascade'); // Área de destino
            $table->string('motivo')->default('Entrega mensual programada'); // Motivo por defecto
            $table->boolean('activa')->default(true);
            $table->foreignId('created_by')->constrained('usuarios')->onDelete('cascade'); // Usuario que la creó
            $table->timestamps();
        });

        // Tabla de detalle de productos en la plantilla
        Schema::create('plantillas_entregas_detalle', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plantilla_id')->constrained('plantillas_entregas')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->integer('cantidad')->unsigned(); // Cantidad fija mensual
            $table->string('observaciones')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plantillas_entregas_detalle');
        Schema::dropIfExists('plantillas_entregas');
    }
};
