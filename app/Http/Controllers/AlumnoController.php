<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class AlumnoController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()?->rol === 'Coordinador', 403);

        $alumnos = Alumno::with('usuario')->get();

        return view('Coordinador.alumnos', compact('alumnos'));
    }
}
