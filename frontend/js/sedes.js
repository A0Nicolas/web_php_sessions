const API = 'backend/api_sedes.php';

let sedesData = [];

document.addEventListener('DOMContentLoaded', () => {
    cargarSedes();
    document.getElementById('input-busqueda').addEventListener('input', debounce(cargarSedeFiltrada, 300));
});

function debounce(fn, ms) {
    let timer;
    return (...args) => { clearTimeout(timer); timer = setTimeout(() => fn(...args), ms); };
}

async function cargarSedeFiltrada() {
    const q = document.getElementById('input-busqueda').value.trim();
    await cargarSedes(q);
}

async function cargarSedes(q = '') {
    const tbody = document.getElementById('cuerpo-tabla');
    try {
        const url = API + '?action=listar' + (q ? '&q=' + encodeURIComponent(q) : '');
        const res = await fetch(url);
        const data = await res.json();
        if (!data.success || data.data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-slate-400 py-8">No se encontraron sedes</td></tr>';
            return;
        }
        sedesData = data.data;
        tbody.innerHTML = data.data.map(s => `
            <tr>
                <td class="font-semibold">${escapeHtml(s.nombre)}</td>
                <td>${escapeHtml(s.direccion)}</td>
                <td>${escapeHtml(s.telefono || '-')}</td>
                <td>${escapeHtml(s.horario || '-')}</td>
                <td>${badgeEstado(String(s.estado), ESTADO_SEDE)}</td>
                <td>
                    <div class="actions-cell">
                        <button class="btn-icon bg-amber-100 text-amber-700 hover:bg-amber-200" onclick="editarSede(${s.id})" title="Editar">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </button>
                        <button class="btn-icon bg-red-100 text-red-700 hover:bg-red-200" onclick="eliminarSede(${s.id}, '${escapeHtml(s.nombre).replace(/'/g, "\\'")}')" title="Eliminar">
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

function abrirModal(titulo = 'Nueva Sede', datos = null) {
    document.getElementById('modal-titulo').textContent = titulo;
    document.getElementById('sed-id').value = datos ? datos.id : '';
    document.getElementById('sed-nombre').value = datos ? datos.nombre : '';
    document.getElementById('sed-direccion').value = datos ? datos.direccion : '';
    document.getElementById('sed-telefono').value = datos ? (datos.telefono || '') : '';
    document.getElementById('sed-horario').value = datos ? (datos.horario || '') : '';
    document.getElementById('sed-estado').value = datos ? String(datos.estado) : '1';
    document.getElementById('modal-sede').classList.add('active');
}

function cerrarModal() {
    document.getElementById('modal-sede').classList.remove('active');
}

function editarSede(id) {
    const s = sedesData.find(x => x.id === id);
    if (s) abrirModal('Editar Sede', s);
}

async function guardarSede() {
    const payload = {
        id: document.getElementById('sed-id').value || null,
        nombre: document.getElementById('sed-nombre').value.trim(),
        direccion: document.getElementById('sed-direccion').value.trim(),
        telefono: document.getElementById('sed-telefono').value.trim(),
        horario: document.getElementById('sed-horario').value.trim(),
        estado: parseInt(document.getElementById('sed-estado').value),
    };
    if (!payload.nombre || !payload.direccion) {
        Swal.fire('Campos obligatorios', 'Nombre y Direccion son requeridos', 'warning');
        return;
    }
    if (payload.nombre.length > 100) {
        Swal.fire('Dato invalido', 'El nombre no puede exceder 100 caracteres', 'warning');
        return;
    }
    if (payload.direccion.length > 200) {
        Swal.fire('Dato invalido', 'La direccion no puede exceder 200 caracteres', 'warning');
        return;
    }
    if (payload.telefono && !/^\d{7,10}$/.test(payload.telefono)) {
        Swal.fire('Dato invalido', 'El telefono debe tener entre 7 y 10 digitos numericos', 'warning');
        return;
    }
    try {
        const res = await fetch(API + '?action=guardar', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
        });
        const data = await res.json();
        if (data.success) {
            Swal.fire('Exito', data.mensaje, 'success');
            cerrarModal();
            cargarSedes();
        } else {
            Swal.fire('Error', data.error, 'error');
        }
    } catch (e) {
        Swal.fire('Error', 'No se pudo conectar al servidor', 'error');
    }
}

async function eliminarSede(id, nombre) {
    const result = await Swal.fire({
        title: 'Eliminar sede?',
        text: `Se eliminara "${nombre}". Esta accion no se puede deshacer.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        confirmButtonText: 'Si, eliminar',
        cancelButtonText: 'Cancelar',
    });
    if (!result.isConfirmed) return;
    try {
        const res = await fetch(API + '?action=eliminar', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id }),
        });
        const data = await res.json();
        if (data.success) {
            Swal.fire('Eliminado', data.mensaje, 'success');
            cargarSedes();
        } else {
            Swal.fire('Error', data.error, 'error');
        }
    } catch (e) {
        Swal.fire('Error', 'No se pudo conectar al servidor', 'error');
    }
}
