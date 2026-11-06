<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CoordinadorAlumnoController extends Controller
{
    // CU-12: Consultar todos los alumnos
    public function index()
    {
        $alumnos = Alumno::with('usuario')->get();
        return response()->json($alumnos, 200);
    }

    // CU-10: Dar de alta un nuevo alumno
    public function store(Request $request)
    {
        $request->validate([
            'correo' => 'required|email|unique:usuario,correo',
            'password' => 'required|min:6',
            'matricula' => 'required|unique:alumno,matricula',
            'nombre' => 'required|string',
            'apellidos' => 'required|string',
            'telefono' => 'nullable|string'
        ]);

        try {
            DB::transaction(function () use ($request) {
                // 1. Crear credenciales genéricas en tabla usuario
                $usuario = Usuario::create([
                    'correo' => $request->correo,
                    'contrasena_hash' => Hash::make($request->password),
                    'rol' => 'Alumno',
                    'estado' => 'Activo'
                ]);

                // 2. Crear expediente específico en tabla alumno
                Alumno::create([
                    'id_usuario' => $usuario->id_usuario,
                    'matricula' => $request->matricula,
                    'nombre' => $request->nombre,
                    'apellidos' => $request->apellidos,
                    'telefono' => $request->telefono
                ]);
            });

            return response()->json(['message' => 'Alumno registrado exitosamente.'], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al registrar alumno.', 'error' => $e->getMessage()], 500);
        }
    }

    // CU-13: Modificar información de alumnado
    public function update(Request $request, $id)
    {
        $alumno = Alumno::findOrFail($id);

        $request->validate([
            'nombre' => 'string',
            'apellidos' => 'string',
            'telefono' => 'nullable|string',
            'correo' => 'email|unique:usuario,correo,' . $alumno->id_usuario . ',id_usuario',
        ]);

        try {
            DB::transaction(function () use ($request, $alumno) {
                // Actualizar datos de Alumno
                $alumno->update($request->only(['nombre', 'apellidos', 'telefono']));

                // Actualizar correo en Usuario si se envió en la petición
                if ($request->has('correo')) {
                    $alumno->usuario()->update(['correo' => $request->correo]);
                }
            });

            return response()->json(['message' => 'Expediente actualizado.'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al actualizar.', 'error' => $e->getMessage()], 500);
        }
    }

    // CU-11: Dar de baja alumno (Baja lógica)
    public function destroy($id)
    {
        $alumno = Alumno::findOrFail($id);
        
        // Se desactiva la cuenta del usuario para impedir el login, sin borrar la tabla 'alumno'
        $alumno->usuario()->update(['estado' => 'Inactivo']);

        return response()->json(['message' => 'Baja lógica aplicada correctamente.'], 200);
    }
}