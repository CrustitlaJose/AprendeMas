<?php

namespace App\Events;

use App\Models\Asesoria;
use App\Models\Disponibilidad;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DisponibilidadEliminada
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Disponibilidad $disponibilidad,
        public readonly ?Asesoria $asesoria = null
    ) {}
}
