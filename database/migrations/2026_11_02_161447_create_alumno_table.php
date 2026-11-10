<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Definición del esquema de la tabla
    public function up(): void
    {
        Schema::create('alumno', function (Blueprint $table) {
            $table->id('id_alumno');
            $table->foreignId('id_usuario')->constrained('usuario','id_usuario')->onDelete('cascade');
            $table->string('matricula')->unique();
            $table->string('nombre');
            $table->string('apellidos');
            $table->string('telefono')->unique();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alumno');
    }
};
