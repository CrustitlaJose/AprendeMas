<?php

namespace App\Listeners;

use App\Events\CitaCancelada;
use App\Events\DisponibilidadEliminada;
use App\Models\Notificacion;

class RegistrarAuditoriaCancelacion
{
    public function handle(CitaCancelada $event): void
    {
        $asesoria = $event->asesoria->loadMissing(['alumno', 'disponibilidad.profesor', 'materia']);
        $mensaje = "La asesoría de {$asesoria->materia->nombre_materia} fue cancelada.";

        foreach ([
            $asesoria->alumno->id_usuario,
            $asesoria->disponibilidad->profesor->id_usuario,
        ] as $idUsuario) {
            $notificacion = new Notificacion;
            $notificacion->id_asesoria = $asesoria->id_asesoria;
            $notificacion->id_usuario = $idUsuario;
            $notificacion->mensaje = $mensaje;
            $notificacion->fecha_envio = now();
            $notificacion->tipo = 'Cancelación';
            $notificacion->save();
        }
    }

    public function __invoke(DisponibilidadEliminada $event): void
    {
        $disponibilidad = $event->disponibilidad->loadMissing('profesor');
        $asesoria = $event->asesoria?->loadMissing(['alumno', 'materia']);
        $mensaje = $asesoria === null
            ? "La disponibilidad del {$disponibilidad->profesor->nombre} para el {$disponibilidad->fecha} de {$disponibilidad->hora_inicio} a {$disponibilidad->hora_fin} fue eliminada."
            : "La asesoría de {$asesoria->materia->nombre_materia} fue cancelada porque se eliminó la disponibilidad.";
        $destinatarios = $asesoria === null
            ? [$disponibilidad->profesor->id_usuario]
            : [$asesoria->alumno->id_usuario, $disponibilidad->profesor->id_usuario];

        foreach ($destinatarios as $idUsuario) {
            $notificacion = new Notificacion;
            $notificacion->id_asesoria = $asesoria?->id_asesoria;
            $notificacion->id_usuario = $idUsuario;
            $notificacion->mensaje = $mensaje;
            $notificacion->fecha_envio = now();
            $notificacion->tipo = 'Eliminación de disponibilidad';
            $notificacion->save();
        }
    }
}
