<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php');
    exit();
}

$usuarioInput = trim($_POST['usuario'] ?? '');
$passwordInput = $_POST['password'] ?? '';

try {
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE usuario = :u AND estado = 1");
    $stmt->execute([':u' => $usuarioInput]);
    $usuarioDB = $stmt->fetch();

    $passwordOk = false;
    if ($usuarioDB) {
        if (password_verify($passwordInput, $usuarioDB['password_hash'])) {
            $passwordOk = true;
        } elseif ($passwordInput === $usuarioDB['password_hash']) {
            $passwordOk = true;
        }
    }

    if ($usuarioDB && $passwordOk) {
        $_SESSION['usuario_activo'] = [
            'id'       => $usuarioDB['id'],
            'usuario'  => $usuarioDB['usuario'],
            'nombre'   => $usuarioDB['nombre'],
            'rol'      => $usuarioDB['rol'],
        ];
        header('Location: ../dashboard.php');
        exit();
    } else {
        header('Location: ../index.php?error=1');
        exit();
    }
} catch (PDOException $e) {
    die("Error en la base de datos: " . $e->getMessage());
}
