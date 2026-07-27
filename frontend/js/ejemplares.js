const API = 'backend/api_ejemplares.php';

let ejemplaresData = [];
let librosList = [];
let sedesList = [];
let libroSeleccionado = null;
let sedeSeleccionada = null;

document.addEventListener('DOMContentLoaded', () => {
    Promise.all([cargarEjemplares(), cargarLibrosList(), cargarSedesList()]);
    document.getElementById('input-busqueda').addEventListener('input', debounce(() => {
        cargarEjemplares(document.getElementById('input-busqueda').value.trim());
    }, 300));
    document.getElementById('filtro-sede').addEventListener('change', () => {
        cargarEjemplares(document.getElementById('input-busqueda').value.trim(), document.getElementById('filtro-sede').value);
    });
    initSearchSelects();
});

function debounce(fn, ms) { let t; return (...a) => { clearTimeout(t); t = setTimeout(() => fn(...a), ms); }; }

async function cargarLibrosList() {
    const res = await fetch('backend/api_libros.php?action=todos');
    const data = await res.json();
    if (data.success) librosList = data.data;
}

async function cargarSedesList() {
    const res = await fetch('backend/api_sedes.php?action=todas');
    const data = await res.json();
    if (data.success) {
        sedesList = data.data;
        const sel = document.getElementById('filtro-sede');
        sel.innerHTML = '<option value="">Todas las sedes</option>';
        sedesList.forEach(s => { const o = document.createElement('option'); o.value = s.id; o.textContent = s.nombre; sel.appendChild(o); });
    }
}

