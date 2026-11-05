<?php

use App\Http\Controllers\AlumnoController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CoordinadorAlumnoController;
use App\Models\Materia;
use Illuminate\Support\Facades\Route;

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

Route::post('/login', [AuthController::class, 'login']);

Route::post('/coordinador/alumnos', [CoordinadorAlumnoController::class, 'store'])
    ->middleware('auth')
    ->name('coordinador.alumnos.store');

Route::get('/alumnos', [AlumnoController::class, 'index'])->middleware('auth')->name('alumnos.get');