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
use Illuminate\Support\Facades\Hash;

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
                match ($usuario->rol) {
                    'Coordinador' => 'coordinador',
                    'Profesor' => 'profesor.disponibilidades.index',
                    default => 'mis-asesorias',
                }
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

    public function sendPasswordResetLink(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'correo' => ['required', 'email:rfc,filter', 'max:255'],
        ]);

        $enlace = null;

        $status = Password::sendResetLink(
            ['correo' => $validated['correo']],
            function (Usuario $usuario, string $token) use (&$enlace) {
                $enlace = route('password.reset', ['token' => $token, 'email' => $usuario->correo]);
            }
        );

        if ($status === Password::RESET_THROTTLED) {
            return back()->withInput()->withErrors([
                'correo' => 'Ya se generó un enlace hace poco. Espera un minuto e inténtalo de nuevo.',
            ]);
        }

        if ($enlace === null) {
            return back()->withInput()->withErrors([
                'correo' => 'No existe una cuenta registrada con ese correo.',
            ]);
        }

        return back()->with('enlace_reset', $enlace);
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'token' => ['required'],
            'correo' => ['required', 'email'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $status = Password::reset(
            [
                'correo' => $data['correo'],
                'password' => $data['password'],
                'password_confirmation' => $request->input('password_confirmation'),
                'token' => $data['token'],
            ],
            function (Usuario $usuario, string $password) {
                $usuario->contrasena_hash = Hash::make($password);
                $usuario->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __($status))
            : back()->withErrors(['correo' => __($status)]);
    }
}
