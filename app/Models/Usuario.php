<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    use Notifiable;

    protected $table = 'usuario';
    protected $primaryKey = 'id_usuario';
    public $timestamps = false;

    protected $fillable = [
        'correo',
        'contrasena_hash',
        'rol',
        'estado'
    ];

    public function getAuthPasswordName()
    {
        return 'contrasena_hash';
    }

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