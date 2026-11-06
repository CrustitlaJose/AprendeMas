<?php

namespace App\Http\Controllers;

use App\Models\Profesor;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CoordinadorProfesorController extends Controller
{
    // Listar todos los profesores (incluyendo si están activos/inactivos desde la tabla usuario)
    public function index()
    {
        $profesores = Profesor::with('usuario')->get();
        return response()->json($profesores, 200);
    }

    // Registrar un nuevo docente en el sistema
    public function store(Request $request)
    {
        $request->validate([
            'correo' => 'required|email|unique:usuario,correo',
            'password' => 'required|min:6',
            'num_empleado' => 'required|unique:profesor,num_empleado',
            'nombre' => 'required|string',
            'apellidos' => 'required|string',
            'departamento' => 'required|string',
            'especialidad' => 'required|string',
        ]);

        try {
            DB::transaction(function () use ($request) {
                // 1. Crear la cuenta de acceso (Auth)
                $usuario = Usuario::create([
                    'correo' => $request->correo,
                    'contrasena_hash' => Hash::make($request->password),
                    'rol' => 'Profesor',
                    'estado' => 'Activo'
                ]);

                // 2. Crear el expediente académico del profesor
                Profesor::create([
                    'id_usuario' => $usuario->id_usuario,
                    'num_empleado' => $request->num_empleado,
                    'nombre' => $request->nombre,
                    'apellidos' => $request->apellidos,
                    'departamento' => $request->departamento,
                    'especialidad' => $request->especialidad
                ]);
            });

            return response()->json(['message' => 'Profesor registrado exitosamente.'], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al registrar al profesor.', 'error' => $e->getMessage()], 500);
        }
    }

    // Modificar el perfil del profesor
    public function update(Request $request, $id)
    {
        $profesor = Profesor::findOrFail($id);

        $request->validate([
            'nombre' => 'string',
            'apellidos' => 'string',
            'departamento' => 'string',
            'especialidad' => 'string',
            'correo' => 'email|unique:usuario,correo,' . $profesor->id_usuario . ',id_usuario',
        ]);

        try {
            DB::transaction(function () use ($request, $profesor) {
                // Actualizar los datos operativos
                $profesor->update($request->only(['nombre', 'apellidos', 'departamento', 'especialidad']));

                // Si mandaron un nuevo correo, actualizarlo en la tabla padre
                if ($request->has('correo')) {
                    $profesor->usuario()->update(['correo' => $request->correo]);
                }
            });

            return response()->json(['message' => 'Perfil de profesor actualizado.'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al actualizar.', 'error' => $e->getMessage()], 500);
        }
    }

    // Baja lógica del profesor (impide login sin borrar su historial de asesorías)
    public function destroy($id)
    {
        $profesor = Profesor::findOrFail($id);
        
        $profesor->usuario()->update(['estado' => 'Inactivo']);

        return response()->json(['message' => 'Profesor dado de baja correctamente.'], 200);
    }
}