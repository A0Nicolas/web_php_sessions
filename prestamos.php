<?php
declare(strict_types=1);
session_start();
if (!isset($_SESSION['usuario_activo'])) { header('Location: index.php'); exit(); }
$usuario = $_SESSION['usuario_activo'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prestamos - Sistema Biblioteca</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="frontend/css/styles.css">
</head>
<body class="bg-slate-100">
    <div class="flex min-h-screen">
        <?php include __DIR__ . '/backend/includes/sidebar.php'; ?>
        <div class="main-content flex-1" style="margin-left: 256px;">
            <?php $tituloPagina = 'Gestion de Prestamos'; include __DIR__ . '/backend/includes/navbar.php'; ?>
            <div class="p-6">
                <div class="page-header">
                    <h2 class="text-2xl font-extrabold text-indigo-900">Prestamos y Devoluciones</h2>
                    <button class="btn-success" onclick="abrirModal()">+ Nuevo Prestamo</button>
                </div>

                <!-- Filtros -->
                <div class="card-stat mb-6">
                    <h3 class="text-sm font-bold text-slate-500 uppercase tracking-wide mb-3">Busqueda Avanzada</h3>
                    <form id="form-filtros" class="flex flex-wrap gap-3 items-end">
                        <div class="form-group mb-0">
                            <label class="text-xs">Fecha Inicio</label>
                            <input type="date" id="filtro-fecha-inicio" class="text-sm" style="padding: 8px 12px; border: 2px solid #e2e8f0; border-radius: 10px;">
                        </div>
                        <div class="form-group mb-0">
                            <label class="text-xs">Fecha Fin</label>
                            <input type="date" id="filtro-fecha-fin" class="text-sm" style="padding: 8px 12px; border: 2px solid #e2e8f0; border-radius: 10px;">
                        </div>
                        <div class="form-group mb-0">
                            <label class="text-xs">Socio (nombre o cedula)</label>
                            <input type="text" id="filtro-socio" placeholder="Buscar socio..." class="search-input" style="width: 220px;">
                        </div>
                        <div class="form-group mb-0">
                            <label class="text-xs">Estado</label>
                            <select id="filtro-estado" class="search-input" style="width: 150px;">
                                <option value="">Todos</option>
                                <option value="Activo">Activo</option>
                                <option value="Devuelto">Devuelto</option>
                                <option value="Vencido">Vencido</option>
                            </select>
                        </div>
                        <div class="flex gap-2">
                            <button type="button" onclick="limpiarFiltros()" class="px-4 py-2 text-sm font-medium text-slate-600 bg-slate-200 rounded-lg hover:bg-slate-300 transition">Limpiar</button>
                            <button type="submit" class="btn-primary text-sm">Buscar</button>
                        </div>
                    </form>
                </div>

                <!-- Tabla -->
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Socio</th>
                                <th>Libro / Ejemplar</th>
                                <th>Prestamo</th>
                                <th>Devolucion Esperada</th>
                                <th>Devolucion Real</th>
                                <th>Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="cuerpo-tabla">
                            <tr><td colspan="8" class="text-center text-slate-400 py-8">Cargando...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Nuevo Prestamo -->
    <div class="modal-overlay" id="modal-prestamo">
        <div class="modal-box">
            <div class="modal-header">
                <h3>Registrar Nuevo Prestamo</h3>
                <button onclick="cerrarModal()" class="text-slate-400 hover:text-slate-600 text-2xl leading-none">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Socio *</label>
                    <div class="searchable-select" id="wrapper-socio">
                        <input type="hidden" id="pre-socio" value="">
                        <input type="text" class="ss-search" id="pre-socio-search" placeholder="Escriba nombre o cedula..." autocomplete="off">
                        <div class="ss-dropdown" id="dropdown-socio"></div>
                    </div>
                </div>
                <div class="form-group">
                    <label>Ejemplar Disponible *</label>
                    <div class="searchable-select" id="wrapper-ejemplar">
                        <input type="hidden" id="pre-ejemplar" value="">
                        <input type="text" class="ss-search" id="pre-ejemplar-search" placeholder="Escriba codigo o titulo..." autocomplete="off">
                        <div class="ss-dropdown" id="dropdown-ejemplar"></div>
                    </div>
                </div>
                <div class="form-group">
                    <label>Dias de Prestamo</label>
                    <input type="number" id="pre-dias" value="15" min="1" max="60">
                </div>
                <div class="form-group">
                    <label>Observaciones</label>
                    <textarea id="pre-obs" rows="2" placeholder="Notas opcionales..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button onclick="cerrarModal()" class="px-4 py-2 text-sm font-medium text-slate-600 bg-slate-200 rounded-lg hover:bg-slate-300 transition">Cancelar</button>
                <button onclick="guardarPrestamo()" class="btn-success text-sm">Registrar Prestamo</button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="frontend/js/utils.js"></script>
    <script src="frontend/js/prestamos.js"></script>
</body>
</html>
