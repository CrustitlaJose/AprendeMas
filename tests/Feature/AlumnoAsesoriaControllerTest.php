<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\Asesoria;
use App\Models\Aula;
use App\Models\Disponibilidad;
use App\Models\Materia;
use App\Models\Profesor;
use App\Models\ProfesorMateria;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AlumnoAsesoriaControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_list_only_their_own_appointments(): void
    {
        ['usuario' => $usuario, 'alumno' => $alumno] = $this->createStudent('alumno@example.com');
        ['alumno' => $otroAlumno] = $this->createStudent('otro@example.com');
        ['profesor' => $profesor, 'materia' => $materia] = $this->createProfessor('profesor@example.com');
        $aula = $this->createAula();
        $fecha = now()->addDays(2)->toDateString();
        $propia = $this->createDisponibilidad($profesor, $fecha, '09:00:00', '10:00:00');
        $ajena = $this->createDisponibilidad($profesor, $fecha, '10:00:00', '11:00:00');
        $asesoriaPropia = $this->createAsesoria($alumno, $propia, $materia, $aula);
        $this->createAsesoria($otroAlumno, $ajena, $materia, $aula);

        $response = $this->actingAs($usuario)->getJson(route('alumno.asesorias.index'));

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id_asesoria', $asesoriaPropia->id_asesoria)
            ->assertJsonPath('data.0.materia.id_materia', $materia->id_materia);
    }

    public function test_only_students_can_access_their_appointments(): void
    {
        $this->getJson(route('alumno.asesorias.index'))->assertUnauthorized();

        $coordinador = Usuario::create([
            'correo' => 'coordinador@example.com',
            'contrasena_hash' => Hash::make('password'),
            'rol' => 'Coordinador',
            'estado' => 'Activo',
        ]);

        $this->actingAs($coordinador)
            ->getJson(route('alumno.asesorias.index'))
            ->assertForbidden();
    }

    public function test_booking_view_explains_that_the_room_is_assigned_automatically(): void
    {
        ['usuario' => $usuario] = $this->createStudent('alumno@example.com');

        $this->actingAs($usuario)
            ->get(route('mis-asesorias'))
            ->assertOk()
            ->assertSee('El sistema asignará automáticamente un cubículo disponible')
            ->assertDontSee('name="id_aula"', false)
            ->assertDontSee('Catálogo de aulas');
    }

    public function test_student_can_book_an_available_slot_and_cannot_book_it_twice(): void
    {
        ['usuario' => $usuario, 'alumno' => $alumno] = $this->createStudent('alumno@example.com');
        ['alumno' => $otroAlumno] = $this->createStudent('otro@example.com');
        ['profesor' => $profesor, 'materia' => $materia] = $this->createProfessor('profesor@example.com');
        ['profesor' => $otroProfesor] = $this->createProfessor('otro-profesor@example.com');
        $aulaOcupada = $this->createAula('C-01');
        $aulaDisponible = $this->createAula('C-02');
        $fecha = now()->addDays(2)->toDateString();
        $disponibilidadOcupada = $this->createDisponibilidad(
            $otroProfesor,
            $fecha,
            '09:30:00',
            '10:30:00',
            'Ocupado'
        );
        $this->createAsesoria($otroAlumno, $disponibilidadOcupada, $materia, $aulaOcupada);
        $disponibilidad = $this->createDisponibilidad(
            $profesor,
            $fecha,
            '09:00:00',
            '10:00:00'
        );

        $response = $this->actingAs($usuario)->postJson(route('alumno.asesorias.store'), [
            'id_alumno' => $otroAlumno->id_alumno,
            'id_disponibilidad' => $disponibilidad->id_disponibilidad,
            'id_materia' => $materia->id_materia,
            'estado' => 'Cancelada',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.id_alumno', $alumno->id_alumno)
            ->assertJsonPath('data.estado', 'Confirmada')
            ->assertJsonPath('data.id_aula', $aulaDisponible->id_aula);
        $this->assertDatabaseHas('asesoria', [
            'id_asesoria' => $response->json('data.id_asesoria'),
            'id_alumno' => $alumno->id_alumno,
            'id_disponibilidad' => $disponibilidad->id_disponibilidad,
            'id_materia' => $materia->id_materia,
            'id_aula' => $aulaDisponible->id_aula,
            'estado' => 'Confirmada',
        ]);
        $this->assertDatabaseHas('disponibilidad', [
            'id_disponibilidad' => $disponibilidad->id_disponibilidad,
            'estado' => 'Ocupado',
        ]);

        $otroUsuario = $this->createStudent('tercer-alumno@example.com')['usuario'];
        $this->actingAs($otroUsuario)
            ->postJson(route('alumno.asesorias.store'), [
                'id_disponibilidad' => $disponibilidad->id_disponibilidad,
                'id_materia' => $materia->id_materia,
            ])
            ->assertStatus(409);

        $this->assertDatabaseCount('asesoria', 2);
    }

    public function test_student_cannot_book_an_unassigned_subject_or_when_no_room_is_available(): void
    {
        ['usuario' => $usuario] = $this->createStudent('alumno@example.com');
        ['profesor' => $profesor, 'materia' => $materia] = $this->createProfessor('profesor@example.com');
        ['profesor' => $otroProfesor, 'materia' => $otraMateria] = $this->createProfessor('otro-profesor@example.com');
        $aula = $this->createAula();
        $fecha = now()->addDays(2)->toDateString();
        $disponibilidad = $this->createDisponibilidad($profesor, $fecha, '09:00:00', '10:00:00');

        $this->actingAs($usuario)
            ->postJson(route('alumno.asesorias.store'), [
                'id_disponibilidad' => $disponibilidad->id_disponibilidad,
                'id_materia' => $otraMateria->id_materia,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('id_materia');

        $disponibilidadOcupada = $this->createDisponibilidad(
            $otroProfesor,
            $fecha,
            '09:30:00',
            '10:30:00',
            'Ocupado'
        );
        $otroAlumno = $this->createStudent('otro-alumno@example.com')['alumno'];
        $this->createAsesoria($otroAlumno, $disponibilidadOcupada, $otraMateria, $aula);

        $this->postJson(route('alumno.asesorias.store'), [
            'id_disponibilidad' => $disponibilidad->id_disponibilidad,
            'id_materia' => $materia->id_materia,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('id_disponibilidad');
    }

    public function test_student_cannot_choose_an_aula_for_their_booking(): void
    {
        ['usuario' => $usuario] = $this->createStudent('alumno@example.com');
        ['profesor' => $profesor, 'materia' => $materia] = $this->createProfessor('profesor@example.com');
        $aula = $this->createAula();
        $disponibilidad = $this->createDisponibilidad(
            $profesor,
            now()->addDays(2)->toDateString(),
            '09:00:00',
            '10:00:00'
        );

        $response = $this->actingAs($usuario)
            ->postJson(route('alumno.asesorias.store'), [
                'id_disponibilidad' => $disponibilidad->id_disponibilidad,
                'id_materia' => $materia->id_materia,
                'id_aula' => $aula->id_aula,
            ]);

        $response->assertUnprocessable()->assertJsonValidationErrors('id_aula');
        $this->assertDatabaseCount('asesoria', 0);
    }

    public function test_virtual_availability_is_assigned_a_virtual_room(): void
    {
        ['usuario' => $usuario] = $this->createStudent('alumno@example.com');
        ['profesor' => $profesor, 'materia' => $materia] = $this->createProfessor('profesor@example.com');
        $this->createAula('C-01', 'Presencial');
        $aulaVirtual = $this->createAula('V-01', 'Virtual');
        $disponibilidad = $this->createDisponibilidad(
            $profesor,
            now()->addDays(2)->toDateString(),
            '09:00:00',
            '10:00:00',
            'Disponible',
            'Virtual'
        );

        $response = $this->actingAs($usuario)
            ->postJson(route('alumno.asesorias.store'), [
                'id_disponibilidad' => $disponibilidad->id_disponibilidad,
                'id_materia' => $materia->id_materia,
            ]);

        $response->assertCreated()->assertJsonPath('data.id_aula', $aulaVirtual->id_aula);
    }

    public function test_student_can_update_their_booking_and_release_the_previous_slot(): void
    {
        ['usuario' => $usuario, 'alumno' => $alumno] = $this->createStudent('alumno@example.com');
        ['profesor' => $profesor, 'materia' => $materia] = $this->createProfessor('profesor@example.com');
        $aulaAnterior = $this->createAula('C-01');
        $aulaNueva = $this->createAula('C-02');
        $fecha = now()->addDays(2)->toDateString();
        $disponibilidadAnterior = $this->createDisponibilidad($profesor, $fecha, '09:00:00', '10:00:00', 'Ocupado');
        $disponibilidadNueva = $this->createDisponibilidad($profesor, $fecha, '10:00:00', '11:00:00');
        $asesoria = $this->createAsesoria($alumno, $disponibilidadAnterior, $materia, $aulaAnterior);
        ['alumno' => $otroAlumno] = $this->createStudent('otro-alumno@example.com');
        ['profesor' => $otroProfesor] = $this->createProfessor('otro-profesor@example.com');
        $disponibilidadConAulaOcupada = $this->createDisponibilidad(
            $otroProfesor,
            $fecha,
            '10:00:00',
            '11:00:00',
            'Ocupado'
        );
        $this->createAsesoria($otroAlumno, $disponibilidadConAulaOcupada, $materia, $aulaAnterior);

        $response = $this->actingAs($usuario)
            ->patchJson(route('alumno.asesorias.update', $asesoria->id_asesoria), [
                'id_disponibilidad' => $disponibilidadNueva->id_disponibilidad,
            ]);

        $response->assertOk()
            ->assertJsonPath('data.id_disponibilidad', $disponibilidadNueva->id_disponibilidad)
            ->assertJsonPath('data.id_aula', $aulaNueva->id_aula);
        $this->assertDatabaseHas('asesoria', [
            'id_asesoria' => $asesoria->id_asesoria,
            'id_disponibilidad' => $disponibilidadNueva->id_disponibilidad,
            'id_aula' => $aulaNueva->id_aula,
        ]);
        $this->assertDatabaseHas('disponibilidad', [
            'id_disponibilidad' => $disponibilidadAnterior->id_disponibilidad,
            'estado' => 'Disponible',
        ]);
        $this->assertDatabaseHas('disponibilidad', [
            'id_disponibilidad' => $disponibilidadNueva->id_disponibilidad,
            'estado' => 'Ocupado',
        ]);
    }

    public function test_student_can_cancel_at_least_twelve_hours_ahead_and_release_the_slot(): void
    {
        $this->travelTo(Carbon::parse('2026-01-01 08:00:00'));
        ['usuario' => $usuario, 'alumno' => $alumno] = $this->createStudent('alumno@example.com');
        ['profesor' => $profesor, 'materia' => $materia] = $this->createProfessor('profesor@example.com');
        $aula = $this->createAula();
        $disponibilidad = $this->createDisponibilidad($profesor, '2026-01-01', '20:00:00', '21:00:00', 'Ocupado');
        $asesoria = $this->createAsesoria($alumno, $disponibilidad, $materia, $aula);

        $response = $this->actingAs($usuario)
            ->deleteJson(route('alumno.asesorias.destroy', $asesoria->id_asesoria));

        $response->assertOk()->assertJsonPath('data.estado', 'Cancelada');
        $this->assertDatabaseHas('asesoria', [
            'id_asesoria' => $asesoria->id_asesoria,
            'estado' => 'Cancelada',
        ]);
        $this->assertDatabaseHas('disponibilidad', [
            'id_disponibilidad' => $disponibilidad->id_disponibilidad,
            'estado' => 'Disponible',
        ]);
    }

    public function test_student_cannot_cancel_another_students_appointment(): void
    {
        $this->travelTo(Carbon::parse('2026-01-01 08:00:00'));
        ['alumno' => $alumno] = $this->createStudent('alumno@example.com');
        ['usuario' => $otroUsuario] = $this->createStudent('otro@example.com');
        ['profesor' => $profesor, 'materia' => $materia] = $this->createProfessor('profesor@example.com');
        $aula = $this->createAula();
        $disponibilidad = $this->createDisponibilidad($profesor, '2026-01-01', '20:00:00', '21:00:00', 'Ocupado');
        $asesoria = $this->createAsesoria($alumno, $disponibilidad, $materia, $aula);

        $this->actingAs($otroUsuario)
            ->deleteJson(route('alumno.asesorias.destroy', $asesoria->id_asesoria))
            ->assertNotFound();

        $this->assertDatabaseHas('asesoria', [
            'id_asesoria' => $asesoria->id_asesoria,
            'estado' => 'Confirmada',
        ]);
        $this->assertDatabaseHas('disponibilidad', [
            'id_disponibilidad' => $disponibilidad->id_disponibilidad,
            'estado' => 'Ocupado',
        ]);
    }

    public function test_student_cannot_cancel_with_less_than_twelve_hours_of_notice(): void
    {
        $this->travelTo(Carbon::parse('2026-01-01 08:00:00'));
        ['usuario' => $usuario, 'alumno' => $alumno] = $this->createStudent('alumno@example.com');
        ['profesor' => $profesor, 'materia' => $materia] = $this->createProfessor('profesor@example.com');
        $aula = $this->createAula();
        $disponibilidad = $this->createDisponibilidad($profesor, '2026-01-01', '19:59:00', '20:59:00', 'Ocupado');
        $asesoria = $this->createAsesoria($alumno, $disponibilidad, $materia, $aula);

        $this->actingAs($usuario)
            ->deleteJson(route('alumno.asesorias.destroy', $asesoria->id_asesoria))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('asesoria');

        $this->assertDatabaseHas('asesoria', [
            'id_asesoria' => $asesoria->id_asesoria,
            'estado' => 'Confirmada',
        ]);
        $this->assertDatabaseHas('disponibilidad', [
            'id_disponibilidad' => $disponibilidad->id_disponibilidad,
            'estado' => 'Ocupado',
        ]);
    }

    /**
     * @return array{usuario: Usuario, alumno: Alumno}
     */
    private function createStudent(string $email): array
    {
        $usuario = Usuario::create([
            'correo' => $email,
            'contrasena_hash' => Hash::make('password'),
            'rol' => 'Alumno',
            'estado' => 'Activo',
        ]);
        $sufijo = strtoupper(substr(hash('sha256', $email), 0, 8));
        $alumno = Alumno::create([
            'id_usuario' => $usuario->id_usuario,
            'matricula' => 'A'.$sufijo,
            'nombre' => 'Ana',
            'apellidos' => 'Pérez',
            'telefono' => 'T'.$sufijo,
            'estado' => 'Activo',
        ]);

        return ['usuario' => $usuario, 'alumno' => $alumno];
    }

    /**
     * @return array{usuario: Usuario, profesor: Profesor, materia: Materia}
     */
    private function createProfessor(string $email): array
    {
        $usuario = Usuario::create([
            'correo' => $email,
            'contrasena_hash' => Hash::make('password'),
            'rol' => 'Profesor',
            'estado' => 'Activo',
        ]);
        $sufijo = strtoupper(substr(hash('sha256', $email), 0, 6));
        $profesor = Profesor::create([
            'id_usuario' => $usuario->id_usuario,
            'num_empleado' => 'P'.$sufijo,
            'nombre' => 'Luis',
            'apellidos' => 'López',
            'departamento' => 'Ciencias',
            'especialidad' => 'Matemáticas',
        ]);
        $materia = Materia::create([
            'nombre_materia' => 'Matemáticas',
            'descripcion' => 'Asesoría de matemáticas',
            'clave' => 'MAT-'.$sufijo,
            'area' => 'Ciencias',
        ]);
        ProfesorMateria::create([
            'id_profesor' => $profesor->id_profesor,
            'id_materia' => $materia->id_materia,
        ]);

        return ['usuario' => $usuario, 'profesor' => $profesor, 'materia' => $materia];
    }

    private function createAula(string $nombre = 'C-01', string $tipo = 'Presencial'): Aula
    {
        return Aula::create([
            'nombre_cubiculo' => $nombre,
            'capacidad' => '4',
            'tipo' => $tipo,
        ]);
    }

    private function createDisponibilidad(
        Profesor $profesor,
        string $fecha,
        string $inicio,
        string $fin,
        string $estado = 'Disponible',
        string $modalidad = 'Presencial'
    ): Disponibilidad {
        return Disponibilidad::create([
            'id_profesor' => $profesor->id_profesor,
            'fecha' => $fecha,
            'hora_inicio' => $inicio,
            'hora_fin' => $fin,
            'modalidad' => $modalidad,
            'estado' => $estado,
        ]);
    }

    private function createAsesoria(
        Alumno $alumno,
        Disponibilidad $disponibilidad,
        Materia $materia,
        Aula $aula
    ): Asesoria {
        return Asesoria::create([
            'id_alumno' => $alumno->id_alumno,
            'id_disponibilidad' => $disponibilidad->id_disponibilidad,
            'id_materia' => $materia->id_materia,
            'id_aula' => $aula->id_aula,
            'fecha_registro' => now(),
            'estado' => 'Confirmada',
        ]);
    }
}
