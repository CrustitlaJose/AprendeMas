<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coordinador extends Model
{
    protected $table = 'coordinador';
    protected $primaryKey = 'id_coordinador';
    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'apellidos',
        'nombre'
    ];

    public function usuario () {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }
}
