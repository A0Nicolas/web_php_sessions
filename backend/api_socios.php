<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=UTF-8');
require_once __DIR__ . '/conexion.php';
session_start();
if (!isset($_SESSION['usuario_activo'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autenticado']);
    exit();
}
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';
$rol = $_SESSION['usuario_activo']['rol'];

try {
    switch ($action) {
        case 'listar':
            $q = $_GET['q'] ?? '';
            $sql = "SELECT * FROM socios";
            $params = [];
            if (!empty($q)) {
                $sql .= " WHERE nombre_completo LIKE :q OR cedula LIKE :q2";
                $params = [':q' => "%$q%", ':q2' => "%$q%"];
            }
            $sql .= " ORDER BY nombre_completo ASC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
            break;

        case 'todos':
            $stmt = $pdo->query("SELECT id, nombre_completo, cedula FROM socios ORDER BY nombre_completo ASC");
            echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
            break;

        case 'buscar':
            $q = $_GET['q'] ?? '';
            if (strlen($q) < 1) { echo json_encode([]); break; }
            $sql = "SELECT id, nombre_completo AS nombre, cedula, telefono FROM socios WHERE nombre_completo LIKE :q OR cedula LIKE :q2 ORDER BY nombre_completo ASC LIMIT 10";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':q' => "%$q%", ':q2' => "%$q%"]);
            echo json_encode($stmt->fetchAll());
            break;

        case 'guardar':
            if ($method !== 'POST') { http_response_code(405); echo json_encode(['error' => 'Metodo no permitido']); break; }
            $input = json_decode(file_get_contents('php://input'), true);
            $id      = isset($input['id']) ? (int)$input['id'] : null;
            $cedula  = trim($input['cedula'] ?? '');
            $nombre  = trim($input['nombre_completo'] ?? '');
            $correo  = trim($input['correo'] ?? '');
            $telefono = trim($input['telefono'] ?? '');
            $direccion = trim($input['direccion'] ?? '');

            if ($cedula === '' || $nombre === '') {
                echo json_encode(['error' => 'Cedula y Nombre son obligatorios']); break;
            }
            if (!preg_match('/^\d{10}$/', $cedula)) {
                echo json_encode(['error' => 'La cedula debe tener exactamente 10 digitos']); break;
            }

            $sqlCheck = "SELECT id FROM socios WHERE cedula = :c";
            $paramsC = [':c' => $cedula];
            if ($id) { $sqlCheck .= " AND id != :id"; $paramsC[':id'] = $id; }
            $stmtC = $pdo->prepare($sqlCheck);
            $stmtC->execute($paramsC);
            if ($stmtC->fetch()) {
                echo json_encode(['error' => 'La cedula ya esta registrada']); break;
            }

            if ($id) {
                $stmt = $pdo->prepare("UPDATE socios SET cedula=:c, nombre_completo=:n, correo=:e, telefono=:t, direccion=:d WHERE id=:id");
                $stmt->execute([':c'=>$cedula, ':n'=>$nombre, ':e'=>$correo, ':t'=>$telefono, ':d'=>$direccion, ':id'=>$id]);
                echo json_encode(['success' => true, 'mensaje' => 'Socio actualizado']);
            } else {
                $stmt = $pdo->prepare("INSERT INTO socios (cedula, nombre_completo, correo, telefono, direccion) VALUES (:c,:n,:e,:t,:d)");
                $stmt->execute([':c'=>$cedula, ':n'=>$nombre, ':e'=>$correo, ':t'=>$telefono, ':d'=>$direccion]);
                echo json_encode(['success' => true, 'mensaje' => 'Socio creado']);
            }
            break;

        case 'eliminar':
            if ($rol === 'bibliotecario') { http_response_code(403); echo json_encode(['error' => 'No autorizado']); break; }
            if ($method !== 'POST') { http_response_code(405); echo json_encode(['error' => 'Metodo no permitido']); break; }
            $input = json_decode(file_get_contents('php://input'), true);
            $id = (int)($input['id'] ?? 0);
            if ($id <= 0) { echo json_encode(['error' => 'ID requerido']); break; }
            $stmtP = $pdo->prepare("SELECT id FROM prestamos WHERE socio_id = :id AND estado = 'Activo' LIMIT 1");
            $stmtP->execute([':id' => $id]);
            if ($stmtP->fetch()) {
                echo json_encode(['error' => 'No se puede eliminar: el socio tiene prestamos activos.']); break;
            }
            $stmt = $pdo->prepare("DELETE FROM socios WHERE id = :id");
            $stmt->execute([':id' => $id]);
            if ($stmt->rowCount() === 0) { echo json_encode(['error' => 'Socio no encontrado']); break; }
            echo json_encode(['success' => true, 'mensaje' => 'Socio eliminado']);
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => 'Accion no valida']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
}
