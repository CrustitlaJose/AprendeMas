<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::get('/', function () {
    return redirect('/login');
});

// Mostrar el formulario de login (Frontend directo)
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