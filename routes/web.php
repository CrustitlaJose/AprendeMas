<?php

use App\Http\Controllers\AlumnoController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CoordinadorAlumnoController;
use App\Http\Controllers\DisponibilidadController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function (Request $request) {
    return redirect()->route(
        $request->user()->rol === 'Coordinador' ? 'coordinador' : 'mis-asesorias'
    );
})->middleware('auth')->name('home');

Route::get('/login', function () {
    return view('login');
})->middleware('guest')->name('login');

// Procesar el login
Route::post('/login', [AuthController::class, 'login'])
    ->middleware('guest')
    ->name('login.post');

Route::post('/forgot-password', [AuthController::class, 'sendPasswordResetLink'])
    ->middleware(['guest', 'throttle:6,1'])
    ->name('password.email');

// Cerrar sesión
Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/mis-asesorias', function () {
        return view('mis-asesorias');
    })->name('mis-asesorias');

    Route::get('/disponibilidades', [DisponibilidadController::class, 'index'])
        ->name('disponibilidades.index');

    Route::get('/coordinador', [AlumnoController::class, 'index'])->name('coordinador');
    Route::get('/alumnos', [AlumnoController::class, 'index'])->name('alumnos.get');
});
Route::get('/test-agendar', function () {
    return view('agendar');
});

Route::post('/coordinador/alumnos', [CoordinadorAlumnoController::class, 'store'])
    ->middleware('auth')
    ->name('coordinador.alumnos.store');
