<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer contraseña | Aprende+</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="bg-white p-8 rounded-xl shadow-lg w-full max-w-md border border-gray-200">
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-blue-900 mb-2">Aprende+</h1>
            <p class="text-gray-500">Crea tu nueva contraseña</p>
        </div>

        @if ($errors->any())
            <div class="mb-5 rounded-lg bg-red-50 border border-red-200 p-3 text-sm text-red-800">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form action="{{ route('password.update') }}" method="POST">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div class="mb-5">
                <label class="block text-gray-700 font-semibold mb-2">Correo</label>
                <input type="email" name="correo" value="{{ old('correo', $correo) }}" readonly
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-50">
            </div>

            <div class="mb-5">
                <label class="block text-gray-700 font-semibold mb-2">Nueva contraseña</label>
                <input type="password" name="password" required minlength="8"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-900">
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 font-semibold mb-2">Confirmar contraseña</label>
                <input type="password" name="password_confirmation" required minlength="8"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-900">
            </div>

            <button type="submit" class="w-full bg-blue-900 hover:bg-blue-800 text-white font-bold py-3 px-4 rounded-lg transition duration-200 shadow-md">
                Guardar contraseña
            </button>
        </form>
    </div>
</body>
</html>