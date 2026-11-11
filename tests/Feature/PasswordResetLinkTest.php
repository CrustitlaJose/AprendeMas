<?php

namespace Tests\Feature;

use App\Models\Usuario;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetLinkTest extends TestCase
{
    public function test_endpoint_requests_a_reset_link_and_returns_a_generic_response(): void
    {
        $correo = 'alumno@example.com';

        Password::shouldReceive('sendResetLink')
            ->once()
            ->with(['correo' => $correo])
            ->andReturn(Password::RESET_LINK_SENT);

        $this->postJson(route('password.email'), ['correo' => $correo])
            ->assertStatus(202)
            ->assertJsonPath(
                'message',
                'Si el correo está registrado, recibirás un enlace para restablecer tu contraseña.'
            );
    }

    public function test_endpoint_returns_the_same_response_for_an_unknown_email(): void
    {
        Password::shouldReceive('sendResetLink')
            ->once()
            ->with(['correo' => 'desconocido@example.com'])
            ->andReturn(Password::INVALID_USER);

        $this->postJson(route('password.email'), ['correo' => 'desconocido@example.com'])
            ->assertStatus(202)
            ->assertJsonPath(
                'message',
                'Si el correo está registrado, recibirás un enlace para restablecer tu contraseña.'
            );
    }

    public function test_endpoint_rejects_an_invalid_email(): void
    {
        $this->postJson(route('password.email'), ['correo' => 'no@dominio'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('correo');
    }

    public function test_password_reset_notification_uses_the_users_correo_address(): void
    {
        $usuario = Usuario::make(['correo' => 'alumno@example.com']);

        $this->assertSame('alumno@example.com', $usuario->getEmailForPasswordReset());
        $this->assertSame('alumno@example.com', $usuario->routeNotificationFor('mail'));
    }

    public function test_password_reset_notification_builds_a_link_from_the_configured_app_url(): void
    {
        $usuario = Usuario::make(['correo' => 'alumno@example.com']);

        $mail = (new ResetPassword('reset-token'))->toMail($usuario);

        $this->assertSame(
            rtrim((string) config('app.url'), '/').'/reset-password/reset-token?email=alumno%40example.com',
            $mail->actionUrl
        );
    }
}
