<?php

namespace Database\Seeders;

use App\Models\StockType;
use App\Models\Section;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StockSystemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ========================================
        // 1. CREAR TIPOS DE STOCK
        // ========================================
        $productos = StockType::create([
            'nombre' => 'Productos Generales',
            'descripcion' => 'Inventario de productos generales del almacén',
            'codigo_prefix' => 'ASS',
            'estado' => true,
        ]);

        $letreros = StockType::create([
            'nombre' => 'Letreros y Señaléticas',
            'descripcion' => 'Inventario de letreros, stickers y señaléticas',
            'codigo_prefix' => 'ASSLT',
            'estado' => true,
        ]);

        $topico = StockType::create([
            'nombre' => 'Tópico',
            'descripcion' => 'Inventario de tópico médico',
            'codigo_prefix' => 'CAJA',
            'estado' => true,
        ]);

        // ========================================
        // 2. CREAR SECCIONES - PRODUCTOS GENERALES
        // ========================================
        Section::create([
            'stock_type_id' => $productos->id,
            'codigo' => 'ASSAL',
            'nombre' => 'Artículos de limpieza',
            'descripcion' => 'Productos y artículos de limpieza',
            'estado' => true,
        ]);

        Section::create([
            'stock_type_id' => $productos->id,
            'codigo' => 'ASSC',
            'nombre' => 'Consumibles',
            'descripcion' => 'Materiales consumibles',
            'estado' => true,
        ]);

        Section::create([
            'stock_type_id' => $productos->id,
            'codigo' => 'ASSOF',
            'nombre' => 'Artículos de oficina',
            'descripcion' => 'Útiles y artículos de oficina',
            'estado' => true,
        ]);

        Section::create([
            'stock_type_id' => $productos->id,
            'codigo' => 'ASSTC',
            'nombre' => 'Tachos',
            'descripcion' => 'Tachos y recipientes',
            'estado' => true,
        ]);

        Section::create([
            'stock_type_id' => $productos->id,
            'codigo' => 'ASSMB',
            'nombre' => 'Materiales básicos',
            'descripcion' => 'Materiales básicos diversos',
            'estado' => true,
        ]);

        Section::create([
            'stock_type_id' => $productos->id,
            'codigo' => 'ASSTP',
            'nombre' => 'Tópico',
            'descripcion' => 'Materiales y suministros médicos',
            'estado' => true,
        ]);

        // ========================================
        // 3. CREAR SECCIONES - LETREROS
        // ========================================
        Section::create([
            'stock_type_id' => $letreros->id,
            'codigo' => 'ASSSP',
            'nombre' => 'Stickers y pegatinas',
            'descripcion' => 'Stickers adhesivos y pegatinas',
            'estado' => true,
        ]);

        Section::create([
            'stock_type_id' => $letreros->id,
            'codigo' => 'ASSAF',
            'nombre' => 'Letreros de Aforo',
            'descripcion' => 'Letreros indicadores de aforo',
            'estado' => true,
        ]);

        Section::create([
            'stock_type_id' => $letreros->id,
            'codigo' => 'ASSSA',
            'nombre' => 'Señaletica ascensores',
            'descripcion' => 'Señalética para ascensores',
            'estado' => true,
        ]);

        Section::create([
            'stock_type_id' => $letreros->id,
            'codigo' => 'ASSLT',
            'nombre' => 'Letreros',
            'descripcion' => 'Letreros informativos diversos',
            'estado' => true,
        ]);

        // ========================================
        // 4. CREAR SECCIONES - TÓPICO
        // ========================================
        Section::create([
            'stock_type_id' => $topico->id,
            'codigo' => 'CAJA 01',
            'nombre' => 'Caja 01',
            'descripcion' => 'Materiales médicos - Caja 01',
            'estado' => true,
        ]);

        Section::create([
            'stock_type_id' => $topico->id,
            'codigo' => 'CAJA 02',
            'nombre' => 'Caja 02',
            'descripcion' => 'Materiales médicos - Caja 02',
            'estado' => true,
        ]);

        Section::create([
            'stock_type_id' => $topico->id,
            'codigo' => 'CAJA 03',
            'nombre' => 'Caja 03',
            'descripcion' => 'Materiales médicos - Caja 03',
            'estado' => true,
        ]);

        Section::create([
            'stock_type_id' => $topico->id,
            'codigo' => 'CAJA 04',
            'nombre' => 'Caja 04',
            'descripcion' => 'Materiales médicos - Caja 04',
            'estado' => true,
        ]);

        Section::create([
            'stock_type_id' => $topico->id,
            'codigo' => 'CAJA 05',
            'nombre' => 'Caja 05',
            'descripcion' => 'Materiales médicos - Caja 05',
            'estado' => true,
        ]);

        Section::create([
            'stock_type_id' => $topico->id,
            'codigo' => 'CAJA 06',
            'nombre' => 'Caja 06',
            'descripcion' => 'Materiales médicos - Caja 06',
            'estado' => true,
        ]);
    }
}
