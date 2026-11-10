<?php

namespace App\Listeners;

use App\Events\CitaAgendada;
use App\Models\Notificacion;

class RegistrarNotificacionAgendamiento
{
    public function handle(CitaAgendada $event): void
    {
        $asesoria = $event->asesoria->loadMissing(['alumno', 'disponibilidad.profesor', 'materia']);
        $mensaje = "La asesoría de {$asesoria->materia->nombre_materia} fue agendada.";

        foreach ([
            $asesoria->alumno->id_usuario,
            $asesoria->disponibilidad->profesor->id_usuario,
        ] as $idUsuario) {
            $notificacion = new Notificacion;
            $notificacion->id_asesoria = $asesoria->id_asesoria;
            $notificacion->id_usuario = $idUsuario;
            $notificacion->mensaje = $mensaje;
            $notificacion->fecha_envio = now();
            $notificacion->tipo = 'Agendamiento';
            $notificacion->save();
        }
    }
}
