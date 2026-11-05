<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use Illuminate\Http\JsonResponse;

class AlumnoController extends Controller
{
    // Obtener la lista completa de alumnos en el sistema
    public function index(): JsonResponse
    {
        abort_unless(request()->user()?->rol === 'Coordinador', 403);

        $alumnos = Alumno::with('usuario')->get();

        return response()->json($alumnos);
    }
}
