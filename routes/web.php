<?php

use App\Http\Controllers\AlumnoAsesoriaController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CoordinadorAlumnoController;
use App\Http\Controllers\CoordinadorDisponibilidadController;
use App\Http\Controllers\CoordinadorProfesorController;
use App\Http\Controllers\DisponibilidadController;
use App\Http\Controllers\ProfesorDisponibilidadController;
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
        Route::apiResource('alumnos', CoordinadorAlumnoController::class)->except(['show']);
        Route::get('disponibilidades', [CoordinadorDisponibilidadController::class, 'index'])
            ->name('disponibilidades.index');
        Route::apiResource('profesores', CoordinadorProfesorController::class)->except(['show']);

    });

Route::get('/coordinador', [CoordinadorAlumnoController::class, 'index'])
    ->middleware(['auth', 'role:Coordinador'])->name('coordinador');

Route::middleware(['auth', 'role:Alumno'])
    ->prefix('alumno')
    ->name('alumno.')
    ->group(function () {
        Route::apiResource('asesorias', AlumnoAsesoriaController::class)->except(['show']);

    });

/*
|--------------------------------------------------------------------------
| Middleware para rol de coordinador
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:Profesor'])
    ->prefix('profesor')
    ->name('profesor.')
    ->group(function () {
        Route::apiResource('disponibilidades', ProfesorDisponibilidadController::class);

    });

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
