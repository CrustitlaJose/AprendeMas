<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ProfesorMateria extends Pivot
{
    protected $table = 'profesor_materia';
    public $timestamps = false;

    protected $fillable = [
        'id_profesor',
        'id_materia',
    ];

    public function __construct(array $attributes = []){
        parent::__construct($attributes);

        $this->setPivotKeys('id_profesor', 'id_materia');
    }

    public function profesor(){
        return $this->belongsTo(Profesor::class, 'id_profesor', 'id_profesor');
    }

    public function materia(){
        return $this->belongsTo(Materia::class, 'id_materia', 'id_materia');
    }
}
