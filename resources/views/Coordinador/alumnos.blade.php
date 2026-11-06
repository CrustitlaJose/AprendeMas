@extends('layouts.app')

@section('title', 'Gestión de Alumnos - Coordinador')

@section('content')
<div class="max-w-6xl mx-auto">
    
    <!-- Cabecera de la sección -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-[#1F4E78]">Panel de Coordinación | Catálogo de Alumnos</h1>
            <p class="text-sm text-gray-600">Módulo administrativo para el registro, consulta, actualización y baja lógica de estudiantes (CU-10 al CU-13).</p>
        </div>
        <!-- Botón para Alta de Alumno (CU-10) -->
        <button onclick="abrirModalAlta()" class="bg-[#1F4E78] hover:bg-blue-900 text-white font-semibold py-2 px-4 rounded-lg text-sm transition shadow-sm flex items-center space-x-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>Registrar Nuevo Alumno</span>
        </button>
    </div>

    <!-- Módulo de Filtros / Consulta General (CU-12 / RF-15) -->
    <div class="bg-white border border-gray-200 rounded-xl p-6 mb-8 shadow-sm">
        <h3 class="font-bold text-gray-800 mb-4 text-sm">Consultar expedientes</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-end">
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Buscar por Matrícula o Nombre</label>
                <input type="text" placeholder="Ej. 23090935" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#1F4E78]">
            </div>
            <div>
                <button class="w-full bg-gray-800 hover:bg-gray-900 text-white font-semibold py-2 px-4 rounded-lg text-sm transition shadow-sm">
                    Buscar Alumno
                </button>
            </div>
        </div>
    </div>

    <!-- Tabla de Alumnos Registrados -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        <div class="p-4 border-b border-gray-200 flex justify-between items-center bg-gray-50">
            <h3 class="font-bold text-gray-800 text-sm">Listado general de alumnos</h3>
            <span class="text-xs text-gray-500 font-medium">Mostrando registros de la BD</span>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-left text-sm">
                <thead class="bg-gray-50 text-gray-600 font-semibold text-xs uppercase tracking-wider">
                    <tr>
                        <th class="py-3 px-6">Matrícula</th>
                        <th class="py-3 px-6">Nombre Completo</th>
                        <th class="py-3 px-6">Estado</th>
                        <th class="py-3 px-6 text-center">Acciones administrativas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 text-gray-700">
                    @forelse ($alumnos as $alumno)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="py-4 px-6 font-medium text-gray-900">{{ $alumno->matricula }}</td>
                            <td class="py-4 px-6">{{ $alumno->nombre }} {{ $alumno->apellidos }}</td>
                            <td class="py-4 px-6">
                                <span class="px-2 py-1 bg-green-50 text-green-700 rounded text-xs font-semibold">{{ $alumno->estado }}</span>
                            </td>
                            <td class="py-4 px-6 text-center space-x-2">
                                <button onclick="abrirModalEditar()" class="text-blue-600 hover:text-blue-800 font-semibold text-xs bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded transition">Editar (CU-13)</button>
                                <button onclick="abrirModalBaja()" class="text-red-600 hover:text-red-800 font-semibold text-xs bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded transition">Baja Lógica (CU-11)</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-6 px-6 text-center text-gray-500">No hay alumnos registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL: Registrar / Alta de Alumno (CU-10) -->
<div id="modal-alta" class="fixed inset-0 bg-black/50 hidden flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg p-6 border border-gray-100">
        <h3 class="text-lg font-bold text-gray-900 mb-1">Registrar Nuevo Alumno (CU-10)</h3>
        <p class="text-xs text-gray-500 mb-4">Ingresa los datos generales del estudiante correspondientes a la tabla alumno.</p>
        
        <form action="{{ route('coordinador.alumnos.store') }}" method="POST" class="space-y-4 text-xs">
            @csrf
            <div>
                <label class="block font-semibold text-gray-700 mb-1">Número de Control / Matrícula</label>
                <input type="text" name="matricula" value="{{ old('matricula') }}" required placeholder="Ej. 23090999" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1F4E78] focus:outline-none">
            </div>
            <div>
                <label class="block font-semibold text-gray-700 mb-1">Nombre(s)</label>
                <input type="text" name="nombre" value="{{ old('nombre') }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1F4E78] focus:outline-none">
            </div>
            <div>
                <label class="block font-semibold text-gray-700 mb-1">Apellidos</label>
                <input type="text" name="apellidos" value="{{ old('apellidos') }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1F4E78] focus:outline-none">
            </div>
            <div>
                <label class="block font-semibold text-gray-700 mb-1">Correo electrónico</label>
                <input type="email" name="correo" value="{{ old('correo') }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1F4E78] focus:outline-none">
            </div>
            <div>
                <label class="block font-semibold text-gray-700 mb-1">Teléfono</label>
                <input type="tel" name="telefono" value="{{ old('telefono') }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1F4E78] focus:outline-none">
            </div>
            <div class="flex justify-end space-x-3 pt-2">
                <button type="button" onclick="cerrarModalAlta()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-50 transition">Cancelar</button>
                <button type="submit" class="px-4 py-2 bg-[#1F4E78] text-white rounded-lg font-semibold hover:bg-blue-900 transition shadow-sm">Guardar Alumno</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: Editar Información (CU-13) -->
