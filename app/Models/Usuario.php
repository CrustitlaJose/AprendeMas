<?php

namespace App\Models;

use Dom\ProcessingInstruction;
use Illuminate\Database\Eloquent\Model;

class Usuario extends Model
{
    protected $table = 'usuario';
    protected $primaryKey = 'id_usuario';
    public $timestamps = false;

    protected $fillable = [
        'correo',
        'contrasena_hash',
        'rol',
        'estado'
    ];

    public function profesor(){
        return $this->hasMany(Profesor::class, 'id_usuario', 'id_usuario');
    }

    public function alumno(){
        return $this->hasMany(Alumno::class, 'id_usuario', 'id_usuario');
    }

    public function coordinador(){
        return $this->hasMany(Coordinador::class, 'id_usuario', 'id_usuario');
    }
}
