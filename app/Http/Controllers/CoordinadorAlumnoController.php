<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
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

class CoordinadorAlumnoController extends Controller
{
    // CU-12: Consultar todos los alumnos
    public function index(Request $request): JsonResponse|View
    {
        $query = Alumno::query()
            ->with('usuario')
            ->when($request->filled('q'), function (Builder $query) use ($request): void {
                $search = $request->string('q')->trim()->toString();

                $query->where(function (Builder $query) use ($search): void {
                    $query->where('matricula', 'ilike', "%{$search}%")
                        ->orWhere('nombre', 'ilike', "%{$search}%")
                        ->orWhere('apellidos', 'ilike', "%{$search}%")
                        ->orWhereHas('usuario', fn (Builder $query) => $query->where('correo', 'ilike', "%{$search}%"));
                });
            })
            ->orderBy('apellidos')
            ->orderBy('nombre');

        if ($request->expectsJson()) {
            return response()->json($query->get());
        }

        $alumnos = $query->paginate(15)->withQueryString();

        return view('Coordinador.alumnos', compact('alumnos'));
    }

    // CU-10: Dar de alta un nuevo alumno
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'correo' => ['required', 'email', 'unique:usuario,correo'],
            'matricula' => ['required', 'unique:alumno,matricula'],
            'nombre' => ['required', 'string'],
            'apellidos' => ['required', 'string'],
            'telefono' => ['required', 'string', 'unique:alumno,telefono'],
        ]);
        $contrasenaTemporal = Str::random(16);

        $alumno = DB::transaction(function () use ($validated, $contrasenaTemporal): Alumno {
            $usuario = new Usuario;
            $usuario->correo = $validated['correo'];
            $usuario->contrasena_hash = Hash::make($contrasenaTemporal);
            $usuario->rol = 'Alumno';
            $usuario->estado = 'Activo';
            $usuario->save();

            $alumno = new Alumno;
            $alumno->id_usuario = $usuario->id_usuario;
            $alumno->matricula = $validated['matricula'];
            $alumno->nombre = $validated['nombre'];
            $alumno->apellidos = $validated['apellidos'];
            $alumno->telefono = $validated['telefono'];
            $alumno->estado = 'Activo';
            $alumno->save();

            return $alumno;
        });

        $alumno->load('usuario');

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Alumno registrado exitosamente.',
                'alumno' => $alumno,
                'contrasena_temporal' => $contrasenaTemporal,
            ], 201);
        }

        return redirect()->route('coordinador')->with([
            'status' => 'Alumno registrado exitosamente.',
            'contrasena_temporal' => $contrasenaTemporal,
        ]);
    }

    // CU-13: Modificar información de alumnado
    public function update(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $alumno = Alumno::findOrFail($id);

        $validated = $request->validate([
            'nombre' => ['sometimes', 'required', 'string'],
            'apellidos' => ['sometimes', 'required', 'string'],
            'telefono' => ['sometimes', 'required', 'string', Rule::unique('alumno', 'telefono')->ignore($alumno->id_alumno, 'id_alumno')],
            'correo' => ['sometimes', 'required', 'email', Rule::unique('usuario', 'correo')->ignore($alumno->id_usuario, 'id_usuario')],
        ]);

        DB::transaction(function () use ($validated, $alumno): void {
            $alumno->fill(array_intersect_key($validated, array_flip(['nombre', 'apellidos', 'telefono'])));

            if ($alumno->isDirty()) {
                $alumno->save();
            }

            if (array_key_exists('correo', $validated)) {
                $alumno->usuario()->update(['correo' => $validated['correo']]);
            }
        });

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Expediente actualizado.']);
        }

        return back()->with('status', 'Datos del alumno actualizados.');
    }

    // CU-11: Dar de baja alumno (Baja lógica)
    public function destroy(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $alumno = Alumno::findOrFail($id);

        $alumno->usuario()->update(['estado' => 'Inactivo']);
        $alumno->estado = 'Inactivo';
        $alumno->save();

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Baja lógica aplicada correctamente.']);
        }

        return back()->with('status', 'La cuenta del alumno se desactivó.');
    }
}
