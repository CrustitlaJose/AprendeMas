<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlumnoControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_coordinator_page_displays_registered_students_from_database(): void
    {
        $usuario = Usuario::create([
            'correo' => 'coordinador@example.com',
            'contrasena_hash' => bcrypt('password'),
            'rol' => 'Coordinador',
            'estado' => 'Activo',
        ]);
        Alumno::create([
            'id_usuario' => $usuario->id_usuario,
            'matricula' => 'A00123456',
            'nombre' => 'Ana',
            'apellidos' => 'Pérez López',
            'telefono' => '5551234567',
            'estado' => 'Activo',
        ]);

        $response = $this->actingAs($usuario)->get('/coordinador');

        $response->assertOk()
            ->assertSeeText('A00123456')
            ->assertSeeText('Ana Pérez López')
            ->assertDontSeeText('23090935');
    }

    public function test_student_cannot_access_coordinator_student_list(): void
    {
        $usuario = Usuario::create([
            'correo' => 'alumno@example.com',
            'contrasena_hash' => bcrypt('password'),
            'rol' => 'Alumno',
            'estado' => 'Activo',
        ]);

        $this->actingAs($usuario)->get('/coordinador')->assertForbidden();
    }
}
