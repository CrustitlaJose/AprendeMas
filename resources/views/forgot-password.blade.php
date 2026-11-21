<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar contraseña | Aprende+</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="bg-white p-8 rounded-xl shadow-lg w-full max-w-md border border-gray-200">
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-blue-900 mb-2">Aprende+</h1>
            <p class="text-gray-500">Recupera tu contraseña</p>
        </div>

        @if (session('enlace_reset'))
            <div class="mb-5 rounded-lg bg-green-50 border border-green-200 p-4 text-sm text-green-800">
                <a href="{{ session('enlace_reset') }}" class="block font-semibold underline break-all">
                    {{ session('enlace_reset') }}
                </a>
                <p class="mt-3 text-xs text-gray-600">
                    El enlace solo se muestra en este apartado para las pruebas; el envío de correos no está contemplado en esta fase.
                </p>
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-5 rounded-lg bg-red-50 border border-red-200 p-3 text-sm text-red-800">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form action="{{ route('password.email') }}" method="POST">
            @csrf
            <div class="mb-6">
                <label class="block text-gray-700 font-semibold mb-2">Correo registrado</label>
                <input type="email" name="correo" value="{{ old('correo') }}" required
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-900">
            </div>

            <button type="submit" class="w-full bg-blue-900 hover:bg-blue-800 text-white font-bold py-3 px-4 rounded-lg transition duration-200 shadow-md">
                Generar enlace
            </button>
        </form>

        <p class="mt-6 text-center text-sm">
            <a href="{{ route('login') }}" class="text-blue-900 hover:underline">Volver al inicio de sesión</a>
        </p>
    </div>
</body>
</html>