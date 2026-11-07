<?php

namespace Database\Seeders;

use App\Models\Coordinador;
use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class CoordinadorSeeder extends Seeder
{
    public function run(): void
    {
        $correo = config('services.coordinador.email');
        $contrasena = config('services.coordinador.password');

        if (! is_string($correo) || $correo === '' || ! is_string($contrasena) || $contrasena === '') {
            throw new RuntimeException(
                'Define COORDINATOR_EMAIL y COORDINATOR_PASSWORD antes de ejecutar CoordinadorSeeder.'
            );
        }

        $usuario = Usuario::firstOrCreate(
            ['correo' => $correo],
            [
                'contrasena_hash' => Hash::make($contrasena),
                'rol' => 'Coordinador',
                'estado' => 'Activo',
            ]
        );

        if ($usuario->rol !== 'Coordinador') {
            throw new RuntimeException('El correo configurado ya pertenece a un usuario que no es coordinador.');
        }

        Coordinador::firstOrCreate(
            ['id_usuario' => $usuario->id_usuario],
            [
                'nombre' => 'Coordinación',
                'apellidos' => 'Académica',
            ]
        );
    }
}
