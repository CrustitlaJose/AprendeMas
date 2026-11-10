const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

function showMessage(element, message, type = 'error') {
    if (!element) {
        return;
    }

    element.textContent = message;
    element.classList.remove('hidden');
    element.classList.toggle('border-red-200', type === 'error');
    element.classList.toggle('bg-red-50', type === 'error');
    element.classList.toggle('text-red-800', type === 'error');
    element.classList.toggle('border-green-200', type === 'success');
    element.classList.toggle('bg-green-50', type === 'success');
    element.classList.toggle('text-green-800', type === 'success');
}

function clearMessage(element) {
    if (!element) {
        return;
    }

    element.textContent = '';
    element.classList.add('hidden');
}

function errorMessage(payload, status) {
    const messages = [];

    if (typeof payload?.message === 'string') {
        messages.push(payload.message);
    }

    if (payload?.errors && typeof payload.errors === 'object') {
        Object.values(payload.errors).forEach((errors) => {
            if (Array.isArray(errors)) {
                messages.push(...errors.filter((error) => typeof error === 'string'));
            } else if (typeof errors === 'string') {
                messages.push(errors);
            }
        });
    }

    if (messages.length > 0) {
        return messages.join(' ');
    }

    return `La solicitud no se pudo completar (HTTP ${status}).`;
}

async function requestJson(url, options = {}) {
    const method = options.method ?? 'GET';
    const headers = {
        Accept: 'application/json',
    };

    if (options.body !== undefined) {
        headers['Content-Type'] = 'application/json';
    }

    if (method !== 'GET') {
        if (!csrfToken) {
            throw new Error('No se encontró el token CSRF para completar la operación.');
        }

        headers['X-CSRF-TOKEN'] = csrfToken;
    }

    const response = await fetch(url, {
        method,
        headers,
        credentials: 'same-origin',
        body: options.body === undefined ? undefined : JSON.stringify(options.body),
    });
    const responseText = await response.text();
    let payload = {};

    if (responseText !== '') {
        try {
            payload = JSON.parse(responseText);
        } catch {
            if (!response.ok) {
                throw new Error(`La solicitud no se pudo completar (HTTP ${response.status}).`);
            }

            throw new Error('El servidor devolvió una respuesta que no es JSON válido.');
        }
    }

    if (!response.ok) {
        throw new Error(errorMessage(payload, response.status));
    }

    return payload;
}

function responseData(payload) {
    if (!payload || !Array.isArray(payload.data) || payload.data.some((record) => (
        !record || typeof record !== 'object' || Array.isArray(record)
    ))) {
        throw new Error('La respuesta del servidor no contiene una lista válida en "data".');
    }

    return payload.data;
}

function formatDate(value) {
    const match = /^(\d{4})-(\d{2})-(\d{2})/.exec(String(value ?? ''));

    if (!match) {
        return String(value ?? '—');
    }

    const date = new Date(Number(match[1]), Number(match[2]) - 1, Number(match[3]));

    return new Intl.DateTimeFormat('es-MX', {
        dateStyle: 'long',
    }).format(date);
}

function formatTime(value) {
    const [hours, minutes] = String(value ?? '').split(':');

    return hours && minutes ? `${hours}:${minutes}` : '—';
}

function elementWithText(tagName, text, className = '') {
    const element = document.createElement(tagName);
    element.textContent = text;

    if (className) {
        element.className = className;
    }

    return element;
}

function populateFilter(select, options, labelKey, valueKey, emptyLabel) {
    const selectedValue = select.value;
    select.replaceChildren(new Option(emptyLabel, ''));

    options.forEach((option) => {
        select.add(new Option(String(option[labelKey]), String(option[valueKey])));
    });

    if ([...select.options].some((option) => option.value === selectedValue)) {
        select.value = selectedValue;
    }
}

