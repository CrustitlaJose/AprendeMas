<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alumno extends Model
{
    // Declaracion de la tabla y llave primaria
    protected $table = 'alumno';
    protected $primaryKey = 'id_alumno';
    public $timestamps = false;

    // Definir los atributos modificables de la tabla (Datos de entrada)
    protected $fillable = [
        'id_usuario',
        'matricula',
        'nombre',
        'apellidos',
        'telefono'
    ];

    // Definir relaciones
    public function usuario(){
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    public function asesoria(){
        return $this->hasMany(Asesoria::class, 'id_alumno', 'id_alumno');
    }

}
