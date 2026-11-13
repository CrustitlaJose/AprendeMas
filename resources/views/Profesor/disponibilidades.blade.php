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

    <section x-data="teacherCalendar()" @keydown.escape.window="deleteModalOpen = false" class="space-y-4" aria-labelledby="weekly-calendar-title">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <h2 id="weekly-calendar-title" class="text-xl font-semibold text-gray-900">Horario semanal</h2>
                <p class="mt-1 text-sm text-gray-600">{{ $weekStart->format('d/m/Y') }} – {{ $weekEnd->format('d/m/Y') }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-4">
                <div class="flex flex-wrap gap-4 text-xs text-gray-600" aria-label="Leyenda del horario">
                    <span class="inline-flex items-center gap-2"><span class="h-3 w-3 rounded-sm bg-blue-600"></span>Ocupada</span>
                    <span class="inline-flex items-center gap-2"><span class="h-3 w-3 rounded-sm bg-gray-300"></span>Disponible</span>
                </div>
                <nav class="flex items-center gap-2" aria-label="Cambiar semana">
                    <a href="{{ route('profesor.disponibilidades.index', ['semana' => $weekStart->subWeek()->toDateString()]) }}" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">Semana anterior</a>
                    <a href="{{ route('profesor.disponibilidades.index', ['semana' => $weekStart->addWeek()->toDateString()]) }}" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">Semana siguiente</a>
                </nav>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-[minmax(0,2fr)_minmax(18rem,1fr)]">
            <section class="min-w-0 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm" aria-label="Tabla de horario semanal">
                <div class="overflow-x-auto">
                    <div class="min-w-[760px]">
                        <div class="grid grid-cols-7 border-b border-gray-200 bg-gray-50">
                            <div class="px-3 py-4 text-xs font-semibold uppercase tracking-wide text-gray-500">Hora</div>
                            @foreach ($weekDays as $day)
                                <div class="border-l border-gray-200 px-2 py-3 text-center">
                                    <p class="text-sm font-semibold text-gray-800">{{ $day['nombre'] }}</p>
                                    <p class="mt-1 text-xs text-gray-500">{{ $day['fecha_corta'] }}</p>
                                </div>
                            @endforeach
                        </div>

                        @foreach ($hours as $hour)
                            <div class="grid grid-cols-7">
                                <div class="min-h-20 border-b border-gray-200 bg-gray-50 px-2 py-2 text-xs font-medium text-gray-500">{{ sprintf('%02d:00', $hour) }}</div>
                                @foreach ($weekDays as $day)
                                    @php($slotBlocks = $blocksBySlot[$day['fecha']][$hour] ?? [])
                                    <div class="min-h-20 space-y-1 border-b border-l border-gray-200 p-1">
                                        @forelse ($slotBlocks as $block)
                                            <button
                                                type="button"
                                                @click="selectBlock({{ $block['id'] }})"
                                                aria-label="Ver disponibilidad del {{ $day['nombre'] }} de {{ $block['hora_inicio'] }} a {{ $block['hora_fin'] }}"
                                                @class([
                                                    'flex w-full flex-col rounded-md border-l-4 px-2 py-2 text-left text-xs transition focus:outline-none focus:ring-2 focus:ring-[#1e3a5f] focus:ring-offset-1',
                                                    'border-blue-800 bg-blue-600 text-white hover:bg-blue-700' => $block['ocupada'],
                                                    'border-gray-500 bg-gray-200 text-gray-800 hover:bg-gray-300' => ! $block['ocupada'],
                                                ])
                                            >
                                                <span class="font-bold">{{ $block['hora_inicio'] }}–{{ $block['hora_fin'] }}</span>
                                                <span class="mt-1 truncate">{{ $block['ocupada'] ? ($block['asesoria']['alumno']['nombre_completo'] ?? 'Ocupada') : 'Disponible' }}</span>
                                            </button>
                                        @empty
                                            <span class="block h-full min-h-16 rounded-md" aria-hidden="true"></span>
                                        @endforelse
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            <aside class="min-h-72 rounded-xl border border-gray-200 bg-white p-5 shadow-sm" aria-labelledby="appointment-details-title">
                <h2 id="appointment-details-title" class="text-lg font-semibold text-gray-900">Panel de detalles</h2>

                <div x-show="! selectedBlock" class="flex min-h-56 items-center justify-center rounded-lg border border-dashed border-gray-300 bg-gray-50 px-6 text-center">
                    <p class="text-sm leading-6 text-gray-500">Selecciona un bloque en el horario semanal para consultar su información</p>
                </div>

                <div x-cloak x-show="selectedBlock" class="mt-4 space-y-4">
                    <section class="rounded-lg bg-gray-50 p-4">
                        <h3 class="font-semibold text-gray-900">Disponibilidad</h3>
                        <dl class="mt-3 space-y-2 text-sm">
                            <div class="flex justify-between gap-3"><dt class="text-gray-500">Fecha</dt><dd class="text-right font-medium text-gray-900" x-text="selectedBlock && selectedBlock.fecha"></dd></div>
                            <div class="flex justify-between gap-3"><dt class="text-gray-500">Horario</dt><dd class="text-right font-medium text-gray-900" x-text="selectedBlock && `${selectedBlock.hora_inicio} – ${selectedBlock.hora_fin}`"></dd></div>
                            <div class="flex justify-between gap-3"><dt class="text-gray-500">Modalidad</dt><dd class="text-right font-medium text-gray-900" x-text="selectedBlock && selectedBlock.modalidad"></dd></div>
                            <div class="flex justify-between gap-3"><dt class="text-gray-500">Estado</dt><dd class="text-right font-medium text-gray-900" x-text="selectedBlock && (selectedBlock.asesoria ? 'Ocupada' : selectedBlock.estado)"></dd></div>
                        </dl>
                    </section>

                    <template x-if="selectedBlock && selectedBlock.asesoria">
                        <div class="space-y-4">
                            <section class="rounded-lg border border-blue-100 bg-blue-50 p-4">
                                <h3 class="font-semibold text-[#1e3a5f]">Datos del alumno</h3>
                                <dl class="mt-3 space-y-2 text-sm">
                                    <div><dt class="text-gray-500">Nombre completo</dt><dd class="font-medium text-gray-900" x-text="selectedBlock.asesoria.alumno.nombre_completo"></dd></div>
                                    <div class="flex justify-between gap-3"><dt class="text-gray-500">ID alumno / usuario</dt><dd class="text-right font-medium text-gray-900" x-text="`${selectedBlock.asesoria.alumno.id} / ${selectedBlock.asesoria.alumno.id_usuario}`"></dd></div>
                                    <div class="flex justify-between gap-3"><dt class="text-gray-500">Matrícula</dt><dd class="text-right font-medium text-gray-900" x-text="selectedBlock.asesoria.alumno.matricula"></dd></div>
                                    <div class="flex justify-between gap-3"><dt class="text-gray-500">Correo</dt><dd class="break-all text-right font-medium text-gray-900" x-text="selectedBlock.asesoria.alumno.correo"></dd></div>
                                    <div class="flex justify-between gap-3"><dt class="text-gray-500">Teléfono</dt><dd class="text-right font-medium text-gray-900" x-text="selectedBlock.asesoria.alumno.telefono"></dd></div>
                                    <div class="flex justify-between gap-3"><dt class="text-gray-500">Rol</dt><dd class="text-right font-medium text-gray-900" x-text="selectedBlock.asesoria.alumno.rol"></dd></div>
                                    <div class="flex justify-between gap-3"><dt class="text-gray-500">Estado de cuenta</dt><dd class="text-right font-medium text-gray-900" x-text="selectedBlock.asesoria.alumno.estado_usuario"></dd></div>
                                </dl>
                            </section>

                            <section class="rounded-lg border border-gray-200 p-4">
                                <h3 class="font-semibold text-gray-900">Detalles de la asesoría</h3>
                                <dl class="mt-3 space-y-2 text-sm">
                                    <div class="flex justify-between gap-3"><dt class="text-gray-500">Folio</dt><dd class="text-right font-medium text-gray-900" x-text="selectedBlock.asesoria.id"></dd></div>
                                    <div class="flex justify-between gap-3"><dt class="text-gray-500">ID disponibilidad</dt><dd class="text-right font-medium text-gray-900" x-text="selectedBlock.asesoria.id_disponibilidad"></dd></div>
                                    <div class="flex justify-between gap-3"><dt class="text-gray-500">Materia</dt><dd class="text-right font-medium text-gray-900" x-text="selectedBlock.asesoria.materia.nombre"></dd></div>
                                    <div class="flex justify-between gap-3"><dt class="text-gray-500">Clave / área</dt><dd class="text-right font-medium text-gray-900" x-text="`${selectedBlock.asesoria.materia.clave} / ${selectedBlock.asesoria.materia.area}`"></dd></div>
                                    <div><dt class="text-gray-500">Descripción</dt><dd class="font-medium text-gray-900" x-text="selectedBlock.asesoria.materia.descripcion"></dd></div>
                                    <div class="flex justify-between gap-3"><dt class="text-gray-500">Aula</dt><dd class="text-right font-medium text-gray-900" x-text="selectedBlock.asesoria.aula ? selectedBlock.asesoria.aula.nombre : 'No asignada'"></dd></div>
                                    <div class="flex justify-between gap-3"><dt class="text-gray-500">Capacidad / tipo</dt><dd class="text-right font-medium text-gray-900" x-text="selectedBlock.asesoria.aula ? `${selectedBlock.asesoria.aula.capacidad} / ${selectedBlock.asesoria.aula.tipo}` : '—'"></dd></div>
                                    <div class="flex justify-between gap-3"><dt class="text-gray-500">Estado de la cita</dt><dd class="text-right font-medium text-gray-900" x-text="selectedBlock.asesoria.estado"></dd></div>
                                    <div><dt class="text-gray-500">Registrada</dt><dd class="font-medium text-gray-900" x-text="selectedBlock.asesoria.fecha_registro"></dd></div>
                                </dl>
                            </section>
                        </div>
                    </template>

                    <p x-cloak x-show="selectedBlock && ! selectedBlock.asesoria && selectedBlock.estado === 'Disponible'" class="rounded-lg border border-gray-200 bg-gray-100 p-4 text-sm font-medium text-gray-800">
                        Disponible (sin alumno asignado)
                    </p>

                    <p x-cloak x-show="selectedBlock && selectedBlock.ocupada && ! selectedBlock.asesoria" class="rounded-lg border border-blue-100 bg-blue-50 p-4 text-sm text-blue-900">
                        Este bloque está ocupado, pero no tiene una asesoría confirmada asociada.
                    </p>

                    <button
                        type="button"
                        x-cloak
                        x-show="selectedBlock && selectedBlock.estado === 'Disponible' && ! selectedBlock.asesoria"
                        @click="deleteModalOpen = true"
                        class="w-full rounded-lg border border-red-200 px-4 py-2 text-sm font-semibold text-red-700 transition hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500"
                    >Eliminar bloque libre</button>
                </div>
            </aside>
        </div>

        <div
            x-cloak
            x-show="deleteModalOpen"
            x-transition.opacity
            @click.self="deleteModalOpen = false"
            class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 p-4"
            role="presentation"
        >
            <section
                x-transition
                role="dialog"
                aria-modal="true"
                aria-labelledby="delete-availability-title"
                class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl"
            >
                <h2 id="delete-availability-title" class="text-lg font-bold text-gray-900">¿Eliminar este bloque libre?</h2>
                <p class="mt-2 text-sm leading-6 text-gray-600">El horario dejará de estar disponible para los alumnos. Esta acción no se puede deshacer.</p>
                <div class="mt-6 flex flex-col-reverse justify-end gap-3 sm:flex-row">
                    <button type="button" @click="deleteModalOpen = false" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Cancelar</button>
                    <form method="POST" :action="deleteAction">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="semana" value="{{ $weekStart->toDateString() }}">
                        <button type="submit" class="w-full rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 sm:w-auto">Sí, eliminar bloque</button>
                    </form>
                </div>
            </section>
        </div>
    </section>
</div>
<style>
    [x-cloak] {
        display: none !important;
    }
</style>
<script>
    window.teacherCalendarBlocks = {{ Illuminate\Support\Js::from($calendarBlocks) }};
    window.teacherCalendar = function () {
        return {
            blocks: window.teacherCalendarBlocks,
            selectedBlock: null,
            deleteModalOpen: false,
            deleteRouteTemplate: {{ Illuminate\Support\Js::from(route('profesor.disponibilidades.destroy', ['disponibilidad' => '__BLOCK_ID__'])) }},
            selectBlock(id) {
                this.selectedBlock = this.blocks.find((block) => block.id === id) || null;
                this.deleteModalOpen = false;
            },
            get deleteAction() {
                return this.selectedBlock
                    ? this.deleteRouteTemplate.replace('__BLOCK_ID__', String(this.selectedBlock.id))
                    : this.deleteRouteTemplate;
            },
        };
    };
</script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.9/dist/cdn.min.js"></script>
@endsection
