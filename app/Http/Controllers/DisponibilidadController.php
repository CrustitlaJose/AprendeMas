<?php

namespace App\Http\Controllers;

use App\Models\Disponibilidad;
use App\Models\Materia;
use App\Models\Profesor;
use App\Models\ProfesorMateria;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DisponibilidadController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user()?->rol === 'Alumno', 403);

        $now = now();
        $availableSlots = static function (Builder $query) use ($now): void {
            $query->where('estado', 'Disponible')
                ->where(function (Builder $query) use ($now): void {
                    $query->where('fecha', '>', $now->toDateString())
                        ->orWhere(function (Builder $query) use ($now): void {
                            $query->whereDate('fecha', $now->toDateString())
                                ->where('hora_inicio', '>', $now->format('H:i:s'));
                        });
                });
        };

        $profesores = Profesor::query()
            ->select(['id_profesor', 'nombre', 'apellidos'])
            ->whereHas('disponibilidad', $availableSlots)
            ->whereHas('profesor_materia.materia')
            ->with([
                'profesor_materia.materia:id_materia,nombre_materia',
                'disponibilidad' => static function (Builder $query) use ($availableSlots): void {
                    $query->select([
                        'id_disponibilidad',
                        'id_profesor',
                        'fecha',
                        'hora_inicio',
                        'hora_fin',
                        'modalidad',
                    ]);
                    $availableSlots($query);
                },
            ])
            ->orderBy('nombre')
            ->orderBy('apellidos')
            ->get();

        $data = $profesores->map(static function (Profesor $profesor): array {
            $materias = $profesor->profesor_materia
                ->map(static fn (ProfesorMateria $asignacion): ?Materia => $asignacion->materia)
                ->filter()
                ->sortBy('nombre_materia')
                ->map(static fn (Materia $materia): array => [
                    'id_materia' => $materia->id_materia,
                    'nombre_materia' => $materia->nombre_materia,
                ])
                ->values();

            return [
                'id_profesor' => $profesor->id_profesor,
                'titulo' => trim($profesor->nombre.' '.$profesor->apellidos),
                'subtitulo' => $materias->pluck('nombre_materia')->implode(', '),
                'materias' => $materias,
                'disponibilidades' => $profesor->disponibilidad
                    ->map(static fn (Disponibilidad $disponibilidad): array => [
                        'id_disponibilidad' => $disponibilidad->id_disponibilidad,
                        'fecha' => $disponibilidad->fecha,
                        'hora_inicio' => $disponibilidad->hora_inicio,
                        'hora_fin' => $disponibilidad->hora_fin,
                        'modalidad' => $disponibilidad->modalidad,
                    ])
                    ->values(),
            ];
        })->values();

        return response()->json(['data' => $data]);
    }
}
