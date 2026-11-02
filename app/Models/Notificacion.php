<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notificacion extends Model
{
    protected $table = 'notificacion';

    protected $primaryKey = 'id_notificacion';

    public $timestamps = false;

    protected $fillable = [
        'id_asesoria',
        'id_usuario',
        'mensaje',
        'fecha_envio',
        'tipo',
    ];

    public function asesoria()
    {
        return $this->belongsTo(Asesoria::class, 'id_asesoria', 'id_asesoria');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }
}
