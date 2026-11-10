@extends('layouts.app')

@section('title', 'Mi agenda docente')

@section('content')
<div class="mx-auto max-w-7xl space-y-6">
    <header>
        <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Gestión académica</p>
        <h1 class="mt-1 text-3xl font-bold text-[#212529]">Mi agenda docente</h1>
        <p class="mt-2 text-sm text-gray-600">Registra y administra tus bloques de disponibilidad. El aula se asignará al confirmar una asesoría.</p>
    </header>

    @if (session('status'))
        <div class="rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-800" role="status">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800" role="alert">
            <p class="font-semibold">No se pudo guardar la disponibilidad:</p>
            <ul class="mt-2 list-inside list-disc">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <details class="rounded-xl border border-gray-200 bg-white shadow-sm" @if (old('_form') === 'disponibilidad-create') open @endif>
        <summary class="cursor-pointer px-5 py-4 font-semibold text-gray-900">Agregar disponibilidad</summary>
        <form action="{{ route('profesor.disponibilidades.store') }}" method="POST" class="grid gap-4 border-t border-gray-100 p-5 sm:grid-cols-2 lg:grid-cols-4">
            @csrf
            <input type="hidden" name="_form" value="disponibilidad-create">
            <label class="text-sm font-medium text-gray-700">
                Fecha
                <input type="date" name="fecha" value="{{ old('_form') === 'disponibilidad-create' ? old('fecha') : '' }}" min="{{ now()->toDateString() }}" required class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2">
            </label>
            <label class="text-sm font-medium text-gray-700">
                Hora de inicio
                <input type="time" name="hora_inicio" value="{{ old('_form') === 'disponibilidad-create' ? old('hora_inicio') : '' }}" required class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2">
            </label>
            <label class="text-sm font-medium text-gray-700">
                Hora de fin
                <input type="time" name="hora_fin" value="{{ old('_form') === 'disponibilidad-create' ? old('hora_fin') : '' }}" required class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2">
            </label>
            <label class="text-sm font-medium text-gray-700">
                Modalidad
                <select name="modalidad" required class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2">
                    <option value="">Selecciona una modalidad</option>
                    <option value="Presencial" @selected(old('modalidad') === 'Presencial')>Presencial</option>
                    <option value="Virtual" @selected(old('modalidad') === 'Virtual')>Virtual</option>
                </select>
            </label>
            <label class="text-sm font-medium text-gray-700">
                Frecuencia
                <select name="repeticion" data-recurrence-select class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2">
                    <option value="unica" @selected(old('repeticion', 'unica') === 'unica')>Solo este día</option>
                    <option value="semanal" @selected(old('repeticion') === 'semanal')>Repetir cada semana</option>
                </select>
            </label>
            <label data-recurrence-end @class([
                'text-sm font-medium text-gray-700',
                'hidden' => old('repeticion', 'unica') !== 'semanal',
            ])>
                Repetir hasta
                <input type="date" name="fecha_fin" value="{{ old('fecha_fin') }}" min="{{ old('fecha', now()->toDateString()) }}" @required(old('repeticion') === 'semanal') class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2">
                <span class="mt-1 block text-xs font-normal text-gray-500">Se incluirá cada semana hasta esta fecha.</span>
            </label>
            <div class="sm:col-span-2 lg:col-span-4">
                <button type="submit" class="rounded-lg bg-[#1F4E78] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#193f61]">Guardar disponibilidad</button>
            </div>
        </form>
    </details>

    <section class="space-y-4" aria-labelledby="availability-list-title">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <h2 id="availability-list-title" class="text-xl font-semibold text-gray-900">Disponibilidades registradas</h2>
                <p class="mt-1 text-sm text-gray-600">{{ $disponibilidades->count() }} bloques</p>
            </div>
        </div>

        @forelse ($disponibilidades as $disponibilidad)
            @php($formId = 'disponibilidad-'.$disponibilidad->id_disponibilidad)
            <details class="rounded-xl border border-gray-200 bg-white shadow-sm" @if (old('_form') === $formId) open @endif>
                <summary class="flex cursor-pointer flex-wrap items-center justify-between gap-3 px-5 py-4">
                    <span class="font-semibold text-gray-900">
                        {{ $disponibilidad->fecha }} · {{ substr((string) $disponibilidad->hora_inicio, 0, 5) }}–{{ substr((string) $disponibilidad->hora_fin, 0, 5) }}
                    </span>
                    <span @class([
                        'rounded-full px-3 py-1 text-xs font-semibold',
                        'bg-green-50 text-green-800' => $disponibilidad->estado === 'Disponible',
                        'bg-amber-50 text-amber-800' => $disponibilidad->estado !== 'Disponible',
                    ])>{{ $disponibilidad->estado }}</span>
                </summary>

                @if ($disponibilidad->estado === 'Disponible' && $disponibilidad->asesorias->isEmpty())
                    <form action="{{ route('profesor.disponibilidades.update', $disponibilidad->id_disponibilidad) }}" method="POST" class="grid gap-4 border-t border-gray-100 p-5 sm:grid-cols-2 lg:grid-cols-4">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="_form" value="{{ $formId }}">
                        <label class="text-sm font-medium text-gray-700">
                            Fecha
                            <input type="date" name="fecha" value="{{ old('_form') === $formId ? old('fecha') : $disponibilidad->fecha }}" min="{{ now()->toDateString() }}" required class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2">
                        </label>
                        <label class="text-sm font-medium text-gray-700">
                            Hora de inicio
                            <input type="time" name="hora_inicio" value="{{ old('_form') === $formId ? old('hora_inicio') : substr((string) $disponibilidad->hora_inicio, 0, 5) }}" required class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2">
                        </label>
                        <label class="text-sm font-medium text-gray-700">
                            Hora de fin
                            <input type="time" name="hora_fin" value="{{ old('_form') === $formId ? old('hora_fin') : substr((string) $disponibilidad->hora_fin, 0, 5) }}" required class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2">
                        </label>
                        <label class="text-sm font-medium text-gray-700">
                            Modalidad
                            <select name="modalidad" required class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2">
                                <option value="Presencial" @selected((old('_form') === $formId ? old('modalidad') : $disponibilidad->modalidad) === 'Presencial')>Presencial</option>
                                <option value="Virtual" @selected((old('_form') === $formId ? old('modalidad') : $disponibilidad->modalidad) === 'Virtual')>Virtual</option>
                            </select>
                        </label>
                        <div class="flex flex-wrap gap-3 sm:col-span-2 lg:col-span-4">
                            <button type="submit" class="rounded-lg bg-[#1F4E78] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#193f61]">Guardar cambios</button>
                        </div>
                    </form>
                    <form action="{{ route('profesor.disponibilidades.destroy', $disponibilidad->id_disponibilidad) }}" method="POST" class="border-t border-gray-100 px-5 py-4" data-confirm-title="¿Eliminar esta disponibilidad?" data-confirm-message="Este bloque dejará de estar disponible para los alumnos." data-confirm-label="Sí, eliminar disponibilidad">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-sm font-semibold text-red-700 hover:text-red-800">Eliminar disponibilidad</button>
                    </form>
                @else
                    <p class="border-t border-gray-100 px-5 py-4 text-sm text-gray-600">Este horario ya está ocupado o asociado a una asesoría y no se puede modificar ni eliminar.</p>
                @endif
            </details>
        @empty
            <p class="rounded-xl border border-dashed border-gray-300 bg-white p-8 text-center text-sm text-gray-600">Aún no tienes disponibilidades registradas.</p>
        @endforelse
    </section>
</div>
<script>
    const recurrenceSelect = document.querySelector('[data-recurrence-select]');
    const recurrenceEnd = document.querySelector('[data-recurrence-end]');
    const availabilityDate = recurrenceSelect.form.querySelector('[name="fecha"]');
    const recurrenceEndDate = recurrenceEnd.querySelector('input');

    function updateRecurrenceFields() {
        const isWeekly = recurrenceSelect.value === 'semanal';
        recurrenceEnd.classList.toggle('hidden', !isWeekly);
        recurrenceEndDate.required = isWeekly;
        recurrenceEndDate.min = availabilityDate.value || availabilityDate.min;
    }

    recurrenceSelect.addEventListener('change', updateRecurrenceFields);
    availabilityDate.addEventListener('change', updateRecurrenceFields);
    updateRecurrenceFields();
</script>
@endsection
