<?php

namespace App\Http\Controllers;

use App\Models\Disponibilidad;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CoordinadorDisponibilidadController extends Controller
{
    public function index(Request $request): JsonResponse|View
    {
        $disponibilidades = Disponibilidad::query()
            ->with('profesor:id_profesor,nombre,apellidos')
            ->orderBy('fecha')
            ->orderBy('hora_inicio')
            ->get();

        if ($request->expectsJson()) {
            return response()->json(['data' => $disponibilidades]);
        }

        return view('Coordinador.disponibilidades', compact('disponibilidades'));
    }
}
