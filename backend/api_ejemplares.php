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
            $sede = $_GET['sede_id'] ?? '';
            $sql = "SELECT e.*, l.titulo AS libro_titulo, l.isbn, s.nombre AS sede_nombre
                    FROM ejemplares e
                    INNER JOIN libros l ON e.libro_id = l.id
                    INNER JOIN sedes_biblioteca s ON e.sede_id = s.id
                    WHERE 1=1";
            $params = [];
            if (!empty($q)) {
                $sql .= " AND (e.codigo_ejemplar LIKE :q OR l.titulo LIKE :q2)";
                $params[':q'] = "%$q%";
                $params[':q2'] = "%$q%";
            }
            if ($sede !== '') {
                $sql .= " AND e.sede_id = :sede";
                $params[':sede'] = (int)$sede;
            }
            $sql .= " ORDER BY e.codigo_ejemplar ASC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
            break;

        case 'disponibles':
            $stmt = $pdo->prepare("SELECT e.id, e.codigo_ejemplar, l.titulo FROM ejemplares e INNER JOIN libros l ON e.libro_id = l.id WHERE e.estado = 'Disponible' ORDER BY l.titulo ASC");
            $stmt->execute();
            echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
            break;

        case 'guardar':
            if ($rol === 'bibliotecario') { http_response_code(403); echo json_encode(['error' => 'No autorizado']); break; }
            if ($method !== 'POST') { http_response_code(405); echo json_encode(['error' => 'Metodo no permitido']); break; }
            $input = json_decode(file_get_contents('php://input'), true);
            $id       = isset($input['id']) ? (int)$input['id'] : null;
            $libro_id = (int)($input['libro_id'] ?? 0);
            $sede_id  = (int)($input['sede_id'] ?? 0);
            $codigo   = trim($input['codigo_ejemplar'] ?? '');
            $estado   = $input['estado'] ?? 'Disponible';

            if ($libro_id <= 0 || $sede_id <= 0 || $codigo === '') {
                echo json_encode(['error' => 'Libro, Sede y Codigo son obligatorios']); break;
            }

            $estados = ['Disponible', 'Prestado', 'Danado', 'Sin disponibilidad'];
            if (!in_array($estado, $estados)) {
                echo json_encode(['error' => 'Estado no valido']); break;
            }

            $sqlCheck = "SELECT id FROM ejemplares WHERE codigo_ejemplar = :cod";
            $paramsC = [':cod' => $codigo];
            if ($id) { $sqlCheck .= " AND id != :id"; $paramsC[':id'] = $id; }
            $stmtC = $pdo->prepare($sqlCheck);
            $stmtC->execute($paramsC);
            if ($stmtC->fetch()) {
                echo json_encode(['error' => 'Ya existe un ejemplar con ese codigo']); break;
            }

            if ($id) {
                $stmt = $pdo->prepare("UPDATE ejemplares SET libro_id=:l, sede_id=:s, codigo_ejemplar=:c, estado=:e WHERE id=:id");
                $stmt->execute([':l'=>$libro_id, ':s'=>$sede_id, ':c'=>$codigo, ':e'=>$estado, ':id'=>$id]);
                echo json_encode(['success' => true, 'mensaje' => 'Ejemplar actualizado']);
            } else {
                $stmt = $pdo->prepare("INSERT INTO ejemplares (libro_id, sede_id, codigo_ejemplar, estado) VALUES (:l,:s,:c,:e)");
                $stmt->execute([':l'=>$libro_id, ':s'=>$sede_id, ':c'=>$codigo, ':e'=>$estado]);
                echo json_encode(['success' => true, 'mensaje' => 'Ejemplar creado']);
            }
            break;

        case 'eliminar':
            if ($rol === 'bibliotecario') { http_response_code(403); echo json_encode(['error' => 'No autorizado']); break; }
            if ($method !== 'POST') { http_response_code(405); echo json_encode(['error' => 'Metodo no permitido']); break; }
            $input = json_decode(file_get_contents('php://input'), true);
            $id = (int)($input['id'] ?? 0);
            if ($id <= 0) { echo json_encode(['error' => 'ID requerido']); break; }
            $stmtP = $pdo->prepare("SELECT id FROM prestamos WHERE ejemplar_id = :id AND estado = 'Activo' LIMIT 1");
            $stmtP->execute([':id' => $id]);
            if ($stmtP->fetch()) {
                echo json_encode(['error' => 'No se puede eliminar: el ejemplar tiene un prestamo activo.']); break;
            }
            $stmt = $pdo->prepare("DELETE FROM ejemplares WHERE id = :id");
            $stmt->execute([':id' => $id]);
            if ($stmt->rowCount() === 0) { echo json_encode(['error' => 'Ejemplar no encontrado']); break; }
            echo json_encode(['success' => true, 'mensaje' => 'Ejemplar eliminado']);
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => 'Accion no valida']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
}
