<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Aprende+ | @yield('title', 'Inicio')</title>
    <!-- Directiva de Vite para cargar Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gray-50 font-sans text-gray-800">

    @php
        $mostrarNotificaciones = auth()->check() && auth()->user()->rol !== 'Coordinador';
        $notificaciones = $mostrarNotificaciones ? auth()->user()->notificaciones : collect();
        $notificacionesSinLeer = $notificaciones->where('leida', false)->count();
    @endphp

    <header class="border-b border-blue-800 bg-[#1e3a5f] text-white shadow-md">
        <div class="container mx-auto flex min-h-16 items-center justify-between gap-4 px-4">
            <a href="{{ match (auth()->user()?->rol) { 'Coordinador' => route('coordinador'), 'Profesor' => route('profesor.disponibilidades.index'), default => route('mis-asesorias') } }}" class="text-2xl font-bold tracking-wide text-white">Aprende+</a>

            <div class="flex items-center gap-4">
                <span class="hidden text-sm text-blue-100 sm:inline">{{ match (auth()->user()?->rol) { 'Coordinador' => 'Portal de coordinación', 'Profesor' => 'Portal docente', default => 'Portal del alumno' } }}</span>
                <span class="hidden h-6 border-l border-blue-700 sm:inline"></span>
                @if ($mostrarNotificaciones)
                    <details class="relative" data-notifications-dropdown data-list-url="{{ route('notificaciones.index') }}" data-read-url="{{ route('notificaciones.read') }}">
                        <summary class="relative flex h-10 w-10 cursor-pointer list-none items-center justify-center rounded-md text-white transition hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-white [&::-webkit-details-marker]:hidden" aria-label="Notificaciones">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17H9.143m9.714 0H5.143c1.286-1.286 2.143-2.571 2.143-7a4.714 4.714 0 0 1 9.428 0c0 4.429.857 5.714 2.143 7ZM10 20h4" />
                            </svg>
                            @if ($notificacionesSinLeer > 0)
                                <span data-notification-indicator class="absolute right-1 top-1 h-3 w-3 animate-pulse rounded-full bg-red-500 ring-2 ring-[#1e3a5f]" aria-hidden="true"></span>
                            @endif
                        </summary>
                        <div class="absolute right-0 z-50 mt-2 max-h-96 w-[min(20rem,calc(100vw-2rem))] overflow-y-auto rounded-lg border border-gray-200 bg-white text-gray-800 shadow-xl">
                            <div class="border-b border-gray-100 px-4 py-3">
                                <h2 class="text-sm font-semibold">Notificaciones</h2>
                            </div>
                            <p data-notifications-error class="hidden border-b border-red-100 bg-red-50 px-4 py-2 text-sm text-red-800" role="alert"></p>
                            <div data-notifications-list aria-live="polite">
                                @forelse ($notificaciones as $notificacion)
                                    <article @class([
                                        'border-b border-gray-100 px-4 py-3 last:border-b-0',
                                        'bg-blue-50' => ! $notificacion->leida,
                                    ])>
                                        <h3 class="text-sm font-bold">{{ $notificacion->tipo }}</h3>
                                        <p class="mt-1 text-sm text-gray-600">{{ $notificacion->mensaje }}</p>
                                        <p class="mt-2 text-xs text-gray-500">{{ $notificacion->fecha_envio->diffForHumans() }}</p>
                                    </article>
                                @empty
                                    <p class="px-4 py-6 text-center text-sm text-gray-500">Sin notificaciones</p>
                                @endforelse
                            </div>
                        </div>
                    </details>
                @endif
                <span class="hidden text-sm font-medium text-white sm:inline">{{ auth()->user()?->coordinador?->first()?->nombre ?? auth()->user()?->correo }}</span>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="rounded-md bg-red-500 px-4 py-2 text-sm font-bold text-white transition hover:bg-red-600">
                        Salir
                    </button>
                </form>
            </div>
        </div>
        @if (auth()->user()?->rol === 'Coordinador')
            <nav aria-label="Gestión de coordinación" class="border-t border-blue-800 bg-[#1e3a5f]">
                <div class="container mx-auto flex gap-1 overflow-x-auto px-4">
                    <a href="{{ route('coordinador.alumnos.index') }}" @class([
                        'border-b-2 px-4 py-3 text-sm font-semibold transition',
                        'border-white text-white' => request()->routeIs('coordinador', 'coordinador.alumnos.*'),
                        'border-transparent text-blue-100 hover:text-white' => ! request()->routeIs('coordinador', 'coordinador.alumnos.*'),
                    ])>Alumnos</a>
                    <a href="{{ route('coordinador.profesores.index') }}" @class([
                        'border-b-2 px-4 py-3 text-sm font-semibold transition',
                        'border-white text-white' => request()->routeIs('coordinador.profesores.*'),
                        'border-transparent text-blue-100 hover:text-white' => ! request()->routeIs('coordinador.profesores.*'),
                    ])>Profesores</a>
                    <a href="{{ route('coordinador.disponibilidades.index') }}" @class([
                        'border-b-2 px-4 py-3 text-sm font-semibold transition',
                        'border-white text-white' => request()->routeIs('coordinador.disponibilidades.*'),
                        'border-transparent text-blue-100 hover:text-white' => ! request()->routeIs('coordinador.disponibilidades.*'),
                    ])>Disponibilidades</a>
                </div>
            </nav>
        @elseif (auth()->user()?->rol === 'Profesor')
            <nav aria-label="Navegación docente" class="border-t border-blue-800 bg-[#1e3a5f]">
                <div class="container mx-auto flex gap-1 overflow-x-auto px-4">
                    <a href="{{ route('profesor.disponibilidades.index') }}" @class([
                        'border-b-2 px-4 py-3 text-sm font-semibold transition',
                        'border-white text-white' => request()->routeIs('profesor.disponibilidades.*'),
                        'border-transparent text-blue-100 hover:text-white' => ! request()->routeIs('profesor.disponibilidades.*'),
                    ])>Mi agenda</a>
                </div>
            </nav>
        @else
            <nav aria-label="Navegación del alumno" class="border-t border-blue-800 bg-[#1e3a5f]">
                <div class="container mx-auto flex gap-1 overflow-x-auto px-4">
                    <a href="{{ route('mis-asesorias') }}" @class([
                        'border-b-2 px-4 py-3 text-sm font-semibold transition',
                        'border-white text-white' => request()->routeIs('mis-asesorias'),
                        'border-transparent text-blue-100 hover:text-white' => ! request()->routeIs('mis-asesorias'),
                    ])>Mis asesorías</a>
                    <a href="{{ route('disponibilidades.index') }}" @class([
                        'border-b-2 px-4 py-3 text-sm font-semibold transition',
                        'border-white text-white' => request()->routeIs('disponibilidades.index'),
                        'border-transparent text-blue-100 hover:text-white' => ! request()->routeIs('disponibilidades.index'),
                    ])>Disponibilidades</a>
                </div>
            </nav>
        @endif
    </header>

    <main class="container mx-auto mt-6 px-4 pb-10">
        @yield('content')
    </main>

    <dialog data-confirmation-modal aria-labelledby="confirmation-modal-title" aria-describedby="confirmation-modal-message" class="z-50 m-auto max-h-[90vh] w-[calc(100%-2rem)] max-w-lg overflow-hidden rounded-2xl border-0 bg-transparent p-0 text-gray-800 shadow-2xl backdrop:bg-gray-900/60 backdrop:backdrop-blur-sm">
        <section class="overflow-hidden rounded-2xl bg-white shadow-2xl">
            <header class="flex items-start gap-4 bg-[#1e3a5f] px-6 py-5 text-white">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3h.008v.008H12V15.75Zm-8.25 3h16.5L12 3 3.75 18.75Z" />
                    </svg>
                </span>
                <div>
                    <h2 id="confirmation-modal-title" data-confirm-title class="text-lg font-bold">Confirmar acción</h2>
                    <p id="confirmation-modal-message" data-confirm-message class="mt-1 text-sm text-blue-100">¿Deseas continuar?</p>
                </div>
            </header>
            <footer class="flex flex-col-reverse gap-3 px-6 py-4 sm:flex-row sm:justify-end">
                <button type="button" data-confirm-cancel class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-300">
                    Regresar
                </button>
                <button type="button" data-confirm-accept class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                    Confirmar
                </button>
            </footer>
        </section>
    </dialog>

    <script src="{{ asset('js/app.js') }}" defer></script>
</body>
</html>