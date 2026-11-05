<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Usuario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CoordinadorAlumnoController extends Controller
{
    // Registrar un nuevo alumno desde la administración
    public function store(Request $request): JsonResponse
    {
        abort_unless($request->user()?->rol === 'Coordinador', 403);

        $validated = $request->validate([
            'correo' => ['required', 'email', 'max:255', 'unique:usuario,correo'],
            'matricula' => ['required', 'string', 'max:255', 'unique:alumno,matricula'],
            'nombre' => ['required', 'string', 'max:255'],
            'apellidos' => ['required', 'string', 'max:255'],
            'telefono' => ['required', 'string', 'max:255', 'unique:alumno,telefono'],
        ]);

        $contrasenaTemporal = hex2bin(random_bytes(16));

        $alumno = DB::transaction(function () use ($validated, $contrasenaTemporal): Alumno {
            $usuario = new Usuario;
            $usuario->correo = $validated['correo'];
            $usuario->contrasena_hash = Hash::make($contrasenaTemporal);
            $usuario->rol = 'Alumno';
            $usuario->estado = 'Activo';
            $usuario->save();

            $alumno = new Alumno;
            $alumno->id_usuario = $usuario->id_usuario;
            $alumno->matricula = $validated['matricula'];
            $alumno->nombre = $validated['nombre'];
            $alumno->apellidos = $validated['apellidos'];
            $alumno->telefono = $validated['telefono'];
            $alumno->estado = 'Activo';
            $alumno->save();

            return $alumno;
        });

        return response()->json([
            'message' => 'Alumno registrado correctamente.',
            'alumno' => [
                'id_alumno' => $alumno->id_alumno,
                'matricula' => $alumno->matricula,
                'nombre' => $alumno->nombre,
                'apellidos' => $alumno->apellidos,
                'telefono' => $alumno->telefono,
                'correo' => $validated['correo'],
            ],
            'contrasena_temporal' => $contrasenaTemporal,
        ], 201);
    }
}
