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
            $sql = "SELECT * FROM libros";
            $params = [];
            if (!empty($q)) {
                $sql .= " WHERE titulo LIKE :q OR autor LIKE :q2 OR isbn LIKE :q3";
                $params = [':q' => "%$q%", ':q2' => "%$q%", ':q3' => "%$q%"];
            }
            $sql .= " ORDER BY titulo ASC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
            break;

        case 'todos':
            $stmt = $pdo->query("SELECT id, titulo FROM libros ORDER BY titulo ASC");
            echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
            break;

        case 'guardar':
            if ($method !== 'POST') { http_response_code(405); echo json_encode(['error' => 'Metodo no permitido']); break; }
            $input = json_decode(file_get_contents('php://input'), true);
            $id       = isset($input['id']) ? (int)$input['id'] : null;
            $isbn     = trim($input['isbn'] ?? '');
            $titulo   = trim($input['titulo'] ?? '');
            $autor    = trim($input['autor'] ?? '');
            $editorial = trim($input['editorial'] ?? '');
            $anio     = !empty($input['anio_publicacion']) ? (int)$input['anio_publicacion'] : null;
            $genero   = trim($input['genero'] ?? '');
            $paginas  = !empty($input['num_paginas']) ? (int)$input['num_paginas'] : null;
            $desc     = trim($input['descripcion'] ?? '');

            if ($isbn === '' || $titulo === '' || $autor === '') {
                echo json_encode(['error' => 'ISBN, Titulo y Autor son obligatorios']); break;
            }

            $sqlCheck = "SELECT id FROM libros WHERE isbn = :isbn";
            $paramsCheck = [':isbn' => $isbn];
            if ($id) { $sqlCheck .= " AND id != :id"; $paramsCheck[':id'] = $id; }
            $stmtC = $pdo->prepare($sqlCheck);
            $stmtC->execute($paramsCheck);
            if ($stmtC->fetch()) {
                echo json_encode(['error' => 'Ya existe un libro con ese ISBN']); break;
            }

            if ($id) {
                $stmt = $pdo->prepare("UPDATE libros SET isbn=:isbn, titulo=:t, autor=:a, editorial=:e, anio_publicacion=:y, genero=:g, num_paginas=:p, descripcion=:d WHERE id=:id");
                $stmt->execute([':isbn'=>$isbn, ':t'=>$titulo, ':a'=>$autor, ':e'=>$editorial, ':y'=>$anio, ':g'=>$genero, ':p'=>$paginas, ':d'=>$desc, ':id'=>$id]);
                echo json_encode(['success' => true, 'mensaje' => 'Libro actualizado']);
            } else {
                $stmt = $pdo->prepare("INSERT INTO libros (isbn, titulo, autor, editorial, anio_publicacion, genero, num_paginas, descripcion) VALUES (:isbn,:t,:a,:e,:y,:g,:p,:d)");
                $stmt->execute([':isbn'=>$isbn, ':t'=>$titulo, ':a'=>$autor, ':e'=>$editorial, ':y'=>$anio, ':g'=>$genero, ':p'=>$paginas, ':d'=>$desc]);
                echo json_encode(['success' => true, 'mensaje' => 'Libro creado']);
            }
            break;

        case 'eliminar':
            if ($method !== 'POST') { http_response_code(405); echo json_encode(['error' => 'Metodo no permitido']); break; }
            $input = json_decode(file_get_contents('php://input'), true);
            $id = (int)($input['id'] ?? 0);
            if ($id <= 0) { echo json_encode(['error' => 'ID requerido']); break; }
            $stmtEj = $pdo->prepare("SELECT id FROM ejemplares WHERE libro_id = :id LIMIT 1");
            $stmtEj->execute([':id' => $id]);
            if ($stmtEj->fetch()) {
                echo json_encode(['error' => 'No se puede eliminar: el libro tiene ejemplares registrados.']); break;
            }
            $stmt = $pdo->prepare("DELETE FROM libros WHERE id = :id");
            $stmt->execute([':id' => $id]);
            if ($stmt->rowCount() === 0) { echo json_encode(['error' => 'Libro no encontrado']); break; }
            echo json_encode(['success' => true, 'mensaje' => 'Libro eliminado']);
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => 'Accion no valida']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
}