async function cargarEjemplares(q = '', sede = '') {
    const tbody = document.getElementById('cuerpo-tabla');
    try {
        let url = API + '?action=listar';
        if (q) url += '&q=' + encodeURIComponent(q);
        if (sede) url += '&sede_id=' + sede;
        const res = await fetch(url);
        const data = await res.json();
        if (!data.success || data.data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-slate-400 py-8">No se encontraron ejemplares</td></tr>';
            return;
        }
        ejemplaresData = data.data;
        tbody.innerHTML = data.data.map(e => `
            <tr>
                <td class="font-mono font-semibold text-indigo-700">${escapeHtml(e.codigo_ejemplar)}</td>
                <td>${escapeHtml(e.libro_titulo)}</td>
                <td>${escapeHtml(e.sede_nombre)}</td>
                <td>${badgeEstado(e.estado, ESTADO_EJEMPLAR)}</td>
                <td>
                    <div class="actions-cell">
                        <button class="btn-icon bg-amber-100 text-amber-700 hover:bg-amber-200" onclick="editarEjemplar(${e.id})" title="Editar">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </button>
                        <button class="btn-icon bg-red-100 text-red-700 hover:bg-red-200" onclick="eliminarEjemplar(${e.id}, '${escapeHtml(e.codigo_ejemplar)}')" title="Eliminar">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
    } catch (e) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center text-red-500 py-8">Error al cargar</td></tr>';
    }
}

function renderDropdownLibro(query) {
    const dd = document.getElementById('dropdown-libro');
    const q = query.toLowerCase();
    const filtered = librosList.filter(l => l.titulo.toLowerCase().includes(q) || l.autor.toLowerCase().includes(q) || l.isbn.toLowerCase().includes(q));
    if (filtered.length === 0) { dd.innerHTML = '<div class="ss-empty">No se encontraron libros</div>'; return; }
    dd.innerHTML = filtered.map(l => `
        <div class="ss-item" onclick="seleccionarLibro(${l.id}, '${escapeHtml(l.titulo).replace(/'/g, "\\'")}')">
            <div class="font-medium">${escapeHtml(l.titulo)}</div>
            <div class="ss-item-sub">${escapeHtml(l.autor)} · ISBN: ${escapeHtml(l.isbn)}</div>
        </div>
    `).join('');
}

function renderDropdownSede(query) {
    const dd = document.getElementById('dropdown-sede');
    const q = query.toLowerCase();
    const filtered = sedesList.filter(s => s.nombre.toLowerCase().includes(q));
    if (filtered.length === 0) { dd.innerHTML = '<div class="ss-empty">No se encontraron sedes</div>'; return; }
    dd.innerHTML = filtered.map(s => `
        <div class="ss-item" onclick="seleccionarSede(${s.id}, '${escapeHtml(s.nombre).replace(/'/g, "\\'")}')">
            <div class="font-medium">${escapeHtml(s.nombre)}</div>
        </div>
    `).join('');
}

function seleccionarLibro(id, titulo) {
    libroSeleccionado = { id, titulo };
    document.getElementById('ej-libro').value = id;
    const wrapper = document.getElementById('wrapper-libro');
    document.getElementById('ej-libro-search').style.display = 'none';
    document.getElementById('dropdown-libro').classList.remove('open');
    const tag = document.createElement('div');
    tag.className = 'ss-selected';
    tag.innerHTML = `<span>${escapeHtml(titulo)}</span><span class="ss-remove" onclick="limpiarLibro()">&times;</span>`;
    wrapper.appendChild(tag);
}

function seleccionarSede(id, nombre) {
    sedeSeleccionada = { id, nombre };
    document.getElementById('ej-sede').value = id;
    const wrapper = document.getElementById('wrapper-sede');
    document.getElementById('ej-sede-search').style.display = 'none';
    document.getElementById('dropdown-sede').classList.remove('open');
    const tag = document.createElement('div');
    tag.className = 'ss-selected';
    tag.innerHTML = `<span>${escapeHtml(nombre)}</span><span class="ss-remove" onclick="limpiarSede()">&times;</span>`;
    wrapper.appendChild(tag);
}

function limpiarLibro() {
    libroSeleccionado = null;
    document.getElementById('ej-libro').value = '';
    const wrapper = document.getElementById('wrapper-libro');
    wrapper.querySelectorAll('.ss-selected').forEach(e => e.remove());
    const s = document.getElementById('ej-libro-search');
    s.value = ''; s.style.display = '';
}

function limpiarSede() {
    sedeSeleccionada = null;
    document.getElementById('ej-sede').value = '';
    const wrapper = document.getElementById('wrapper-sede');
    wrapper.querySelectorAll('.ss-selected').forEach(e => e.remove());
    const s = document.getElementById('ej-sede-search');
    s.value = ''; s.style.display = '';
}

function seleccionarLibroPorId(id) {
    const l = librosList.find(x => x.id == id);
    if (l) seleccionarLibro(l.id, l.titulo);
}

function seleccionarSedePorId(id) {
    const s = sedesList.find(x => x.id == id);
    if (s) seleccionarSede(s.id, s.nombre);
}

function abrirModal(titulo = 'Nuevo Ejemplar', datos = null) {
    libroSeleccionado = null;
    sedeSeleccionada = null;
    document.getElementById('ej-id').value = datos ? datos.id : '';
    document.getElementById('ej-codigo').value = datos ? datos.codigo_ejemplar : '';
    document.getElementById('ej-estado').value = datos ? datos.estado : 'Disponible';

    const wLibro = document.getElementById('wrapper-libro');
    const wSede = document.getElementById('wrapper-sede');
    wLibro.querySelectorAll('.ss-selected').forEach(e => e.remove());
    wSede.querySelectorAll('.ss-selected').forEach(e => e.remove());
    document.getElementById('ej-libro').value = '';
    document.getElementById('ej-sede').value = '';

    const sLibro = document.getElementById('ej-libro-search');
    const sSede = document.getElementById('ej-sede-search');
    sLibro.value = ''; sLibro.style.display = '';
    sSede.value = ''; sSede.style.display = '';
    document.getElementById('dropdown-libro').innerHTML = '';
    document.getElementById('dropdown-libro').classList.remove('open');
    document.getElementById('dropdown-sede').innerHTML = '';
    document.getElementById('dropdown-sede').classList.remove('open');

    if (datos) {
        if (datos.libro_id) seleccionarLibroPorId(datos.libro_id);
        if (datos.sede_id) seleccionarSedePorId(datos.sede_id);
    }

    document.getElementById('modal-ejemplar').classList.add('active');
}

function cerrarModal() { document.getElementById('modal-ejemplar').classList.remove('active'); }

function editarEjemplar(id) {
    const e = ejemplaresData.find(x => x.id === id);
    if (e) abrirModal('Editar Ejemplar', e);
}

async function guardarEjemplar() {
    const payload = {
        id: document.getElementById('ej-id').value || null,
        libro_id: parseInt(document.getElementById('ej-libro').value),
        sede_id: parseInt(document.getElementById('ej-sede').value),
        codigo_ejemplar: document.getElementById('ej-codigo').value.trim(),
        estado: document.getElementById('ej-estado').value,
    };
    if (!payload.libro_id || !payload.sede_id || !payload.codigo_ejemplar) {
        Swal.fire('Campos obligatorios', 'Libro, Sede y Codigo son requeridos', 'warning');
        return;
    }
    if (payload.codigo_ejemplar.length > 30) {
        Swal.fire('Dato invalido', 'El codigo no puede exceder 30 caracteres', 'warning');
        return;
    }
    try {
        const res = await fetch(API + '?action=guardar', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
        const data = await res.json();
        if (data.success) { Swal.fire('Exito', data.mensaje, 'success'); cerrarModal(); cargarEjemplares(); }
        else Swal.fire('Error', data.error, 'error');
    } catch (e) { Swal.fire('Error', 'No se pudo conectar al servidor', 'error'); }
}

async function eliminarEjemplar(id, codigo) {
    const r = await Swal.fire({ title: 'Eliminar ejemplar?', text: `"${codigo}" sera eliminado.`, icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc2626', confirmButtonText: 'Si, eliminar', cancelButtonText: 'Cancelar' });
    if (!r.isConfirmed) return;
    try {
        const res = await fetch(API + '?action=eliminar', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ id }) });
        const data = await res.json();
        if (data.success) { Swal.fire('Eliminado', data.mensaje, 'success'); cargarEjemplares(); }
        else Swal.fire('Error', data.error, 'error');
    } catch (e) { Swal.fire('Error', 'No se pudo conectar al servidor', 'error'); }
}

function initSearchSelects() {
    const binds = [
        { search: 'ej-libro-search', dropdown: 'dropdown-libro', wrapper: 'wrapper-libro', render: renderDropdownLibro },
        { search: 'ej-sede-search', dropdown: 'dropdown-sede', wrapper: 'wrapper-sede', render: renderDropdownSede },
    ];
    binds.forEach(b => {
        const sEl = document.getElementById(b.search);
        const dEl = document.getElementById(b.dropdown);
        sEl.addEventListener('focus', () => { b.render(sEl.value); dEl.classList.add('open'); });
        sEl.addEventListener('input', () => { b.render(sEl.value); dEl.classList.add('open'); });
    });
    document.addEventListener('click', (e) => {
        binds.forEach(b => {
            if (!document.getElementById(b.wrapper).contains(e.target)) document.getElementById(b.dropdown).classList.remove('open');
        });
    });
}
