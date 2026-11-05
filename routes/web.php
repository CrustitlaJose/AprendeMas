<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AlumnoController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CoordinadorAlumnoController;
use App\Models\Materia;

Route::get('/', function () {
    return redirect('/login');
});

// Mostrar el formulario de login (Frontend directo)
/*
Route::get('/test-db', function () {
    return Materia::all();
});
*/
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

// Ruta protegida de ejemplo (a la que redirige tras el login exitoso)
Route::middleware('auth')->group(function () {
    Route::get('/mis-asesorias', function () {
        return view('mis-asesorias');
    })->name('mis-asesorias');
});
Route::get('/test-agendar', function () {
    return view('agendar');
});
Route::get('/test-coordinador', function () {
    return view('coordinador.alumnos');
});
Route::post('/login', [AuthController::class, 'login']);

Route::post('/coordinador/alumnos', [CoordinadorAlumnoController::class, 'store'])
    ->middleware('auth')
    ->name('coordinador.alumnos.store');

Route::get('/alumnos', [AlumnoController::class, 'index'])->middleware('auth')->name('alumnos.get');
