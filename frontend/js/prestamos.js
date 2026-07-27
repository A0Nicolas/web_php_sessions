const API = 'backend/api_prestamos.php';

let prestamosData = [];
let sociosList = [];
let ejemplaresDisponibles = [];
let socioSeleccionado = null;
let ejemplarSeleccionado = null;

document.addEventListener('DOMContentLoaded', () => {
    cargarPrestamos();
    cargarSociosList();
    cargarEjemplaresDisponibles();
    document.getElementById('form-filtros').addEventListener('submit', (e) => { e.preventDefault(); cargarPrestamosConFiltros(); });
});

async function cargarSociosList() {
    const res = await fetch('backend/api_socios.php?action=todos');
    const data = await res.json();
    if (data.success) sociosList = data.data;
}

async function cargarEjemplaresDisponibles() {
    const res = await fetch('backend/api_ejemplares.php?action=disponibles');
    const data = await res.json();
    if (data.success) ejemplaresDisponibles = data.data;
}

function limpiarFiltros() {
    document.getElementById('filtro-fecha-inicio').value = '';
    document.getElementById('filtro-fecha-fin').value = '';
    document.getElementById('filtro-socio').value = '';
    document.getElementById('filtro-estado').value = '';
    cargarPrestamos();
}

async function cargarPrestamosConFiltros() {
    const fi = document.getElementById('filtro-fecha-inicio').value;
    const ff = document.getElementById('filtro-fecha-fin').value;
    const socio = document.getElementById('filtro-socio').value.trim();
    const estado = document.getElementById('filtro-estado').value;
    let url = API + '?action=listar';
    if (fi) url += '&fecha_inicio=' + fi;
    if (ff) url += '&fecha_fin=' + ff;
    if (socio) url += '&socio=' + encodeURIComponent(socio);
    if (estado) url += '&estado=' + estado;
    const tbody = document.getElementById('cuerpo-tabla');
    try {
        const res = await fetch(url);
        const data = await res.json();
        renderTabla(data.success ? data.data : []);
    } catch (e) {
        tbody.innerHTML = '<tr><td colspan="8" class="text-center text-red-500 py-8">Error al cargar</td></tr>';
    }
}

async function cargarPrestamos() {
    const tbody = document.getElementById('cuerpo-tabla');
    try {
        const res = await fetch(API + '?action=listar');
        const data = await res.json();
        if (!data.success || data.data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8" class="text-center text-slate-400 py-8">No hay prestamos registrados</td></tr>';
            return;
        }
        prestamosData = data.data;
        renderTabla(data.data);
    } catch (e) {
        tbody.innerHTML = '<tr><td colspan="8" class="text-center text-red-500 py-8">Error al cargar</td></tr>';
    }
}

