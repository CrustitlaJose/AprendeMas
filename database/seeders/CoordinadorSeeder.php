<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Usuario;
use App\Models\Coordinador;
use Illuminate\Support\Facades\Hash;

class CoordinadorSeeder extends Seeder
{
    public function run(): void
    {
        $usuario = Usuario::create([
            'correo' => 'admin@aprendemas.com',
            'contrasena_hash' => Hash::make('admin123'),
            'rol' => 'Coordinador',
            'estado' => 'Activo'
        ]);

        Coordinador::create([
            'id_usuario' => $usuario->id_usuario,
            'nombre' => 'Coordinación',
            'apellidos' => 'Académica'
        ]);
    }
}