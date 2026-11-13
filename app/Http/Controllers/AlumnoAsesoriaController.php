<?php

namespace App\Http\Controllers;

use App\Events\CitaAgendada;
use App\Events\CitaCancelada;
use App\Models\Alumno;
use App\Models\Asesoria;
use App\Models\Aula;
use App\Models\Disponibilidad;
use App\Models\ProfesorMateria;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AlumnoAsesoriaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $asesorias = $this->alumnoFrom($request)
            ->asesoria()
            ->where('estado', '!=', 'Cancelada')
            ->with(['disponibilidad.profesor', 'materia', 'aula'])
            ->orderByDesc('fecha_registro')
            ->orderByDesc('id_asesoria')
            ->get();

        return response()->json(['data' => $asesorias]);
    }

    public function store(Request $request): JsonResponse
    {
        $alumno = $this->alumnoFrom($request);
        $booking = $this->validatedBooking($request);

        $asesoria = DB::transaction(function () use ($alumno, $booking): Asesoria {
            $disponibilidad = Disponibilidad::query()
                ->whereKey($booking['id_disponibilidad'])
                ->lockForUpdate()
                ->firstOrFail();

            $this->assertSlotAvailable($disponibilidad);
            $this->assertFutureSlot($disponibilidad);
            $this->assertMateriaBelongsToProfesor($disponibilidad, $booking['id_materia']);
            $aula = $this->aulaDisponible($disponibilidad);

            $asesoria = $alumno->asesoria()->create([
                'id_disponibilidad' => $disponibilidad->id_disponibilidad,
                'id_materia' => $booking['id_materia'],
                'id_aula' => $aula->id_aula,
                'fecha_registro' => now('America/Mexico_City'),
                'estado' => 'Confirmada',
            ]);

            $disponibilidad->update(['estado' => 'Ocupado']);
            CitaAgendada::dispatch($asesoria);

            return $asesoria->load(['disponibilidad.profesor', 'materia', 'aula']);
        });

        return response()->json(['data' => $asesoria], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $alumno = $this->alumnoFrom($request);
        $booking = $this->validatedBooking($request, true);

        $asesoria = DB::transaction(function () use ($alumno, $booking, $id): Asesoria {
            $asesoria = $alumno->asesoria()
                ->whereKey($id)
                ->lockForUpdate()
                ->firstOrFail();

            abort_if($asesoria->estado !== 'Confirmada', 409, 'La asesoría ya no está activa.');

            $disponibilidadAnterior = Disponibilidad::query()
                ->whereKey($asesoria->id_disponibilidad)
                ->lockForUpdate()
                ->firstOrFail();
            $datosActualizados = array_merge([
                'id_disponibilidad' => $disponibilidadAnterior->id_disponibilidad,
                'id_materia' => $asesoria->id_materia,
                'id_aula' => $asesoria->id_aula,
            ], $booking);
            $mismaDisponibilidad = $datosActualizados['id_disponibilidad'] === (int) $disponibilidadAnterior->id_disponibilidad;
            $disponibilidad = $mismaDisponibilidad
                ? $disponibilidadAnterior
                : Disponibilidad::query()
                    ->whereKey($datosActualizados['id_disponibilidad'])
                    ->lockForUpdate()
                    ->firstOrFail();

            $this->assertSlotAvailable($disponibilidad, $mismaDisponibilidad ? $asesoria : null);
            $this->assertFutureSlot($disponibilidad);
            $this->assertMateriaBelongsToProfesor($disponibilidad, $datosActualizados['id_materia']);

            if (! $mismaDisponibilidad) {
                $datosActualizados['id_aula'] = $this->aulaDisponible($disponibilidad, $asesoria->id_asesoria)->id_aula;
            }

            $asesoria->update($datosActualizados);

            if (! $mismaDisponibilidad) {
                $disponibilidadAnterior->update(['estado' => 'Disponible']);
                $disponibilidad->update(['estado' => 'Ocupado']);
            }

            return $asesoria->refresh()->load(['disponibilidad.profesor', 'materia', 'aula']);
        });

        return response()->json(['data' => $asesoria]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $alumno = $this->alumnoFrom($request);

        $asesoria = DB::transaction(function () use ($alumno, $id): Asesoria {
            $asesoria = $alumno->asesoria()
                ->whereKey($id)
                ->lockForUpdate()
                ->firstOrFail();

            abort_if($asesoria->estado !== 'Confirmada', 409, 'La asesoría ya no está activa.');

            $disponibilidad = Disponibilidad::query()
                ->whereKey($asesoria->id_disponibilidad)
                ->lockForUpdate()
                ->firstOrFail();
            $inicio = Carbon::parse($disponibilidad->fecha.' '.$disponibilidad->hora_inicio);

            if ($inicio->lt(now()->addHours(12))) {
                throw ValidationException::withMessages([
                    'asesoria' => 'La asesoría solo puede cancelarse con al menos 12 horas de anticipación.',
                ]);
            }

            $asesoria->update(['estado' => 'Cancelada']);
            $disponibilidad->update(['estado' => 'Disponible']);
            CitaCancelada::dispatch($asesoria);

            return $asesoria->refresh()->load(['disponibilidad.profesor', 'materia', 'aula']);
        });

        return response()->json(['data' => $asesoria]);
    }

    private function alumnoFrom(Request $request): Alumno
    {
        $usuario = $request->user();
        abort_unless($usuario instanceof Usuario && $usuario->rol === 'Alumno', 403);

        return $usuario->alumno()->firstOrFail();
    }

    /**
     * @return array{id_disponibilidad?: int, id_materia?: int}
     */
    private function validatedBooking(Request $request, bool $partial = false): array
    {
        $presenceRules = $partial ? ['sometimes', 'required'] : ['required'];
        $validated = $request->validate([
            'id_disponibilidad' => [...$presenceRules, 'integer', 'exists:disponibilidad,id_disponibilidad'],
            'id_materia' => [...$presenceRules, 'integer', 'exists:materia,id_materia'],
            'id_aula' => ['prohibited'],
        ]);

        if ($partial && $validated === []) {
            throw ValidationException::withMessages([
                'asesoria' => 'Debes indicar al menos un dato para actualizar la asesoría.',
            ]);
        }

        return array_map(static fn (string|int $id): int => (int) $id, $validated);
    }

    private function assertSlotAvailable(Disponibilidad $disponibilidad, ?Asesoria $current = null): void
    {
        $isCurrentAvailability = $current !== null
            && (int) $disponibilidad->id_disponibilidad === (int) $current->id_disponibilidad;

        if ($disponibilidad->estado !== 'Disponible' && ! $isCurrentAvailability) {
            abort(409, 'El horario seleccionado ya no está disponible.');
        }

        $activeBookingExists = $disponibilidad->asesorias()
            ->where('estado', 'Confirmada')
            ->when(
                $current !== null,
                static fn (Builder $query): Builder => $query->where('id_asesoria', '!=', $current->id_asesoria)
            )
            ->exists();

        if ($activeBookingExists) {
            abort(409, 'El horario seleccionado ya no está disponible.');
        }
    }

    private function assertFutureSlot(Disponibilidad $disponibilidad): void
    {
        $inicio = Carbon::parse($disponibilidad->fecha.' '.$disponibilidad->hora_inicio);

        if ($inicio->lte(now())) {
            throw ValidationException::withMessages([
                'id_disponibilidad' => 'No puedes reservar un horario que ya comenzó.',
            ]);
        }
    }

    private function assertMateriaBelongsToProfesor(Disponibilidad $disponibilidad, int $idMateria): void
    {
        $materiaAsignada = ProfesorMateria::query()
            ->where('id_profesor', $disponibilidad->id_profesor)
            ->where('id_materia', $idMateria)
            ->exists();

        if (! $materiaAsignada) {
            throw ValidationException::withMessages([
                'id_materia' => 'La materia seleccionada no está asignada al profesor de ese horario.',
            ]);
        }
    }

    private function aulaDisponible(
        Disponibilidad $disponibilidad,
        ?int $exceptAsesoriaId = null
    ): Aula {
        $aulas = Aula::query()
            ->where('tipo', $disponibilidad->modalidad)
            ->orderBy('id_aula')
            ->lockForUpdate()
            ->get();
        $aulasOcupadas = Asesoria::query()
            ->where('estado', 'Confirmada')
            ->whereHas('disponibilidad', static function (Builder $query) use ($disponibilidad): void {
                $query->where('fecha', $disponibilidad->fecha)
                    ->where('hora_inicio', '<', $disponibilidad->hora_fin)
                    ->where('hora_fin', '>', $disponibilidad->hora_inicio);
            })
            ->when(
                $exceptAsesoriaId !== null,
                static fn (Builder $query): Builder => $query->where('id_asesoria', '!=', $exceptAsesoriaId)
            )
            ->pluck('id_aula')
            ->map(static fn (int|string $idAula): int => (int) $idAula)
            ->all();

        foreach ($aulas as $aula) {
            if (! in_array((int) $aula->id_aula, $aulasOcupadas, true)) {
                return $aula;
            }
        }

        throw ValidationException::withMessages([
            'id_disponibilidad' => 'No hay aulas disponibles para el horario seleccionado.',
        ]);
    }
}
