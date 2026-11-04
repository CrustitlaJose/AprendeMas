<?php

use Illuminate\Support\Facades\Route;
use App\Models\Materia;
use App\Http\Controllers\AuthController;

Route::get('/test-db', function () {
    return Materia::all();
});

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', [AuthController::class, 'login']);