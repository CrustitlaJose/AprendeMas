<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            MateriaSeeder::class,
            AulaSeeder::class,
            CoordinadorSeeder::class,
            // UsuarioSeeder::class,
        ]);
    }
}
