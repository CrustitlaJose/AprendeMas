<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            MateriaSeeder::class,
            CoordinadorSeeder::class,
            // AulaSeeder::class,
            // UsuarioSeeder::class,
        ]);
    }
}
