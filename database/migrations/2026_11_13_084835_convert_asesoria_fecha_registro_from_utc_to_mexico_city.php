<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement(
            "UPDATE asesoria SET fecha_registro = (fecha_registro AT TIME ZONE 'UTC') AT TIME ZONE 'America/Mexico_City'"
        );
    }

    public function down(): void
    {
        DB::statement(
            "UPDATE asesoria SET fecha_registro = (fecha_registro AT TIME ZONE 'America/Mexico_City') AT TIME ZONE 'UTC'"
        );
    }
};
