<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cancelacion extends Model
{
    protected $table = 'cancelaciones';

    protected $primaryKey = 'asesoria_id';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'asesoria_id',
        'motivo',
        'fecha_hora_cancelacion',
        'cancelado_por',
        'observaciones',
    ];

    protected $casts = [
        'fecha_hora_cancelacion' => 'datetime',
    ];

    public function asesoria()
    {
        return $this->belongsTo(Asesoria::class, 'asesoria_id', 'id_asesoria');
    }
}
