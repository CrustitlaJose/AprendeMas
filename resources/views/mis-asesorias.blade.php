<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Asesorías Agendadas - Aprende +</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#F8F9FA] text-[#212529] font-sans">

    <!-- Header institucional -->
    <header class="bg-white border-b border-gray-200 px-8 py-4 flex justify-between items-center shadow-sm">
        <div class="flex items-center space-x-2">
            <span class="text-xl font-bold text-[#1F4E78]">Aprende +</span>
        </div>
        <span class="text-sm text-gray-600">Portal del alumno</span>
    </header>

    <main class="max-w-6xl mx-auto px-6 py-8">
        <!-- Títulos de sección -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-[#1F4E78]">Mis Asesorías Agendadas</h1>
                <p class="text-sm text-gray-600">Consulta tus citas activas y solicita una cancelación cuando el plazo lo permita.</p>
            </div>
            <span class="text-xs text-gray-500 font-semibold tracking-wide uppercase">Citas agendadas</span>
        </div>

        <!-- Tarjeta de referencia temporal -->
        <div class="bg-white border border-gray-200 rounded-lg p-4 mb-6 shadow-sm">
            <div class="flex justify-between items-center text-sm">
                <span class="font-medium text-gray-700">Fecha y hora de referencia: 23 oct 2026, 09:10 h</span>
                <span class="text-gray-500 text-xs">Hora local del centro</span>
            </div>
            <p class="text-xs text-gray-500 mt-1">Solo puedes cancelar con al menos 12 horas de anticipación al inicio de la asesoría.</p>
        </div>

        <!-- Alerta de éxito (Oculta por defecto, se puede mostrar al cancelar) -->
        <div id="toast-success" class="hidden mb-6 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm flex items-center justify-between">
            <span><strong>Cita cancelada correctamente. El cupo ha sido liberado.</strong> Física · Daniel Ruiz · 24 oct 2026 · 12:00–13:00 h · Aula C-102</span>
        </div>

        <!-- Tabla de citas activas -->
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center bg-gray-50">
                <h3 class="font-bold text-gray-800">Citas activas</h3>
                <span class="text-xs text-gray-500" id="contador-citas">3 citas agendadas</span>
            </div>

            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-gray-200 text-xs text-gray-500 uppercase tracking-wider bg-gray-50/50">
                        <th class="py-3 px-6">Materia</th>
                        <th class="py-3 px-6">Profesor</th>
                        <th class="py-3 px-6">Fecha</th>
                        <th class="py-3 px-6">Hora</th>
                        <th class="py-3 px-6">Aula</th>
                        <th class="py-3 px-6">Tiempo restante</th>
                        <th class="py-3 px-6 text-center">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 text-sm">
                    <!-- Fila 1: Menos de 12 horas (Deshabilitada por RN-01) -->
                    <tr class="bg-gray-50/60">
                        <td class="py-4 px-6 font-semibold text-gray-900">
                            Matemáticas
                            <div class="text-xs text-red-600 font-normal mt-0.5">Fuera de plazo: No es posible cancelar con menos de 12 horas de anticipación</div>
                        </td>
                        <td class="py-4 px-6 text-gray-600">Laura Martínez</td>
                        <td class="py-4 px-6 text-gray-600">23 oct 2026</td>
                        <td class="py-4 px-6 text-gray-600">16:00–17:00 h</td>
                        <td class="py-4 px-6 text-gray-600">B-204</td>
                        <td class="py-4 px-6 text-gray-600 font-medium">6 h 50 min</td>
                        <td class="py-4 px-6 text-center">
                            <button disabled class="px-4 py-1.5 bg-gray-200 text-gray-400 rounded cursor-not-allowed text-xs font-semibold">Cancelar</button>
                        </td>
                    </tr>

                    <!-- Fila 2: Válida para cancelar (>= 12 horas) -->
                    <tr id="fila-fisica" class="hover:bg-gray-50 transition">
                        <td class="py-4 px-6 font-semibold text-gray-900">Física</td>
                        <td class="py-4 px-6 text-gray-600">Daniel Ruiz</td>
                        <td class="py-4 px-6 text-gray-600">24 oct 2026</td>
                        <td class="py-4 px-6 text-gray-600">12:00–13:00 h</td>
                        <td class="py-4 px-6 text-gray-600">C-102</td>
                        <td class="py-4 px-6 text-gray-600 font-medium">26 h 50 min</td>
                        <td class="py-4 px-6 text-center">
                            <button onclick="abrirModal()" class="px-4 py-1.5 border border-[#1F4E78] text-[#1F4E78] hover:bg-[#1F4E78] hover:text-white rounded text-xs font-semibold transition">Cancelar</button>
                        </td>
                    </tr>

                    <!-- Fila 3: Válida para cancelar -->
                    <tr class="hover:bg-gray-50 transition">
                        <td class="py-4 px-6 font-semibold text-gray-900">Química</td>
                        <td class="py-4 px-6 text-gray-600">Ana López</td>
                        <td class="py-4 px-6 text-gray-600">26 oct 2026</td>
                        <td class="py-4 px-6 text-gray-600">10:00–11:00 h</td>
                        <td class="py-4 px-6 text-gray-600">A-101</td>
                        <td class="py-4 px-6 text-gray-600 font-medium">72 h 50 min</td>
                        <td class="py-4 px-6 text-center">
                            <button class="px-4 py-1.5 border border-[#1F4E78] text-[#1F4E78] hover:bg-[#1F4E78] hover:text-white rounded text-xs font-semibold transition">Cancelar</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="mt-4 text-xs text-gray-500">
            Las citas se mantienen activas hasta que confirmes su cancelación.
        </div>
    </main>

    <!-- Modal de Confirmación de Cancelación -->
    <div id="modal-cancelar" class="fixed inset-0 bg-black/50 hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6 border border-gray-200">
            <h3 class="text-lg font-bold text-gray-900 mb-2">¿Deseas cancelar la asesoría de Física?</h3>
            <p class="text-xs text-gray-600 mb-4">Al confirmar, la cita dejará de estar activa y el cupo se liberará automáticamente.</p>
            
            <div class="bg-gray-50 p-4 rounded-lg text-xs space-y-1 mb-6 border border-gray-100">
                <p class="font-bold text-gray-800">Física · Daniel Ruiz</p>
                <p class="text-gray-600">24 oct 2026 · 12:00–13:00 h · Aula C-102</p>
                <hr class="my-2 border-gray-200">
                <p class="font-semibold text-gray-900">Faltan 26 h 50 min · Cumple el mínimo de 12 horas</p>
                <p class="text-gray-500">Referencia: 23 oct 2026, 09:10 h · Hora local del centro</p>
            </div>

            <div class="flex justify-end space-x-3">
                <button onclick="cerrarModal()" class="px-4 py-2 border border-gray-300 text-gray-700 hover:bg-gray-100 rounded text-xs font-semibold transition">Regresar</button>
                <button onclick="confirmarCancelacion()" class="px-4 py-2 bg-[#1F4E78] hover:bg-blue-900 text-white rounded text-xs font-semibold transition">Sí, cancelar</button>
            </div>
        </div>
    </div>

    <!-- Script de interactividad básica para simular la regla del prototipo -->
    <script>
        function abrirModal() {
            document.getElementById('modal-cancelar').classList.remove('hidden');
        }
        function cerrarModal() {
            document.getElementById('modal-cancelar').classList.add('hidden');
        }
        function confirmarCancelacion() {
            cerrarModal();
            // Oculta la fila de física y muestra la alerta de éxito
            document.getElementById('fila-fisica').style.display = 'none';
            document.getElementById('toast-success').classList.remove('hidden');
            document.getElementById('contador-citas').innerText = '2 citas agendadas';
        }
    </script>
</body>
</html>