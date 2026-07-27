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

try {
    switch ($action) {
        case 'listar':
            $sql = "SELECT p.*, s.nombre_completo AS socio_nombre, s.cedula AS socio_cedula,
                           l.titulo AS libro_titulo, e.codigo_ejemplar, u.nombre AS usuario_nombre
                    FROM prestamos p
                    INNER JOIN socios s ON p.socio_id = s.id
                    INNER JOIN ejemplares e ON p.ejemplar_id = e.id
                    INNER JOIN libros l ON e.libro_id = l.id
                    LEFT JOIN usuarios u ON p.usuario_id = u.id
                    WHERE 1=1";
            $params = [];

            $fechaInicio = $_GET['fecha_inicio'] ?? '';
            $fechaFin = $_GET['fecha_fin'] ?? '';
            $socioFiltro = $_GET['socio'] ?? '';
            $estadoFiltro = $_GET['estado'] ?? '';

            if (!empty($fechaInicio)) {
                $sql .= " AND p.fecha_prestamo >= :fi";
                $params[':fi'] = $fechaInicio;
            }
            if (!empty($fechaFin)) {
                $sql .= " AND p.fecha_prestamo <= :ff";
                $params[':ff'] = $fechaFin;
            }
            if (!empty($socioFiltro)) {
                $sql .= " AND (s.nombre_completo LIKE :soc OR s.cedula LIKE :soc2)";
                $params[':soc'] = "%$socioFiltro%";
                $params[':soc2'] = "%$socioFiltro%";
            }
            if (!empty($estadoFiltro)) {
                $sql .= " AND p.estado = :est";
                $params[':est'] = $estadoFiltro;
            }

            $sql .= " ORDER BY p.fecha_registro DESC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
            break;

        case 'guardar':
            if ($method !== 'POST') { http_response_code(405); echo json_encode(['error' => 'Metodo no permitido']); break; }
            $input = json_decode(file_get_contents('php://input'), true);
            $socio_id   = (int)($input['socio_id'] ?? 0);
            $ejemplar_id = (int)($input['ejemplar_id'] ?? 0);
            $dias       = (int)($input['dias_prestamo'] ?? 15);
            $obs        = trim($input['observaciones'] ?? '');

            if ($socio_id <= 0 || $ejemplar_id <= 0) {
                echo json_encode(['error' => 'Socio y Ejemplar son obligatorios']); break;
            }

            $stmtCheck = $pdo->prepare("SELECT estado FROM ejemplares WHERE id = :id");
            $stmtCheck->execute([':id' => $ejemplar_id]);
            $ej = $stmtCheck->fetch();
            if (!$ej) { echo json_encode(['error' => 'Ejemplar no encontrado']); break; }
            if ($ej['estado'] !== 'Disponible') { echo json_encode(['error' => 'El ejemplar no esta disponible']); break; }

            $stmtSocio = $pdo->prepare("SELECT id FROM prestamos WHERE socio_id = :s AND estado = 'Activo'");
            $stmtSocio->execute([':s' => $socio_id]);
            if ($stmtSocio->fetch()) {
                echo json_encode(['error' => 'El socio ya tiene un prestamo activo. Debe devolver primero.']); break;
            }

            $pdo->beginTransaction();
            try {
                $fechaPrestamo = date('Y-m-d');
                $fechaDevEsperada = date('Y-m-d', strtotime("+{$dias} days"));
                $usuario_id = $_SESSION['usuario_activo']['id'];

                $stmt = $pdo->prepare("INSERT INTO prestamos (socio_id, ejemplar_id, fecha_prestamo, fecha_devolucion_esperada, estado, observaciones, usuario_id) VALUES (:s,:e,:fp,:fd,'Activo',:o,:u)");
                $stmt->execute([':s'=>$socio_id, ':e'=>$ejemplar_id, ':fp'=>$fechaPrestamo, ':fd'=>$fechaDevEsperada, ':o'=>$obs, ':u'=>$usuario_id]);

                $stmtUpd = $pdo->prepare("UPDATE ejemplares SET estado = 'Prestado' WHERE id = :id");
                $stmtUpd->execute([':id' => $ejemplar_id]);

                $pdo->commit();
                echo json_encode(['success' => true, 'mensaje' => 'Prestamo registrado exitosamente']);
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }
            break;

        case 'devolver':
            if ($method !== 'POST') { http_response_code(405); echo json_encode(['error' => 'Metodo no permitido']); break; }
            $input = json_decode(file_get_contents('php://input'), true);
            $prestamo_id = (int)($input['prestamo_id'] ?? 0);
            if ($prestamo_id <= 0) { echo json_encode(['error' => 'ID de prestamo requerido']); break; }

            $pdo->beginTransaction();
            try {
                $stmtP = $pdo->prepare("SELECT p.*, e.id AS ej_id FROM prestamos p INNER JOIN ejemplares e ON p.ejemplar_id = e.id WHERE p.id = :id FOR UPDATE");
                $stmtP->execute([':id' => $prestamo_id]);
                $prestamo = $stmtP->fetch();
                if (!$prestamo) { $pdo->rollBack(); echo json_encode(['error' => 'Prestamo no encontrado']); break; }
                if ($prestamo['estado'] !== 'Activo') { $pdo->rollBack(); echo json_encode(['error' => 'El prestamo ya fue devuelto o anulado']); break; }

                $stmtDev = $pdo->prepare("UPDATE prestamos SET estado = 'Devuelto', fecha_devolucion_real = CURDATE() WHERE id = :id");
                $stmtDev->execute([':id' => $prestamo_id]);

                $stmtEj = $pdo->prepare("UPDATE ejemplares SET estado = 'Disponible' WHERE id = :id");
                $stmtEj->execute([':id' => $prestamo['ej_id']]);

                $pdo->commit();
                echo json_encode(['success' => true, 'mensaje' => 'Devolucion registrada exitosamente']);
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => 'Accion no valida']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
}
