<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Profesor;
use App\Models\Usuario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // Validar parámetros de entrada
        $request->validate([
            'identificador' => ['required'],
            'password' => ['required'],
        ]);

        $identificador = $request->input('identificador');
        $credenciales = ['password' => $request->input('password')];

        // Identificar el tipo de credencial enviada
        if (filter_var($identificador, FILTER_VALIDATE_EMAIL)) {
            // Autenticación por correo electrónico (aplica para Alumno, Profesor y Coordinador)
            $credenciales['correo'] = $identificador;
        } else {
            // Búsqueda por identificadores específicos (solo Alumno y Profesor)
            $alumno = Alumno::where('matricula', $identificador)->first();

            if ($alumno) {
                $credenciales['id_usuario'] = $alumno->id_usuario;
            } else {
                $profesor = Profesor::where('num_empleado', $identificador)->first();

                if ($profesor) {
                    $credenciales['id_usuario'] = $profesor->id_usuario;
                } else {
                    return response()->json(['message' => 'El usuario no existe o el identificador es inválido.'], 404);
                }
            }
        }

        // Intento de autenticación en la BD
        if (Auth::attempt($credenciales)) {
            $request->session()->regenerate();
            /** @var Usuario $usuario */
            $usuario = Auth::user();

            if ($usuario->estado === 'Inactivo') {
                Auth::logout();

                return response()->json(['message' => 'La cuenta está inactiva.'], 403);
            }

            return redirect()->route(
                $usuario->rol === 'Coordinador' ? 'coordinador' : 'mis-asesorias'
            );
        }

        return response()->json([
            'message' => 'Las credenciales son incorrectas.',
        ], 401);
    }

    public function logout(Request $request): JsonResponse|RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Sesión cerrada correctamente.'], 200);
        }

        return redirect()->route('login');
    }

    public function sendPasswordResetLink(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'correo' => ['required', 'email', 'max:255'],
        ]);

        Password::sendResetLink(['correo' => $validated['correo']]);

        return response()->json([
            'message' => 'Si el correo está registrado, recibirás un enlace para restablecer tu contraseña.',
        ], 202);
    }
}
