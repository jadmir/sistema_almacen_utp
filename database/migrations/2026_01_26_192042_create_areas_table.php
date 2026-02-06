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
        Schema::create('areas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique(); // Ej: Administración, Enfermería, Deportes, etc.
            $table->string('codigo', 20)->unique(); // Ej: ADM, ENF, DEP
            $table->text('descripcion')->nullable();
            $table->string('responsable')->nullable(); // Nombre del responsable del área
            $table->boolean('estado')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('codigo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('areas');
    }
};
