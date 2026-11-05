<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aprende+ | @yield('title', 'Inicio')</title>
    <!-- Directiva de Vite para cargar Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-800 font-sans">
    
    <!-- Navbar / Barra de navegación principal -->
    <nav class="bg-blue-900 text-white p-4 shadow-md">
        <div class="container mx-auto flex justify-between items-center">
            <a href="#" class="text-2xl font-bold tracking-wider">Aprende+</a>
            
            <div class="flex items-center space-x-6">
                <a href="{{ route('mis-asesorias') }}" class="hover:text-blue-300 transition">Mis Asesorías</a>
                <a href="#" class="hover:text-blue-300 transition">Agendar</a>
                
                <div class="border-l border-blue-700 pl-6 flex items-center">
                    <span class="mr-4 text-sm font-medium">Angel Flores Gutierrez</span>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="bg-red-500 hover:bg-red-600 px-4 py-2 rounded-md text-sm font-bold transition">
                            Salir
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <!-- Contenedor dinámico donde se inyectará cada vista -->
    <main class="container mx-auto p-4 mt-6">
        @yield('content')
    </main>

</body>
</html>