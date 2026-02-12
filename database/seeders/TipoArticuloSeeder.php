<?php

namespace Database\Seeders;

use App\Models\Section;
use App\Models\StockType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TipoArticuloSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Crea secciones para tipos de artículos:
     * - Consumibles
     * - Insumos de limpieza
     * - Insumos de botiquín
     */
    public function run(): void
    {
        // Obtener el tipo de stock "Productos Generales"
        $productosGenerales = StockType::where('nombre', 'Productos Generales')->first();

        if (!$productosGenerales) {
            $this->command->error('No se encontró el tipo de stock "Productos Generales". Ejecuta StockSystemSeeder primero.');
            return;
        }

        // 1. CONSUMIBLES
        $consumibles = Section::where('nombre', 'Consumibles')->first();
        if (!$consumibles) {
            Section::create([
                'stock_type_id' => $productosGenerales->id,
                'codigo' => 'ASSCONS',
                'nombre' => 'Consumibles',
                'descripcion' => 'Artículos y materiales consumibles generales',
                'estado' => true,
            ]);
            $this->command->info('✓ Sección "Consumibles" creada');
        } else {
            $this->command->warn('- Sección "Consumibles" ya existe');
        }

        // 2. INSUMOS DE LIMPIEZA
        $insumosLimpieza = Section::where('nombre', 'Insumos de limpieza')->first();
        if (!$insumosLimpieza) {
            Section::create([
                'stock_type_id' => $productosGenerales->id,
                'codigo' => 'ASSINSL',
                'nombre' => 'Insumos de limpieza',
                'descripcion' => 'Productos e insumos para limpieza y mantenimiento',
                'estado' => true,
            ]);
            $this->command->info('✓ Sección "Insumos de limpieza" creada');
        } else {
            $this->command->warn('- Sección "Insumos de limpieza" ya existe');
        }

        // 3. INSUMOS DE BOTIQUÍN
        $insumosBotiquin = Section::where('nombre', 'Insumos de botiquín')->first();
        if (!$insumosBotiquin) {
            Section::create([
                'stock_type_id' => $productosGenerales->id,
                'codigo' => 'ASSBOTIQ',
                'nombre' => 'Insumos de botiquín',
                'descripcion' => 'Insumos médicos y materiales de botiquín de primeros auxilios',
                'estado' => true,
            ]);
            $this->command->info('✓ Sección "Insumos de botiquín" creada');
        } else {
            $this->command->warn('- Sección "Insumos de botiquín" ya existe');
        }

        $this->command->info('');
        $this->command->info('=== Seeder de Tipos de Artículos completado ===');
    }
}
