<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Alumno;
use App\Models\Profesor;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'identificador' => ['required'], 
            'password' => ['required'],
        ]);

        $identificador = $request->input('identificador');
        $credenciales = ['password' => $request->input('password')];

        if (filter_var($identificador, FILTER_VALIDATE_EMAIL)) {
            $credenciales['correo'] = $identificador;
        } else {
            $alumno = Alumno::where('matricula', $identificador)->first();
            
            if ($alumno) {
                $credenciales['id_usuario'] = $alumno->id_usuario;
            } else {
                $profesor = Profesor::where('num_empleado', $identificador)->first();
                if ($profesor) {
                    $credenciales['id_usuario'] = $profesor->id_usuario;
                } else {
                    return response()->json(['message' => 'El usuario no existe.'], 404);
                }
            }
        }

        if (Auth::attempt($credenciales)) {
            $request->session()->regenerate();
            $usuario = Auth::user();

            if ($usuario->estado === 'Inactivo') {
                Auth::logout();
                return response()->json(['message' => 'La cuenta está inactiva.'], 403);
            }

            return response()->json([
                'message' => 'Autenticación exitosa',
                'usuario' => $usuario
            ], 200);
        }

        return response()->json([
            'message' => 'Las credenciales son incorrectas.'
        ], 401);
    }
}