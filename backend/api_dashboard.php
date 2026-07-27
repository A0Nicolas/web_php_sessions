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

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'stats':
            $totalLibros = $pdo->query("SELECT COUNT(*) FROM libros")->fetchColumn();
            $ejemplaresDisponibles = $pdo->query("SELECT COUNT(*) FROM ejemplares WHERE estado = 'Disponible'")->fetchColumn();
            $prestamosActivos = $pdo->query("SELECT COUNT(*) FROM prestamos WHERE estado = 'Activo'")->fetchColumn();
            $totalSocios = $pdo->query("SELECT COUNT(*) FROM socios")->fetchColumn();
            echo json_encode([
                'success' => true,
                'libros' => (int)$totalLibros,
                'ejemplares_disponibles' => (int)$ejemplaresDisponibles,
                'prestamos_activos' => (int)$prestamosActivos,
                'socios' => (int)$totalSocios,
            ]);
            break;

        case 'ultimos_prestamos':
            $sql = "SELECT p.id, p.fecha_prestamo, p.fecha_devolucion_esperada, p.estado,
                           s.nombre_completo AS socio,
                           l.titulo AS libro
                    FROM prestamos p
                    INNER JOIN socios s ON p.socio_id = s.id
                    INNER JOIN ejemplares e ON p.ejemplar_id = e.id
                    INNER JOIN libros l ON e.libro_id = l.id
                    ORDER BY p.fecha_registro DESC
                    LIMIT 10";
            $stmt = $pdo->query($sql);
            echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => 'Accion no valida']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
}
