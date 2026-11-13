<?php

namespace App\Http\Controllers;

use App\Events\DisponibilidadEliminada;
use App\Models\Disponibilidad;
use App\Models\Materia;
use App\Models\Profesor;
use App\Models\ProfesorMateria;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DisponibilidadController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $profesor = $this->profesorAutenticado($request);
        $validated = $request->validate([
            ...$this->reglasDisponibilidad(),
            'repeticion' => ['required', 'in:unica,semanal'],
            'fecha_fin' => ['exclude_unless:repeticion,semanal', 'required', 'date_format:Y-m-d', 'after_or_equal:fecha'],
        ]);
        $fechas = $this->fechasDeRepeticion($validated);

        DB::transaction(function () use ($profesor, $validated, $fechas): void {
            foreach ($fechas as $fecha) {
                $this->validarTraslape($profesor, [
                    ...$validated,
                    'fecha' => $fecha,
                ]);
            }

            foreach ($fechas as $fecha) {
                $profesor->disponibilidad()->create([
                    'fecha' => $fecha,
                    'hora_inicio' => $validated['hora_inicio'],
                    'hora_fin' => $validated['hora_fin'],
                    'modalidad' => $validated['modalidad'],
                    'estado' => 'Disponible',
                ]);
            }
        });

        return redirect()->route('profesor.disponibilidades.index')
            ->with('status', count($fechas) === 1
                ? 'La disponibilidad se registró correctamente.'
                : count($fechas).' disponibilidades semanales se registraron correctamente.');
    }

    public function update(Request $request, int $disponibilidadId): RedirectResponse
    {
        $profesor = $this->profesorAutenticado($request);
        $disponibilidad = $profesor->disponibilidad()
            ->whereKey($disponibilidadId)
            ->firstOrFail();
        $this->validarEditable($disponibilidad);

        $validated = $request->validate($this->reglasDisponibilidad());
        $this->validarTraslape($profesor, $validated, $disponibilidad->id_disponibilidad);
        $disponibilidad->fill($validated)->save();

        return redirect()->route('profesor.disponibilidades.index')
            ->with('status', 'La disponibilidad se actualizó correctamente.');
    }

    public function destroy(Request $request, int $disponibilidadId): RedirectResponse
    {
        $profesor = $this->profesorAutenticado($request);
        $week = $request->validate([
            'semana' => ['sometimes', 'date_format:Y-m-d'],
        ])['semana'] ?? null;

        DB::transaction(function () use ($profesor, $disponibilidadId): void {
            $disponibilidad = $profesor->disponibilidad()
                ->whereKey($disponibilidadId)
                ->lockForUpdate()
                ->firstOrFail();
            $asesorias = $disponibilidad->asesorias()
                ->where('estado', 'Confirmada')
                ->lockForUpdate()
                ->get();

            if ($asesorias->isEmpty() && $disponibilidad->estado !== 'Disponible') {
                throw ValidationException::withMessages([
                    'disponibilidad' => 'No se puede eliminar una disponibilidad ocupada sin una asesoría confirmada.',
                ]);
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

        return redirect()->route('profesor.disponibilidades.index', $week === null ? [] : ['semana' => $week])
            ->with('status', 'La disponibilidad se eliminó correctamente.');
    }

    public function index(Request $request): JsonResponse|View
    {
        abort_unless($request->user()?->rol === 'Alumno', 403);

        if (! $request->expectsJson()) {
            return view('Alumno.agendar');
        }

        $now = now();
        $availableSlots = static function (Builder|Relation $query) use ($now): void {
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
                'disponibilidad' => static function (Builder|Relation $query) use ($availableSlots): void {
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

    private function profesorAutenticado(Request $request): Profesor
    {
        return Profesor::query()
            ->where('id_usuario', $request->user()->getAuthIdentifier())
            ->firstOrFail();
    }

    /**
     * @return array<string, list<string>>
     */
    private function reglasDisponibilidad(): array
    {
        return [
            'fecha' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'hora_inicio' => ['required', 'date_format:H:i'],
            'hora_fin' => ['required', 'date_format:H:i', 'after:hora_inicio'],
            'modalidad' => ['required', 'in:Presencial,Virtual'],
        ];
    }

    /**
     * @param  array{fecha: string, repeticion: string, fecha_fin?: string}  $validated
     * @return list<string>
     */
    private function fechasDeRepeticion(array $validated): array
    {
        $fechaInicial = CarbonImmutable::parse($validated['fecha'])->startOfDay();
        $fechaFinal = $validated['repeticion'] === 'semanal'
            ? CarbonImmutable::parse($validated['fecha_fin'])->startOfDay()
            : $fechaInicial;
        $fechas = [];

        for ($fecha = $fechaInicial; $fecha->lessThanOrEqualTo($fechaFinal); $fecha = $fecha->addWeek()) {
            $fechas[] = $fecha->toDateString();
        }

        return $fechas;
    }

    /**
     * @param  array{fecha: string, hora_inicio: string, hora_fin: string, modalidad: string}  $validated
     */
    private function validarTraslape(Profesor $profesor, array $validated, ?int $disponibilidadId = null): void
    {
        if ($validated['fecha'] === now()->toDateString() && $validated['hora_inicio'] <= now()->format('H:i')) {
            throw ValidationException::withMessages([
                'hora_inicio' => 'La hora de inicio debe ser posterior a la hora actual.',
            ]);
        }

        $query = $profesor->disponibilidad()
            ->where('fecha', $validated['fecha'])
            ->where('hora_inicio', '<', $validated['hora_fin'])
            ->where('hora_fin', '>', $validated['hora_inicio']);

        if ($disponibilidadId !== null) {
            $query->where('id_disponibilidad', '!=', $disponibilidadId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'hora_inicio' => 'El horario se traslapa con otra disponibilidad registrada.',
            ]);
        }
    }

    private function validarEditable(Disponibilidad $disponibilidad): void
    {
        if ($disponibilidad->estado !== 'Disponible' || $disponibilidad->asesorias()->where('estado', 'Confirmada')->exists()) {
            throw ValidationException::withMessages([
                'disponibilidad' => 'No se puede modificar ni eliminar un horario ocupado o asociado a una asesoría.',
            ]);
        }
    }
}
