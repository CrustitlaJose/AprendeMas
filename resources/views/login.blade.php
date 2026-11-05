<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Aprende +</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#F8F9FA] flex items-center justify-center h-screen">
    <div class="bg-white p-8 rounded-lg shadow-md w-96 border border-gray-200">
        <h2 class="text-2xl font-bold text-[#1F4E78] mb-6 text-center">Aprende +</h2>
        
        @if($errors->any())
            <div class="mb-4 p-3 bg-red-100 text-red-700 text-sm rounded">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('login') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Correo o Matrícula</label>
                <input type="text" name="identificador" value="{{ old('identificador') }}" required class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-[#1F4E78]" placeholder="Ej. 23090935 o correo@tec.mx">
            </div>
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2">Contraseña</label>
                <input type="password" name="password" required class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-[#1F4E78]">
            </div>
            <button type="submit" class="w-full bg-[#1F4E78] text-white font-bold py-2 px-4 rounded-lg hover:bg-blue-900 transition">
                Iniciar Sesión
            </button>
        </form>
    </div>
</body>
</html>