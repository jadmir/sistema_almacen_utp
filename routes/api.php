<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UsuarioController;

// Rutas públicas
Route::get('/test', function () {
    return response()->json([
        'message' => 'API funcionando correctamente',
        'timestamp' => now(),
        'status' => 'success'
    ]);
});

// Autenticación
Route::post('/login', [AuthController::class, 'login']);

// Rutas protegidas con JWT
Route::middleware(['jwt.auth'])->group(function () {
    
    // Auth
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Roles - Solo Admin
    Route::middleware(['role:Admin'])->group(function () {
        Route::apiResource('roles', RoleController::class);
        Route::apiResource('usuarios', UsuarioController::class);
    });

    // Usuarios - Admin y Almacenero pueden ver
    Route::middleware(['role:Admin,Almacenero'])->group(function () {
        Route::get('usuarios-list', [UsuarioController::class, 'index']);
    });
});
