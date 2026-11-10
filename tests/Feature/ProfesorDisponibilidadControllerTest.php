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
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfesorDisponibilidadControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_professor_can_create_list_show_update_and_delete_own_availability(): void
    {
        ['usuario' => $usuario, 'profesor' => $profesor] = $this->createProfessor('profesor@example.com');
        $fecha = now()->addDays(2)->toDateString();

        $createResponse = $this->actingAs($usuario)->postJson(route('profesor.api.disponibilidades.store'), [
            'fecha' => $fecha,
            'hora_inicio' => '09:00',
            'hora_fin' => '10:00',
            'modalidad' => 'Presencial',
            'id_profesor' => $profesor->id_profesor + 1,
            'estado' => 'Ocupado',
        ]);

        $createResponse->assertCreated()
            ->assertJsonPath('data.id_profesor', $profesor->id_profesor)
            ->assertJsonPath('data.estado', 'Disponible');

        $idDisponibilidad = $createResponse->json('data.id_disponibilidad');
        $this->assertDatabaseHas('disponibilidad', [
            'id_disponibilidad' => $idDisponibilidad,
            'id_profesor' => $profesor->id_profesor,
            'fecha' => $fecha,
            'estado' => 'Disponible',
        ]);

        $this->getJson(route('profesor.api.disponibilidades.index'))
            ->assertOk()
            ->assertJsonPath('data.0.id_disponibilidad', $idDisponibilidad);
        $this->getJson(route('profesor.api.disponibilidades.show', $idDisponibilidad))
            ->assertOk()
            ->assertJsonPath('data.id_disponibilidad', $idDisponibilidad);

        $this->patchJson(route('profesor.api.disponibilidades.update', $idDisponibilidad), [
            'modalidad' => 'Virtual',
        ])->assertOk()
            ->assertJsonPath('data.modalidad', 'Virtual');

        $this->assertDatabaseHas('disponibilidad', [
            'id_disponibilidad' => $idDisponibilidad,
            'modalidad' => 'Virtual',
        ]);

        $this->deleteJson(route('profesor.api.disponibilidades.destroy', $idDisponibilidad))
            ->assertNoContent();

        $this->assertDatabaseMissing('disponibilidad', [
            'id_disponibilidad' => $idDisponibilidad,
        ]);
    }

    public function test_professor_cannot_create_overlapping_availability_or_access_another_professors_slot(): void
    {
        ['usuario' => $usuario, 'profesor' => $profesor] = $this->createProfessor('profesor@example.com');
        ['profesor' => $otroProfesor] = $this->createProfessor('otro-profesor@example.com');
        $fecha = now()->addDays(2)->toDateString();
        $disponibilidad = Disponibilidad::create([
            'id_profesor' => $profesor->id_profesor,
            'fecha' => $fecha,
            'hora_inicio' => '09:00',
            'hora_fin' => '10:00',
            'modalidad' => 'Presencial',
            'estado' => 'Disponible',
        ]);

        $this->actingAs($usuario)
            ->postJson(route('profesor.api.disponibilidades.store'), [
                'fecha' => $fecha,
                'hora_inicio' => '09:30',
                'hora_fin' => '10:30',
                'modalidad' => 'Presencial',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('hora_inicio');

        $this->actingAs($otroProfesor->usuario()->firstOrFail())
            ->getJson(route('profesor.api.disponibilidades.show', $disponibilidad->id_disponibilidad))
            ->assertNotFound();

        $this->assertDatabaseCount('disponibilidad', 1);
    }

    public function test_professor_cannot_update_or_delete_availability_with_an_associated_appointment(): void
    {
        ['usuario' => $usuario, 'profesor' => $profesor] = $this->createProfessor('profesor@example.com');
        $alumnoUsuario = Usuario::create([
            'correo' => 'alumno@example.com',
            'contrasena_hash' => Hash::make('password'),
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
        $aula = Aula::create([
            'nombre_cubiculo' => 'C-01',
            'capacidad' => '4',
            'tipo' => 'Presencial',
        ]);
        $materia = Materia::create([
            'nombre_materia' => 'Matemáticas',
            'descripcion' => 'Matemáticas',
            'clave' => 'MAT-01',
            'area' => 'Ciencias',
        ]);
        $disponibilidad = Disponibilidad::create([
            'id_profesor' => $profesor->id_profesor,
            'fecha' => now()->addDays(2)->toDateString(),
            'hora_inicio' => '09:00',
            'hora_fin' => '10:00',
            'modalidad' => 'Presencial',
            'estado' => 'Ocupado',
        ]);
        Asesoria::create([
            'id_alumno' => $alumno->id_alumno,
            'id_disponibilidad' => $disponibilidad->id_disponibilidad,
            'id_materia' => $materia->id_materia,
            'id_aula' => $aula->id_aula,
            'fecha_registro' => now(),
            'estado' => 'Confirmada',
        ]);

        $this->actingAs($usuario)
            ->patchJson(route('profesor.api.disponibilidades.update', $disponibilidad->id_disponibilidad), [
                'modalidad' => 'Virtual',
            ])
            ->assertStatus(409);

        $this->deleteJson(route('profesor.api.disponibilidades.destroy', $disponibilidad->id_disponibilidad))
            ->assertStatus(409);

        $this->assertDatabaseHas('disponibilidad', [
            'id_disponibilidad' => $disponibilidad->id_disponibilidad,
        ]);
        $this->assertDatabaseHas('asesoria', [
            'id_disponibilidad' => $disponibilidad->id_disponibilidad,
        ]);
    }

    public function test_only_professors_can_access_their_availabilities(): void
    {
        $this->getJson(route('profesor.api.disponibilidades.index'))
            ->assertUnauthorized();

        $coordinador = Usuario::create([
            'correo' => 'coordinador@example.com',
            'contrasena_hash' => Hash::make('password'),
            'rol' => 'Coordinador',
            'estado' => 'Activo',
        ]);

        $this->actingAs($coordinador)
            ->getJson(route('profesor.api.disponibilidades.index'))
            ->assertForbidden();
    }

    /**
     * @return array{usuario: Usuario, profesor: Profesor}
     */
    private function createProfessor(string $email): array
    {
        $usuario = Usuario::create([
            'correo' => $email,
            'contrasena_hash' => Hash::make('password'),
            'rol' => 'Profesor',
            'estado' => 'Activo',
        ]);
        $profesor = Profesor::create([
            'id_usuario' => $usuario->id_usuario,
            'num_empleado' => strtoupper(substr(md5($email), 0, 8)),
            'nombre' => 'Ana',
            'apellidos' => 'López',
            'departamento' => 'Ciencias',
            'especialidad' => 'Matemáticas',
        ]);

        return [
            'usuario' => $usuario,
            'profesor' => $profesor,
        ];
    }
}
