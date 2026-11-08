<?php

namespace App\Http\Controllers;

use App\Models\Disponibilidad;
use Illuminate\Http\JsonResponse;

class CoordinadorDisponibilidadController extends Controller
{
    public function index(): JsonResponse
    {
        $disponibilidades = Disponibilidad::query()
            ->with('profesor:id_profesor,nombre,apellidos')
            ->orderBy('fecha')
            ->orderBy('hora_inicio')
            ->get();

        return response()->json(['data' => $disponibilidades]);
    }
}
