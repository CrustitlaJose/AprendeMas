<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aprende+ | @yield('title', 'Inicio')</title>
    <!-- Directiva de Vite para cargar Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gray-50 font-sans text-gray-800">
    
    <header class="border-b border-blue-800 bg-blue-900 text-white shadow-md">
        <div class="container mx-auto flex min-h-16 items-center justify-between gap-4 px-4">
            <a href="{{ auth()->user()?->rol === 'Coordinador' ? route('coordinador') : route('mis-asesorias') }}" class="text-2xl font-bold tracking-wide text-white">Aprende+</a>

            <div class="flex items-center gap-4">
                <span class="hidden text-sm text-blue-100 sm:inline">{{ auth()->user()?->rol === 'Coordinador' ? 'Portal de coordinación' : 'Portal del alumno' }}</span>
                <span class="hidden h-6 border-l border-blue-700 sm:inline"></span>
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
            <nav aria-label="Gestión de coordinación" class="border-t border-blue-800 bg-blue-900">
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
                </div>
            </nav>
        @else
            <nav aria-label="Navegación del alumno" class="border-t border-blue-800 bg-blue-900">
                <div class="container mx-auto flex gap-1 overflow-x-auto px-4">
                    <a href="{{ route('mis-asesorias') }}" class="border-b-2 border-transparent px-4 py-3 text-sm font-semibold text-blue-100 transition hover:text-white">Mis asesorías</a>
                    <a href="{{ route('disponibilidades.index') }}" class="border-b-2 border-transparent px-4 py-3 text-sm font-semibold text-blue-100 transition hover:text-white">Disponibilidades</a>
                </div>
            </nav>
        @endif
    </header>

    <main class="container mx-auto mt-6 px-4 pb-10">
        @yield('content')
    </main>

</body>
</html>