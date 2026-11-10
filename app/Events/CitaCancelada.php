<?php

namespace App\Events;

use App\Models\Asesoria;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CitaCancelada
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly Asesoria $asesoria) {}
}
