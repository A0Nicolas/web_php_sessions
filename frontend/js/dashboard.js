const API = 'backend/api_dashboard.php';

document.addEventListener('DOMContentLoaded', () => {
    cargarStats();
    cargarUltimosPrestamos();
});

async function cargarStats() {
    try {
        const res = await fetch(API + '?action=stats');
        const data = await res.json();
        if (data.success) {
            document.getElementById('stat-libros').textContent = data.libros;
            document.getElementById('stat-ejemplares').textContent = data.ejemplares_disponibles;
            document.getElementById('stat-prestamos').textContent = data.prestamos_activos;
            document.getElementById('stat-socios').textContent = data.socios;
        }
    } catch (e) {
        console.error('Error cargando stats:', e);
    }
}

async function cargarUltimosPrestamos() {
    const tbody = document.getElementById('ultimos-prestamos');
    try {
        const res = await fetch(API + '?action=ultimos_prestamos');
        const data = await res.json();
        if (!data.success || data.data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-slate-400 py-8">No hay prestamos registrados</td></tr>';
            return;
        }
        tbody.innerHTML = data.data.map(p => `
            <tr>
                <td class="font-semibold text-indigo-700">#${p.id}</td>
                <td>${escapeHtml(p.socio)}</td>
                <td>${escapeHtml(p.libro)}</td>
                <td>${formatDate(p.fecha_prestamo)}</td>
                <td>${formatDate(p.fecha_devolucion_esperada)}</td>
                <td>${badgeEstado(p.estado, ESTADO_PRESTAMO)}</td>
            </tr>
        `).join('');
    } catch (e) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center text-red-500 py-8">Error al cargar datos</td></tr>';
    }
}
