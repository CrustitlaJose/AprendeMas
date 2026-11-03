<?php

use Illuminate\Support\Facades\Route;
use App\Models\Materia;

Route::get('/test-db', function () {
    return Materia::all();
});

Route::get('/', function () {
    return view('welcome');
});
