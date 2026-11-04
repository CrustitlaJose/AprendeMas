<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Materia;

class MateriaSeeder extends Seeder
{
    public function run(): void
    {
        $materias = [
            [
                'nombre_materia' => 'Matemáticas',
                'descripcion' => 'Asesoría académica de matemáticas y cálculo',
                'clave' => 'MAT-001',
                'area' => 'Ciencias Básicas'
            ],
            [
                'nombre_materia' => 'Programación',
                'descripcion' => 'Asesoría en lógica de programación y algoritmos',
                'clave' => 'PROG-101',
                'area' => 'Ingeniería de Software'
            ],
            [
                'nombre_materia' => 'Inglés',
                'descripcion' => 'Asesoría de inglés conversacional y gramática',
                'clave' => 'ING-050',
                'area' => 'Idiomas'
            ]
        ];

        Materia::insert($materias);
    }
}