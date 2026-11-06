<?php

namespace Tests\Feature;

use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_coordinator_login_redirects_to_coordinator_page(): void
    {
        $usuario = Usuario::create([
            'correo' => 'coordinador@example.com',
            'contrasena_hash' => Hash::make('password'),
            'rol' => 'Coordinador',
            'estado' => 'Activo',
        ]);

        $response = $this->post('/login', [
            'identificador' => 'coordinador@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('coordinador'));
        $this->assertAuthenticatedAs($usuario);
        $this->get(route('coordinador'))->assertOk();
    }
}
