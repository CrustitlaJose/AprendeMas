<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cancelaciones', function (Blueprint $table) {

            $table->unsignedBigInteger('asesoria_id')->primary();
            $table->foreign('asesoria_id')->references('id')->on('asesorias')->onDelete('cascade');
            $table->text('motivo');
            $table->timestamp('fecha_hora_cancelacion');
            $table->enum('cancelado_por', ['alumno', 'profesor', 'coordinador']);
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cancelaciones');
    }
};
