<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asesoria', function (Blueprint $table) {
            $table->id('id_asesoria');
            $table->foreignId('id_alumno')->constrained('alumno', 'id_alumno')->onDelete('cascade');
            $table->foreignId('id_disponibilidad')->constrained('disponibilidad', 'id_disponibilidad')->onDelete('cascade');
            $table->foreignId('id_materia')->constrained('materia', 'id_materia')->onDelete('cascade');
            $table->foreignId('id_aula')->constrained('aula', 'id_aula')->onDelete('cascade');
            $table->dateTime('fecha_registro');
            $table->string('estado')->default('Confirmada');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asesoria');
    }
};
