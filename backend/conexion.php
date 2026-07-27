<?php
declare(strict_types=1);

$host     = '127.0.0.1';
$port     = '3307';
$db       = 'biblioteca_db';
$user     = 'root';
$password = 'root123';
$charset  = 'utf8mb4';

$opciones = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

$dsn = "mysql:host={$host};port={$port};dbname={$db};charset={$charset}";

try {
    $pdo = new PDO($dsn, $user, $password, $opciones);
} catch (PDOException $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Error de conexion: ' . $e->getMessage()]);
    exit();
}
