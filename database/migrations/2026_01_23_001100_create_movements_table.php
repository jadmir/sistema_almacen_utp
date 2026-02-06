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
        Schema::create('movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('usuarios')->onDelete('restrict');
            $table->enum('tipo', ['ENTRADA', 'SALIDA', 'AJUSTE']); // Tipo de movimiento
            $table->integer('cantidad');
            $table->integer('stock_anterior');
            $table->integer('stock_posterior');
            $table->string('motivo'); // Compra, Venta, Devolución, Ajuste de inventario, etc.
            $table->text('observaciones')->nullable();
            $table->string('documento_referencia')->nullable(); // N° de factura, guía, etc.
            $table->timestamp('fecha_movimiento');
            $table->timestamps();
            
            $table->index(['product_id', 'fecha_movimiento']);
            $table->index('user_id');
            $table->index('tipo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movements');
    }
};
