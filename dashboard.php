<?php
declare(strict_types=1);
session_start();
if (!isset($_SESSION['usuario_activo'])) {
    header('Location: index.php');
    exit();
}
$usuario = $_SESSION['usuario_activo'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistema Biblioteca</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="frontend/css/styles.css">
</head>
<body class="bg-slate-100">
    <div class="flex min-h-screen">
        <?php include __DIR__ . '/backend/includes/sidebar.php'; ?>
        <div class="main-content flex-1" style="margin-left: 256px;">
            <?php $tituloPagina = 'Dashboard General'; include __DIR__ . '/backend/includes/navbar.php'; ?>
            <div class="p-6">
                <!-- Cards resumen -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 mb-8" id="stats-cards">
                    <div class="card-stat">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Total Libros</p>
                                <p class="text-3xl font-extrabold text-indigo-900 mt-1" id="stat-libros">-</p>
                            </div>
                            <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center">
                                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                            </div>
                        </div>
                    </div>
                    <div class="card-stat">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Ejemplares Disponibles</p>
                                <p class="text-3xl font-extrabold text-emerald-700 mt-1" id="stat-ejemplares">-</p>
                            </div>
                            <div class="w-12 h-12 bg-emerald-100 rounded-xl flex items-center justify-center">
                                <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                            </div>
                        </div>
                    </div>
                    <div class="card-stat">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Prestamos Activos</p>
                                <p class="text-3xl font-extrabold text-amber-600 mt-1" id="stat-prestamos">-</p>
                            </div>
                            <div class="w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center">
                                <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                        </div>
                    </div>
                    <div class="card-stat">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Socios Registrados</p>
                                <p class="text-3xl font-extrabold text-cyan-600 mt-1" id="stat-socios">-</p>
                            </div>
                            <div class="w-12 h-12 bg-cyan-100 rounded-xl flex items-center justify-center">
                                <svg class="w-6 h-6 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabla ultimos prestamos -->
                <div class="card-stat">
                    <h3 class="text-lg font-bold text-indigo-900 mb-4">Ultimos Prestamos Registrados</h3>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Socio</th>
                                    <th>Libro</th>
                                    <th>Fecha Prestamo</th>
                                    <th>Devolucion Esperada</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody id="ultimos-prestamos">
                                <tr><td colspan="6" class="text-center text-slate-400 py-8">Cargando...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="frontend/js/utils.js"></script>
    <script src="frontend/js/dashboard.js"></script>
</body>
</html>
