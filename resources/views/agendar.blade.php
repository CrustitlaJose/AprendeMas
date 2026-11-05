@extends('layouts.app')

@section('title', 'Agendar Asesoría')

@section('content')
<div class="max-w-6xl mx-auto">
    
    <!-- Títulos de sección -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-[#1F4E78]">Agendar asesoría</h1>
            <p class="text-sm text-gray-600">Consulta la oferta académica y reserva un horario disponible.</p>
        </div>
        <!-- Pasos del proceso (Indicador visual de 3 clics) -->
        <div class="flex items-center space-x-2 text-xs font-semibold">
            <span class="px-3 py-1 bg-[#1F4E78] text-white rounded">1 Buscar</span>
            <span class="px-3 py-1 bg-gray-200 text-gray-600 rounded">2 Seleccionar</span>
            <span class="px-3 py-1 bg-gray-200 text-gray-600 rounded">3 Aceptar</span>
            <span class="text-gray-400 font-normal ml-2">Reserva en máximo 3 clics</span>
        </div>
    </div>

    <!-- Módulo de Búsqueda (Filtros CU-06 / RF-07) -->
    <div class="bg-white border border-gray-200 rounded-lg p-6 mb-8 shadow-sm">
        <h3 class="font-bold text-gray-800 mb-4 text-sm">Buscar asesoría</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Profesor</label>
                <select class="w-full px-3 py-2 border border-gray-300 rounded text-sm focus:outline-none focus:ring-2 focus:ring-[#1F4E78]">
                    <option>Todos los profesores</option>
                    <option>Calipso</option>
                    <option>Claudia</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Materia</label>
                <select class="w-full px-3 py-2 border border-gray-300 rounded text-sm focus:outline-none focus:ring-2 focus:ring-[#1F4E78]">
                    <option>Matemáticas</option>
                    <option>Inglés</option>
                    <option>Programación</option>
                </select>
            </div>
            <div>
                <button class="w-full bg-[#1F4E78] hover:bg-blue-900 text-white font-semibold py-2 px-4 rounded text-sm transition">
                    Buscar
                </button>
            </div>
        </div>
    </div>

    <!-- Alerta de Colisión / Error de Aula (Flujo alterno A1) -->
    <div id="alerta-error" class="hidden mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm flex items-center space-x-3">
        <svg class="w-5 h-5 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        <span><strong>El horario seleccionado ya no está disponible o el aula está ocupada.</strong> Selecciona otro horario disponible en los resultados.</span>
    </div>

    <!-- Resultados: Horarios Disponibles (Tarjetas / Cards) -->
    <div class="mb-4 flex justify-between items-center">
        <h3 class="font-bold text-gray-800 text-base">Horarios disponibles</h3>
        <span class="text-xs text-gray-500 font-medium">3 horarios · Oferta académica</span>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Tarjeta 1 -->
        <div class="bg-white border border-gray-200 rounded-lg p-5 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex justify-between items-start mb-2">
                    <h4 class="font-bold text-gray-900 text-lg">Programación</h4>
                    <span class="text-xs bg-green-50 text-green-700 px-2 py-0.5 rounded font-semibold">Disponible</span>
                </div>
                <p class="text-xs text-gray-600 mb-4">Calipso</p>
                <div class="text-xs text-gray-500 space-y-1 mb-6">
                    <p>Lunes, 2 de noviembre de 2026</p>
                    <p class="font-medium text-gray-800">Inicio: 07:00 &nbsp;&nbsp; Fin: 09:00</p>
                    <p>Modalidad: Presencial</p>
                </div>
            </div>
            <button onclick="simularReserva()" class="w-full bg-[#1F4E78] hover:bg-blue-900 text-white font-semibold py-2 rounded text-xs transition">
                Seleccionar
            </button>
        </div>

        <!-- Tarjeta 2 -->
        <div class="bg-white border border-gray-200 rounded-lg p-5 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex justify-between items-start mb-2">
                    <h4 class="font-bold text-gray-900 text-lg">Matemáticas</h4>
                    <span class="text-xs bg-green-50 text-green-700 px-2 py-0.5 rounded font-semibold">Disponible</span>
                </div>
                <p class="text-xs text-gray-600 mb-4">Claudia</p>
                <div class="text-xs text-gray-500 space-y-1 mb-6">
                    <p>Miércoles, 4 de noviembre de 2026</p>
                    <p class="font-medium text-gray-800">Inicio: 10:00 &nbsp;&nbsp; Fin: 12:00</p>
                    <p>Modalidad: Presencial</p>
                </div>
            </div>
            <button onclick="simularReserva()" class="w-full bg-[#1F4E78] hover:bg-blue-900 text-white font-semibold py-2 rounded text-xs transition">
                Seleccionar
            </button>
        </div>

        <!-- Tarjeta 3 -->
        <div class="bg-white border border-gray-200 rounded-lg p-5 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex justify-between items-start mb-2">
                    <h4 class="font-bold text-gray-900 text-lg">Inglés</h4>
                    <span class="text-xs bg-green-50 text-green-700 px-2 py-0.5 rounded font-semibold">Disponible</span>
                </div>
                <p class="text-xs text-gray-600 mb-4">Calipso</p>
                <div class="text-xs text-gray-500 space-y-1 mb-6">
                    <p>Jueves, 5 de noviembre de 2026</p>
                    <p class="font-medium text-gray-800">Inicio: 12:00 &nbsp;&nbsp; Fin: 14:00</p>
                    <p>Modalidad: En línea</p>
                </div>
            </div>
            <button onclick="simularReserva()" class="w-full bg-[#1F4E78] hover:bg-blue-900 text-white font-semibold py-2 rounded text-xs transition">
                Seleccionar
            </button>
        </div>
    </div>

    <div class="mt-8 text-xs text-gray-500">
        Consulta: 4 nov 2026, 21:35 h
    </div>
</div>

<!-- Modal de Reserva Confirmada (Aparece tras seleccionar) -->
<div id="modal-confirmacion" class="fixed inset-0 bg-black/50 hidden flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-lg p-6 border border-gray-200">
        <div class="flex items-center space-x-2 text-green-600 mb-3">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span class="font-bold text-sm">Asesoría agendada exitosamente</span>
        </div>
        <h3 class="text-lg font-bold text-gray-900 mb-1">Reserva confirmada</h3>
        <p class="text-xs text-gray-600 mb-4">El aula está disponible y tu asesoría quedó registrada. Este es el acuse de tu reserva.</p>
        
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 text-xs space-y-2 mb-6">
            <div class="flex justify-between items-center border-b pb-2">
                <span class="font-bold text-gray-700">Acuse de reserva</span>
                <span class="px-2 py-0.5 bg-green-100 text-green-800 rounded font-semibold text-[10px]">Confirmada</span>
            </div>
            <div class="grid grid-cols-2 gap-2 pt-1">
                <p><span class="text-gray-500">Folio:</span> AP-20261104-014</p>
                <p><span class="text-gray-500">Materia:</span> Programación</p>
                <p><span class="text-gray-500">Profesor:</span> Calipso</p>
                <p><span class="text-gray-500">Día:</span> 2 nov 2026</p>
                <p><span class="text-gray-500">Horario:</span> 07:00 a 09:00 h</p>
                <p><span class="text-gray-500">Modalidad:</span> Presencial</p>
                <p class="col-span-2 font-semibold text-[#1F4E78]"><span class="text-gray-500 font-normal">Aula asignada:</span> XA</p>
            </div>
        </div>

        <div class="flex justify-end space-x-3">
            <button onclick="cerrarModal()" class="px-4 py-2 border border-gray-300 text-gray-700 hover:bg-gray-100 rounded text-xs font-semibold transition">Cancelar</button>
            <button onclick="cerrarModal()" class="px-4 py-2 bg-[#1F4E78] hover:bg-blue-900 text-white rounded text-xs font-semibold transition">Aceptar</button>
        </div>
    </div>
</div>

<!-- Script para simular el comportamiento visual de confirmación -->
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