<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener roles
        $adminRole = Role::where('nombre', 'Admin')->first();
        $asistenteRole = Role::where('nombre', 'Asistente')->first();

        // ADMIN - Todos los permisos
        $allPermissions = Permission::all()->pluck('id')->toArray();
        $adminRole->permissions()->sync($allPermissions);

        // ASISTENTE - Permisos de inventario completo
        $asistentePermissions = Permission::whereIn('slug', [
            'inventario.ver',
            'inventario.crear',
            'inventario.editar',
            'inventario.eliminar',
            'inventario.entrada',
            'inventario.salida',
            'inventario.ajustar',
        ])->pluck('id')->toArray();
        $asistenteRole->permissions()->sync($asistentePermissions);
    }
}