function initOfferPanel(root) {
    const filters = root.querySelector('[data-offer-filters]');
    const professorFilter = root.querySelector('[data-professor-filter]');
    const subjectFilter = root.querySelector('[data-subject-filter]');
    const results = root.querySelector('[data-offer-results]');
    const state = root.querySelector('[data-offer-state]');
    const count = root.querySelector('[data-offer-count]');
    const error = root.querySelector('[data-offer-error]');
    const message = root.querySelector('[data-offer-message]');
    const bookingPanel = root.querySelector('[data-booking-panel]');
    const bookingError = root.querySelector('[data-booking-error]');
    const bookingForm = root.querySelector('[data-reservation-form]');
    let teachers = [];

    function renderResults() {
        const selectedProfessor = professorFilter.value;
        const selectedSubject = subjectFilter.value;
        const slots = teachers.flatMap((teacher) => (teacher.disponibilidades ?? [])
            .filter((slot) => !selectedProfessor || String(teacher.id_profesor) === selectedProfessor)
            .filter((slot) => !selectedSubject || (teacher.materias ?? []).some((subject) => String(subject.id_materia) === selectedSubject))
            .map((slot) => ({ ...slot, teacher })));

        results.replaceChildren();
        count.textContent = `${slots.length} ${slots.length === 1 ? 'horario' : 'horarios'}`;

        if (slots.length === 0) {
            state.textContent = 'No hay horarios disponibles para los filtros seleccionados.';
            state.classList.remove('hidden');

            return;
        }

        state.classList.add('hidden');

        slots.forEach((slot) => {
            const card = document.createElement('article');
            card.className = 'flex flex-col justify-between gap-4 rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition hover:shadow-md';

            const details = document.createElement('div');
            details.append(
                elementWithText('h3', slot.teacher.titulo ?? '', 'text-lg font-semibold text-gray-900'),
                elementWithText('p', (slot.teacher.materias ?? []).map((subject) => subject.nombre_materia).filter(Boolean).join(', ') || '—', 'mt-1 text-sm text-gray-600'),
            );

            const schedule = document.createElement('dl');
            schedule.className = 'space-y-2 rounded-lg bg-gray-50 p-3 text-sm';
            [
                ['Fecha', formatDate(slot.fecha)],
                ['Horario', `${slot.hora_inicio ?? '—'} a ${slot.hora_fin ?? '—'}`],
                ['Modalidad', slot.modalidad ?? '—'],
            ].forEach(([label, value]) => {
                const row = document.createElement('div');
                row.className = 'flex flex-wrap justify-between gap-2';
                row.append(
                    elementWithText('dt', label, 'text-gray-500'),
                    elementWithText('dd', value, 'font-medium text-gray-800'),
                );
                schedule.append(row);
            });
            details.append(schedule);

            const choices = document.createElement('div');
            choices.className = 'grid gap-2';
            (slot.teacher.materias ?? [])
                .filter((subject) => !selectedSubject || String(subject.id_materia) === selectedSubject)
                .forEach((subject) => {
                    const choose = elementWithText('button', `Seleccionar ${subject.nombre_materia}`, 'w-full rounded-lg bg-[#1F4E78] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#193f61]');
                    choose.type = 'button';
                    choose.addEventListener('click', () => {
                        bookingForm.elements.id_disponibilidad.value = slot.id_disponibilidad;
                        bookingForm.elements.id_materia.value = subject.id_materia;
                        root.querySelector('[data-selected-subject]').textContent = subject.nombre_materia;
                        root.querySelector('[data-selected-slot]').textContent = `${slot.teacher.titulo} · ${formatDate(slot.fecha)} · ${slot.hora_inicio} a ${slot.hora_fin}`;
                        clearMessage(bookingError);
                        bookingPanel.hidden = false;
                        bookingPanel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    });
                    choices.append(choose);
                });

            card.append(details, choices);
            results.append(card);
        });
    }

    [professorFilter, subjectFilter].forEach((filter) => {
        filter.addEventListener('change', () => {
            bookingPanel.hidden = true;
            renderResults();
        });
    });

    filters.addEventListener('submit', (event) => {
        event.preventDefault();
        clearMessage(error);
        clearMessage(message);
        renderResults();
    });

    root.querySelector('[data-close-booking]').addEventListener('click', () => {
        bookingPanel.hidden = true;
        clearMessage(bookingError);
    });

    bookingForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        clearMessage(bookingError);

        const body = {
            id_disponibilidad: bookingForm.elements.id_disponibilidad.value,
            id_materia: bookingForm.elements.id_materia.value,
        };

        if (!body.id_disponibilidad || !body.id_materia) {
            showMessage(bookingError, 'La reserva requiere disponibilidad y materia.');

            return;
        }

        try {
            const response = await requestJson(root.dataset.bookingUrl, {
                method: 'POST',
                body,
            });
            showMessage(message, response.message ?? 'La reserva fue registrada.', 'success');
            bookingPanel.hidden = true;
            bookingForm.reset();
            document.querySelector('[data-appointments-panel]')?.dispatchEvent(new Event('appointments:reload'));
        } catch (requestError) {
            showMessage(bookingError, requestError.message);
        }
    });

    async function loadOffer() {
        state.textContent = 'Cargando oferta…';
        state.classList.remove('hidden');
        clearMessage(error);

        try {
            const payload = await requestJson(root.dataset.endpointUrl);
            teachers = responseData(payload);

            const professors = teachers.map((teacher) => ({
                id_profesor: teacher.id_profesor,
                titulo: teacher.titulo,
            }));
            const subjects = new Map();
            teachers.forEach((teacher) => {
                (teacher.materias ?? []).forEach((subject) => {
                    subjects.set(String(subject.id_materia), subject);
                });
            });

            populateFilter(professorFilter, professors, 'titulo', 'id_profesor', 'Todos los docentes');
            populateFilter(subjectFilter, [...subjects.values()], 'nombre_materia', 'id_materia', 'Todas las materias');
            renderResults();
        } catch (requestError) {
            results.replaceChildren();
            count.textContent = '';
            state.classList.add('hidden');
            showMessage(error, requestError.message);
        }
    }

    loadOffer();
}

