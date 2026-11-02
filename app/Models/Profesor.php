<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Profesor extends Model
{
    protected $table = 'profesor';
    protected $primaryKey = 'id_profesor';
    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'num_empleado',
        'nombre',
        'apellidos',
        'departamento'
    ];

    public function usuario(){
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    public function profesor_materia(){
        return $this->hasMany(ProfesorMateria::class, 'id_profesor', 'id_profesor');
    }

    public function disponibilidad(){
        return $this->hasMany(Disponibilidad::class, 'id_profesor', 'id_profesor');
    }
}
