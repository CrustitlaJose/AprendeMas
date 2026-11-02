<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuario', function (Blueprint $table) {
            $table->id('id_usuario');
            $table->string('correo')->unique();
            $table->string('contrasena_hash');
            $table->string('rol'); // Alumno, Profesor, Coordinador
            $table->string('estado')->default('Activo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuario');
    }
};
