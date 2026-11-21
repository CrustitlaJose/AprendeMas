<?php

namespace App\Services;

use App\Models\Usuario;
use Illuminate\Support\Facades\Password;

class EnlaceRestablecimiento
{
    /**
     * Envía el correo de restablecimiento.
     * Solo en entorno local devuelve el enlace para mostrarlo en pantalla (práctica).
     */
    public static function enviar(string $correo): ?string
    {
        $credenciales = ['correo' => $correo];

        if (! app()->isLocal()) {
            try {
                Password::sendResetLink($credenciales);
            } catch (\Throwable $e) {
                report($e);
            }

            return null;
        }

        $enlace = null;

        Password::sendResetLink($credenciales, function (Usuario $usuario, string $token) use (&$enlace) {
            $enlace = route('password.reset', ['token' => $token, 'email' => $usuario->correo]);

            try {
                $usuario->sendPasswordResetNotification($token);
            } catch (\Throwable $e) {
                report($e);
            }
        });

        return $enlace;
    }
}