function displayValue(value) {
    if (value === null || value === undefined || value === '') {
        return '—';
    }

    if (Array.isArray(value)) {
        return value.map(displayValue).join(', ');
    }

    if (typeof value === 'object') {
        return Object.entries(value).map(([key, nestedValue]) => `${key}: ${displayValue(nestedValue)}`).join('; ');
    }

    return String(value);
}

function appendRecordDetails(container, record) {
    const details = document.createElement('dl');
    details.className = 'grid gap-3 text-sm';

    Object.entries(record).forEach(([key, value]) => {
        const row = document.createElement('div');
        row.className = 'grid gap-1 border-b border-gray-100 pb-2 sm:grid-cols-[minmax(8rem,0.4fr)_1fr]';
        row.append(
            elementWithText('dt', key.replaceAll('_', ' '), 'font-medium capitalize text-gray-500'),
            elementWithText('dd', displayValue(value), 'break-words text-gray-900'),
        );
        details.append(row);
    });

    container.append(details);
}

function initRecordPanel(root) {
    const state = root.querySelector('[data-load-state]');
    const error = root.querySelector('[data-record-error]');
    const list = root.querySelector('[data-record-list]');

    async function loadRecords() {
        state.textContent = `Cargando ${root.dataset.recordLabel}…`;
        state.classList.remove('hidden');
        clearMessage(error);
        list.replaceChildren();

        try {
            const records = responseData(await requestJson(root.dataset.endpointUrl));
            state.textContent = records.length === 0 ? `No hay ${root.dataset.recordLabel} para mostrar.` : '';
            state.classList.toggle('hidden', records.length > 0);

            records.forEach((record) => {
                const card = document.createElement('article');
                card.className = 'space-y-4 rounded-xl border border-gray-200 bg-white p-5 shadow-sm';
                card.append(elementWithText('h3', root.dataset.recordLabel === 'disponibilidades' ? 'Disponibilidad' : 'Registro', 'font-semibold text-gray-900'));
                appendRecordDetails(card, record);
                list.append(card);
            });
        } catch (requestError) {
            state.classList.add('hidden');
            showMessage(error, requestError.message);
        }
    }

    root.querySelector('[data-reload-records]').addEventListener('click', loadRecords);
    loadRecords();
}

