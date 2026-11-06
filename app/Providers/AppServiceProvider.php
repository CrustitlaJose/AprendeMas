<?php

namespace App\Providers;

use App\Models\Usuario;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        ResetPassword::toMailUsing(static function (Usuario $usuario, string $token): MailMessage {
            $resetUrl = rtrim((string) config('app.url'), '/').'/reset-password/'.rawurlencode($token).'?'.http_build_query([
                'email' => $usuario->getEmailForPasswordReset(),
            ]);

            return (new MailMessage)
                ->subject('Restablecimiento de contraseña')
                ->line('Recibimos una solicitud para restablecer la contraseña de tu cuenta.')
                ->action('Restablecer contraseña', $resetUrl)
                ->line('Este enlace expirará en '.config('auth.passwords.users.expire').' minutos.')
                ->line('Si no solicitaste este cambio, puedes ignorar este correo.');
        });
    }
}
