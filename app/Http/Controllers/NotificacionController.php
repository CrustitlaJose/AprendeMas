<?php

namespace App\Http\Controllers;

use App\Models\Notificacion;
use App\Models\Usuario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificacionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $usuario = $request->user();

        if (! $usuario instanceof Usuario) {
            abort(403);
        }

        $notificaciones = $usuario->notificaciones;

        return response()->json([
            'notificaciones' => $notificaciones->map(fn (Notificacion $notificacion): array => [
                'id' => $notificacion->id_notificacion,
                'tipo' => $notificacion->tipo,
                'mensaje' => $notificacion->mensaje,
                'fecha_relativa' => $notificacion->fecha_envio->diffForHumans(),
                'leida' => $notificacion->leida,
            ])->values(),
            'sin_leer' => $notificaciones->where('leida', false)->count(),
        ]);
    }

    public function markAsRead(Request $request): JsonResponse
    {
        $usuario = $request->user();

        if (! $usuario instanceof Usuario) {
            abort(403);
        }

        $usuario->notificaciones()
            ->where('leida', false)
            ->update(['leida' => true]);

        return response()->json([
            'message' => 'Notificaciones marcadas como leídas.',
        ]);
    }
}
