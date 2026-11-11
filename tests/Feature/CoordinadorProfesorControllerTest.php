<?php

namespace Tests\Feature;

use App\Models\Materia;
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
        $materiaMatematicas = Materia::create([
            'nombre_materia' => 'Matemáticas',
            'descripcion' => 'Matemáticas',
            'clave' => 'MAT-01',
            'area' => 'Ciencias',
        ]);
        $materiaProgramacion = Materia::create([
            'nombre_materia' => 'Programación',
            'descripcion' => 'Programación',
            'clave' => 'PROG-01',
            'area' => 'Ingeniería',
        ]);

        $response = $this->actingAs($coordinador)
            ->postJson(route('coordinador.profesores.store'), [
                'correo' => 'profesor@example.com',
                'num_empleado' => 'P001',
                'nombre' => 'Luis',
                'apellidos' => 'García',
                'departamento' => 'Sistemas',
                'especialidad' => 'Programación',
                'materias' => [$materiaMatematicas->id_materia, $materiaProgramacion->id_materia],
            ]);

        $response->assertCreated()
            ->assertJsonPath('profesor.especialidad', 'Programación')
            ->assertJsonPath('profesor.usuario.correo', 'profesor@example.com');

        $this->assertDatabaseHas('profesor', [
            'num_empleado' => 'P001',
            'especialidad' => 'Programación',
        ]);
        $profesorId = $response->json('profesor.id_profesor');
        $this->assertDatabaseHas('profesor_materia', [
            'id_profesor' => $profesorId,
            'id_materia' => $materiaMatematicas->id_materia,
        ]);
        $this->assertDatabaseHas('profesor_materia', [
            'id_profesor' => $profesorId,
            'id_materia' => $materiaProgramacion->id_materia,
        ]);

        $this->get(route('coordinador.profesores.index', ['q' => 'P001']))
            ->assertOk()
            ->assertViewIs('Coordinador.profesores')
            ->assertSee('Luis García')
            ->assertSee('Programación')
            ->assertSee('profesor@example.com');
    }

    public function test_coordinator_cannot_register_a_professor_with_an_unavailable_subject(): void
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
                'materias' => [999],
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('materias.0');
        $this->assertDatabaseMissing('profesor', ['num_empleado' => 'P001']);
        $this->assertDatabaseMissing('usuario', ['correo' => 'profesor@example.com']);
    }

    public function test_coordinator_cannot_register_a_professor_with_email_without_domain_termination(): void
    {
        $coordinador = Usuario::create([
            'correo' => 'coordinador@example.com',
            'contrasena_hash' => Hash::make('password'),
            'rol' => 'Coordinador',
            'estado' => 'Activo',
        ]);
        $materia = Materia::create([
            'nombre_materia' => 'Matemáticas',
            'descripcion' => 'Matemáticas',
            'clave' => 'MAT-01',
            'area' => 'Ciencias',
        ]);

        $response = $this->actingAs($coordinador)
            ->postJson(route('coordinador.profesores.store'), [
                'correo' => 'profesor@dominio',
                'nombre' => 'Luis',
                'apellidos' => 'García',
                'departamento' => 'Sistemas',
                'especialidad' => 'Programación',
                'materias' => [$materia->id_materia],
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('correo');
        $this->assertDatabaseMissing('usuario', ['correo' => 'profesor@dominio']);
        $this->assertDatabaseCount('profesor', 0);
    }

    public function test_coordinator_must_select_at_least_one_subject_when_registering_a_professor(): void
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
                'materias' => [],
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('materias');
        $this->assertDatabaseMissing('profesor', ['num_empleado' => 'P001']);
    }

    public function test_coordinator_cannot_select_the_same_subject_more_than_once(): void
    {
        $coordinador = Usuario::create([
            'correo' => 'coordinador@example.com',
            'contrasena_hash' => Hash::make('password'),
            'rol' => 'Coordinador',
            'estado' => 'Activo',
        ]);
        $materia = Materia::create([
            'nombre_materia' => 'Matemáticas',
            'descripcion' => 'Matemáticas',
            'clave' => 'MAT-01',
            'area' => 'Ciencias',
        ]);

        $response = $this->actingAs($coordinador)
            ->postJson(route('coordinador.profesores.store'), [
                'correo' => 'profesor@example.com',
                'num_empleado' => 'P001',
                'nombre' => 'Luis',
                'apellidos' => 'García',
                'departamento' => 'Sistemas',
                'especialidad' => 'Programación',
                'materias' => [$materia->id_materia, $materia->id_materia],
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('materias.1');
        $this->assertDatabaseMissing('profesor', ['num_empleado' => 'P001']);
    }
}
