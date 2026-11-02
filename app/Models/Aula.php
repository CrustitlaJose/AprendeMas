<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Aula extends Model
{
    protected $table = 'aula';
    protected $primaryKey = 'id_aula';
    public $timestamps = false; 

    protected $fillable = [
        'nombre_cubiculo',
        'capacidad',
        'tipo'
    ];

    public function asesorias()
    {
        return $this->hasMany(Asesoria::class, 'id_aula', 'id_aula');
    }
}