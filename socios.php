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
    <title>Socios - Sistema Biblioteca</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="frontend/css/styles.css">
</head>
<body class="bg-slate-100">
    <div class="flex min-h-screen">
        <?php include __DIR__ . '/backend/includes/sidebar.php'; ?>
        <div class="main-content flex-1" style="margin-left: 256px;">
            <?php $tituloPagina = 'Gestion de Socios'; include __DIR__ . '/backend/includes/navbar.php'; ?>
            <div class="p-6">
                <div class="page-header">
                    <h2 class="text-2xl font-extrabold text-indigo-900">Socios Registrados</h2>
                    <div class="flex gap-3 items-center">
                        <input type="text" id="input-busqueda" class="search-input" placeholder="Buscar por nombre o cedula...">
                        <button class="btn-primary" onclick="abrirModal()">+ Nuevo Socio</button>
                    </div>
                </div>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Cedula</th>
                                <th>Nombre Completo</th>
                                <th>Telefono</th>
                                <th>Correo</th>
                                <th>Registro</th>
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

    <div class="modal-overlay" id="modal-socio">
        <div class="modal-box">
            <div class="modal-header">
                <h3 id="modal-titulo">Nuevo Socio</h3>
                <button onclick="cerrarModal()" class="text-slate-400 hover:text-slate-600 text-2xl leading-none">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="soc-id">
                <div class="form-group">
                    <label>Cedula / RUC *</label>
                    <input type="text" id="soc-cedula" placeholder="10 digitos" maxlength="10" inputmode="numeric" pattern="\d{10}" data-solo-numeros>
                </div>
                <div class="form-group">
                    <label>Nombre Completo *</label>
                    <input type="text" id="soc-nombre" placeholder="Nombres y apellidos" maxlength="150">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="form-group">
                        <label>Correo Electronico</label>
                        <input type="email" id="soc-correo" placeholder="correo@email.com" maxlength="100">
                    </div>
                    <div class="form-group">
                        <label>Telefono</label>
                        <input type="text" id="soc-telefono" placeholder="0991234567" maxlength="10" inputmode="numeric" pattern="\d{10}" data-solo-numeros>
                    </div>
                </div>
                <div class="form-group">
                    <label>Direccion</label>
                    <input type="text" id="soc-direccion" placeholder="Direccion de residencia">
                </div>
            </div>
            <div class="modal-footer">
                <button onclick="cerrarModal()" class="px-4 py-2 text-sm font-medium text-slate-600 bg-slate-200 rounded-lg hover:bg-slate-300 transition">Cancelar</button>
                <button onclick="guardarSocio()" class="btn-primary text-sm">Guardar</button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>const ROL_USUARIO = '<?php echo $usuario['rol']; ?>';</script>
    <script src="frontend/js/utils.js"></script>
    <script src="frontend/js/socios.js"></script>
</body>
</html>
