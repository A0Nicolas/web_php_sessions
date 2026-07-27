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
    <title>Sedes - Sistema Biblioteca</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="frontend/css/styles.css">
</head>
<body class="bg-slate-100">
    <div class="flex min-h-screen">
        <?php include __DIR__ . '/backend/includes/sidebar.php'; ?>
        <div class="main-content flex-1" style="margin-left: 256px;">
            <?php $tituloPagina = 'Gestion de Sedes'; include __DIR__ . '/backend/includes/navbar.php'; ?>
            <div class="p-6">
                <div class="page-header">
                    <h2 class="text-2xl font-extrabold text-indigo-900">Sedes Bibliotecarias</h2>
                    <div class="flex gap-3 items-center">
                        <input type="text" id="input-busqueda" class="search-input" placeholder="Buscar por nombre o direccion...">
                        <button class="btn-primary" onclick="abrirModal()">+ Nueva Sede</button>
                    </div>
                </div>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Direccion</th>
                                <th>Telefono</th>
                                <th>Horario</th>
                                <th>Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="cuerpo-tabla">
                            <tr><td colspan="6" class="text-center text-slate-400 py-8">Cargando...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal-overlay" id="modal-sede">
        <div class="modal-box">
            <div class="modal-header">
                <h3 id="modal-titulo">Nueva Sede</h3>
                <button onclick="cerrarModal()" class="text-slate-400 hover:text-slate-600 text-2xl leading-none">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="sed-id">
                <div class="form-group">
                    <label>Nombre *</label>
                    <input type="text" id="sed-nombre" placeholder="Nombre de la sede" maxlength="100">
                </div>
                <div class="form-group">
                    <label>Direccion *</label>
                    <input type="text" id="sed-direccion" placeholder="Direccion completa" maxlength="200">
                </div>
                <div class="form-group">
                    <label>Telefono</label>
                    <input type="text" id="sed-telefono" placeholder="022345678" maxlength="10" inputmode="numeric" pattern="\d{7,10}" data-solo-numeros>
                </div>
                <div class="form-group">
                    <label>Horario</label>
                    <input type="text" id="sed-horario" placeholder="Ej: Lun-Vie 8:00 - 18:00">
                </div>
                <div class="form-group">
                    <label>Estado</label>
                    <select id="sed-estado">
                        <option value="1">Activa</option>
                        <option value="0">Inactiva</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button onclick="cerrarModal()" class="px-4 py-2 text-sm font-medium text-slate-600 bg-slate-200 rounded-lg hover:bg-slate-300 transition">Cancelar</button>
                <button onclick="guardarSede()" class="btn-primary text-sm">Guardar</button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="frontend/js/utils.js"></script>
    <script src="frontend/js/sedes.js"></script>
</body>
</html>
