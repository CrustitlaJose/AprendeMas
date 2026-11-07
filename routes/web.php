<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CoordinadorAlumnoController;
use App\Http\Controllers\CoordinadorProfesorController;
use App\Http\Controllers\DisponibilidadController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Middleware para rol de coordinador
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:Coordinador'])
    ->prefix('coordinador')
    ->name('coordinador.')
    ->group(function () {
        // Todo este CRUD ahora es impenetrable para Alumnos o Profesores
        Route::apiResource('alumnos', CoordinadorAlumnoController::class)->except(['show']);
        Route::apiResource('profesores', CoordinadorProfesorController::class)->except(['show']);

    });

Route::get('/coordinador', [CoordinadorAlumnoController::class, 'index'])
    ->middleware(['auth', 'role:Coordinador'])->name('coordinador');

/*
|--------------------------------------------------------------------------
| Rutas Base y Vistas Públicas
|--------------------------------------------------------------------------
*/
Route::get('/', function (Request $request) {
    return redirect()->route(
        $request->user()->rol === 'Coordinador' ? 'coordinador' : 'mis-asesorias'
    );
})->middleware('auth')->name('home');

Route::get('/login', function () {
    return view('login');
})->middleware('guest')->name('login');

/*
|--------------------------------------------------------------------------
| Procesamiento de Autenticación
|--------------------------------------------------------------------------
*/
Route::post('/login', [AuthController::class, 'login'])
    ->middleware('guest')->name('login.post');

Route::post('/forgot-password', [AuthController::class, 'sendPasswordResetLink'])
    ->middleware(['guest', 'throttle:6,1'])->name('password.email');

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')->name('logout');

/*
|--------------------------------------------------------------------------
| Rutas Protegidas (Requieren Sesión Iniciada)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    // --- Vistas y Paneles Generales ---
    Route::get('/mis-asesorias', function () {
        return view('mis-asesorias');
    })->name('mis-asesorias');

    // --- Módulo Disponibilidad (Profesor) ---
    Route::get('/disponibilidades', [DisponibilidadController::class, 'index'])
        ->name('disponibilidades.index');

});
