<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Section;
use App\Models\Movement;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductExampleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener secciones
        $secciones = Section::all()->keyBy('codigo');

        // Obtener usuario admin para los movimientos
        $admin = User::first();

        // ========================================
        // PRODUCTOS - Artículos de Oficina (ASSOF)
        // ========================================

        // Producto 1: Lapicero azul (Stock normal)
        $lapicero = Product::create([
            'section_id' => $secciones['ASSOF']->id,
            'codigo' => 'ASSOF-0001',
            'nombre' => 'Lapicero azul Faber Castell',
            'descripcion' => 'Lapicero de tinta azul, punta fina 0.7mm',
            'unidad_medida' => 'UNIDAD',
            'stock_actual' => 150,
            'stock_minimo' => 50,
            'stock_maximo' => 500,
            'tiene_vencimiento' => false,
            'ubicacion' => 'Estante A-3',
            'estado' => true,
        ]);

        // Movimientos del lapicero
        Movement::create([
            'product_id' => $lapicero->id,
            'user_id' => $admin->id,
            'tipo' => 'ENTRADA',
            'cantidad' => 100,
            'stock_anterior' => 0,
            'stock_posterior' => 100,
            'motivo' => 'Compra inicial',
            'observaciones' => 'Stock inicial del sistema',
            'fecha_movimiento' => now()->subDays(30),
        ]);

        Movement::create([
            'product_id' => $lapicero->id,
            'user_id' => $admin->id,
            'tipo' => 'ENTRADA',
            'cantidad' => 50,
            'stock_anterior' => 100,
            'stock_posterior' => 150,
            'motivo' => 'Compra mensual',
            'observaciones' => 'Proveedor: Librería San José',
            'fecha_movimiento' => now()->subDays(15),
        ]);

        // Producto 2: Corrector líquido
        $corrector = Product::create([
            'section_id' => $secciones['ASSOF']->id,
            'codigo' => 'ASSOF-0149',
            'nombre' => 'Corrector líquido Artesco',
            'descripcion' => 'Corrector líquido blanco 20ml',
            'unidad_medida' => 'UNIDAD',
            'stock_actual' => 30,
            'stock_minimo' => 10,
            'stock_maximo' => 100,
            'tiene_vencimiento' => false,
            'ubicacion' => 'Estante B-2',
            'estado' => true,
        ]);

        Movement::create([
            'product_id' => $corrector->id,
            'user_id' => $admin->id,
            'tipo' => 'ENTRADA',
            'cantidad' => 30,
            'stock_anterior' => 0,
            'stock_posterior' => 30,
            'motivo' => 'Compra inicial',
            'observaciones' => 'Stock inicial del sistema',
            'fecha_movimiento' => now()->subDays(30),
        ]);

        // Producto 3: Grapas (Stock bajo - para alertas)
        $grapas = Product::create([
            'section_id' => $secciones['ASSOF']->id,
            'codigo' => 'ASSOF-0089',
            'nombre' => 'Grapas 26/6',
            'descripcion' => 'Caja de grapas estándar 26/6 x 1000 unidades',
            'unidad_medida' => 'CAJA',
            'stock_actual' => 5,
            'stock_minimo' => 20,
            'stock_maximo' => 100,
            'tiene_vencimiento' => false,
            'ubicacion' => 'Estante C-1',
            'estado' => true,
        ]);

        Movement::create([
            'product_id' => $grapas->id,
            'user_id' => $admin->id,
            'tipo' => 'ENTRADA',
            'cantidad' => 50,
            'stock_anterior' => 0,
            'stock_posterior' => 50,
            'motivo' => 'Compra inicial',
            'fecha_movimiento' => now()->subDays(60),
        ]);

        Movement::create([
            'product_id' => $grapas->id,
            'user_id' => $admin->id,
            'tipo' => 'SALIDA',
            'cantidad' => 45,
            'stock_anterior' => 50,
            'stock_posterior' => 5,
            'motivo' => 'Entrega a departamentos',
            'observaciones' => 'Distribución trimestral',
            'fecha_movimiento' => now()->subDays(10),
        ]);

        // Producto 4: Papel bond
        Product::create([
            'section_id' => $secciones['ASSOF']->id,
            'codigo' => 'ASSOF-0250',
            'nombre' => 'Papel bond A4 75g',
            'descripcion' => 'Paquete de 500 hojas tamaño A4',
            'unidad_medida' => 'PAQUETE',
            'stock_actual' => 20,
            'stock_minimo' => 5,
            'stock_maximo' => 100,
            'tiene_vencimiento' => false,
            'ubicacion' => 'Estante D-1',
            'estado' => true,
        ]);

        // ========================================
        // PRODUCTOS - Artículos de Limpieza (ASSAL)
        // ========================================

        // Producto 5: Desinfectante (Stock bajo)
        $desinfectante = Product::create([
            'section_id' => $secciones['ASSAL']->id,
            'codigo' => 'ASSAL-0023',
            'nombre' => 'Desinfectante pino',
            'descripcion' => 'Desinfectante aroma pino 1L',
            'unidad_medida' => 'LITRO',
            'stock_actual' => 3,
            'stock_minimo' => 10,
            'stock_maximo' => 50,
            'tiene_vencimiento' => false,
            'ubicacion' => 'Almacén Principal - Zona B',
            'estado' => true,
        ]);

        Movement::create([
            'product_id' => $desinfectante->id,
            'user_id' => $admin->id,
            'tipo' => 'ENTRADA',
            'cantidad' => 20,
            'stock_anterior' => 0,
            'stock_posterior' => 20,
            'motivo' => 'Compra inicial',
            'fecha_movimiento' => now()->subDays(45),
        ]);

        Movement::create([
            'product_id' => $desinfectante->id,
            'user_id' => $admin->id,
            'tipo' => 'SALIDA',
            'cantidad' => 17,
            'stock_anterior' => 20,
            'stock_posterior' => 3,
            'motivo' => 'Entrega a servicios de limpieza',
            'observaciones' => 'Consumo mensual',
            'fecha_movimiento' => now()->subDays(5),
        ]);

        // Producto 6: Lejía
        Product::create([
            'section_id' => $secciones['ASSAL']->id,
            'codigo' => 'ASSAL-0015',
            'nombre' => 'Lejía desinfectante',
            'descripcion' => 'Lejía al 5% - Garrafa 4L',
            'unidad_medida' => 'GARRAFA',
            'stock_actual' => 15,
            'stock_minimo' => 8,
            'stock_maximo' => 40,
            'tiene_vencimiento' => false,
            'ubicacion' => 'Almacén Principal - Zona B',
            'estado' => true,
        ]);

        // Producto 7: Escobas
        Product::create([
            'section_id' => $secciones['ASSAL']->id,
            'codigo' => 'ASSAL-0042',
            'nombre' => 'Escoba de palma',
            'descripcion' => 'Escoba de palma con mango de madera',
            'unidad_medida' => 'UNIDAD',
            'stock_actual' => 8,
            'stock_minimo' => 5,
            'stock_maximo' => 20,
            'tiene_vencimiento' => false,
            'ubicacion' => 'Almacén Principal - Zona C',
            'estado' => true,
        ]);

        // ========================================
        // PRODUCTOS - Consumibles (ASSC)
        // ========================================

        Product::create([
            'section_id' => $secciones['ASSC']->id,
            'codigo' => 'ASSC-0010',
            'nombre' => 'Vasos descartables 7oz',
            'descripcion' => 'Paquete de 100 vasos descartables transparentes',
            'unidad_medida' => 'PAQUETE',
            'stock_actual' => 25,
            'stock_minimo' => 10,
            'stock_maximo' => 100,
            'tiene_vencimiento' => false,
            'ubicacion' => 'Estante E-2',
            'estado' => true,
        ]);

        Product::create([
            'section_id' => $secciones['ASSC']->id,
            'codigo' => 'ASSC-0025',
            'nombre' => 'Servilletas de papel',
            'descripcion' => 'Paquete de 100 servilletas blancas',
            'unidad_medida' => 'PAQUETE',
            'stock_actual' => 30,
            'stock_minimo' => 15,
            'stock_maximo' => 80,
            'tiene_vencimiento' => false,
            'ubicacion' => 'Estante E-3',
            'estado' => true,
        ]);

        // ========================================
        // PRODUCTOS - Letreros (ASSSP)
        // ========================================

        Product::create([
            'section_id' => $secciones['ASSSP']->id,
            'codigo' => 'ASSSP-0001',
            'nombre' => 'Sticker "Prohibido fumar"',
            'descripcion' => 'Sticker adhesivo 15x15cm',
            'unidad_medida' => 'UNIDAD',
            'stock_actual' => 50,
            'stock_minimo' => 20,
            'stock_maximo' => 200,
            'tiene_vencimiento' => false,
            'ubicacion' => 'Estante F-1',
            'estado' => true,
        ]);

        Product::create([
            'section_id' => $secciones['ASSSP']->id,
            'codigo' => 'ASSSP-0015',
            'nombre' => 'Sticker "Salida de emergencia"',
            'descripcion' => 'Sticker fotoluminiscente 20x30cm',
            'unidad_medida' => 'UNIDAD',
            'stock_actual' => 35,
            'stock_minimo' => 15,
            'stock_maximo' => 100,
            'tiene_vencimiento' => false,
            'ubicacion' => 'Estante F-1',
            'estado' => true,
        ]);

        // ========================================
        // PRODUCTOS - Letreros de Aforo (ASSAF)
        // ========================================

        Product::create([
            'section_id' => $secciones['ASSAF']->id,
            'codigo' => 'ASSAF-0001',
            'nombre' => 'Letrero "Aforo máximo 20 personas"',
            'descripcion' => 'Letrero acrílico 30x20cm',
            'unidad_medida' => 'UNIDAD',
            'stock_actual' => 12,
            'stock_minimo' => 5,
            'stock_maximo' => 30,
            'tiene_vencimiento' => false,
            'ubicacion' => 'Estante F-2',
            'estado' => true,
        ]);

        // ========================================
        // PRODUCTOS - Tópico (CAJA 01-06)
        // ========================================

        // Productos con vencimiento en Tópico
        $alcohol = Product::create([
            'section_id' => $secciones['CAJA 01']->id,
            'codigo' => 'CAJA01-0001',
            'nombre' => 'Alcohol medicinal 96°',
            'descripcion' => 'Frasco de 250ml',
            'unidad_medida' => 'FRASCO',
            'stock_actual' => 18,
            'stock_minimo' => 10,
            'stock_maximo' => 50,
            'tiene_vencimiento' => true,
            'fecha_vencimiento' => '2027-12-31',
            'ubicacion' => 'Tópico - Caja 01',
            'estado' => true,
        ]);

        Movement::create([
            'product_id' => $alcohol->id,
            'user_id' => $admin->id,
            'tipo' => 'ENTRADA',
            'cantidad' => 18,
            'stock_anterior' => 0,
            'stock_posterior' => 18,
            'motivo' => 'Compra mensual',
            'observaciones' => 'Lote: AB12345, Venc: 31/12/2027',
            'fecha_movimiento' => now()->subDays(20),
        ]);

        Product::create([
            'section_id' => $secciones['CAJA 01']->id,
            'codigo' => 'CAJA01-0005',
            'nombre' => 'Gasas estériles 10x10cm',
            'descripcion' => 'Paquete de 10 unidades',
            'unidad_medida' => 'PAQUETE',
            'stock_actual' => 25,
            'stock_minimo' => 15,
            'stock_maximo' => 60,
            'tiene_vencimiento' => true,
            'fecha_vencimiento' => '2026-08-15',
            'ubicacion' => 'Tópico - Caja 01',
            'estado' => true,
        ]);

        Product::create([
            'section_id' => $secciones['CAJA 02']->id,
            'codigo' => 'CAJA02-0001',
            'nombre' => 'Paracetamol 500mg',
            'descripcion' => 'Caja de 100 tabletas',
            'unidad_medida' => 'CAJA',
            'stock_actual' => 8,
            'stock_minimo' => 5,
            'stock_maximo' => 30,
            'tiene_vencimiento' => true,
            'fecha_vencimiento' => '2027-06-30',
            'ubicacion' => 'Tópico - Caja 02',
            'estado' => true,
        ]);

        Product::create([
            'section_id' => $secciones['CAJA 03']->id,
            'codigo' => 'CAJA03-0001',
            'nombre' => 'Termómetro digital',
            'descripcion' => 'Termómetro digital infrarrojo',
            'unidad_medida' => 'UNIDAD',
            'stock_actual' => 3,
            'stock_minimo' => 2,
            'stock_maximo' => 10,
            'tiene_vencimiento' => false,
            'ubicacion' => 'Tópico - Caja 03',
            'estado' => true,
        ]);

        // ========================================
        // PRODUCTOS - Tachos (ASSTC)
        // ========================================

        Product::create([
            'section_id' => $secciones['ASSTC']->id,
            'codigo' => 'ASSTC-0001',
            'nombre' => 'Tacho de basura 50L verde',
            'descripcion' => 'Tacho plástico con tapa 50 litros',
            'unidad_medida' => 'UNIDAD',
            'stock_actual' => 6,
            'stock_minimo' => 3,
            'stock_maximo' => 15,
            'tiene_vencimiento' => false,
            'ubicacion' => 'Almacén Principal - Zona D',
            'estado' => true,
        ]);

        // ========================================
        // PRODUCTOS - Materiales Básicos (ASSMB)
        // ========================================

        Product::create([
            'section_id' => $secciones['ASSMB']->id,
            'codigo' => 'ASSMB-0001',
            'nombre' => 'Cinta adhesiva transparente',
            'descripcion' => 'Cinta adhesiva 48mm x 40m',
            'unidad_medida' => 'UNIDAD',
            'stock_actual' => 40,
            'stock_minimo' => 20,
            'stock_maximo' => 100,
            'tiene_vencimiento' => false,
            'ubicacion' => 'Estante G-1',
            'estado' => true,
        ]);

        Product::create([
            'section_id' => $secciones['ASSMB']->id,
            'codigo' => 'ASSMB-0010',
            'nombre' => 'Pegamento en barra 40g',
            'descripcion' => 'Pegamento en barra para papel',
            'unidad_medida' => 'UNIDAD',
            'stock_actual' => 28,
            'stock_minimo' => 15,
            'stock_maximo' => 80,
            'tiene_vencimiento' => false,
            'ubicacion' => 'Estante G-2',
            'estado' => true,
        ]);

        echo "\n✅ Se crearon " . Product::count() . " productos de ejemplo\n";
        echo "✅ Se crearon " . Movement::count() . " movimientos de ejemplo\n";
        echo "⚠️  Productos con stock bajo: " . Product::whereRaw('stock_actual <= stock_minimo')->count() . "\n";
    }
}

