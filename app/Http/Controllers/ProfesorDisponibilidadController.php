<?php

namespace App\Http\Controllers;

use App\Events\DisponibilidadEliminada;
use App\Models\Disponibilidad;
use App\Models\Profesor;
use App\Models\Usuario;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProfesorDisponibilidadController extends Controller
{
    public function calendar(Request $request): View
    {
        $validated = $request->validate([
            'semana' => ['sometimes', 'date_format:Y-m-d'],
        ]);
        $weekStart = CarbonImmutable::parse($validated['semana'] ?? today()->toDateString())
            ->startOfWeek(CarbonImmutable::MONDAY);
        $weekEnd = $weekStart->addDays(5);
        $profesor = $this->profesorFrom($request);
        $disponibilidades = $profesor->disponibilidad()
            ->whereBetween('fecha', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->with(['asesorias' => static function (Builder|Relation $query): void {
                $query->where('estado', 'Confirmada')
                    ->with(['alumno.usuario', 'materia', 'aula']);
            }])
            ->orderBy('fecha')
            ->orderBy('hora_inicio')
            ->get();

        $nombresDias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
        $weekDays = [];

        foreach ($nombresDias as $offset => $nombreDia) {
            $fecha = $weekStart->addDays($offset);
            $weekDays[] = [
                'fecha' => $fecha->toDateString(),
                'nombre' => $nombreDia,
                'fecha_corta' => $fecha->format('d/m'),
            ];
        }

        $calendarBlocks = $disponibilidades->map(static function (Disponibilidad $disponibilidad): array {
            $asesoria = $disponibilidad->asesorias->first();
            $alumno = $asesoria?->alumno;
            $usuario = $alumno?->usuario;
            $materia = $asesoria?->materia;
            $aula = $asesoria?->aula;
            $horaInicio = substr((string) $disponibilidad->hora_inicio, 0, 5);
            $horaFin = substr((string) $disponibilidad->hora_fin, 0, 5);
            $horaFinEntera = (int) substr($horaFin, 0, 2) + ((int) substr($horaFin, 3, 2) > 0 ? 1 : 0);

            return [
                'id' => (int) $disponibilidad->id_disponibilidad,
                'fecha' => (string) $disponibilidad->fecha,
                'hora_inicio' => $horaInicio,
                'hora_fin' => $horaFin,
                'hora_inicio_entera' => (int) substr($horaInicio, 0, 2),
                'hora_fin_entera' => $horaFinEntera,
                'modalidad' => $disponibilidad->modalidad,
                'estado' => $disponibilidad->estado,
                'ocupada' => $asesoria !== null || $disponibilidad->estado !== 'Disponible',
                'asesoria' => $asesoria === null ? null : [
                    'id' => (int) $asesoria->id_asesoria,
                    'id_alumno' => (int) $asesoria->id_alumno,
                    'id_disponibilidad' => (int) $asesoria->id_disponibilidad,
                    'id_materia' => (int) $asesoria->id_materia,
                    'id_aula' => (int) $asesoria->id_aula,
                    'fecha_registro' => Carbon::parse($asesoria->fecha_registro, 'America/Mexico_City')
                        ->setTimezone('America/Mexico_City')
                        ->format('d/m/Y h:i A'),
                    'estado' => $asesoria->estado,
                    'alumno' => [
                        'id' => $alumno?->id_alumno,
                        'id_usuario' => $usuario?->id_usuario,
                        'nombre' => $alumno?->nombre,
                        'apellidos' => $alumno?->apellidos,
                        'nombre_completo' => trim(($alumno?->nombre ?? '').' '.($alumno?->apellidos ?? '')),
                        'matricula' => $alumno?->matricula,
                        'telefono' => $alumno?->telefono,
                        'correo' => $usuario?->correo,
                        'rol' => $usuario?->rol,
                        'estado_usuario' => $usuario?->estado,
                    ],
                    'materia' => $materia === null ? null : [
                        'id' => (int) $materia->id_materia,
                        'nombre' => $materia->nombre_materia,
                        'clave' => $materia->clave,
                        'area' => $materia->area,
                        'descripcion' => $materia->descripcion,
                    ],
                    'aula' => $aula === null ? null : [
                        'id' => (int) $aula->id_aula,
                        'nombre' => $aula->nombre_cubiculo,
                        'capacidad' => $aula->capacidad,
                        'tipo' => $aula->tipo,
                    ],
                ],
            ];
        })->values()->all();

        $blocksBySlot = [];

        foreach ($calendarBlocks as $calendarBlock) {
            $blocksBySlot[$calendarBlock['fecha']][$calendarBlock['hora_inicio_entera']][] = $calendarBlock;
        }

        $hoursStart = $calendarBlocks === [] ? 8 : min(array_column($calendarBlocks, 'hora_inicio_entera'));
        $hoursEnd = $calendarBlocks === [] ? 20 : max(array_column($calendarBlocks, 'hora_fin_entera'));
        $hours = range($hoursStart, max($hoursStart + 1, $hoursEnd) - 1);

        return view('Profesor.disponibilidades', compact(
            'blocksBySlot',
            'calendarBlocks',
            'hours',
            'weekDays',
            'weekEnd',
            'weekStart',
        ));
    }

    public function index(Request $request): JsonResponse
    {
        $disponibilidades = $this->profesorFrom($request)
            ->disponibilidad()
            ->orderBy('fecha')
            ->orderBy('hora_inicio')
            ->get();

        return response()->json(['data' => $disponibilidades]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $disponibilidad = $this->profesorFrom($request)
            ->disponibilidad()
            ->findOrFail($id);

        return response()->json(['data' => $disponibilidad]);
    }

    public function store(Request $request): JsonResponse
    {
        $profesor = $this->profesorFrom($request);
        $horario = $this->validatedSchedule($request);

        $disponibilidad = DB::transaction(function () use ($profesor, $horario): Disponibilidad {
            Profesor::query()
                ->whereKey($profesor->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $this->assertNoOverlap($profesor, $horario);

            return $profesor->disponibilidad()->create([
                ...$horario,
                'estado' => 'Disponible',
            ]);
        });

        return response()->json(['data' => $disponibilidad], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $profesor = $this->profesorFrom($request);

        $disponibilidad = DB::transaction(function () use ($id, $profesor, $request): Disponibilidad {
            Profesor::query()
                ->whereKey($profesor->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $disponibilidad = $profesor->disponibilidad()
                ->where('id_disponibilidad', $id)
                ->lockForUpdate()
                ->firstOrFail();

            $this->assertNotBooked($disponibilidad);

            $horario = $this->validatedSchedule($request, $disponibilidad);
            $this->assertNoOverlap($profesor, $horario, $disponibilidad->id_disponibilidad);

            $disponibilidad->fill($horario);
            $disponibilidad->save();

            return $disponibilidad->refresh();
        });

        return response()->json(['data' => $disponibilidad]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $profesor = $this->profesorFrom($request);

        DB::transaction(function () use ($id, $profesor): void {
            Profesor::query()
                ->whereKey($profesor->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $disponibilidad = $profesor->disponibilidad()
                ->where('id_disponibilidad', $id)
                ->lockForUpdate()
                ->firstOrFail();

            $asesorias = $disponibilidad->asesorias()
                ->where('estado', 'Confirmada')
                ->lockForUpdate()
                ->get();

            if ($asesorias->isEmpty() && $disponibilidad->estado !== 'Disponible') {
                abort(409, 'No puedes eliminar una disponibilidad ocupada sin una asesoría confirmada.');
            }

            foreach ($asesorias as $asesoria) {
                $asesoria->update(['estado' => 'Cancelada']);
                DisponibilidadEliminada::dispatch($disponibilidad, $asesoria);
            }

            if ($asesorias->isEmpty()) {
                DisponibilidadEliminada::dispatch($disponibilidad);
            }

            $disponibilidad->delete();
        });

        return response()->json(null, 204);
    }

    private function profesorFrom(Request $request): Profesor
    {
        $usuario = $request->user();
        abort_unless($usuario instanceof Usuario && $usuario->rol === 'Profesor', 403);

        return $usuario->profesor()->firstOrFail();
    }

    /**
     * @return array{fecha: string, hora_inicio: string, hora_fin: string, modalidad: string}
     */
    private function validatedSchedule(Request $request, ?Disponibilidad $existing = null): array
    {
        $presenceRules = $existing === null ? ['required'] : ['sometimes', 'required'];
        $validated = $request->validate([
            'fecha' => [...$presenceRules, 'date_format:Y-m-d', 'after_or_equal:today'],
            'hora_inicio' => [...$presenceRules, 'date_format:H:i,H:i:s'],
            'hora_fin' => [...$presenceRules, 'date_format:H:i,H:i:s'],
            'modalidad' => [...$presenceRules, 'string', 'max:255'],
        ]);

        if ($existing !== null && $validated === []) {
            throw ValidationException::withMessages([
                'disponibilidad' => 'Debes indicar al menos un dato para actualizar.',
            ]);
        }

        $horario = array_merge([
            'fecha' => $existing?->fecha,
            'hora_inicio' => $existing === null ? null : substr($existing->hora_inicio, 0, 5),
            'hora_fin' => $existing === null ? null : substr($existing->hora_fin, 0, 5),
            'modalidad' => $existing?->modalidad,
        ], $validated);

        $horario['hora_inicio'] = substr($horario['hora_inicio'], 0, 5);
        $horario['hora_fin'] = substr($horario['hora_fin'], 0, 5);

        if ($horario['hora_fin'] <= $horario['hora_inicio']) {
            throw ValidationException::withMessages([
                'hora_fin' => 'La hora de fin debe ser posterior a la hora de inicio.',
            ]);
        }

        if (
            $horario['fecha'] === today()->toDateString()
            && $horario['hora_inicio'] <= now()->format('H:i')
        ) {
            throw ValidationException::withMessages([
                'hora_inicio' => 'La hora de inicio debe ser posterior a la hora actual.',
            ]);
        }

        return $horario;
    }

    /**
     * @param  array{fecha: string, hora_inicio: string, hora_fin: string, modalidad: string}  $horario
     */
    private function assertNoOverlap(Profesor $profesor, array $horario, ?int $exceptId = null): void
    {
        $overlapExists = $profesor->disponibilidad()
            ->where('fecha', $horario['fecha'])
            ->where('hora_inicio', '<', $horario['hora_fin'])
            ->where('hora_fin', '>', $horario['hora_inicio'])
            ->when(
                $exceptId !== null,
                fn (Builder $query): Builder => $query->where('id_disponibilidad', '!=', $exceptId)
            )
            ->exists();

        if ($overlapExists) {
            throw ValidationException::withMessages([
                'hora_inicio' => 'El horario se empalma con otra disponibilidad.',
            ]);
        }
    }

    private function assertNotBooked(Disponibilidad $disponibilidad): void
    {
        abort_if(
            $disponibilidad->asesorias()->where('estado', 'Confirmada')->exists(),
            409,
            'No puedes modificar ni eliminar una disponibilidad con una asesoría asociada.'
        );
    }
}
