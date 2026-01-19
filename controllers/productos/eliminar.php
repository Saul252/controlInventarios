<?php
session_start();

header('Content-Type: application/json; charset=UTF-8');

ini_set('display_errors', 0);
error_reporting(0);

require_once "../../config/conexion.php";

date_default_timezone_set('America/Mexico_City');

/* =====================
   SEGURIDAD
===================== */
if (!isset($_SESSION['user_id'], $_SESSION['rol'])) {
    echo json_encode(['ok' => false, 'msg' => 'Sesión inválida']);
    exit;
}

if (strtoupper($_SESSION['rol']) !== 'ADMIN') {
    echo json_encode(['ok' => false, 'msg' => 'Permisos insuficientes']);
    exit;
}

$usuario_id = (int) $_SESSION['user_id'];

/* =====================
   DATOS
===================== */
$data = json_decode(file_get_contents('php://input'), true);
$id = isset($data['id']) ? (int)$data['id'] : 0;

if ($id <= 0) {
    echo json_encode(['ok' => false, 'msg' => 'ID inválido']);
    exit;
}

/* =====================
   TRANSACCIÓN
===================== */
$conexion->begin_transaction();

try {

    /* =====================
       MOVIMIENTO (REGISTRO)
    ===================== */
    $q = $conexion->prepare("
        SELECT producto_id, ubicacion_id, cantidad
        FROM stock_actual
        WHERE producto_id = ?
    ");
    $q->bind_param("i", $id);
    $q->execute();
    $stocks = $q->get_result();

    while ($s = $stocks->fetch_assoc()) {
        $m = $conexion->prepare("
            INSERT INTO movimientos_inventario
            (producto_id, ubicacion_id, cantidad_anterior, cantidad_nueva, motivo, usuario_id)
            VALUES (?, ?, ?, 0, 'ELIMINADO', ?)
        ");
        $m->bind_param(
            "iidi",
            $id,
            $s['ubicacion_id'],
            $s['cantidad'],
            $usuario_id
        );
        $m->execute();
    }

    /* =====================
       ELIMINACIONES
    ===================== */
    $conexion->query("DELETE FROM inventario_diario WHERE producto_id = $id");
    $conexion->query("DELETE FROM stock_actual WHERE producto_id = $id");
    $conexion->query("DELETE FROM ubicacion_productos WHERE producto_id = $id");
    $conexion->query("DELETE FROM movimientos_inventario WHERE producto_id = $id");
    $conexion->query("DELETE FROM productos WHERE id = $id");

    $conexion->commit();

    echo json_encode([
        'ok' => true,
        'msg' => 'Producto eliminado correctamente'
    ]);

} catch (Exception $e) {

    $conexion->rollback();

    echo json_encode([
        'ok' => false,
        'msg' => 'No se pudo eliminar el producto'
    ]);
}
