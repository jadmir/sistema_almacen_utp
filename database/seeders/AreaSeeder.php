<?php

namespace Database\Seeders;

use App\Models\Area;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AreaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $areas = [
            [
                'nombre' => 'Administración',
                'codigo' => 'ADM',
                'descripcion' => 'Área administrativa y de gestión',
                'responsable' => 'Director(a) Administrativo',
                'estado' => true,
            ],
            [
                'nombre' => 'Enfermería',
                'codigo' => 'ENF',
                'descripcion' => 'Tópico y atención médica',
                'responsable' => 'Enfermera(o) Jefe',
                'estado' => true,
            ],
            [
                'nombre' => 'Deportes',
                'codigo' => 'DEP',
                'descripcion' => 'Área de educación física y deportes',
                'responsable' => 'Coordinador de Deportes',
                'estado' => true,
            ],
            [
                'nombre' => 'Biblioteca',
                'codigo' => 'BIB',
                'descripcion' => 'Biblioteca y recursos educativos',
                'responsable' => 'Bibliotecario(a)',
                'estado' => true,
            ],
            [
                'nombre' => 'Laboratorio',
                'codigo' => 'LAB',
                'descripcion' => 'Laboratorio de ciencias',
                'responsable' => 'Jefe de Laboratorio',
                'estado' => true,
            ],
            [
                'nombre' => 'Mantenimiento',
                'codigo' => 'MAN',
                'descripcion' => 'Área de mantenimiento y servicios generales',
                'responsable' => 'Jefe de Mantenimiento',
                'estado' => true,
            ],
            [
                'nombre' => 'Secretaría',
                'codigo' => 'SEC',
                'descripcion' => 'Secretaría académica',
                'responsable' => 'Secretaria(o) General',
                'estado' => true,
            ],
            [
                'nombre' => 'Sala de Profesores',
                'codigo' => 'PROF',
                'descripcion' => 'Sala de docentes',
                'responsable' => 'Coordinador Académico',
                'estado' => true,
            ],
        ];

        foreach ($areas as $area) {
            Area::create($area);
        }

        echo "✅ Se crearon " . count($areas) . " áreas\n";
    }
}
