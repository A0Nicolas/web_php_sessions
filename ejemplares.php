<?php
declare(strict_types=1);
session_start();
if (!isset($_SESSION['usuario_activo'])) { header('Location: index.php'); exit(); }
$usuario = $_SESSION['usuario_activo'];
if ($usuario['rol'] === 'bibliotecario') { header('Location: dashboard.php'); exit(); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ejemplares - Sistema Biblioteca</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="frontend/css/styles.css">
</head>
<body class="bg-slate-100">
    <div class="flex min-h-screen">
        <?php include __DIR__ . '/backend/includes/sidebar.php'; ?>
        <div class="main-content flex-1" style="margin-left: 256px;">
            <?php $tituloPagina = 'Gestion de Ejemplares'; include __DIR__ . '/backend/includes/navbar.php'; ?>
            <div class="p-6">
                <div class="page-header">
                    <h2 class="text-2xl font-extrabold text-indigo-900">Ejemplares Fisicos</h2>
                    <div class="flex gap-3 items-center">
                        <select id="filtro-sede" class="search-input" style="width: 200px;">
                            <option value="">Todas las sedes</option>
                        </select>
                        <input type="text" id="input-busqueda" class="search-input" placeholder="Buscar por codigo o titulo...">
                        <button class="btn-primary" onclick="abrirModal()">+ Nuevo Ejemplar</button>
                    </div>
                </div>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Codigo</th>
                                <th>Libro</th>
                                <th>Sede</th>
                                <th>Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="cuerpo-tabla">
                            <tr><td colspan="5" class="text-center text-slate-400 py-8">Cargando...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="modal-ejemplar">
        <div class="modal-box">
            <div class="modal-header">
                <h3 id="modal-titulo">Nuevo Ejemplar</h3>
                <button onclick="cerrarModal()" class="text-slate-400 hover:text-slate-600 text-2xl leading-none">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="ej-id">
                <div class="form-group">
                    <label>Libro *</label>
                    <div class="searchable-select" id="wrapper-libro">
                        <input type="hidden" id="ej-libro" value="">
                        <input type="text" class="ss-search" id="ej-libro-search" placeholder="Escriba titulo o autor..." autocomplete="off">
                        <div class="ss-dropdown" id="dropdown-libro"></div>
                    </div>
                </div>
                <div class="form-group">
                    <label>Sede *</label>
                    <div class="searchable-select" id="wrapper-sede">
                        <input type="hidden" id="ej-sede" value="">
                        <input type="text" class="ss-search" id="ej-sede-search" placeholder="Escriba nombre de sede..." autocomplete="off">
                        <div class="ss-dropdown" id="dropdown-sede"></div>
                    </div>
                </div>
                <div class="form-group">
                    <label>Codigo Ejemplar *</label>
                    <input type="text" id="ej-codigo" placeholder="EJ-XXX">
                </div>
                <div class="form-group">
                    <label>Estado</label>
                    <select id="ej-estado">
                        <option value="Disponible">Disponible</option>
                        <option value="Prestado">Prestado</option>
                        <option value="Danado">Danado</option>
                        <option value="Sin disponibilidad">Sin disponibilidad</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button onclick="cerrarModal()" class="px-4 py-2 text-sm font-medium text-slate-600 bg-slate-200 rounded-lg hover:bg-slate-300 transition">Cancelar</button>
                <button onclick="guardarEjemplar()" class="btn-primary text-sm">Guardar</button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="frontend/js/utils.js"></script>
    <script src="frontend/js/ejemplares.js"></script>
</body>
</html>
