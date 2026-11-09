<section id="oferta" class="mx-auto max-w-7xl space-y-6" data-offer-panel data-endpoint-url="{{ route('disponibilidades.index') }}" data-booking-url="{{ url('/alumno/asesorias') }}">
    <header class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Oferta académica</p>
            <h1 class="mt-1 text-3xl font-bold text-[#212529]">Agendar asesoría</h1>
            <p class="mt-2 text-sm text-gray-600">Consulta docentes, materias y horarios disponibles.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2 text-xs font-semibold" aria-label="Proceso de reserva en tres pasos">
            <span class="rounded bg-[#1F4E78] px-3 py-1 text-white">1 Buscar</span>
            <span class="rounded bg-gray-200 px-3 py-1 text-gray-600">2 Seleccionar</span>
            <span class="rounded bg-gray-200 px-3 py-1 text-gray-600">3 Confirmar</span>
        </div>
    </header>

    <div data-offer-error class="hidden rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800" role="alert"></div>
    <div data-offer-message class="hidden rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-800" role="status"></div>

    <form data-offer-filters class="grid gap-4 rounded-xl border border-gray-200 bg-white p-5 shadow-sm sm:grid-cols-2 lg:items-end">
        <label class="text-sm font-medium text-gray-700">
            Docente
            <select data-professor-filter class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                <option value="">Todos los docentes</option>
            </select>
        </label>
        <label class="text-sm font-medium text-gray-700">
            Materia
            <select data-subject-filter class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                <option value="">Todas las materias</option>
            </select>
        </label>
    </form>

    <section aria-labelledby="horarios-disponibles">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <h2 id="horarios-disponibles" class="text-lg font-semibold text-gray-900">Horarios disponibles</h2>
            <p data-offer-count class="text-sm text-gray-500" aria-live="polite"></p>
        </div>
        <p data-offer-state class="text-sm text-gray-500" role="status">Cargando oferta…</p>
        <div data-offer-results class="grid gap-4 md:grid-cols-2 xl:grid-cols-3"></div>
    </section>

    <section data-booking-panel hidden class="space-y-4 rounded-xl border border-gray-200 bg-white p-5 shadow-sm" aria-labelledby="booking-title">
        <div>
            <h2 id="booking-title" class="text-lg font-semibold text-gray-900">Confirmar reserva</h2>
            <p data-selected-slot class="mt-1 text-sm text-gray-600"></p>
        </div>
        <div data-booking-error class="hidden rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800" role="alert"></div>
        <form data-reservation-form class="grid gap-4 sm:grid-cols-2">
            <input type="hidden" name="id_disponibilidad">
            <input type="hidden" name="id_materia">
            <p class="text-sm text-gray-700">Materia seleccionada: <span data-selected-subject class="font-semibold"></span></p>
            <label class="text-sm font-medium text-gray-700">
                Aula
                <select name="id_aula" disabled class="mt-1 w-full rounded-lg border border-gray-300 bg-gray-100 px-3 py-2 text-sm">
                    <option value="">Catálogo de aulas no disponible</option>
                </select>
            </label>
            <p class="text-sm text-amber-800 sm:col-span-2" role="status">No se puede confirmar la reserva hasta que el sistema proporcione las aulas disponibles. No se asignará un aula a una disponibilidad docente.</p>
            <div class="flex flex-wrap gap-3 sm:col-span-2">
                <button type="submit" disabled class="rounded-lg bg-[#1F4E78] px-4 py-2 text-sm font-semibold text-white opacity-50">Confirmar reserva</button>
                <button type="button" data-close-booking class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Cerrar</button>
            </div>
        </form>
    </section>
</section>
