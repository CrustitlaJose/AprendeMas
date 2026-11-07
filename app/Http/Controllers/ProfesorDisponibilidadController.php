<?php

namespace App\Http\Controllers;

use App\Models\Disponibilidad;
use App\Models\Profesor;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProfesorDisponibilidadController extends Controller
{
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

            $this->assertNotBooked($disponibilidad);
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
            $disponibilidad->asesoria()->exists(),
            409,
            'No puedes modificar ni eliminar una disponibilidad con una asesoría asociada.'
        );
    }
}
