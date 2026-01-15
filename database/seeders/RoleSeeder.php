<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'nombre' => 'Admin',
                'descripcion' => 'Administrador del sistema con acceso completo',
                'estado' => true,
            ],
            [
                'nombre' => 'Usuario',
                'descripcion' => 'Usuario regular del sistema',
                'estado' => true,
            ],
            [
                'nombre' => 'Almacenero',
                'descripcion' => 'Encargado del almacén',
                'estado' => true,
            ],
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }
    }
}
