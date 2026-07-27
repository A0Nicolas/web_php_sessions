const API = 'backend/api_libros.php';

let librosData = [];

document.addEventListener('DOMContentLoaded', () => {
    cargarLibros();
    document.getElementById('input-busqueda').addEventListener('input', debounce(() => {
        cargarLibros(document.getElementById('input-busqueda').value.trim());
    }, 300));
});

function debounce(fn, ms) {
    let t; return (...a) => { clearTimeout(t); t = setTimeout(() => fn(...a), ms); };
}

async function cargarLibros(q = '') {
    const tbody = document.getElementById('cuerpo-tabla');
    try {
        const res = await fetch(API + '?action=listar' + (q ? '&q=' + encodeURIComponent(q) : ''));
        const data = await res.json();
        if (!data.success || data.data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center text-slate-400 py-8">No se encontraron libros</td></tr>';
            return;
        }
        librosData = data.data;
        tbody.innerHTML = data.data.map(l => `
            <tr>
                <td class="font-mono text-xs text-slate-500">${escapeHtml(l.isbn)}</td>
                <td class="font-semibold">${escapeHtml(l.titulo)}</td>
                <td>${escapeHtml(l.autor)}</td>
                <td>${escapeHtml(l.editorial || '-')}</td>
                <td>${escapeHtml(l.genero || '-')}</td>
                <td>${escapeHtml(String(l.anio_publicacion || '-'))}</td>
                <td>
                    <div class="actions-cell">
                        <button class="btn-icon bg-amber-100 text-amber-700 hover:bg-amber-200" onclick="editarLibro(${l.id})" title="Editar">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </button>
                        <button class="btn-icon bg-red-100 text-red-700 hover:bg-red-200" onclick="eliminarLibro(${l.id}, '${escapeHtml(l.titulo).replace(/'/g, "\\'")}')" title="Eliminar">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
    } catch (e) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center text-red-500 py-8">Error al cargar</td></tr>';
    }
}

function abrirModal(titulo = 'Nuevo Libro', datos = null) {
    document.getElementById('modal-titulo').textContent = titulo;
    document.getElementById('lib-id').value = datos ? datos.id : '';
    document.getElementById('lib-isbn').value = datos ? datos.isbn : '';
    document.getElementById('lib-titulo').value = datos ? datos.titulo : '';
    document.getElementById('lib-autor').value = datos ? datos.autor : '';
    document.getElementById('lib-editorial').value = datos ? (datos.editorial || '') : '';
    document.getElementById('lib-anio').value = datos ? (datos.anio_publicacion || '') : '';
    document.getElementById('lib-genero').value = datos ? (datos.genero || '') : '';
    document.getElementById('lib-paginas').value = datos ? (datos.num_paginas || '') : '';
    document.getElementById('lib-desc').value = datos ? (datos.descripcion || '') : '';
    document.getElementById('modal-libro').classList.add('active');
}

function cerrarModal() { document.getElementById('modal-libro').classList.remove('active'); }

function editarLibro(id) {
    const l = librosData.find(x => x.id === id);
    if (l) abrirModal('Editar Libro', l);
}

async function guardarLibro() {
    const payload = {
        id: document.getElementById('lib-id').value || null,
        isbn: document.getElementById('lib-isbn').value.trim(),
        titulo: document.getElementById('lib-titulo').value.trim(),
        autor: document.getElementById('lib-autor').value.trim(),
        editorial: document.getElementById('lib-editorial').value.trim(),
        anio_publicacion: document.getElementById('lib-anio').value || null,
        genero: document.getElementById('lib-genero').value.trim(),
        num_paginas: document.getElementById('lib-paginas').value || null,
        descripcion: document.getElementById('lib-desc').value.trim(),
    };
    if (!payload.isbn || !payload.titulo || !payload.autor) {
        Swal.fire('Campos obligatorios', 'ISBN, Titulo y Autor son requeridos', 'warning');
        return;
    }
    if (payload.isbn.length > 20) {
        Swal.fire('Dato invalido', 'El ISBN no puede exceder 20 caracteres', 'warning');
        return;
    }
    if (payload.anio_publicacion && (payload.anio_publicacion < 1000 || payload.anio_publicacion > 2099)) {
        Swal.fire('Dato invalido', 'El anio debe estar entre 1000 y 2099', 'warning');
        return;
    }
    if (payload.num_paginas && payload.num_paginas < 1) {
        Swal.fire('Dato invalido', 'El numero de paginas debe ser mayor a 0', 'warning');
        return;
    }
    try {
        const res = await fetch(API + '?action=guardar', {
            method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload),
        });
        const data = await res.json();
        if (data.success) { Swal.fire('Exito', data.mensaje, 'success'); cerrarModal(); cargarLibros(); }
        else Swal.fire('Error', data.error, 'error');
    } catch (e) { Swal.fire('Error', 'No se pudo conectar al servidor', 'error'); }
}

async function eliminarLibro(id, titulo) {
    const r = await Swal.fire({ title: 'Eliminar libro?', text: `"${titulo}" sera eliminado.`, icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc2626', confirmButtonText: 'Si, eliminar', cancelButtonText: 'Cancelar' });
    if (!r.isConfirmed) return;
    try {
        const res = await fetch(API + '?action=eliminar', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ id }) });
        const data = await res.json();
        if (data.success) { Swal.fire('Eliminado', data.mensaje, 'success'); cargarLibros(); }
        else Swal.fire('Error', data.error, 'error');
    } catch (e) { Swal.fire('Error', 'No se pudo conectar al servidor', 'error'); }
}
