<?php

namespace Tests\Feature;

use App\Models\Usuario;
use Tests\TestCase;

class LoginPageTest extends TestCase
{
    public function test_login_page_is_rendered(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertViewIs('login');
    }

    public function test_authenticated_student_is_redirected_from_login_page_to_home(): void
    {
        $usuario = Usuario::make(['rol' => 'Alumno']);

        $this->actingAs($usuario)
            ->get('/login')
            ->assertRedirect(route('home'));
    }

    public function test_authenticated_student_cannot_submit_login_form(): void
    {
        $usuario = Usuario::make(['rol' => 'Alumno']);

        $this->actingAs($usuario)
            ->post('/login', [])
            ->assertRedirect(route('home'));
    }

    public function test_home_redirects_authenticated_student_to_student_page(): void
    {
        $usuario = Usuario::make(['rol' => 'Alumno']);

        $this->actingAs($usuario)
            ->get(route('home'))
            ->assertRedirect(route('mis-asesorias'));
    }

    public function test_home_redirects_authenticated_coordinator_to_coordinator_page(): void
    {
        $usuario = Usuario::make(['rol' => 'Coordinador']);

        $this->actingAs($usuario)
            ->get(route('home'))
            ->assertRedirect(route('coordinador'));
    }

    public function test_home_redirects_authenticated_professor_to_availability_management(): void
    {
        $usuario = Usuario::make(['rol' => 'Profesor']);

        $this->actingAs($usuario)
            ->get(route('home'))
            ->assertRedirect(route('profesor.disponibilidades.index'));
    }

    public function test_professor_agenda_link_redirects_to_availability_management(): void
    {
        $usuario = Usuario::make(['rol' => 'Profesor']);

        $this->actingAs($usuario)
            ->get(route('mis-asesorias'))
            ->assertRedirect(route('profesor.disponibilidades.index'));
    }
}
