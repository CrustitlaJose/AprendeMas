<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CoordinadorAlumnoControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_401_when_guest_attempts_to_register_student(): void
    {
        $this->postJson('/coordinador/alumnos', [
            'correo' => 'alumno@example.com',
        ])->assertStatus(401);
    }

    public function test_returns_403_when_non_coordinator_attempts_to_register_student(): void
    {
        $alumno = Usuario::create([
            'correo' => 'alumno@example.com',
            'contrasena_hash' => Hash::make('password'),
            'rol' => 'Alumno',
            'estado' => 'Activo',
        ]);

        $this->actingAs($alumno)
            ->postJson('/coordinador/alumnos', [
                'correo' => 'nuevo@example.com',
            ])
            ->assertStatus(403);
    }

    public function test_returns_422_when_required_student_data_is_missing(): void
    {
        $coordinador = Usuario::create([
            'correo' => 'coordinador@example.com',
            'contrasena_hash' => Hash::make('password'),
            'rol' => 'Coordinador',
            'estado' => 'Activo',
        ]);

        $this->actingAs($coordinador)
            ->postJson('/coordinador/alumnos', [
                'correo' => 'nuevo@example.com',
            ])
            ->assertStatus(422);

        $this->assertDatabaseCount('usuario', 1);
        $this->assertDatabaseCount('alumno', 0);
    }

    public function test_creates_student_with_hashed_temporary_password_and_returns_201(): void
    {
        $coordinador = Usuario::create([
            'correo' => 'coordinador@example.com',
            'contrasena_hash' => Hash::make('password'),
            'rol' => 'Coordinador',
            'estado' => 'Activo',
        ]);

        $response = $this->actingAs($coordinador)
            ->postJson('/coordinador/alumnos', [
                'correo' => 'nuevo@example.com',
                'matricula' => 'A00123456',
                'nombre' => 'Ana',
                'apellidos' => 'Pérez López',
                'telefono' => '5551234567',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('alumno.usuario.correo', 'nuevo@example.com');

        $this->assertDatabaseHas('usuario', [
            'correo' => 'nuevo@example.com',
            'rol' => 'Alumno',
            'estado' => 'Activo',
        ]);
        $this->assertDatabaseHas('alumno', [
            'matricula' => 'A00123456',
            'nombre' => 'Ana',
            'apellidos' => 'Pérez López',
            'telefono' => '5551234567',
            'estado' => 'Activo',
        ]);

        $usuarioCreado = Usuario::where('correo', 'nuevo@example.com')->firstOrFail();
        $contrasenaTemporal = $response->json('contrasena_temporal');

        $this->assertIsString($contrasenaTemporal);
        $this->assertNotSame('', $contrasenaTemporal);
        $this->assertNotSame($contrasenaTemporal, $usuarioCreado->contrasena_hash);
        $this->assertTrue(Hash::check($contrasenaTemporal, $usuarioCreado->contrasena_hash));
    }

    public function test_redirects_to_coordinator_view_after_registering_student(): void
    {
        $coordinador = Usuario::create([
            'correo' => 'coordinador@example.com',
            'contrasena_hash' => Hash::make('password'),
            'rol' => 'Coordinador',
            'estado' => 'Activo',
        ]);

        $response = $this->actingAs($coordinador)
            ->post('/coordinador/alumnos', [
                'correo' => 'nuevo@example.com',
                'matricula' => 'A00123456',
                'nombre' => 'Ana',
                'apellidos' => 'Pérez López',
                'telefono' => '5551234567',
            ]);

        $response->assertRedirect(route('coordinador'))
            ->assertSessionHas('status', 'Alumno registrado exitosamente.')
            ->assertSessionHas('contrasena_temporal');

        $this->assertDatabaseHas('alumno', [
            'matricula' => 'A00123456',
            'nombre' => 'Ana',
            'apellidos' => 'Pérez López',
            'telefono' => '5551234567',
            'estado' => 'Activo',
        ]);
        $this->assertDatabaseHas('usuario', [
            'correo' => 'nuevo@example.com',
            'rol' => 'Alumno',
            'estado' => 'Activo',
        ]);

        $this->get(route('coordinador'))
            ->assertOk()
            ->assertSee('A00123456')
            ->assertSee('Ana Pérez López')
            ->assertSee('Contraseña temporal del alumno');
    }

    public function test_coordinator_can_view_and_search_student_records(): void
    {
        $coordinador = Usuario::create([
            'correo' => 'coordinador@example.com',
            'contrasena_hash' => Hash::make('password'),
            'rol' => 'Coordinador',
            'estado' => 'Activo',
        ]);
        $usuarioAlumno = Usuario::create([
            'correo' => 'ana@example.com',
            'contrasena_hash' => Hash::make('password'),
            'rol' => 'Alumno',
            'estado' => 'Activo',
        ]);
        $alumno = new Alumno([
            'id_usuario' => $usuarioAlumno->id_usuario,
            'matricula' => 'A00123456',
            'nombre' => 'Ana',
            'apellidos' => 'Pérez López',
            'telefono' => '5551234567',
        ]);
        $alumno->estado = 'Activo';
        $alumno->save();

        $this->actingAs($coordinador)
            ->get(route('coordinador.alumnos.index', ['q' => 'A00123456']))
            ->assertOk()
            ->assertViewIs('Coordinador.alumnos')
            ->assertSee('A00123456')
            ->assertSee('Ana Pérez López')
            ->assertSee('ana@example.com');
    }
}
