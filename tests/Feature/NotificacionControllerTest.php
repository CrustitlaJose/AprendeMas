<?php

namespace Tests\Feature;

use App\Models\Notificacion;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificacionControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_opening_the_notification_menu_marks_only_the_current_users_notifications_as_read(): void
    {
        $usuario = Usuario::create([
            'correo' => 'alumno@example.com',
            'contrasena_hash' => bcrypt('password'),
            'rol' => 'Alumno',
            'estado' => 'Activo',
        ]);
        $otroUsuario = Usuario::create([
            'correo' => 'otro-alumno@example.com',
            'contrasena_hash' => bcrypt('password'),
            'rol' => 'Alumno',
            'estado' => 'Activo',
        ]);

        $notificacionSinLeer = $this->createNotification($usuario, false);
        $notificacionLeida = $this->createNotification($usuario, true);
        $notificacionDeOtroUsuario = $this->createNotification($otroUsuario, false);

        $this->actingAs($usuario)
            ->get(route('mis-asesorias'))
            ->assertOk()
            ->assertSee('data-notification-indicator', false);

        $this->actingAs($usuario)
            ->getJson(route('notificaciones.index'))
            ->assertOk()
            ->assertJsonPath('sin_leer', 1)
            ->assertJsonFragment(['mensaje' => $notificacionSinLeer->mensaje]);

        $this->actingAs($usuario)
            ->postJson(route('notificaciones.read'))
            ->assertOk()
            ->assertJsonPath('message', 'Notificaciones marcadas como leídas.');

        $this->assertDatabaseHas('notificacion', [
            'id_notificacion' => $notificacionSinLeer->id_notificacion,
            'leida' => true,
        ]);
        $this->assertDatabaseHas('notificacion', [
            'id_notificacion' => $notificacionLeida->id_notificacion,
            'leida' => true,
        ]);
        $this->assertDatabaseHas('notificacion', [
            'id_notificacion' => $notificacionDeOtroUsuario->id_notificacion,
            'leida' => false,
        ]);

        $this->get(route('mis-asesorias'))
            ->assertOk()
            ->assertDontSee('data-notification-indicator', false)
            ->assertSeeText($notificacionSinLeer->mensaje);

        $this->getJson(route('notificaciones.index'))
            ->assertOk()
            ->assertJsonPath('sin_leer', 0);
    }

    private function createNotification(Usuario $usuario, bool $leida): Notificacion
    {
        $notificacion = new Notificacion;
        $notificacion->id_usuario = $usuario->id_usuario;
        $notificacion->mensaje = 'Notificación de prueba.';
        $notificacion->fecha_envio = '2026-11-10 10:00:00';
        $notificacion->tipo = 'Agendamiento';
        $notificacion->leida = $leida;
        $notificacion->save();

        return $notificacion;
    }
}
