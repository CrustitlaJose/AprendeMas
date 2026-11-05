@extends('layouts.app')

@section('title', 'Agendar Asesoría')

@section('content')
<div class="max-w-5xl mx-auto">
    
    <!-- Títulos de sección -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-blue-900">Agendar asesoría</h1>
            <p class="text-sm text-gray-600">Consulta la oferta académica y reserva un horario disponible.</p>
        </div>
        <!-- Indicador visual del proceso -->
        <div class="flex items-center space-x-2 text-xs font-semibold">
            <span class="px-3 py-1 bg-blue-900 text-white rounded">1 Buscar</span>
            <span class="px-3 py-1 bg-gray-200 text-gray-600 rounded">2 Seleccionar</span>
            <span class="px-3 py-1 bg-gray-200 text-gray-600 rounded">3 Aceptar</span>
        </div>
    </div>

    <!-- Módulo de Búsqueda (Filtros CU-06 / RF-07) -->
    <div class="bg-white border border-gray-200 rounded-xl p-6 mb-8 shadow-sm">
        <h3 class="font-bold text-gray-800 mb-4 text-sm">Filtros de búsqueda</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Profesor</label>
                <select class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-900">
                    <option>Todos los profesores</option>
                    <option>Calipso</option>
                    <option>Claudia</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Materia</label>
                <select class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-900">
                    <option>Todas las materias</option>
                    <option>Matemáticas</option>
                    <option>Inglés</option>
                    <option>Programación</option>
                </select>
            </div>
            <div>
                <button class="w-full bg-blue-900 hover:bg-blue-800 text-white font-semibold py-2 px-4 rounded-lg text-sm transition shadow-sm">
                    Buscar Disponibilidad
                </button>
            </div>
        </div>
    </div>

    <!-- Resultados: Horarios Disponibles (Tarjetas / Cards) -->
    <div class="mb-4 flex justify-between items-center">
        <h3 class="font-bold text-gray-800 text-base">Horarios ofertados</h3>
        <span class="text-xs text-gray-500 font-medium">Mostrando bloques activos</span>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Tarjeta 1 -->
        <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm flex flex-col justify-between hover:shadow-md transition">
            <div>
                <div class="flex justify-between items-start mb-2">
                    <h4 class="font-bold text-gray-900 text-base">Programación</h4>
                    <span class="text-xs bg-green-50 text-green-700 px-2.5 py-1 rounded-full font-semibold">Disponible</span>
                </div>
                <p class="text-xs text-gray-600 mb-4">Prof. Calipso</p>
                <div class="text-xs text-gray-500 space-y-1.5 mb-6 bg-gray-50 p-3 rounded-lg">
                    <p>📅 Lunes</p>
                    <p class="font-medium text-gray-800">⏰ 07:00 a 09:00 hrs</p>
                    <p>📍 Aula: <span class="font-semibold text-blue-900">XA</span></p>
                </div>
            </div>
            <button onclick="simularReserva()" class="w-full bg-blue-900 hover:bg-blue-800 text-white font-semibold py-2 rounded-lg text-xs transition shadow-sm">
                Seleccionar y Agendar
            </button>
        </div>

        <!-- Tarjeta 2 -->
        <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm flex flex-col justify-between hover:shadow-md transition">
            <div>
                <div class="flex justify-between items-start mb-2">
                    <h4 class="font-bold text-gray-900 text-base">Matemáticas</h4>
                    <span class="text-xs bg-green-50 text-green-700 px-2.5 py-1 rounded-full font-semibold">Disponible</span>
                </div>
                <p class="text-xs text-gray-600 mb-4">Prof. Claudia</p>
                <div class="text-xs text-gray-500 space-y-1.5 mb-6 bg-gray-50 p-3 rounded-lg">
                    <p>📅 Miércoles</p>
                    <p class="font-medium text-gray-800">⏰ 10:00 a 12:00 hrs</p>
                    <p>📍 Aula: <span class="font-semibold text-blue-900">CC1</span></p>
                </div>
            </div>
            <button onclick="simularReserva()" class="w-full bg-blue-900 hover:bg-blue-800 text-white font-semibold py-2 rounded-lg text-xs transition shadow-sm">
                Seleccionar y Agendar
            </button>
        </div>

        <!-- Tarjeta 3 -->
        <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm flex flex-col justify-between hover:shadow-md transition">
            <div>
                <div class="flex justify-between items-start mb-2">
                    <h4 class="font-bold text-gray-900 text-base">Inglés</h4>
                    <span class="text-xs bg-green-50 text-green-700 px-2.5 py-1 rounded-full font-semibold">Disponible</span>
                </div>
                <p class="text-xs text-gray-600 mb-4">Prof. Calipso</p>
                <div class="text-xs text-gray-500 space-y-1.5 mb-6 bg-gray-50 p-3 rounded-lg">
                    <p>📅 Jueves</p>
                    <p class="font-medium text-gray-800">⏰ 12:00 a 14:00 hrs</p>
                    <p>📍 Aula: <span class="font-semibold text-blue-900">B-102</span></p>
                </div>
            </div>
            <button onclick="simularReserva()" class="w-full bg-blue-900 hover:bg-blue-800 text-white font-semibold py-2 rounded-lg text-xs transition shadow-sm">
                Seleccionar y Agendar
            </button>
        </div>
    </div>

</div>

<!-- Modal de Reserva Confirmada -->
<div id="modal-confirmacion" class="fixed inset-0 bg-black/50 hidden flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md p-6 border border-gray-100">
        <div class="flex items-center space-x-2 text-green-600 mb-3">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span class="font-bold text-sm">Validación de aula y cupo exitosa</span>
        </div>
        <h3 class="text-lg font-bold text-gray-900 mb-1">¡Asesoría Agendada!</h3>
        <p class="text-xs text-gray-500 mb-4">El sistema validó que el espacio físico está libre. Este es tu acuse de reserva.</p>
        
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 text-xs space-y-2 mb-6">
            <div class="flex justify-between items-center border-b border-gray-200 pb-2">
                <span class="font-bold text-gray-700">Folio: AP-20261104-019</span>
                <span class="px-2 py-0.5 bg-green-100 text-green-800 rounded font-semibold text-[10px]">Activa</span>
            </div>
            <div class="grid grid-cols-2 gap-2 pt-1 text-gray-600">
                <p><span class="text-gray-400">Alumno:</span> Angel Flores</p>
                <p><span class="text-gray-400">Control:</span> 23090935</p>
                <p><span class="text-gray-400">Aula Asignada:</span> XA</p>
                <p><span class="text-gray-400">Estado:</span> Confirmada</p>
            </div>
        </div>

        <div class="flex justify-end space-x-3">
            <button onclick="cerrarModal()" class="px-4 py-2 bg-blue-900 hover:bg-blue-800 text-white rounded-lg text-xs font-semibold transition shadow-sm">
                Entendido
            </button>
        </div>
    </div>
</div>

<script>
    function simularReserva() {
        document.getElementById('modal-confirmacion').classList.remove('hidden');
    }
    function cerrarModal() {
        document.getElementById('modal-confirmacion').classList.add('hidden');
        window.location.href = "{{ route('mis-asesorias') }}";
    }
</script>
@endsection