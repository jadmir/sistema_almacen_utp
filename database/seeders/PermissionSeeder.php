<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Usuarios
            ['nombre' => 'Ver Usuarios', 'slug' => 'usuarios.ver', 'descripcion' => 'Ver lista de usuarios', 'modulo' => 'Usuarios'],
            ['nombre' => 'Crear Usuarios', 'slug' => 'usuarios.crear', 'descripcion' => 'Crear nuevos usuarios', 'modulo' => 'Usuarios'],
            ['nombre' => 'Editar Usuarios', 'slug' => 'usuarios.editar', 'descripcion' => 'Editar usuarios existentes', 'modulo' => 'Usuarios'],
            ['nombre' => 'Eliminar Usuarios', 'slug' => 'usuarios.eliminar', 'descripcion' => 'Eliminar usuarios', 'modulo' => 'Usuarios'],
            
            // Roles
            ['nombre' => 'Ver Roles', 'slug' => 'roles.ver', 'descripcion' => 'Ver lista de roles', 'modulo' => 'Roles'],
            ['nombre' => 'Crear Roles', 'slug' => 'roles.crear', 'descripcion' => 'Crear nuevos roles', 'modulo' => 'Roles'],
            ['nombre' => 'Editar Roles', 'slug' => 'roles.editar', 'descripcion' => 'Editar roles existentes', 'modulo' => 'Roles'],
            ['nombre' => 'Eliminar Roles', 'slug' => 'roles.eliminar', 'descripcion' => 'Eliminar roles', 'modulo' => 'Roles'],
            
            // Permisos
            ['nombre' => 'Gestionar Permisos', 'slug' => 'permisos.gestionar', 'descripcion' => 'Gestionar permisos del sistema', 'modulo' => 'Permisos'],
            
            // Inventario - Gestión General
            ['nombre' => 'Ver Inventario', 'slug' => 'inventario.ver', 'descripcion' => 'Ver productos y stock', 'modulo' => 'Inventario'],
            ['nombre' => 'Crear Productos', 'slug' => 'inventario.crear', 'descripcion' => 'Crear productos, secciones y tipos', 'modulo' => 'Inventario'],
            ['nombre' => 'Editar Productos', 'slug' => 'inventario.editar', 'descripcion' => 'Editar información de productos', 'modulo' => 'Inventario'],
            ['nombre' => 'Eliminar Productos', 'slug' => 'inventario.eliminar', 'descripcion' => 'Eliminar productos', 'modulo' => 'Inventario'],
            
            // Inventario - Movimientos de Stock
            ['nombre' => 'Registrar Entrada', 'slug' => 'inventario.entrada', 'descripcion' => 'Registrar entradas de stock', 'modulo' => 'Inventario'],
            ['nombre' => 'Registrar Salida', 'slug' => 'inventario.salida', 'descripcion' => 'Registrar salidas de stock', 'modulo' => 'Inventario'],
            ['nombre' => 'Ajustar Stock', 'slug' => 'inventario.ajustar', 'descripcion' => 'Realizar ajustes de inventario', 'modulo' => 'Inventario'],
            
            // Reportes
            ['nombre' => 'Ver Reportes', 'slug' => 'reportes.ver', 'descripcion' => 'Ver reportes del sistema', 'modulo' => 'Reportes'],
            ['nombre' => 'Generar Reportes', 'slug' => 'reportes.generar', 'descripcion' => 'Generar reportes', 'modulo' => 'Reportes'],
        ];

        foreach ($permissions as $permission) {
            Permission::create($permission);
        }
    }
}
