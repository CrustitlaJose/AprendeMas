@extends('layouts.app')

@section('title', 'Gestión de alumnos')

@section('content')
<div class="mx-auto max-w-7xl space-y-6">
    <header class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Gestión académica</p>
            <h1 class="mt-1 text-3xl font-bold text-[#212529]">Gestión de alumnos</h1>
            <p class="mt-2 text-sm text-gray-600">Consulta y administra los expedientes de alumnos.</p>
        </div>
        <a href="{{ route('coordinador.alumnos.create') }}" class="inline-flex items-center justify-center rounded-lg bg-[#1F4E78] px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#193f61] focus:outline-none focus:ring-2 focus:ring-[#1F4E78] focus:ring-offset-2">
            <span class="mr-2 text-lg leading-none">+</span> Registrar alumno
        </a>
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

    <section class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="flex flex-col justify-between gap-4 border-b border-gray-200 p-5 sm:flex-row sm:items-center">
            <div>
                <h2 class="font-semibold text-gray-900">Alumnos registrados</h2>
                <p class="mt-1 text-sm text-gray-500">{{ $alumnos->total() }} registros</p>
            </div>
            <form method="GET" action="{{ route('coordinador.alumnos.index') }}" class="flex gap-2">
                <label for="buscar-alumno" class="sr-only">Buscar alumnos</label>
                <input id="buscar-alumno" name="q" value="{{ request('q') }}" placeholder="Matrícula, nombre o correo" class="min-w-0 rounded-lg border border-gray-300 px-3 py-2 text-sm">
                <button class="rounded-lg bg-[#1F4E78] px-4 py-2 text-sm font-semibold text-white hover:bg-[#193f61]">Buscar</button>
                @if (request()->filled('q'))
                    <a href="{{ route('coordinador.alumnos.index') }}" class="rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50">Limpiar</a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-left text-sm">
                <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-600">
                    <tr>
                        <th class="px-5 py-3">Matrícula</th>
                        <th class="px-5 py-3">Alumno</th>
                        <th class="px-5 py-3">Correo</th>
                        <th class="px-5 py-3">Teléfono</th>
                        <th class="px-5 py-3">Estado</th>
                        <th class="px-5 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($alumnos as $alumno)
                        @php($formId = 'alumno-edit-'.$alumno->id_alumno)
                        <tr class="align-top transition hover:bg-gray-50">
                            <td class="whitespace-nowrap px-5 py-4 font-medium text-gray-900">{{ $alumno->matricula }}</td>
                            <td class="px-5 py-4">{{ $alumno->nombre }} {{ $alumno->apellidos }}</td>
                            <td class="px-5 py-4">{{ $alumno->usuario?->correo ?? '—' }}</td>
                            <td class="px-5 py-4">{{ $alumno->telefono }}</td>
                            <td class="px-5 py-4">
                                @if (($alumno->usuario?->estado ?? $alumno->estado) === 'Activo')
                                    <span class="rounded-full bg-green-50 px-2.5 py-1 text-xs font-semibold text-[#218838]">Activo</span>
                                @else
                                    <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-700">Inactivo</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex justify-end gap-3">
                                    <button type="button" onclick="document.getElementById('modal-{{ $formId }}').showModal()" class="font-semibold text-[#1F4E78] hover:text-[#193f61]">Editar</button>
                                    @if (($alumno->usuario?->estado ?? $alumno->estado) === 'Activo')
                                        <form action="{{ route('coordinador.alumnos.destroy', $alumno->id_alumno) }}" method="POST" data-confirm-title="¿Dar de baja a este alumno?" data-confirm-message="La cuenta del alumno se desactivará y ya no podrá acceder al sistema." data-confirm-label="Sí, dar de baja">
                                            @csrf
                                            @method('DELETE')
                                            <button class="font-semibold text-[#DC3545] hover:text-red-800">Dar de baja</button>
                                        </form>
                                    @else
                                        <form action="{{ route('coordinador.alumnos.active', $alumno->id_alumno) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button class="font-semibold text-green-700 hover:text-green-800">Dar de alta</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-gray-500">No hay alumnos que coincidan con la búsqueda.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($alumnos->hasPages())
            <div class="border-t border-gray-200 p-5">{{ $alumnos->links() }}</div>
        @endif
    </section>

    @foreach ($alumnos as $alumno)
        @php($formId = 'alumno-edit-'.$alumno->id_alumno)
        <dialog id="modal-{{ $formId }}" aria-labelledby="titulo-{{ $formId }}" data-reopen="{{ old('_form') === $formId ? 'true' : 'false' }}" class="max-h-[90vh] w-[calc(100%-2rem)] max-w-2xl overflow-y-auto rounded-xl p-0 shadow-2xl backdrop:bg-gray-900/60">
            <form id="{{ $formId }}" action="{{ route('coordinador.alumnos.update', $alumno->id_alumno) }}" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="_form" value="{{ $formId }}">
                <div class="sticky top-0 z-10 flex items-start justify-between gap-4 border-b border-gray-200 bg-white px-6 py-5">
                    <div>
                        <h2 id="titulo-{{ $formId }}" class="text-xl font-bold text-gray-900">Editar alumno</h2>
                        <p class="mt-1 text-sm text-gray-500">{{ $alumno->nombre }} {{ $alumno->apellidos }} · {{ $alumno->matricula }}</p>
                    </div>
                    <button type="button" onclick="this.closest('dialog').close()" aria-label="Cerrar" class="rounded-lg px-3 py-1 text-2xl leading-none text-gray-500 hover:bg-gray-100 hover:text-gray-800">&times;</button>
                </div>
                <div class="grid gap-4 p-6 sm:grid-cols-2">
                    <label class="text-sm font-medium text-gray-700">Nombre
                        <input name="nombre" value="{{ old('_form') === $formId ? old('nombre') : $alumno->nombre }}" required class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2">
                    </label>
                    <label class="text-sm font-medium text-gray-700">Apellidos
                        <input name="apellidos" value="{{ old('_form') === $formId ? old('apellidos') : $alumno->apellidos }}" required class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2">
                    </label>
                    <label class="text-sm font-medium text-gray-700">Correo
                        <input type="email" name="correo" value="{{ old('_form') === $formId ? old('correo') : $alumno->usuario?->correo }}" required class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2">
                    </label>
                    <label class="text-sm font-medium text-gray-700">Teléfono
                        <input type="tel" name="telefono" value="{{ old('_form') === $formId ? old('telefono') : $alumno->telefono }}" required inputmode="numeric" pattern="[0-9]{10}" minlength="10" maxlength="10" title="Ingresa exactamente 10 dígitos." class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2" autocomplete="tel">
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
