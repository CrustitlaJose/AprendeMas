<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Materia extends Model
{
    protected $table = 'materia';
    protected $primaryKey = 'id_materia';
    public $timestamps = false;

    protected $fillable = [
        'nombre_materia',
        'descripcion',
        'clave',
        'area'
    ];
    
    public function profesor_materia(){
        return $this->hasMany(ProfesorMateria::class, 'id_materia', 'id_materia');
    }
}