<div id="modal-editar" class="fixed inset-0 bg-black/50 hidden flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg p-6 border border-gray-100">
        <h3 class="text-lg font-bold text-gray-900 mb-1">Modificar Datos del Alumno (CU-13)</h3>
        <p class="text-xs text-gray-500 mb-4">Actualiza la información del expediente seleccionado.</p>
        
        <form class="space-y-4 text-xs">
            <div>
                <label class="block font-semibold text-gray-700 mb-1">Matrícula (No modificable)</label>
                <input type="text" value="23090935" disabled class="w-full px-3 py-2 border border-gray-200 bg-gray-100 rounded-lg text-gray-500">
            </div>
            <div>
                <label class="block font-semibold text-gray-700 mb-1">Nombre Completo</label>
                <input type="text" value="Angel Flores Gutierrez" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1F4E78] focus:outline-none">
            </div>
            <div class="flex justify-end space-x-3 pt-2">
                <button type="button" onclick="cerrarModalEditar()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-50 transition">Cancelar</button>
                <button type="button" onclick="cerrarModalEditar()" class="px-4 py-2 bg-[#1F4E78] text-white rounded-lg font-semibold hover:bg-blue-900 transition shadow-sm">Actualizar Cambios</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: Baja Lógica (CU-11) -->
<div id="modal-baja" class="fixed inset-0 bg-black/50 hidden flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md p-6 border border-gray-100">
        <div class="flex items-center space-x-2 text-red-600 mb-3">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <span class="font-bold text-sm">Confirmar Baja Lógica (CU-11)</span>
        </div>
        <h3 class="text-base font-bold text-gray-900 mb-2">¿Desactivar cuenta del estudiante?</h3>
        <p class="text-xs text-gray-600 mb-6">Esta acción aplicará una baja lógica al registro del alumno en la base de datos, bloqueando su acceso al sistema pero conservando su historial de citas.</p>
        
        <div class="flex justify-end space-x-3">
            <button onclick="cerrarModalBaja()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-xs font-semibold hover:bg-gray-50 transition">Cancelar</button>
            <button onclick="cerrarModalBaja()" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-semibold transition shadow-sm">Sí, aplicar baja lógica</button>
        </div>
    </div>
</div>

<!-- Scripts de control de modales -->
<script>
    function abrirModalAlta() { document.getElementById('modal-alta').classList.remove('hidden'); }
    function cerrarModalAlta() { document.getElementById('modal-alta').classList.add('hidden'); }

    function abrirModalEditar() { document.getElementById('modal-editar').classList.remove('hidden'); }
    function cerrarModalEditar() { document.getElementById('modal-editar').classList.add('hidden'); }

    function abrirModalBaja() { document.getElementById('modal-baja').classList.remove('hidden'); }
    function cerrarModalBaja() { document.getElementById('modal-baja').classList.add('hidden'); }
</script>
@endsection