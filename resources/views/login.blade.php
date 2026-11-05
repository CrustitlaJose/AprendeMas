<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión | Aprende+</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    
    <div class="bg-white p-8 rounded-xl shadow-lg w-full max-w-md border border-gray-200">
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-blue-900 mb-2">Aprende+</h1>
            <p class="text-gray-500">Centro de Asesorías ITZ</p>
        </div>

        <form action="{{ route('login.post') }}" method="POST">
            @csrf
            
            <div class="mb-5">
                <label class="block text-gray-700 font-semibold mb-2">Número de Control / Usuario</label>
                <input type="text" name="identificador" value="23090935" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-900 focus:border-transparent transition" placeholder="Ej. 23090935">
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 font-semibold mb-2">Contraseña</label>
                <input type="password" name="password" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-900 focus:border-transparent transition" placeholder="********">
            </div>

            <button type="submit" class="w-full bg-blue-900 hover:bg-blue-800 text-white font-bold py-3 px-4 rounded-lg transition duration-200 shadow-md">
                Ingresar al Sistema
            </button>
        </form>
    </div>

</body>
</html>