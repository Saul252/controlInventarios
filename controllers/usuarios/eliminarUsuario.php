<?php
session_start();
require_once "../../config/conexion.php";

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['ok' => false, 'msg' => 'Sesión inválida']);
    exit;
}

$id = (int) ($_POST['id'] ?? 0);

if ($id <= 0) {
    echo json_encode(['ok' => false, 'msg' => 'ID inválido']);
    exit;
}

$stmt = $conexion->prepare("DELETE FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode([
        'ok' => true,
        'msg' => 'Usuario eliminado correctamente'
    ]);
} else {
    echo json_encode([
        'ok' => false,
        'msg' => 'No se pudo eliminar'
    ]);
}
