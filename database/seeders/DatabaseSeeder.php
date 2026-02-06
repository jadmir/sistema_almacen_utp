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
        
        // Crear permisos
        $this->call(PermissionSeeder::class);
        
        // Asignar permisos a roles
        $this->call(RolePermissionSeeder::class);

        // Crear usuario administrador
        $adminRole = Role::where('nombre', 'Admin')->first();
        
        User::create([
            'rol_id' => $adminRole->id,
            'nombre' => 'Administrador',
            'email' => 'admin@almacen.com',
            'password' => Hash::make('Admin123'),
            'estado' => true,
        ]);

        // Crear sistema de inventario
        $this->call(StockSystemSeeder::class);
        
        // Crear áreas
        $this->call(AreaSeeder::class);
        
        // Crear productos y movimientos de ejemplo
        $this->call(ProductExampleSeeder::class);
    }
}
