<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\StockTypeController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\MovementController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PlantillaEntregaController;

// ========================================
// RUTAS PÚBLICAS
// ========================================
Route::post('login', [AuthController::class, 'login']);

// ========================================
// RUTAS PROTEGIDAS CON JWT
// ========================================
Route::middleware(['jwt.auth'])->group(function () {
    
    // Autenticación
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);
    Route::post('auth/cambiar-password', [AuthController::class, 'cambiarPassword']);
    Route::get('auth/refresh-permissions', [AuthController::class, 'refreshPermissions']);

    // ========================================
    // DASHBOARD (Pantalla principal)
    // ========================================
    Route::get('dashboard', [DashboardController::class, 'index'])->middleware('permission:inventario.ver');
    Route::get('dashboard/estadisticas', [DashboardController::class, 'estadisticas'])->middleware('permission:inventario.ver');

    // ========================================
    // GESTIÓN DE USUARIOS (por permisos)
    // ========================================
    Route::get('usuarios', [UsuarioController::class, 'index'])->middleware('permission:usuarios.ver');
    Route::post('usuarios', [UsuarioController::class, 'store'])->middleware('permission:usuarios.crear');
    Route::get('usuarios/{usuario}', [UsuarioController::class, 'show'])->middleware('permission:usuarios.ver');
    Route::put('usuarios/{usuario}', [UsuarioController::class, 'update'])->middleware('permission:usuarios.editar');
    Route::delete('usuarios/{usuario}', [UsuarioController::class, 'destroy'])->middleware('permission:usuarios.eliminar');
    
    // Permisos de usuario
    Route::get('usuarios/{id}/permisos', [UsuarioController::class, 'getUserPermissions'])->middleware('permission:usuarios.ver');
    Route::post('usuarios/{id}/permisos', [UsuarioController::class, 'assignPermissions'])->middleware('role:Admin');
    Route::delete('usuarios/{userId}/permisos/{permissionId}', [UsuarioController::class, 'removePermission'])->middleware('role:Admin');
    
    // Usuarios eliminados
    Route::get('usuarios-eliminados', [UsuarioController::class, 'trashed'])->middleware('role:Admin');
    Route::post('usuarios/{id}/restaurar', [UsuarioController::class, 'restore'])->middleware('role:Admin');
    Route::delete('usuarios/{id}/forzar-eliminacion', [UsuarioController::class, 'forceDestroy'])->middleware('role:Admin');

    // ========================================
    // GESTIÓN DE ROLES (por permisos)
    // ========================================
    Route::get('roles', [RoleController::class, 'index'])->middleware('permission:roles.ver');
    Route::post('roles', [RoleController::class, 'store'])->middleware('permission:roles.crear');
    Route::get('roles/{role}', [RoleController::class, 'show'])->middleware('permission:roles.ver');
    Route::put('roles/{role}', [RoleController::class, 'update'])->middleware('permission:roles.editar');
    Route::delete('roles/{role}', [RoleController::class, 'destroy'])->middleware('permission:roles.eliminar');
    
    // Permisos de rol
    Route::get('roles/{roleId}/permisos', [PermissionController::class, 'getRolePermissions'])->middleware('permission:roles.ver');
    Route::post('roles/{roleId}/permisos', [PermissionController::class, 'assignToRole'])->middleware('role:Admin');
    Route::delete('roles/{roleId}/permisos/{permissionId}', [PermissionController::class, 'removeFromRole'])->middleware('role:Admin');

    // ========================================
    // GESTIÓN DE PERMISOS (Solo Admin)
    // ========================================
    Route::middleware(['role:Admin'])->group(function () {
        Route::apiResource('permissions', PermissionController::class);
    });

    // ========================================
    // SISTEMA DE INVENTARIO
    // ========================================
    
    // Tipos de Stock (Stock de Productos, Letreros, Tópico)
    Route::get('stock-types', [StockTypeController::class, 'index'])->middleware('permission:inventario.ver');
    Route::post('stock-types', [StockTypeController::class, 'store'])->middleware('permission:inventario.crear');
    Route::get('stock-types/{id}', [StockTypeController::class, 'show'])->middleware('permission:inventario.ver');
    Route::put('stock-types/{id}', [StockTypeController::class, 'update'])->middleware('permission:inventario.editar');
    Route::delete('stock-types/{id}', [StockTypeController::class, 'destroy'])->middleware('permission:inventario.eliminar');

    // Secciones (ASSAL, ASSOF, CAJA 01, etc.)
    Route::get('sections', [SectionController::class, 'index'])->middleware('permission:inventario.ver');
    Route::post('sections', [SectionController::class, 'store'])->middleware('permission:inventario.crear');
    Route::get('sections/{id}', [SectionController::class, 'show'])->middleware('permission:inventario.ver');
    Route::put('sections/{id}', [SectionController::class, 'update'])->middleware('permission:inventario.editar');
    Route::delete('sections/{id}', [SectionController::class, 'destroy'])->middleware('permission:inventario.eliminar');

    // Productos
    Route::get('products/alertas/stock-bajo', [ProductController::class, 'productosStockBajo'])->middleware('permission:inventario.ver');
    Route::get('products/{id}/historial', [ProductController::class, 'historialMovimientos'])->middleware('permission:inventario.ver');
    Route::get('sections/{sectionId}/next-code', [ProductController::class, 'getNextCode'])->middleware('permission:inventario.ver');
    Route::get('products', [ProductController::class, 'index'])->middleware('permission:inventario.ver');
    Route::post('products', [ProductController::class, 'store'])->middleware('permission:inventario.crear');
    Route::get('products/{id}', [ProductController::class, 'show'])->middleware('permission:inventario.ver');
    Route::put('products/{id}', [ProductController::class, 'update'])->middleware('permission:inventario.editar');
    Route::delete('products/{id}', [ProductController::class, 'destroy'])->middleware('permission:inventario.eliminar');
    
    // Movimientos de Stock (Individual)
    Route::post('products/{id}/entrada', [ProductController::class, 'registrarEntrada'])->middleware('permission:inventario.entrada');
    Route::post('products/{id}/salida', [ProductController::class, 'registrarSalida'])->middleware('permission:inventario.salida');
    Route::post('products/{id}/ajuste', [ProductController::class, 'registrarAjuste'])->middleware('permission:inventario.ajustar');
    
    // Movimientos de Stock (Masivo)
    Route::post('products/entrada-masiva', [ProductController::class, 'registrarEntradaMasiva'])->middleware('permission:inventario.entrada');
    Route::post('products/salida-masiva', [ProductController::class, 'registrarSalidaMasiva'])->middleware('permission:inventario.salida');
    Route::post('products/ajuste-masivo', [ProductController::class, 'registrarAjusteMasivo'])->middleware('permission:inventario.ajustar');

    
    // Movimientos (Historial general)
    Route::get('movements', [MovementController::class, 'index'])->middleware('permission:inventario.ver');
    Route::get('movements/{id}', [MovementController::class, 'show'])->middleware('permission:inventario.ver');
    Route::get('movements/estadisticas/general', [MovementController::class, 'estadisticas'])->middleware('permission:inventario.ver');
    
    // ========================================
    // ÁREAS
    // ========================================
    Route::get('areas/activas', [AreaController::class, 'activas'])->middleware('permission:inventario.ver');
    Route::get('areas', [AreaController::class, 'index'])->middleware('permission:inventario.ver');
    Route::post('areas', [AreaController::class, 'store'])->middleware('permission:inventario.crear');
    Route::get('areas/{id}', [AreaController::class, 'show'])->middleware('permission:inventario.ver');
    Route::put('areas/{id}', [AreaController::class, 'update'])->middleware('permission:inventario.editar');
    Route::delete('areas/{id}', [AreaController::class, 'destroy'])->middleware('permission:inventario.eliminar');
    
    // ========================================
    // REPORTES EN EXCEL
    // ========================================
    Route::get('reportes/productos', [ReportController::class, 'productos'])->middleware('permission:inventario.ver');
    Route::get('reportes/stock-bajo', [ReportController::class, 'stockBajo'])->middleware('permission:inventario.ver');
    Route::get('reportes/proximos-vencer', [ReportController::class, 'proximosVencer'])->middleware('permission:inventario.ver');
    Route::get('reportes/vencidos', [ReportController::class, 'vencidos'])->middleware('permission:inventario.ver');
    Route::get('reportes/movimientos', [ReportController::class, 'movimientos'])->middleware('permission:inventario.ver');
    Route::get('reportes/kardex', [ReportController::class, 'kardex'])->middleware('permission:inventario.ver');
    Route::get('reportes/tipo-stock', [ReportController::class, 'tipoStock'])->middleware('permission:inventario.ver');
    Route::get('reportes/seccion', [ReportController::class, 'seccion'])->middleware('permission:inventario.ver');
    
    // ========================================
    // REPORTES EN PDF
    // ========================================
    Route::get('reportes/pdf/productos', [ReportController::class, 'productosPdf'])->middleware('permission:inventario.ver');
    Route::get('reportes/pdf/stock-bajo', [ReportController::class, 'stockBajoPdf'])->middleware('permission:inventario.ver');
    Route::get('reportes/pdf/proximos-vencer', [ReportController::class, 'proximosVencerPdf'])->middleware('permission:inventario.ver');
    Route::get('reportes/pdf/vencidos', [ReportController::class, 'vencidosPdf'])->middleware('permission:inventario.ver');
    Route::get('reportes/pdf/movimientos', [ReportController::class, 'movimientosPdf'])->middleware('permission:inventario.ver');
    Route::get('reportes/pdf/kardex', [ReportController::class, 'kardexPdf'])->middleware('permission:inventario.ver');
    Route::get('reportes/pdf/seccion', [ReportController::class, 'seccionPdf'])->middleware('permission:inventario.ver');
    
    // ========================================
    // PLANTILLAS DE ENTREGAS MENSUALES
    // ========================================
    Route::get('plantillas-entregas', [PlantillaEntregaController::class, 'index'])->middleware('permission:inventario.ver');
    Route::post('plantillas-entregas', [PlantillaEntregaController::class, 'store'])->middleware('permission:inventario.crear');
    Route::get('plantillas-entregas/{id}', [PlantillaEntregaController::class, 'show'])->middleware('permission:inventario.ver');
    Route::put('plantillas-entregas/{id}', [PlantillaEntregaController::class, 'update'])->middleware('permission:inventario.editar');
    Route::delete('plantillas-entregas/{id}', [PlantillaEntregaController::class, 'destroy'])->middleware('permission:inventario.eliminar');
    Route::post('plantillas-entregas/{id}/ejecutar', [PlantillaEntregaController::class, 'ejecutar'])->middleware('permission:inventario.salida');
});
