<?php

use App\Http\Controllers\AlumnoController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CoordinadorAlumnoController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('/login');
});

Route::get('/login', function () {
    return view('login');
})->name('login');

// Procesar el login
Route::post('/login', [AuthController::class, 'login'])->name('login.post');

// Cerrar sesión
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/mis-asesorias', function () {
        return view('mis-asesorias');
    })->name('mis-asesorias');

    Route::get('/coordinador', [AlumnoController::class, 'index'])->name('coordinador');
    Route::get('/alumnos', [AlumnoController::class, 'index'])->name('alumnos.get');
});
Route::get('/test-agendar', function () {
    return view('agendar');
});

Route::post('/coordinador/alumnos', [CoordinadorAlumnoController::class, 'store'])
    ->middleware('auth')
    ->name('coordinador.alumnos.store');