function appointmentCard(record) {
    const card = document.createElement('article');
    card.className = 'space-y-4 rounded-xl border border-gray-200 bg-white p-5 shadow-sm';

    const availability = record.disponibilidad ?? {};
    const teacher = availability.profesor ?? {};
    const teacherName = [teacher.nombre, teacher.apellidos].filter(Boolean).join(' ');
    const appointmentId = record.id_asesoria;
    const header = document.createElement('div');
    header.className = 'flex flex-wrap items-start justify-between gap-3';
    header.append(elementWithText(
        'h3',
        teacherName ? `Asesoría con ${teacherName}` : 'Asesoría',
        'font-semibold text-gray-900',
    ));

    const status = record.estado ?? 'Sin estado';
    const statusClass = status === 'Confirmada'
        ? 'bg-green-50 text-green-800'
        : status === 'Cancelada'
            ? 'bg-red-50 text-red-800'
            : 'bg-gray-100 text-gray-700';
    header.append(elementWithText('span', status, `rounded-full px-3 py-1 text-xs font-semibold ${statusClass}`));
    card.append(header);

    const details = document.createElement('dl');
    details.className = 'grid gap-3 text-sm sm:grid-cols-2';
    [
        ['Materia', record.materia?.nombre_materia ?? '—'],
        ['Fecha', formatDate(availability.fecha)],
        ['Horario', `${formatTime(availability.hora_inicio)} a ${formatTime(availability.hora_fin)}`],
        ['Modalidad', availability.modalidad ?? '—'],
        ['Aula', record.aula?.nombre_cubiculo ?? '—'],
    ].forEach(([label, value]) => {
        const row = document.createElement('div');
        row.className = 'rounded-lg bg-gray-50 p-3';
        row.append(
            elementWithText('dt', label, 'text-gray-500'),
            elementWithText('dd', String(value), 'mt-1 font-medium text-gray-900'),
        );
        details.append(row);
    });
    card.append(details);

    const itemError = document.createElement('p');
    itemError.className = 'hidden rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-800';
    itemError.setAttribute('role', 'alert');
    itemError.dataset.itemError = '';
    card.append(itemError);

    if (appointmentId === undefined || appointmentId === null) {
        return card;
    }

    if (status === 'Confirmada') {
        const cancel = elementWithText('button', 'Cancelar asesoría', 'rounded-lg border border-red-300 px-4 py-2 text-sm font-semibold text-red-700 transition hover:bg-red-50');
        cancel.type = 'button';
        cancel.dataset.cancelAppointment = '';
        cancel.dataset.recordId = String(appointmentId);
        card.append(cancel);
    }

    return card;
}

function initAppointments(root) {
    const state = root.querySelector('[data-appointments-state]');
    const error = root.querySelector('[data-appointments-error]');
    const message = root.querySelector('[data-appointments-message]');
    const list = root.querySelector('[data-appointments-list]');

    async function loadAppointments() {
        state.textContent = 'Cargando asesorías…';
        state.classList.remove('hidden');
        clearMessage(error);
        list.replaceChildren();

        try {
            const appointments = responseData(await requestJson(root.dataset.endpointUrl));
            state.textContent = appointments.length === 0 ? 'No tienes asesorías registradas.' : '';
            state.classList.toggle('hidden', appointments.length > 0);
            appointments.forEach((appointment) => list.append(appointmentCard(appointment)));
        } catch (requestError) {
            state.classList.add('hidden');
            showMessage(error, requestError.message);
        }
    }

    root.querySelector('[data-reload-appointments]').addEventListener('click', loadAppointments);

    root.addEventListener('appointments:reload', loadAppointments);

    root.addEventListener('click', async (event) => {
        const button = event.target.closest('[data-cancel-appointment]');

        if (!button || !window.confirm('¿Deseas cancelar esta asesoría?')) {
            return;
        }

        const card = button.closest('article');
        clearMessage(card.querySelector('[data-item-error]'));

        try {
            const response = await requestJson(`${root.dataset.endpointUrl}/${encodeURIComponent(button.dataset.recordId)}`, {
                method: 'DELETE',
            });
            showMessage(message, response.message ?? 'La asesoría fue cancelada.', 'success');
            await loadAppointments();
        } catch (requestError) {
            showMessage(card.querySelector('[data-item-error]'), requestError.message);
        }
    });

    loadAppointments();
}

document.querySelectorAll('[data-offer-panel]').forEach(initOfferPanel);
document.querySelectorAll('[data-record-panel]').forEach(initRecordPanel);
document.querySelectorAll('[data-appointments-panel]').forEach(initAppointments);
