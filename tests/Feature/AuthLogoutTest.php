<?php

namespace Tests\Feature;

use App\Models\Usuario;
use Tests\TestCase;

class AuthLogoutTest extends TestCase
{
    public function test_logout_redirects_authenticated_user_to_login_and_ends_session(): void
    {
        $usuario = Usuario::make(['rol' => 'Alumno']);

        $response = $this->actingAs($usuario)->post(route('logout'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_guest_cannot_access_logout_endpoint(): void
    {
        $this->post(route('logout'))
            ->assertRedirect(route('login'));
    }
}
