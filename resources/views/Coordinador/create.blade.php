@extends('layouts.app')

@section('title', 'Registrar alumno')

@section('content')
<div class="mx-auto max-w-4xl space-y-6">
    <header class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Gestión académica</p>
            <h1 class="mt-1 text-3xl font-bold text-[#212529]">Registrar alumno</h1>
            <p class="mt-2 text-sm text-gray-600">Completa los datos para crear una cuenta de alumno.</p>
        </div>
        <a href="{{ route('coordinador.alumnos.index') }}" class="text-sm font-semibold text-[#1F4E78] hover:text-[#193f61]">Volver a alumnos</a>
    </header>

    @if ($errors->any())
        <div class="rounded-lg border border-[#DC3545]/40 bg-red-50 p-4 text-sm text-red-800" role="alert">
            <p class="font-semibold">No se pudo completar el registro:</p>
            <ul class="mt-2 list-inside list-disc">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('coordinador.alumnos.store') }}" method="POST" class="grid gap-5 rounded-xl border border-gray-200 bg-white p-6 shadow-sm sm:grid-cols-2">
        @csrf
        <label class="text-sm font-medium text-gray-700">Matrícula
            <input name="matricula" value="{{ old('matricula') }}" required class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2" autocomplete="off">
        </label>
        <label class="text-sm font-medium text-gray-700">Correo
            <input type="email" name="correo" value="{{ old('correo') }}" required class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2" autocomplete="email">
        </label>
        <label class="text-sm font-medium text-gray-700">Nombre
            <input name="nombre" value="{{ old('nombre') }}" required class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2" autocomplete="given-name">
        </label>
        <label class="text-sm font-medium text-gray-700">Apellidos
            <input name="apellidos" value="{{ old('apellidos') }}" required class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2" autocomplete="family-name">
        </label>
        <label class="text-sm font-medium text-gray-700">Teléfono
            <input type="tel" name="telefono" value="{{ old('telefono') }}" required class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2" autocomplete="tel">
        </label>
        <p class="self-end text-xs text-gray-500">Se generará una contraseña temporal, que se mostrará una sola vez al guardar.</p>
        <div class="flex justify-end gap-3 border-t border-gray-100 pt-5 sm:col-span-2">
            <a href="{{ route('coordinador.alumnos.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Cancelar</a>
            <button type="submit" class="rounded-lg bg-[#1F4E78] px-5 py-2 text-sm font-semibold text-white transition hover:bg-[#193f61]">Guardar alumno</button>
        </div>
    </form>
</div>
@endsection
