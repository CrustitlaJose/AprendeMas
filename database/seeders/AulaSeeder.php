<?php

namespace Database\Seeders;

use App\Models\Aula;
use Illuminate\Database\Seeder;

class AulaSeeder extends Seeder
{
    public function run(): void
    {
        $aulas = [
            ['nombre_cubiculo' => 'C-01', 'capacidad' => '4', 'tipo' => 'Presencial'],
            ['nombre_cubiculo' => 'C-02', 'capacidad' => '4', 'tipo' => 'Presencial'],
            ['nombre_cubiculo' => 'C-03', 'capacidad' => '4', 'tipo' => 'Presencial'],
            ['nombre_cubiculo' => 'V-01', 'capacidad' => '4', 'tipo' => 'Virtual'],
            ['nombre_cubiculo' => 'V-02', 'capacidad' => '4', 'tipo' => 'Virtual'],
            ['nombre_cubiculo' => 'V-03', 'capacidad' => '4', 'tipo' => 'Virtual'],
        ];

        foreach ($aulas as $aula) {
            Aula::updateOrCreate(
                ['nombre_cubiculo' => $aula['nombre_cubiculo']],
                ['capacidad' => $aula['capacidad'], 'tipo' => $aula['tipo']],
            );
        }
    }
}
