@extends('layouts.app')

@section('title', 'Mis asesorías')

@section('content')
    @include('partials.oferta-asesorias')

    <section id="citas" class="mx-auto mt-10 max-w-7xl space-y-4" data-appointments-panel data-endpoint-url="{{ url('/alumno/asesorias') }}">
        <header class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Seguimiento</p>
                <h2 class="mt-1 text-2xl font-bold text-[#212529]">Mis asesorías</h2>
                <p class="mt-2 text-sm text-gray-600">Consulta, actualiza o cancela tus citas.</p>
            </div>
            <button type="button" data-reload-appointments class="rounded-lg border border-gray-300 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Actualizar</button>
        </header>
        <p data-appointments-state class="text-sm text-gray-500" role="status">Cargando asesorías…</p>
        <div data-appointments-error class="hidden rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800" role="alert"></div>
        <div data-appointments-message class="hidden rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-800" role="status"></div>
        <div data-appointments-list class="grid gap-4 md:grid-cols-2 xl:grid-cols-3"></div>
    </section>
@endsection
