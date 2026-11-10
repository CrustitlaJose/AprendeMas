<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\Asesoria;
use App\Models\Aula;
use App\Models\Disponibilidad;
use App\Models\Materia;
use App\Models\Profesor;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DisponibilidadManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_professor_sees_only_their_own_availability(): void
    {
        $profesor = $this->crearProfesor('profesor@example.com');
        $otroProfesor = $this->crearProfesor('otro-profesor@example.com');
        Disponibilidad::create([
            'id_profesor' => $profesor->profesor()->firstOrFail()->id_profesor,
            'fecha' => '2030-01-02',
            'hora_inicio' => '09:00',
            'hora_fin' => '10:00',
            'modalidad' => 'Presencial',
            'estado' => 'Disponible',
        ]);
        Disponibilidad::create([
            'id_profesor' => $otroProfesor->profesor()->firstOrFail()->id_profesor,
            'fecha' => '2030-01-03',
            'hora_inicio' => '09:00',
            'hora_fin' => '10:00',
            'modalidad' => 'Virtual',
            'estado' => 'Disponible',
        ]);

        $this->actingAs($profesor)
            ->get(route('profesor.disponibilidades.index'))
            ->assertOk()
            ->assertViewIs('Profesor.disponibilidades')
            ->assertSee('2030-01-02')
            ->assertDontSee('2030-01-03');
    }

    public function test_availability_form_offers_only_virtual_and_in_person_modalities(): void
    {
        $profesor = $this->crearProfesor('profesor@example.com');

        $this->actingAs($profesor)
            ->get(route('profesor.disponibilidades.index'))
            ->assertOk()
            ->assertSee('<option value="Presencial">Presencial</option>', false)
            ->assertSee('<option value="Virtual">Virtual</option>', false);
    }

    public function test_professor_can_create_availability_and_cannot_set_its_state(): void
    {
        $profesor = $this->crearProfesor('profesor@example.com');
        $idProfesor = $profesor->profesor()->firstOrFail()->id_profesor;

        $this->actingAs($profesor)
            ->post(route('profesor.disponibilidades.store'), [
                'fecha' => '2030-01-02',
                'hora_inicio' => '09:00',
                'hora_fin' => '10:00',
                'modalidad' => 'Presencial',
                'repeticion' => 'unica',
                'estado' => 'Ocupado',
            ])
            ->assertRedirect(route('profesor.disponibilidades.index'))
            ->assertSessionHas('status', 'La disponibilidad se registró correctamente.');

        $this->assertDatabaseHas('disponibilidad', [
            'id_profesor' => $idProfesor,
            'fecha' => '2030-01-02',
            'hora_inicio' => '09:00:00',
            'estado' => 'Disponible',
        ]);
    }

    public function test_professor_can_create_weekly_availability_through_the_selected_end_date(): void
    {
        $profesor = $this->crearProfesor('profesor@example.com');
        $idProfesor = $profesor->profesor()->firstOrFail()->id_profesor;

        $this->actingAs($profesor)
            ->post(route('profesor.disponibilidades.store'), [
                'fecha' => '2030-01-02',
                'fecha_fin' => '2030-01-23',
                'hora_inicio' => '09:00',
                'hora_fin' => '10:00',
                'modalidad' => 'Virtual',
                'repeticion' => 'semanal',
            ])
            ->assertRedirect(route('profesor.disponibilidades.index'))
            ->assertSessionHas('status', '4 disponibilidades semanales se registraron correctamente.');

        foreach (['2030-01-02', '2030-01-09', '2030-01-16', '2030-01-23'] as $fecha) {
            $this->assertDatabaseHas('disponibilidad', [
                'id_profesor' => $idProfesor,
                'fecha' => $fecha,
                'hora_inicio' => '09:00:00',
                'hora_fin' => '10:00:00',
                'modalidad' => 'Virtual',
                'estado' => 'Disponible',
            ]);
        }

        $this->assertDatabaseCount('disponibilidad', 4);
    }

    public function test_weekly_availability_is_not_partially_created_if_a_repeated_date_overlaps(): void
    {
        $profesor = $this->crearProfesor('profesor@example.com');
        $idProfesor = $profesor->profesor()->firstOrFail()->id_profesor;
        Disponibilidad::create([
            'id_profesor' => $idProfesor,
            'fecha' => '2030-01-16',
            'hora_inicio' => '09:30',
            'hora_fin' => '10:30',
            'modalidad' => 'Presencial',
            'estado' => 'Disponible',
        ]);

        $this->actingAs($profesor)
            ->from(route('profesor.disponibilidades.index'))
            ->post(route('profesor.disponibilidades.store'), [
                'fecha' => '2030-01-02',
                'fecha_fin' => '2030-01-23',
                'hora_inicio' => '09:00',
                'hora_fin' => '10:00',
                'modalidad' => 'Presencial',
                'repeticion' => 'semanal',
            ])
            ->assertRedirect(route('profesor.disponibilidades.index'))
            ->assertSessionHasErrors('hora_inicio');

        $this->assertDatabaseCount('disponibilidad', 1);
        $this->assertDatabaseMissing('disponibilidad', [
            'id_profesor' => $idProfesor,
            'fecha' => '2030-01-02',
        ]);
    }

    public function test_professor_cannot_create_availability_with_another_modality(): void
    {
        $profesor = $this->crearProfesor('profesor@example.com');

        $this->actingAs($profesor)
            ->from(route('profesor.disponibilidades.index'))
            ->post(route('profesor.disponibilidades.store'), [
                'fecha' => '2030-01-02',
                'hora_inicio' => '09:00',
                'hora_fin' => '10:00',
                'modalidad' => 'Híbrida',
                'repeticion' => 'unica',
            ])
            ->assertRedirect(route('profesor.disponibilidades.index'))
            ->assertSessionHasErrors('modalidad');
    }

    public function test_professor_cannot_create_overlapping_availability(): void
    {
        $profesor = $this->crearProfesor('profesor@example.com');
        $idProfesor = $profesor->profesor()->firstOrFail()->id_profesor;
        Disponibilidad::create([
            'id_profesor' => $idProfesor,
            'fecha' => '2030-01-02',
            'hora_inicio' => '09:00',
            'hora_fin' => '10:00',
            'modalidad' => 'Presencial',
            'estado' => 'Disponible',
        ]);

        $this->actingAs($profesor)
            ->from(route('profesor.disponibilidades.index'))
            ->post(route('profesor.disponibilidades.store'), [
                'fecha' => '2030-01-02',
                'hora_inicio' => '09:30',
                'hora_fin' => '10:30',
                'modalidad' => 'Virtual',
            ])
            ->assertRedirect(route('profesor.disponibilidades.index'))
            ->assertSessionHasErrors('hora_inicio');

        $this->assertDatabaseMissing('disponibilidad', [
            'id_profesor' => $idProfesor,
            'fecha' => '2030-01-02',
            'hora_inicio' => '09:30:00',
        ]);
    }

    public function test_professor_cannot_create_a_slot_in_the_past_today(): void
    {
        $this->travelTo('2026-11-09 08:00:00');
        $profesor = $this->crearProfesor('profesor@example.com');
        $idProfesor = $profesor->profesor()->firstOrFail()->id_profesor;

        $this->actingAs($profesor)
            ->from(route('profesor.disponibilidades.index'))
            ->post(route('profesor.disponibilidades.store'), [
                'fecha' => '2026-11-09',
                'hora_inicio' => '07:00',
                'hora_fin' => '08:00',
                'modalidad' => 'Presencial',
            ])
            ->assertRedirect(route('profesor.disponibilidades.index'))
            ->assertSessionHasErrors('hora_inicio');

        $this->assertDatabaseMissing('disponibilidad', [
            'id_profesor' => $idProfesor,
            'fecha' => '2026-11-09',
        ]);
    }

    public function test_professor_can_update_and_delete_their_available_slot(): void
    {
        $profesor = $this->crearProfesor('profesor@example.com');
        $idProfesor = $profesor->profesor()->firstOrFail()->id_profesor;
        $disponibilidad = Disponibilidad::create([
            'id_profesor' => $idProfesor,
            'fecha' => '2030-01-02',
            'hora_inicio' => '09:00',
            'hora_fin' => '10:00',
            'modalidad' => 'Presencial',
            'estado' => 'Disponible',
        ]);

        $this->actingAs($profesor)
            ->put(route('profesor.disponibilidades.update', $disponibilidad->id_disponibilidad), [
                'fecha' => '2030-01-03',
                'hora_inicio' => '11:00',
                'hora_fin' => '12:00',
                'modalidad' => 'Virtual',
            ])
            ->assertRedirect(route('profesor.disponibilidades.index'))
            ->assertSessionHas('status', 'La disponibilidad se actualizó correctamente.');

        $this->assertDatabaseHas('disponibilidad', [
            'id_disponibilidad' => $disponibilidad->id_disponibilidad,
            'fecha' => '2030-01-03',
            'hora_inicio' => '11:00:00',
            'estado' => 'Disponible',
        ]);

        $this->delete(route('profesor.disponibilidades.destroy', $disponibilidad->id_disponibilidad))
            ->assertRedirect(route('profesor.disponibilidades.index'))
            ->assertSessionHas('status', 'La disponibilidad se eliminó correctamente.');

        $this->assertDatabaseMissing('disponibilidad', [
            'id_disponibilidad' => $disponibilidad->id_disponibilidad,
        ]);
        $this->assertDatabaseHas('notificacion', [
            'id_asesoria' => null,
            'id_usuario' => $profesor->id_usuario,
            'tipo' => 'Eliminación de disponibilidad',
        ]);
    }

    public function test_professor_can_delete_availability_with_a_confirmed_appointment_and_notify_both_users(): void
    {
        $profesorUsuario = $this->crearProfesor('profesor@example.com');
        $profesor = $profesorUsuario->profesor()->firstOrFail();
        $alumnoUsuario = Usuario::create([
            'correo' => 'alumno@example.com',
            'contrasena_hash' => bcrypt('password'),
            'rol' => 'Alumno',
            'estado' => 'Activo',
        ]);
        $alumno = Alumno::create([
            'id_usuario' => $alumnoUsuario->id_usuario,
            'matricula' => 'A001',
            'nombre' => 'Luis',
            'apellidos' => 'Pérez',
            'telefono' => '5550000001',
            'estado' => 'Activo',
        ]);
        $materia = Materia::create([
            'nombre_materia' => 'Matemáticas',
            'descripcion' => 'Matemáticas',
            'clave' => 'MAT-01',
            'area' => 'Ciencias',
        ]);
        $aula = Aula::create([
            'nombre_cubiculo' => 'C-01',
            'capacidad' => '4',
            'tipo' => 'Presencial',
        ]);
        $disponibilidad = Disponibilidad::create([
            'id_profesor' => $profesor->id_profesor,
            'fecha' => '2030-01-02',
            'hora_inicio' => '09:00',
            'hora_fin' => '10:00',
            'modalidad' => 'Presencial',
            'estado' => 'Ocupado',
        ]);
        $asesoria = Asesoria::create([
            'id_alumno' => $alumno->id_alumno,
            'id_disponibilidad' => $disponibilidad->id_disponibilidad,
            'id_materia' => $materia->id_materia,
            'id_aula' => $aula->id_aula,
            'fecha_registro' => now(),
            'estado' => 'Confirmada',
        ]);

        $this->actingAs($profesorUsuario)
            ->delete(route('profesor.disponibilidades.destroy', $disponibilidad->id_disponibilidad))
            ->assertRedirect(route('profesor.disponibilidades.index'))
            ->assertSessionHas('status', 'La disponibilidad se eliminó correctamente.');

        $this->assertDatabaseHas('asesoria', [
            'id_asesoria' => $asesoria->id_asesoria,
            'id_disponibilidad' => null,
            'estado' => 'Cancelada',
        ]);
        $this->assertDatabaseHas('notificacion', [
            'id_asesoria' => $asesoria->id_asesoria,
            'id_usuario' => $alumnoUsuario->id_usuario,
            'tipo' => 'Eliminación de disponibilidad',
        ]);
        $this->assertDatabaseHas('notificacion', [
            'id_asesoria' => $asesoria->id_asesoria,
            'id_usuario' => $profesorUsuario->id_usuario,
            'tipo' => 'Eliminación de disponibilidad',
        ]);
        $this->assertDatabaseCount('notificacion', 2);
    }

    public function test_professor_cannot_update_another_professors_availability(): void
    {
        $profesor = $this->crearProfesor('profesor@example.com');
        $otroProfesor = $this->crearProfesor('otro-profesor@example.com');
        $disponibilidad = Disponibilidad::create([
            'id_profesor' => $otroProfesor->profesor()->firstOrFail()->id_profesor,
            'fecha' => '2030-01-02',
            'hora_inicio' => '09:00',
            'hora_fin' => '10:00',
            'modalidad' => 'Presencial',
            'estado' => 'Disponible',
        ]);

        $this->actingAs($profesor)
            ->put(route('profesor.disponibilidades.update', $disponibilidad->id_disponibilidad), [
                'fecha' => '2030-01-02',
                'hora_inicio' => '11:00',
                'hora_fin' => '12:00',
                'modalidad' => 'Virtual',
            ])
            ->assertNotFound();

        $this->assertDatabaseHas('disponibilidad', [
            'id_disponibilidad' => $disponibilidad->id_disponibilidad,
            'hora_inicio' => '09:00:00',
        ]);
    }

    public function test_professor_cannot_edit_or_delete_occupied_availability(): void
    {
        $profesor = $this->crearProfesor('profesor@example.com');
        $disponibilidad = Disponibilidad::create([
            'id_profesor' => $profesor->profesor()->firstOrFail()->id_profesor,
            'fecha' => '2030-01-02',
            'hora_inicio' => '09:00',
            'hora_fin' => '10:00',
            'modalidad' => 'Presencial',
            'estado' => 'Ocupado',
        ]);

        $this->actingAs($profesor)
            ->from(route('profesor.disponibilidades.index'))
            ->put(route('profesor.disponibilidades.update', $disponibilidad->id_disponibilidad), [
                'fecha' => '2030-01-02',
                'hora_inicio' => '11:00',
                'hora_fin' => '12:00',
                'modalidad' => 'Virtual',
            ])
            ->assertRedirect(route('profesor.disponibilidades.index'))
            ->assertSessionHasErrors('disponibilidad');

        $this->delete(route('profesor.disponibilidades.destroy', $disponibilidad->id_disponibilidad))
            ->assertRedirect(route('profesor.disponibilidades.index'))
            ->assertSessionHasErrors('disponibilidad');

        $this->assertModelExists($disponibilidad);
    }

    public function test_non_professor_cannot_access_availability_management(): void
    {
        $alumno = Usuario::create([
            'correo' => 'alumno@example.com',
            'contrasena_hash' => bcrypt('password'),
            'rol' => 'Alumno',
            'estado' => 'Activo',
        ]);

        $this->actingAs($alumno)
            ->get(route('profesor.disponibilidades.index'))
            ->assertForbidden();
    }

    private function crearProfesor(string $correo): Usuario
    {
        $usuario = Usuario::create([
            'correo' => $correo,
            'contrasena_hash' => bcrypt('password'),
            'rol' => 'Profesor',
            'estado' => 'Activo',
        ]);

        Profesor::create([
            'id_usuario' => $usuario->id_usuario,
            'num_empleado' => $correo,
            'nombre' => 'Ana',
            'apellidos' => 'López',
            'departamento' => 'Ciencias',
            'especialidad' => 'Matemáticas',
        ]);

        return $usuario;
    }
}
