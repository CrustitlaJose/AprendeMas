<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Asesoria extends Model
{
    protected $table = 'asesoria';

    protected $primaryKey = 'id_asesoria';

    public $timestamps = false;

    protected $fillable = [
        'id_alumno',
        'id_disponibilidad',
        'id_materia',
        'id_aula',
        'fecha_registro',
        'estado',
    ];

    public function alumno()
    {
        return $this->belongsTo(Alumno::class, 'id_alumno', 'id_alumno');
    }

    public function aula()
    {
        return $this->belongsTo(Aula::class, 'id_aula', 'id_aula');
    }

    public function disponibilidad()
    {
        return $this->belongsTo(Disponibilidad::class, 'id_disponibilidad', 'id_disponibilidad');
    }

    public function materia()
    {
        return $this->belongsTo(Materia::class, 'id_materia', 'id_materia');
    }

    public function notificaciones()
    {
        return $this->hasMany(Notificacion::class, 'id_asesoria', 'id_asesoria');
    }

    public function cancelacion()
    {
        return $this->hasOne(Cancelacion::class);
    }
}
