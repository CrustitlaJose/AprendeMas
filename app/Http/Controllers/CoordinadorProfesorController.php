<?php

namespace App\Http\Controllers;

use App\Models\Profesor;
use App\Models\ProfesorMateria;
use App\Models\Usuario;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CoordinadorProfesorController extends Controller
{
    public function index(Request $request): JsonResponse|View
    {
        $query = Profesor::query()
            ->with('usuario')
            ->when($request->filled('q'), function (Builder $query) use ($request): void {
                $search = $request->string('q')->trim()->toString();

                $query->where(function (Builder $query) use ($search): void {
                    $query->where('num_empleado', 'ilike', "%{$search}%")
                        ->orWhere('nombre', 'ilike', "%{$search}%")
                        ->orWhere('apellidos', 'ilike', "%{$search}%")
                        ->orWhere('departamento', 'ilike', "%{$search}%")
                        ->orWhereHas('usuario', fn (Builder $query) => $query->where('correo', 'ilike', "%{$search}%"));
                });
            })
            ->orderBy('apellidos')
            ->orderBy('nombre');

        if ($request->expectsJson()) {
            return response()->json($query->get());
        }

        $profesores = $query->paginate(15)->withQueryString();

        return view('Coordinador.profesores', compact('profesores'));
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'correo' => ['required', 'email', 'unique:usuario,correo'],
            'nombre' => ['required', 'string'],
            'apellidos' => ['required', 'string'],
            'departamento' => ['required', 'string'],
            'especialidad' => ['required', 'string'],
            'materias' => ['required', 'array', 'min:1'],
            'materias.*' => ['required', 'integer', 'distinct', Rule::exists('materia', 'id_materia')],
        ]);
        $contrasenaTemporal = Str::random(16);

        $profesor = DB::transaction(function () use ($validated, $contrasenaTemporal): Profesor {
            $usuario = new Usuario;
            $usuario->correo = $validated['correo'];
            $usuario->contrasena_hash = Hash::make($contrasenaTemporal);
            $usuario->rol = 'Profesor';
            $usuario->estado = 'Activo';
            $usuario->save();

            $profesor = new Profesor;
            $profesor->id_usuario = $usuario->id_usuario;
            $profesor->num_empleado = 'EMP-'.sprintf('%04d', $usuario->id_usuario);
            $profesor->nombre = $validated['nombre'];
            $profesor->apellidos = $validated['apellidos'];
            $profesor->departamento = $validated['departamento'];
            $profesor->especialidad = $validated['especialidad'];
            $profesor->save();

            foreach ($validated['materias'] as $idMateria) {
                ProfesorMateria::create([
                    'id_profesor' => $profesor->id_profesor,
                    'id_materia' => $idMateria,
                ]);
            }

            return $profesor;
        });

        $profesor->load('usuario');

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Profesor registrado exitosamente.',
                'profesor' => $profesor,
                'contrasena_temporal' => $contrasenaTemporal,
            ], 201);
        }

        return redirect()->route('coordinador.profesores.index')->with([
            'status' => 'Profesor registrado exitosamente.',
            'contrasena_temporal' => $contrasenaTemporal,
        ]);
    }

    public function update(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $profesor = Profesor::findOrFail($id);

        $validated = $request->validate([
            'num_empleado' => ['sometimes', 'required', Rule::unique('profesor', 'num_empleado')->ignore($profesor->id_profesor, 'id_profesor')],
            'nombre' => ['sometimes', 'required', 'string'],
            'apellidos' => ['sometimes', 'required', 'string'],
            'departamento' => ['sometimes', 'required', 'string'],
            'especialidad' => ['sometimes', 'required', 'string'],
            'correo' => ['sometimes', 'required', 'email', Rule::unique('usuario', 'correo')->ignore($profesor->id_usuario, 'id_usuario')],
        ]);

        DB::transaction(function () use ($validated, $profesor): void {
            $profesor->fill(array_intersect_key(
                $validated,
                array_flip(['num_empleado', 'nombre', 'apellidos', 'departamento', 'especialidad'])
            ));

            if ($profesor->isDirty()) {
                $profesor->save();
            }

            if (array_key_exists('correo', $validated)) {
                $profesor->usuario()->update(['correo' => $validated['correo']]);
            }
        });

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Perfil de profesor actualizado.']);
        }

        return back()->with('status', 'Datos del profesor actualizados.');
    }

    public function destroy(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $profesor = Profesor::findOrFail($id);

        $profesor->usuario()->update(['estado' => 'Inactivo']);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Profesor dado de baja correctamente.']);
        }

        return back()->with('status', 'La cuenta del profesor se desactivó.');
    }
}
