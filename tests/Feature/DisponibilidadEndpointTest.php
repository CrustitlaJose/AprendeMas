<?php

namespace Tests\Feature;

use App\Models\Disponibilidad;
use App\Models\Materia;
use App\Models\Profesor;
use App\Models\ProfesorMateria;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DisponibilidadEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_gets_professor_cards_with_linked_subjects_and_future_available_slots(): void
    {
        $this->travelTo(Carbon::parse('2026-01-01 08:00:00'));

        $alumno = Usuario::create([
            'correo' => 'alumno@example.com',
            'contrasena_hash' => bcrypt('password'),
            'rol' => 'Alumno',
            'estado' => 'Activo',
        ]);
        $usuarioProfesor = Usuario::create([
            'correo' => 'profesor@example.com',
            'contrasena_hash' => bcrypt('password'),
            'rol' => 'Profesor',
            'estado' => 'Activo',
        ]);
        $profesor = Profesor::create([
            'id_usuario' => $usuarioProfesor->id_usuario,
            'num_empleado' => 'P001',
            'nombre' => 'Ana',
            'apellidos' => 'López',
            'departamento' => 'Ciencias',
            'especialidad' => 'Matemáticas',
        ]);
        $materiaIngles = Materia::create([
            'nombre_materia' => 'Inglés',
            'descripcion' => 'Inglés',
            'clave' => 'ING-01',
            'area' => 'Idiomas',
        ]);
        $materiaMatematicas = Materia::create([
            'nombre_materia' => 'Matemáticas',
            'descripcion' => 'Matemáticas',
            'clave' => 'MAT-01',
            'area' => 'Ciencias',
        ]);
        ProfesorMateria::create([
            'id_profesor' => $profesor->id_profesor,
            'id_materia' => $materiaIngles->id_materia,
        ]);
        ProfesorMateria::create([
            'id_profesor' => $profesor->id_profesor,
            'id_materia' => $materiaMatematicas->id_materia,
        ]);
        $disponibilidadFutura = Disponibilidad::create([
            'id_profesor' => $profesor->id_profesor,
            'fecha' => '2026-01-01',
            'hora_inicio' => '09:00:00',
            'hora_fin' => '10:00:00',
            'modalidad' => 'Presencial',
            'estado' => 'Disponible',
        ]);
        Disponibilidad::create([
            'id_profesor' => $profesor->id_profesor,
            'fecha' => '2026-01-01',
            'hora_inicio' => '07:00:00',
            'hora_fin' => '08:00:00',
            'modalidad' => 'Presencial',
            'estado' => 'Disponible',
        ]);
        Disponibilidad::create([
            'id_profesor' => $profesor->id_profesor,
            'fecha' => '2026-01-02',
            'hora_inicio' => '09:00:00',
            'hora_fin' => '10:00:00',
            'modalidad' => 'Presencial',
            'estado' => 'Ocupado',
        ]);

        $response = $this->actingAs($alumno)->getJson(route('disponibilidades.index'));

        $response->assertOk()
            ->assertJsonPath('data.0.titulo', 'Ana López')
            ->assertJsonPath('data.0.subtitulo', 'Inglés, Matemáticas')
            ->assertJsonPath('data.0.materias.0.id_materia', $materiaIngles->id_materia)
            ->assertJsonPath('data.0.materias.1.id_materia', $materiaMatematicas->id_materia)
            ->assertJsonPath(
                'data.0.disponibilidades.0.id_disponibilidad',
                $disponibilidadFutura->id_disponibilidad
            );
        $this->assertCount(1, $response->json('data.0.disponibilidades'));
    }

    public function test_student_sees_only_availability_search_on_the_disponibilidades_page(): void
    {
        $alumno = Usuario::create([
            'correo' => 'alumno@example.com',
            'contrasena_hash' => bcrypt('password'),
            'rol' => 'Alumno',
            'estado' => 'Activo',
        ]);

        $this->actingAs($alumno)
            ->get(route('disponibilidades.index'))
            ->assertOk()
            ->assertViewIs('Alumno.agendar')
            ->assertSee('data-offer-panel', false)
            ->assertDontSee('data-appointments-panel', false)
            ->assertDontSee('Seguimiento');
    }

    public function test_student_sees_only_appointments_on_the_mis_asesorias_page(): void
    {
        $alumno = Usuario::create([
            'correo' => 'alumno@example.com',
            'contrasena_hash' => bcrypt('password'),
            'rol' => 'Alumno',
            'estado' => 'Activo',
        ]);

        $this->actingAs($alumno)
            ->get(route('mis-asesorias'))
            ->assertOk()
            ->assertSee('data-appointments-panel', false)
            ->assertDontSee('data-offer-panel', false);
    }

    public function test_guest_cannot_query_disponibilidades(): void
    {
        $this->getJson(route('disponibilidades.index'))->assertStatus(401);
    }

    public function test_non_student_cannot_query_disponibilidades(): void
    {
        $coordinador = Usuario::create([
            'correo' => 'coordinador@example.com',
            'contrasena_hash' => bcrypt('password'),
            'rol' => 'Coordinador',
            'estado' => 'Activo',
        ]);

        $this->actingAs($coordinador)
            ->getJson(route('disponibilidades.index'))
            ->assertForbidden();
    }
}
