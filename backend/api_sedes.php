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
if ($_SESSION['usuario_activo']['rol'] === 'bibliotecario') {
    http_response_code(403);
    echo json_encode(['error' => 'No autorizado']);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'listar':
            $q = $_GET['q'] ?? '';
            $sql = "SELECT * FROM sedes_biblioteca";
            $params = [];
            if (!empty($q)) {
                $sql .= " WHERE nombre LIKE :q OR direccion LIKE :q2";
                $params = [':q' => "%$q%", ':q2' => "%$q%"];
            }
            $sql .= " ORDER BY nombre ASC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
            break;

        case 'todas':
            $stmt = $pdo->query("SELECT id, nombre FROM sedes_biblioteca WHERE estado = 1 ORDER BY nombre ASC");
            echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
            break;

        case 'guardar':
            if ($method !== 'POST') { http_response_code(405); echo json_encode(['error' => 'Metodo no permitido']); break; }
            $input = json_decode(file_get_contents('php://input'), true);
            $id        = isset($input['id']) ? (int)$input['id'] : null;
            $nombre    = trim($input['nombre'] ?? '');
            $direccion = trim($input['direccion'] ?? '');
            $telefono  = trim($input['telefono'] ?? '');
            $horario   = trim($input['horario'] ?? '');
            $estado    = isset($input['estado']) ? (int)$input['estado'] : 1;

            if ($nombre === '' || $direccion === '') {
                echo json_encode(['error' => 'Nombre y Direccion son obligatorios']); break;
            }

            if ($id) {
                $stmtEstado = $pdo->prepare("SELECT estado FROM sedes_biblioteca WHERE id = :id");
                $stmtEstado->execute([':id' => $id]);
                $estadoAnterior = $stmtEstado->fetchColumn();

                $stmt = $pdo->prepare("UPDATE sedes_biblioteca SET nombre=:n, direccion=:d, telefono=:t, horario=:h, estado=:e WHERE id=:id");
                $stmt->execute([':n'=>$nombre, ':d'=>$direccion, ':t'=>$telefono, ':h'=>$horario, ':e'=>$estado, ':id'=>$id]);

                if ($estadoAnterior !== false && (int)$estadoAnterior !== $estado) {
                    if ($estado === 0) {
                        $pdo->prepare("UPDATE ejemplares SET estado = 'Sin disponibilidad' WHERE sede_id = :id AND estado = 'Disponible'")->execute([':id' => $id]);
                    } else {
                        $pdo->prepare("UPDATE ejemplares SET estado = 'Disponible' WHERE sede_id = :id AND estado = 'Sin disponibilidad'")->execute([':id' => $id]);
                    }
                }

                echo json_encode(['success' => true, 'mensaje' => 'Sede actualizada']);
            } else {
                $stmt = $pdo->prepare("INSERT INTO sedes_biblioteca (nombre, direccion, telefono, horario, estado) VALUES (:n,:d,:t,:h,:e)");
                $stmt->execute([':n'=>$nombre, ':d'=>$direccion, ':t'=>$telefono, ':h'=>$horario, ':e'=>$estado]);
                echo json_encode(['success' => true, 'mensaje' => 'Sede creada']);
            }
            break;

        case 'eliminar':
            if ($method !== 'POST') { http_response_code(405); echo json_encode(['error' => 'Metodo no permitido']); break; }
            $input = json_decode(file_get_contents('php://input'), true);
            $id = (int)($input['id'] ?? 0);
            if ($id <= 0) { echo json_encode(['error' => 'ID requerido']); break; }
            $stmtEj = $pdo->prepare("SELECT id FROM ejemplares WHERE sede_id = :id LIMIT 1");
            $stmtEj->execute([':id' => $id]);
            if ($stmtEj->fetch()) {
                echo json_encode(['error' => 'No se puede eliminar: la sede tiene ejemplares registrados.']); break;
            }
            $stmt = $pdo->prepare("DELETE FROM sedes_biblioteca WHERE id = :id");
            $stmt->execute([':id' => $id]);
            if ($stmt->rowCount() === 0) { echo json_encode(['error' => 'Sede no encontrada']); break; }
            echo json_encode(['success' => true, 'mensaje' => 'Sede eliminada']);
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => 'Accion no valida']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
}
