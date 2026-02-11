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
        Schema::table('movements', function (Blueprint $table) {
            // Campos para vale de cargo (solo en salidas)
            $table->string('numero_vale', 20)->nullable()->unique()->after('documento_referencia');
            $table->string('recibido_por', 255)->nullable()->after('numero_vale');
            $table->string('dni_receptor', 20)->nullable()->after('recibido_por');
            $table->string('cargo_receptor', 100)->nullable()->after('dni_receptor');
            $table->text('observaciones_receptor')->nullable()->after('cargo_receptor');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('movements', function (Blueprint $table) {
            $table->dropColumn([
                'numero_vale',
                'recibido_por',
                'dni_receptor',
                'cargo_receptor',
                'observaciones_receptor'
            ]);
        });
    }
};
