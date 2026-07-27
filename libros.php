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
    <title>Libros - Sistema Biblioteca</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="frontend/css/styles.css">
</head>
<body class="bg-slate-100">
    <div class="flex min-h-screen">
        <?php include __DIR__ . '/backend/includes/sidebar.php'; ?>
        <div class="main-content flex-1" style="margin-left: 256px;">
            <?php $tituloPagina = 'Catalogo de Libros'; include __DIR__ . '/backend/includes/navbar.php'; ?>
            <div class="p-6">
                <div class="page-header">
                    <h2 class="text-2xl font-extrabold text-indigo-900">Catalogo de Libros</h2>
                    <div class="flex gap-3 items-center">
                        <input type="text" id="input-busqueda" class="search-input" placeholder="Buscar por titulo, autor o ISBN...">
                        <button class="btn-primary" onclick="abrirModal()">+ Nuevo Libro</button>
                    </div>
                </div>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ISBN</th>
                                <th>Titulo</th>
                                <th>Autor</th>
                                <th>Editorial</th>
                                <th>Genero</th>
                                <th>Anio</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="cuerpo-tabla">
                            <tr><td colspan="7" class="text-center text-slate-400 py-8">Cargando...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="modal-libro">
        <div class="modal-box" style="max-width: 550px;">
            <div class="modal-header">
                <h3 id="modal-titulo">Nuevo Libro</h3>
                <button onclick="cerrarModal()" class="text-slate-400 hover:text-slate-600 text-2xl leading-none">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="lib-id">
                <div class="grid grid-cols-2 gap-4">
                    <div class="form-group">
                        <label>ISBN *</label>
                        <input type="text" id="lib-isbn" placeholder="978-X-XXX-XXXXX-X" maxlength="20">
                    </div>
                    <div class="form-group">
                        <label>Genero</label>
                        <input type="text" id="lib-genero" placeholder="Ciencia Ficcion">
                    </div>
                </div>
                <div class="form-group">
                    <label>Titulo *</label>
                    <input type="text" id="lib-titulo" placeholder="Titulo del libro" maxlength="200">
                </div>
                <div class="form-group">
                    <label>Autor *</label>
                    <input type="text" id="lib-autor" placeholder="Nombre del autor" maxlength="150">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="form-group">
                        <label>Editorial</label>
                        <input type="text" id="lib-editorial" placeholder="Editorial" maxlength="100">
                    </div>
                    <div class="form-group">
                        <label>Anio Publicacion</label>
                        <input type="number" id="lib-anio" placeholder="2024" min="1000" max="2099">
                    </div>
                </div>
                <div class="form-group">
                    <label>Num. Paginas</label>
                    <input type="number" id="lib-paginas" placeholder="350" min="1">
                </div>
                <div class="form-group">
                    <label>Descripcion</label>
                    <textarea id="lib-desc" rows="2" placeholder="Breve descripcion del libro"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button onclick="cerrarModal()" class="px-4 py-2 text-sm font-medium text-slate-600 bg-slate-200 rounded-lg hover:bg-slate-300 transition">Cancelar</button>
                <button onclick="guardarLibro()" class="btn-primary text-sm">Guardar</button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="frontend/js/utils.js"></script>
    <script src="frontend/js/libros.js"></script>
</body>
</html>
