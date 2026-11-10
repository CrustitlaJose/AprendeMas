<?php

namespace Tests\Feature;

use App\Models\Disponibilidad;
use App\Models\Profesor;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CoordinadorDisponibilidadControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_coordinator_can_list_all_availabilities_with_professor_details(): void
    {
        $coordinador = Usuario::create([
            'correo' => 'coordinador@example.com',
            'contrasena_hash' => Hash::make('password'),
            'rol' => 'Coordinador',
            'estado' => 'Activo',
        ]);
        $profesorTarde = $this->createProfessor('tarde@example.com', 'P002', 'Bruno');
        $profesorTemprano = $this->createProfessor('temprano@example.com', 'P001', 'Ana');
        $fecha = now()->addDays(2)->toDateString();
        $disponibilidadTarde = $this->createDisponibilidad($profesorTarde, $fecha, '11:00:00', '12:00:00');
        $disponibilidadTemprano = $this->createDisponibilidad($profesorTemprano, $fecha, '09:00:00', '10:00:00');

        $response = $this->actingAs($coordinador)
            ->getJson(route('coordinador.disponibilidades.index'));

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.id_disponibilidad', $disponibilidadTemprano->id_disponibilidad)
            ->assertJsonPath('data.0.profesor.nombre', 'Ana')
            ->assertJsonPath('data.1.id_disponibilidad', $disponibilidadTarde->id_disponibilidad)
            ->assertJsonPath('data.1.profesor.nombre', 'Bruno');
    }

    public function test_guests_cannot_list_coordinator_availabilities(): void
    {
        $this->getJson(route('coordinador.disponibilidades.index'))
            ->assertUnauthorized();
    }

    public function test_coordinator_can_open_the_dedicated_availability_view(): void
    {
        $coordinador = Usuario::create([
            'correo' => 'coordinador@example.com',
            'contrasena_hash' => Hash::make('password'),
            'rol' => 'Coordinador',
            'estado' => 'Activo',
        ]);
        $profesor = $this->createProfessor('ana@example.com', 'P001', 'Ana');
        $fecha = now()->addDays(2)->toDateString();
        $this->createDisponibilidad($profesor, $fecha, '09:00:00', '10:00:00');

        $this->actingAs($coordinador)
            ->get(route('coordinador.disponibilidades.index'))
            ->assertOk()
            ->assertViewIs('Coordinador.disponibilidades')
            ->assertSee('Supervisión de disponibilidades')
            ->assertSee('Ana Pérez')
            ->assertDontSee('data-record-panel', false)
            ->assertDontSee('id_profesor');
    }

    public function test_non_coordinators_cannot_list_coordinator_availabilities(): void
    {
        $alumno = Usuario::create([
            'correo' => 'alumno@example.com',
            'contrasena_hash' => Hash::make('password'),
            'rol' => 'Alumno',
            'estado' => 'Activo',
        ]);

        $this->actingAs($alumno)
            ->getJson(route('coordinador.disponibilidades.index'))
            ->assertForbidden();
    }

    private function createProfessor(string $email, string $employeeNumber, string $name): Profesor
    {
        $usuario = Usuario::create([
            'correo' => $email,
            'contrasena_hash' => Hash::make('password'),
            'rol' => 'Profesor',
            'estado' => 'Activo',
        ]);

        return Profesor::create([
            'id_usuario' => $usuario->id_usuario,
            'num_empleado' => $employeeNumber,
            'nombre' => $name,
            'apellidos' => 'Pérez',
            'departamento' => 'Ciencias',
            'especialidad' => 'Matemáticas',
        ]);
    }

    private function createDisponibilidad(
        Profesor $profesor,
        string $fecha,
        string $horaInicio,
        string $horaFin
    ): Disponibilidad {
        return Disponibilidad::create([
            'id_profesor' => $profesor->id_profesor,
            'fecha' => $fecha,
            'hora_inicio' => $horaInicio,
            'hora_fin' => $horaFin,
            'modalidad' => 'Presencial',
            'estado' => 'Disponible',
        ]);
    }
}
