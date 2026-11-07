<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Disponibilidad extends Model
{
    protected $table = 'disponibilidad';

    protected $primaryKey = 'id_disponibilidad';

    public $timestamps = false;

    protected $fillable = [
        'id_profesor',
        'fecha',
        'hora_inicio',
        'hora_fin',
        'modalidad',
        'estado',
    ];

    public function profesor()
    {
        return $this->belongsTo(Profesor::class, 'id_profesor', 'id_profesor');
    }

    public function asesorias()
    {
        return $this->hasMany(Asesoria::class, 'id_disponibilidad', 'id_disponibilidad');
    }
}
