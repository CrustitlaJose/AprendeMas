<?php

namespace Tests\Feature;

use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CoordinadorProfesorControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_coordinator_can_register_and_view_a_professor(): void
    {
        $coordinador = Usuario::create([
            'correo' => 'coordinador@example.com',
            'contrasena_hash' => Hash::make('password'),
            'rol' => 'Coordinador',
            'estado' => 'Activo',
        ]);

        $response = $this->actingAs($coordinador)
            ->postJson(route('coordinador.profesores.store'), [
                'correo' => 'profesor@example.com',
                'num_empleado' => 'P001',
                'nombre' => 'Luis',
                'apellidos' => 'García',
                'departamento' => 'Sistemas',
                'especialidad' => 'Programación',
            ]);

        $response->assertCreated()
            ->assertJsonPath('profesor.especialidad', 'Programación')
            ->assertJsonPath('profesor.usuario.correo', 'profesor@example.com');

        $this->assertDatabaseHas('profesor', [
            'num_empleado' => 'P001',
            'especialidad' => 'Programación',
        ]);

        $this->get(route('coordinador.profesores.index', ['q' => 'P001']))
            ->assertOk()
            ->assertViewIs('Coordinador.profesores')
            ->assertSee('Luis García')
            ->assertSee('Programación')
            ->assertSee('profesor@example.com');
    }
}
