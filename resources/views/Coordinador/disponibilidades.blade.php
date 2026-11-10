@extends('layouts.app')

@section('title', 'Supervisión de disponibilidades')

@section('content')
<div class="mx-auto max-w-7xl space-y-6">
    <header>
        <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Gestión académica</p>
        <h1 class="mt-1 text-3xl font-bold text-[#212529]">Supervisión de disponibilidades</h1>
        <p class="mt-2 text-sm text-gray-600">Consulta las disponibilidades registradas por los profesores.</p>
    </header>

    <section class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="flex items-center justify-between gap-4 border-b border-gray-200 p-5">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Disponibilidades registradas</h2>
                <p class="mt-1 text-sm text-gray-600">La información se actualiza al consultar el listado.</p>
            </div>
            <a href="{{ route('coordinador.disponibilidades.index') }}" class="rounded-lg border border-gray-300 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Actualizar</a>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-left text-sm">
                <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-600">
                    <tr>
                        <th class="px-5 py-3">Profesor</th>
                        <th class="px-5 py-3">Fecha</th>
                        <th class="px-5 py-3">Horario</th>
                        <th class="px-5 py-3">Modalidad</th>
                        <th class="px-5 py-3">Estado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($disponibilidades as $disponibilidad)
                        <tr class="transition hover:bg-gray-50">
                            <td class="whitespace-nowrap px-5 py-4 font-medium text-gray-900">
                                {{ $disponibilidad->profesor?->nombre ?? '—' }} {{ $disponibilidad->profesor?->apellidos ?? '' }}
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-gray-700">{{ $disponibilidad->fecha }}</td>
                            <td class="whitespace-nowrap px-5 py-4 text-gray-700">{{ $disponibilidad->hora_inicio }} – {{ $disponibilidad->hora_fin }}</td>
                            <td class="px-5 py-4 text-gray-700">{{ $disponibilidad->modalidad }}</td>
                            <td class="px-5 py-4">
                                <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-700">{{ $disponibilidad->estado }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-10 text-center text-gray-500">No hay disponibilidades registradas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
