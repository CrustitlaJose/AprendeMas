@extends('layouts.app')

@section('title', 'Gestión de profesores')

@section('content')
<div class="mx-auto max-w-7xl space-y-6">
    <header class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Gestión académica</p>
            <h1 class="mt-1 text-3xl font-bold text-[#212529]">Gestión de profesores</h1>
            <p class="mt-2 text-sm text-gray-600">Consulta y administra los perfiles de profesores.</p>
        </div>
        <span class="text-sm text-gray-500">Personal docente</span>
    </header>

    @if (session('status'))
        <div class="rounded-lg border border-[#28A745]/30 bg-green-50 p-4 text-sm text-green-800" role="status">
            <p>{{ session('status') }}</p>
            @if (session('contrasena_temporal'))
                <p class="mt-2">Contraseña temporal (guárdala ahora): <strong>{{ session('contrasena_temporal') }}</strong></p>
            @endif
        </div>
    @endif

    @if ($errors->any())
        <div class="rounded-lg border border-[#DC3545]/40 bg-red-50 p-4 text-sm text-red-800" role="alert">
            <p class="font-semibold">No se pudo completar la operación:</p>
            <ul class="mt-2 list-inside list-disc">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <details class="rounded-lg border border-gray-200 bg-white" @if (old('_form') === 'profesor-create') open @endif>
        <summary class="cursor-pointer list-none px-5 py-4 font-semibold text-[#212529]">
            <span class="mr-2 text-lg">+</span> Registrar profesor
        </summary>
        <form action="{{ route('coordinador.profesores.store') }}" method="POST" class="grid gap-4 border-t border-gray-100 p-6 sm:grid-cols-2 lg:grid-cols-3">
            @csrf
            <input type="hidden" name="_form" value="profesor-create">
            <label class="text-sm font-medium text-gray-700">Nombre
                <input name="nombre" value="{{ old('nombre') }}" required class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2" autocomplete="given-name">
            </label>
            <label class="text-sm font-medium text-gray-700">Apellidos
                <input name="apellidos" value="{{ old('apellidos') }}" required class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2" autocomplete="family-name">
            </label>
            <label class="text-sm font-medium text-gray-700">Correo
                <input type="email" name="correo" value="{{ old('correo') }}" required class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2" autocomplete="email">
            </label>
            <label class="text-sm font-medium text-gray-700">Departamento
                <input name="departamento" value="{{ old('departamento') }}" required class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2">
            </label>
            <label class="text-sm font-medium text-gray-700">Especialidad
                <input name="especialidad" value="{{ old('especialidad') }}" required class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2">
            </label>
            <div class="sm:col-span-2 lg:col-span-3">
                <label class="block text-sm font-medium text-gray-700 mb-2">Materias que puede impartir (Seleccione al menos una)</label>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 rounded-lg border border-gray-300 p-3 bg-gray-50">
                    <!-- Ejemplo estático o dinámico iterando el catálogo de materias -->
                    <label class="flex items-center space-x-2 text-sm text-gray-700">
                        <input type="checkbox" name="materias[]" value="1" class="rounded border-gray-300 text-[#1F4E78] focus:ring-[#1F4E78]">
                        <span>Matemáticas</span>
                    </label>
                    <label class="flex items-center space-x-2 text-sm text-gray-700">
                        <input type="checkbox" name="materias[]" value="2" class="rounded border-gray-300 text-[#1F4E78] focus:ring-[#1F4E78]">
                        <span>Programación</span>
                    </label>
                    <label class="flex items-center space-x-2 text-sm text-gray-700">
                        <input type="checkbox" name="materias[]" value="3" class="rounded border-gray-300 text-[#1F4E78] focus:ring-[#1F4E78]">
                        <span>Inglés</span>
                    </label>
                </div>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full rounded-lg bg-[#1F4E78] px-4 py-2 font-semibold text-white transition hover:bg-[#193f61]">Guardar profesor</button>
            </div>
            <p class="text-xs text-gray-500 sm:col-span-2 lg:col-span-3">Se generará una contraseña temporal, que se mostrará una sola vez al guardar.</p>
        </form>
    </details>

    <section class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="flex flex-col justify-between gap-4 border-b border-gray-200 p-5 sm:flex-row sm:items-center">
            <div>
                <h2 class="font-semibold text-gray-900">Profesores registrados</h2>
                <p class="mt-1 text-sm text-gray-500">{{ $profesores->total() }} registros</p>
            </div>
            <form method="GET" action="{{ route('coordinador.profesores.index') }}" class="flex gap-2">
                <label for="buscar-profesor" class="sr-only">Buscar profesores</label>
                <input id="buscar-profesor" name="q" value="{{ request('q') }}" placeholder="Número, nombre, correo o departamento" class="min-w-0 rounded-lg border border-gray-300 px-3 py-2 text-sm">
                <button class="rounded-lg bg-[#1F4E78] px-4 py-2 text-sm font-semibold text-white hover:bg-[#193f61]">Buscar</button>
                @if (request()->filled('q'))
                    <a href="{{ route('coordinador.profesores.index') }}" class="rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50">Limpiar</a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-left text-sm">
                <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-600">
                    <tr>
                        <th class="px-5 py-3">Número</th>
                        <th class="px-5 py-3">Profesor</th>
                        <th class="px-5 py-3">Correo</th>
                        <th class="px-5 py-3">Departamento / especialidad</th>
                        <th class="px-5 py-3">Estado</th>
                        <th class="px-5 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($profesores as $profesor)
                        @php($formId = 'profesor-edit-'.$profesor->id_profesor)
                        <tr class="align-top transition hover:bg-gray-50">
                            <td class="whitespace-nowrap px-5 py-4 font-medium text-gray-900">{{ $profesor->num_empleado }}</td>
                            <td class="px-5 py-4">{{ $profesor->nombre }} {{ $profesor->apellidos }}</td>
                            <td class="px-5 py-4">{{ $profesor->usuario?->correo ?? '—' }}</td>
                            <td class="px-5 py-4">
                                <span class="block">{{ $profesor->departamento }}</span>
                                <span class="mt-1 block text-xs text-gray-500">{{ $profesor->especialidad }}</span>
                            </td>
                            <td class="px-5 py-4">
                                @if ($profesor->usuario?->estado === 'Activo')
                                    <span class="rounded-full bg-green-50 px-2.5 py-1 text-xs font-semibold text-[#218838]">Activo</span>
                                @else
                                    <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-700">Inactivo</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex justify-end gap-3">
                                    <button type="button" onclick="document.getElementById('modal-{{ $formId }}').showModal()" class="font-semibold text-[#1F4E78] hover:text-[#193f61]">Editar</button>
                                    @if ($profesor->usuario?->estado === 'Activo')
                                        <form action="{{ route('coordinador.profesores.destroy', $profesor->id_profesor) }}" method="POST" data-confirm-title="¿Dar de baja a este profesor?" data-confirm-message="La cuenta del profesor se desactivará y ya no podrá acceder al sistema." data-confirm-label="Sí, dar de baja">
                                            @csrf
                                            @method('DELETE')
                                            <button class="font-semibold text-[#DC3545] hover:text-red-800">Dar de baja</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-gray-500">No hay profesores que coincidan con la búsqueda.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($profesores->hasPages())
            <div class="border-t border-gray-200 p-5">{{ $profesores->links() }}</div>
        @endif
    </section>

    @foreach ($profesores as $profesor)
        @php($formId = 'profesor-edit-'.$profesor->id_profesor)
        <dialog id="modal-{{ $formId }}" aria-labelledby="titulo-{{ $formId }}" data-reopen="{{ old('_form') === $formId ? 'true' : 'false' }}" class="max-h-[90vh] w-[calc(100%-2rem)] max-w-2xl overflow-y-auto rounded-xl p-0 shadow-2xl backdrop:bg-gray-900/60">
            <form action="{{ route('coordinador.profesores.update', $profesor->id_profesor) }}" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="_form" value="{{ $formId }}">
                <div class="sticky top-0 z-10 flex items-start justify-between gap-4 border-b border-gray-200 bg-white px-6 py-5">
                    <div>
                        <h2 id="titulo-{{ $formId }}" class="text-xl font-bold text-gray-900">Editar profesor</h2>
                        <p class="mt-1 text-sm text-gray-500">{{ $profesor->nombre }} {{ $profesor->apellidos }} · {{ $profesor->num_empleado }}</p>
                    </div>
                    <button type="button" onclick="this.closest('dialog').close()" aria-label="Cerrar" class="rounded-lg px-3 py-1 text-2xl leading-none text-gray-500 hover:bg-gray-100 hover:text-gray-800">&times;</button>
                </div>
                <div class="grid gap-4 p-6 sm:grid-cols-2">
                    <label class="text-sm font-medium text-gray-700">Número de empleado
                        <input name="num_empleado" value="{{ old('_form') === $formId ? old('num_empleado') : $profesor->num_empleado }}" required class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2">
                    </label>
                    <label class="text-sm font-medium text-gray-700">Correo
                        <input type="email" name="correo" value="{{ old('_form') === $formId ? old('correo') : $profesor->usuario?->correo }}" required class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2">
                    </label>
                    <label class="text-sm font-medium text-gray-700">Nombre
                        <input name="nombre" value="{{ old('_form') === $formId ? old('nombre') : $profesor->nombre }}" required class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2">
                    </label>
                    <label class="text-sm font-medium text-gray-700">Apellidos
                        <input name="apellidos" value="{{ old('_form') === $formId ? old('apellidos') : $profesor->apellidos }}" required class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2">
                    </label>
                    <label class="text-sm font-medium text-gray-700">Departamento
                        <input name="departamento" value="{{ old('_form') === $formId ? old('departamento') : $profesor->departamento }}" required class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2">
                    </label>
                    <label class="text-sm font-medium text-gray-700">Especialidad
                        <input name="especialidad" value="{{ old('_form') === $formId ? old('especialidad') : $profesor->especialidad }}" required class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2">
                    </label>
                </div>
                <div class="sticky bottom-0 flex justify-end gap-3 border-t border-gray-200 bg-white px-6 py-4">
                    <button type="button" onclick="this.closest('dialog').close()" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Cancelar</button>
                    <button type="submit" class="rounded-lg bg-[#1F4E78] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#193f61]">Guardar cambios</button>
                </div>
            </form>
        </dialog>
    @endforeach
</div>
<script>
    document.querySelectorAll('dialog[data-reopen="true"]').forEach((dialog) => dialog.showModal());
</script>
@endsection