function renderTabla(data) {
    const tbody = document.getElementById('cuerpo-tabla');
    if (data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" class="text-center text-slate-400 py-8">No se encontraron resultados</td></tr>';
        return;
    }
    prestamosData = data;
    tbody.innerHTML = data.map(p => `
        <tr>
            <td class="font-semibold text-indigo-700">#${p.id}</td>
            <td>
                <div class="font-medium">${escapeHtml(p.socio_nombre)}</div>
                <div class="text-xs text-slate-400">${escapeHtml(p.socio_cedula)}</div>
            </td>
            <td>
                <div class="font-medium">${escapeHtml(p.libro_titulo)}</div>
                <div class="text-xs text-slate-400 font-mono">${escapeHtml(p.codigo_ejemplar)}</div>
            </td>
            <td>${formatDate(p.fecha_prestamo)}</td>
            <td>${formatDate(p.fecha_devolucion_esperada)}</td>
            <td>${p.fecha_devolucion_real ? formatDate(p.fecha_devolucion_real) : '<span class="text-slate-400">-</span>'}</td>
            <td>${badgeEstado(p.estado, ESTADO_PRESTAMO)}</td>
            <td>
                <div class="actions-cell">
                    ${p.estado === 'Activo' ? `<button class="btn-icon bg-emerald-100 text-emerald-700 hover:bg-emerald-200" onclick="devolverPrestamo(${p.id}, '${escapeHtml(p.socio_nombre).replace(/'/g, "\\'")}')" title="Devolver">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </button>` : ''}
                </div>
            </td>
        </tr>
    `).join('');
}

function renderDropdownSocio(query) {
    const dd = document.getElementById('dropdown-socio');
    const q = query.toLowerCase();
    const filtered = sociosList.filter(s =>
        s.nombre_completo.toLowerCase().includes(q) || s.cedula.includes(q)
    );
    if (filtered.length === 0) {
        dd.innerHTML = '<div class="ss-empty">No se encontraron socios</div>';
        return;
    }
    dd.innerHTML = filtered.map(s => `
        <div class="ss-item" data-id="${s.id}" onclick="seleccionarSocio(${s.id}, '${escapeHtml(s.nombre_completo).replace(/'/g, "\\'")}', '${escapeHtml(s.cedula).replace(/'/g, "\\'")}')">
            <div class="font-medium">${escapeHtml(s.nombre_completo)}</div>
            <div class="ss-item-sub">CI: ${escapeHtml(s.cedula)}</div>
        </div>
    `).join('');
}

function renderDropdownEjemplar(query) {
    const dd = document.getElementById('dropdown-ejemplar');
    const q = query.toLowerCase();
    const filtered = ejemplaresDisponibles.filter(e =>
        e.codigo_ejemplar.toLowerCase().includes(q) || e.titulo.toLowerCase().includes(q)
    );
    if (filtered.length === 0) {
        dd.innerHTML = '<div class="ss-empty">No hay ejemplares disponibles</div>';
        return;
    }
    dd.innerHTML = filtered.map(e => `
        <div class="ss-item" data-id="${e.id}" onclick="seleccionarEjemplar(${e.id}, '${escapeHtml(e.codigo_ejemplar).replace(/'/g, "\\'")}', '${escapeHtml(e.titulo).replace(/'/g, "\\'")}')">
            <div class="font-medium">${escapeHtml(e.codigo_ejemplar)}</div>
            <div class="ss-item-sub">${escapeHtml(e.titulo)}</div>
        </div>
    `).join('');
}

function seleccionarSocio(id, nombre, cedula) {
    socioSeleccionado = { id, nombre_completo: nombre, cedula };
    document.getElementById('pre-socio').value = id;
    const wrapper = document.getElementById('wrapper-socio');
    document.getElementById('pre-socio-search').style.display = 'none';
    document.getElementById('dropdown-socio').classList.remove('open');
    const tag = document.createElement('div');
    tag.className = 'ss-selected';
    tag.innerHTML = `<span>${escapeHtml(nombre)} (${escapeHtml(cedula)})</span><span class="ss-remove" onclick="limpiarSocio()">&times;</span>`;
    wrapper.appendChild(tag);
}

function seleccionarEjemplar(id, codigo, titulo) {
    ejemplarSeleccionado = { id, codigo_ejemplar: codigo, titulo };
    document.getElementById('pre-ejemplar').value = id;
    const wrapper = document.getElementById('wrapper-ejemplar');
    document.getElementById('pre-ejemplar-search').style.display = 'none';
    document.getElementById('dropdown-ejemplar').classList.remove('open');
    const tag = document.createElement('div');
    tag.className = 'ss-selected';
    tag.innerHTML = `<span>${escapeHtml(codigo)} - ${escapeHtml(titulo)}</span><span class="ss-remove" onclick="limpiarEjemplar()">&times;</span>`;
    wrapper.appendChild(tag);
}

function limpiarSocio() {
    socioSeleccionado = null;
    document.getElementById('pre-socio').value = '';
    const wrapper = document.getElementById('wrapper-socio');
    const tag = wrapper.querySelector('.ss-selected');
    if (tag) tag.remove();
    const search = document.getElementById('pre-socio-search');
    search.value = '';
    search.style.display = '';
}

function limpiarEjemplar() {
    ejemplarSeleccionado = null;
    document.getElementById('pre-ejemplar').value = '';
    const wrapper = document.getElementById('wrapper-ejemplar');
    const tag = wrapper.querySelector('.ss-selected');
    if (tag) tag.remove();
    const search = document.getElementById('pre-ejemplar-search');
    search.value = '';
    search.style.display = '';
}

function abrirModal() {
    socioSeleccionado = null;
    ejemplarSeleccionado = null;
    document.getElementById('pre-socio').value = '';
    document.getElementById('pre-ejemplar').value = '';

    const wSocio = document.getElementById('wrapper-socio');
    const wEjemplar = document.getElementById('wrapper-ejemplar');
    wSocio.querySelectorAll('.ss-selected').forEach(e => e.remove());
    wEjemplar.querySelectorAll('.ss-selected').forEach(e => e.remove());

    const sSocio = document.getElementById('pre-socio-search');
    const sEjemplar = document.getElementById('pre-ejemplar-search');
    sSocio.value = '';
    sSocio.style.display = '';
    sEjemplar.value = '';
    sEjemplar.style.display = '';

    document.getElementById('dropdown-socio').innerHTML = '';
    document.getElementById('dropdown-socio').classList.remove('open');
    document.getElementById('dropdown-ejemplar').innerHTML = '';
    document.getElementById('dropdown-ejemplar').classList.remove('open');

    document.getElementById('pre-dias').value = '15';
    document.getElementById('pre-obs').value = '';
    document.getElementById('modal-prestamo').classList.add('active');
}

function cerrarModal() { document.getElementById('modal-prestamo').classList.remove('active'); }

async function guardarPrestamo() {
    const payload = {
        socio_id: parseInt(document.getElementById('pre-socio').value),
        ejemplar_id: parseInt(document.getElementById('pre-ejemplar').value),
        dias_prestamo: parseInt(document.getElementById('pre-dias').value) || 15,
        observaciones: document.getElementById('pre-obs').value.trim(),
    };
    if (!payload.socio_id || !payload.ejemplar_id) {
        Swal.fire('Campos obligatorios', 'Socio y Ejemplar son requeridos', 'warning');
        return;
    }
    try {
        const res = await fetch(API + '?action=guardar', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
        const data = await res.json();
        if (data.success) {
            Swal.fire('Exito', data.mensaje, 'success');
            cerrarModal();
            cargarPrestamos();
            cargarEjemplaresDisponibles();
        } else Swal.fire('Error', data.error, 'error');
    } catch (e) { Swal.fire('Error', 'No se pudo conectar al servidor', 'error'); }
}

async function devolverPrestamo(id, socio) {
    const r = await Swal.fire({
        title: 'Devolver prestamo?',
        text: `Confirmar devolucion de ${socio}.`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#059669',
        confirmButtonText: 'Si, devolver',
        cancelButtonText: 'Cancelar',
    });
    if (!r.isConfirmed) return;
    try {
        const res = await fetch(API + '?action=devolver', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ prestamo_id: id }) });
        const data = await res.json();
        if (data.success) {
            Swal.fire('Devolucion', data.mensaje, 'success');
            cargarPrestamos();
            cargarEjemplaresDisponibles();
        } else Swal.fire('Error', data.error, 'error');
    } catch (e) { Swal.fire('Error', 'No se pudo conectar al servidor', 'error'); }
}

document.addEventListener('DOMContentLoaded', () => {
    const sSocio = document.getElementById('pre-socio-search');
    const sEjemplar = document.getElementById('pre-ejemplar-search');
    const ddSocio = document.getElementById('dropdown-socio');
    const ddEjemplar = document.getElementById('dropdown-ejemplar');

    sSocio.addEventListener('focus', () => {
        renderDropdownSocio(sSocio.value);
        ddSocio.classList.add('open');
    });

    sSocio.addEventListener('input', () => {
        renderDropdownSocio(sSocio.value);
        ddSocio.classList.add('open');
    });

    sEjemplar.addEventListener('focus', () => {
        renderDropdownEjemplar(sEjemplar.value);
        ddEjemplar.classList.add('open');
    });

    sEjemplar.addEventListener('input', () => {
        renderDropdownEjemplar(sEjemplar.value);
        ddEjemplar.classList.add('open');
    });

    document.addEventListener('click', (e) => {
        if (!document.getElementById('wrapper-socio').contains(e.target)) ddSocio.classList.remove('open');
        if (!document.getElementById('wrapper-ejemplar').contains(e.target)) ddEjemplar.classList.remove('open');
    });
});
