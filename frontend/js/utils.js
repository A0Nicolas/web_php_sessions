function escapeHtml(str) {
    if (str === null || str === undefined) return '';
    const div = document.createElement('div');
    div.appendChild(document.createTextNode(String(str)));
    return div.innerHTML;
}

function formatMoney(val) {
    return '$' + Number(val || 0).toFixed(2);
}

function formatDate(str) {
    if (!str) return '-';
    const d = new Date(str);
    return d.toLocaleDateString('es-EC', { day: '2-digit', month: '2-digit', year: 'numeric' });
}

function formatDateTime(str) {
    if (!str) return '-';
    const d = new Date(str);
    return d.toLocaleDateString('es-EC', { day: '2-digit', month: '2-digit', year: 'numeric' }) +
           ' ' + d.toLocaleTimeString('es-EC', { hour: '2-digit', minute: '2-digit' });
}

function badgeEstado(estado, mapa) {
    const cfg = mapa[estado] || { color: 'bg-gray-100 text-gray-700', label: estado };
    return `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold ${cfg.color}">${escapeHtml(cfg.label || estado)}</span>`;
}

const ESTADO_EJEMPLAR = {
    'Disponible': { color: 'bg-emerald-100 text-emerald-700' },
    'Prestado':   { color: 'bg-amber-100 text-amber-700' },
    'Danado':     { color: 'bg-red-100 text-red-700' },
    'Sin disponibilidad': { color: 'bg-gray-200 text-gray-600' },
};

const ESTADO_PRESTAMO = {
    'Activo':   { color: 'bg-blue-100 text-blue-700' },
    'Devuelto': { color: 'bg-emerald-100 text-emerald-700' },
    'Vencido':  { color: 'bg-red-100 text-red-700' },
};

const ESTADO_SEDE = {
    '1': { color: 'bg-emerald-100 text-emerald-700', label: 'Activa' },
    '0': { color: 'bg-red-100 text-red-700', label: 'Inactiva' },
};

function soloNumeros(e) {
    e.target.value = e.target.value.replace(/[^0-9]/g, '');
}

function bindSoloNumeros() {
    document.querySelectorAll('[data-solo-numeros]').forEach(el => {
        el.addEventListener('input', soloNumeros);
    });
}

document.addEventListener('DOMContentLoaded', bindSoloNumeros);
