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

        $response->assertRedirect(route('coordinador.alumnos.index'))
            ->assertSessionHas('status', 'Alumno registrado exitosamente.')
            ->assertSessionHas('contrasena_temporal');

        $this->assertDatabaseHas('alumno', [
            'matricula' => 'A00123456',
            'nombre' => 'Ana',
            'apellidos' => 'Pérez López',
            'telefono' => '5551234567',
        ]);
        $this->assertDatabaseHas('usuario', [
            'correo' => 'nuevo@example.com',
            'rol' => 'Alumno',
            'estado' => 'Activo',
        ]);

        $this->get(route('coordinador.alumnos.index'))
            ->assertOk()
            ->assertSee('A00123456')
            ->assertSee('Ana Pérez López')
            ->assertSee('Contraseña temporal');
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
        $alumno->save();

        $this->actingAs($coordinador)
            ->get(route('coordinador.alumnos.index', ['q' => 'A00123456']))
            ->assertOk()
            ->assertViewIs('Coordinador.alumnos')
            ->assertSee('A00123456')
            ->assertSee('Ana Pérez López')
            ->assertSee('ana@example.com')
            ->assertSee('Registrar alumno')
            ->assertDontSee('Supervisión de disponibilidades')
            ->assertDontSee('Guardar alumno');
    }

    public function test_inactive_student_row_offers_the_reactivation_action(): void
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
            'estado' => 'Inactivo',
        ]);
        $alumno = new Alumno([
            'id_usuario' => $usuarioAlumno->id_usuario,
            'matricula' => 'A00123456',
            'nombre' => 'Ana',
            'apellidos' => 'Pérez López',
            'telefono' => '5551234567',
        ]);
        $alumno->id_alumno = 900000000;
        $alumno->save();

        $this->actingAs($coordinador)
            ->get(route('coordinador.alumnos.index'))
            ->assertOk()
            ->assertSee('Dar de alta')
            ->assertDontSee('Dar de baja');
    }

    public function test_coordinator_can_reactivate_an_inactive_student(): void
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
            'estado' => 'Inactivo',
        ]);
        $alumno = new Alumno([
            'id_usuario' => $usuarioAlumno->id_usuario,
            'matricula' => 'A00123456',
            'nombre' => 'Ana',
            'apellidos' => 'Pérez López',
            'telefono' => '5551234567',
        ]);
        $alumno->id_alumno = 900000000;
        $alumno->save();

        $this->actingAs($coordinador)
            ->patchJson(route('coordinador.alumnos.active', $alumno->id_alumno))
            ->assertOk()
            ->assertJsonPath('message', 'Alta lógica aplicada correctamente.');

        $this->assertDatabaseHas('usuario', [
            'id_usuario' => $usuarioAlumno->id_usuario,
            'estado' => 'Activo',
        ]);
        $this->assertDatabaseHas('usuario', [
            'id_usuario' => $coordinador->id_usuario,
            'estado' => 'Activo',
        ]);

        $this->actingAs($usuarioAlumno->fresh())
            ->getJson(route('alumno.asesorias.index'))
            ->assertOk();
    }

    public function test_coordinator_can_deactivate_the_selected_student_without_changing_other_users(): void
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
        $alumno->id_alumno = 900000000;
        $alumno->save();

        $this->actingAs($coordinador)
            ->deleteJson(route('coordinador.alumnos.destroy', $alumno->id_alumno))
            ->assertOk()
            ->assertJsonPath('message', 'Baja lógica aplicada correctamente.');

        $this->assertDatabaseHas('usuario', [
            'id_usuario' => $usuarioAlumno->id_usuario,
            'estado' => 'Inactivo',
        ]);
        $this->assertDatabaseHas('usuario', [
            'id_usuario' => $coordinador->id_usuario,
            'estado' => 'Activo',
        ]);

        $this->actingAs($usuarioAlumno->fresh())
            ->getJson(route('alumno.asesorias.index'))
            ->assertForbidden();
    }

    public function test_student_registration_rejects_phone_numbers_without_exactly_ten_digits(): void
    {
        $coordinador = Usuario::create([
            'correo' => 'coordinador@example.com',
            'contrasena_hash' => Hash::make('password'),
            'rol' => 'Coordinador',
            'estado' => 'Activo',
        ]);

        $this->actingAs($coordinador);

        foreach (['123456789', '12345678901', '12345abcde'] as $index => $telefono) {
            $this->postJson('/coordinador/alumnos', [
                'correo' => "alumno{$index}@example.com",
                'nombre' => 'Ana',
                'apellidos' => 'Pérez',
                'telefono' => $telefono,
            ])
                ->assertStatus(422)
                ->assertJsonValidationErrors('telefono');
        }
    }

    public function test_student_registration_rejects_invalid_email_addresses(): void
    {
        $coordinador = Usuario::create([
            'correo' => 'coordinador@example.com',
            'contrasena_hash' => Hash::make('password'),
            'rol' => 'Coordinador',
            'estado' => 'Activo',
        ]);

        foreach (['esto-no-es-un-correo', 'ana@dominio'] as $correo) {
            $this->actingAs($coordinador)
                ->postJson('/coordinador/alumnos', [
                    'correo' => $correo,
                    'nombre' => 'Ana',
                    'apellidos' => 'Pérez',
                    'telefono' => '5551234567',
                ])
                ->assertStatus(422)
                ->assertJsonValidationErrors('correo')
                ->assertJsonPath('errors.correo.0', 'El correo debe tener un formato válido e incluir una terminación como .com o .mx.');

            $this->assertDatabaseMissing('usuario', ['correo' => $correo]);
        }
    }

    public function test_student_update_rejects_invalid_phone_and_email_values(): void
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
        $alumno->save();

        $this->actingAs($coordinador)
            ->patchJson(route('coordinador.alumnos.update', $alumno->id_alumno), [
                'correo' => 'ana@dominio',
                'telefono' => '123456789',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['correo', 'telefono']);
    }

    public function test_coordinator_can_open_the_dedicated_student_registration_view(): void
    {
        $coordinador = Usuario::create([
            'correo' => 'coordinador@example.com',
            'contrasena_hash' => Hash::make('password'),
            'rol' => 'Coordinador',
            'estado' => 'Activo',
        ]);

        $this->actingAs($coordinador)
            ->get(route('coordinador.alumnos.create'))
            ->assertOk()
            ->assertViewIs('Coordinador.create')
            ->assertSee('Registrar alumno')
            ->assertSee('name="matricula"', false)
            ->assertSee('Guardar alumno');
    }

    public function test_student_registration_form_requires_email_and_exactly_ten_phone_digits(): void
    {
        $coordinador = Usuario::create([
            'correo' => 'coordinador@example.com',
            'contrasena_hash' => Hash::make('password'),
            'rol' => 'Coordinador',
            'estado' => 'Activo',
        ]);

        $this->actingAs($coordinador)
            ->get(route('coordinador.alumnos.create'))
            ->assertOk()
            ->assertSee('type="email" name="correo"', false)
            ->assertSee('type="tel" name="telefono"', false)
            ->assertSee('pattern="[0-9]{10}"', false)
            ->assertSee('minlength="10" maxlength="10"', false);
    }
}
