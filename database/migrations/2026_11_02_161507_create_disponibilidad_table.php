<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disponibilidad', function (Blueprint $table) {
            $table->id('id_disponibilidad');
            $table->foreignId('id_profesor')->constrained('profesor', 'id_profesor')->onDelete('cascade');
            $table->date('fecha');
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->string('modalidad');
            $table->string('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disponibilidad');
    }
};
