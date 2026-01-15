<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Crear roles
        $this->call(RoleSeeder::class);

        // Crear usuario administrador
        $adminRole = Role::where('nombre', 'Admin')->first();
        
        User::create([
            'rol_id' => $adminRole->id,
            'nombre' => 'Administrador',
            'email' => 'admin@almacen.com',
            'password' => Hash::make('admin123'),
            'estado' => true,
        ]);
    }
}
