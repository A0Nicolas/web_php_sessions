const API = 'backend/api_socios.php';

let sociosData = [];

document.addEventListener('DOMContentLoaded', () => {
    cargarSocios();
    document.getElementById('input-busqueda').addEventListener('input', debounce(() => {
        cargarSocios(document.getElementById('input-busqueda').value.trim());
    }, 300));
});

function debounce(fn, ms) { let t; return (...a) => { clearTimeout(t); t = setTimeout(() => fn(...a), ms); }; }

async function cargarSocios(q = '') {
    const tbody = document.getElementById('cuerpo-tabla');
    try {
        const res = await fetch(API + '?action=listar' + (q ? '&q=' + encodeURIComponent(q) : ''));
        const data = await res.json();
        if (!data.success || data.data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center text-slate-400 py-8">No se encontraron socios</td></tr>';
            return;
        }
        sociosData = data.data;
        tbody.innerHTML = data.data.map(s => `
            <tr>
                <td class="font-mono text-sm">${escapeHtml(s.cedula)}</td>
                <td class="font-semibold">${escapeHtml(s.nombre_completo)}</td>
                <td>${escapeHtml(s.telefono || '-')}</td>
                <td>${escapeHtml(s.correo || '-')}</td>
                <td>${formatDate(s.fecha_registro)}</td>
                <td>
                    <div class="actions-cell">
                        <button class="btn-icon bg-amber-100 text-amber-700 hover:bg-amber-200" onclick="editarSocio(${s.id})" title="Editar">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </button>
                        ${typeof ROL_USUARIO !== 'undefined' && ROL_USUARIO !== 'bibliotecario' ? `
                        <button class="btn-icon bg-red-100 text-red-700 hover:bg-red-200" onclick="eliminarSocio(${s.id}, '${escapeHtml(s.nombre_completo).replace(/'/g, "\\'")}')" title="Eliminar">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>` : ''}
                    </div>
                </td>
            </tr>
        `).join('');
    } catch (e) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center text-red-500 py-8">Error al cargar</td></tr>';
    }
}

function abrirModal(titulo = 'Nuevo Socio', datos = null) {
    document.getElementById('modal-titulo').textContent = titulo;
    document.getElementById('soc-id').value = datos ? datos.id : '';
    document.getElementById('soc-cedula').value = datos ? datos.cedula : '';
    document.getElementById('soc-nombre').value = datos ? datos.nombre_completo : '';
    document.getElementById('soc-correo').value = datos ? (datos.correo || '') : '';
    document.getElementById('soc-telefono').value = datos ? (datos.telefono || '') : '';
    document.getElementById('soc-direccion').value = datos ? (datos.direccion || '') : '';
    document.getElementById('modal-socio').classList.add('active');
}

function cerrarModal() { document.getElementById('modal-socio').classList.remove('active'); }

function editarSocio(id) {
    const s = sociosData.find(x => x.id === id);
    if (s) abrirModal('Editar Socio', s);
}

async function guardarSocio() {
    const payload = {
        id: document.getElementById('soc-id').value || null,
        cedula: document.getElementById('soc-cedula').value.trim(),
        nombre_completo: document.getElementById('soc-nombre').value.trim(),
        correo: document.getElementById('soc-correo').value.trim(),
        telefono: document.getElementById('soc-telefono').value.trim(),
        direccion: document.getElementById('soc-direccion').value.trim(),
    };
    if (!payload.cedula || !payload.nombre_completo) {
        Swal.fire('Campos obligatorios', 'Cedula y Nombre son requeridos', 'warning');
        return;
    }
    if (!/^\d{10}$/.test(payload.cedula)) {
        Swal.fire('Dato invalido', 'La cedula debe tener exactamente 10 digitos numericos', 'warning');
        return;
    }
    if (payload.telefono && !/^\d{7,10}$/.test(payload.telefono)) {
        Swal.fire('Dato invalido', 'El telefono debe tener entre 7 y 10 digitos numericos', 'warning');
        return;
    }
    if (payload.correo && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(payload.correo)) {
        Swal.fire('Dato invalido', 'El correo electronico no es valido', 'warning');
        return;
    }
    try {
        const res = await fetch(API + '?action=guardar', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
        const data = await res.json();
        if (data.success) { Swal.fire('Exito', data.mensaje, 'success'); cerrarModal(); cargarSocios(); }
        else Swal.fire('Error', data.error, 'error');
    } catch (e) { Swal.fire('Error', 'No se pudo conectar al servidor', 'error'); }
}

async function eliminarSocio(id, nombre) {
    const r = await Swal.fire({ title: 'Eliminar socio?', text: `"${nombre}" sera eliminado.`, icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc2626', confirmButtonText: 'Si, eliminar', cancelButtonText: 'Cancelar' });
    if (!r.isConfirmed) return;
    try {
        const res = await fetch(API + '?action=eliminar', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ id }) });
        const data = await res.json();
        if (data.success) { Swal.fire('Eliminado', data.mensaje, 'success'); cargarSocios(); }
        else Swal.fire('Error', data.error, 'error');
    } catch (e) { Swal.fire('Error', 'No se pudo conectar al servidor', 'error'); }
}
