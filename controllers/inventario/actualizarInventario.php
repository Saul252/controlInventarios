<?php
session_start();

/* RESPUESTA JSON LIMPIA */
header('Content-Type: application/json; charset=UTF-8');
ini_set('display_errors', 0);
error_reporting(0);

require_once "../../config/conexion.php";

/* =====================
   ZONA HORARIA
===================== */
date_default_timezone_set('America/Mexico_City');

/* =====================
   VALIDAR SESIÓN
===================== */
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        'ok' => false,
        'msg' => 'Sesión expirada'
    ]);
    exit;
}

$user_id = (int) $_SESSION['user_id'];

/* =====================
   VALIDAR MÉTODO
===================== */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'ok' => false,
        'msg' => 'Método no permitido'
    ]);
    exit;
}

/* =====================
   DATOS
===================== */
$ubicacion_id = (int) ($_POST['ubicacion_id'] ?? 0);
$stocks       = $_POST['stock_actual'] ?? [];

/* FECHA SOLO BACKEND */
$fecha = date('Y-m-d');

if (!$ubicacion_id || empty($stocks)) {
    http_response_code(400);
    echo json_encode([
        'ok' => false,
        'msg' => 'Datos incompletos'
    ]);
    exit;
}

/* =====================
   TRANSACCIÓN
===================== */
$conexion->begin_transaction();

try {

    foreach ($stocks as $producto_id => $cantidad) {

        $producto_id = (int) $producto_id;
        $cantidad    = (float) $cantidad;

        /* STOCK ANTERIOR */
        $stmtPrev = $conexion->prepare("
            SELECT cantidad
            FROM stock_actual
            WHERE producto_id = ?
              AND ubicacion_id = ?
        ");
        $stmtPrev->bind_param("ii", $producto_id, $ubicacion_id);
        $stmtPrev->execute();
        $prev = $stmtPrev->get_result()->fetch_assoc();

        $cantidad_anterior = $prev['cantidad'] ?? 0;

        /* INVENTARIO DIARIO */
        $stmtInv = $conexion->prepare("
            INSERT INTO inventario_diario
                (producto_id, ubicacion_id, fecha, cantidad, usuario_id)
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                cantidad = VALUES(cantidad),
                usuario_id = VALUES(usuario_id)
        ");
        $stmtInv->bind_param(
            "iisdi",
            $producto_id,
            $ubicacion_id,
            $fecha,
            $cantidad,
            $user_id
        );
        $stmtInv->execute();

        /* STOCK ACTUAL */
        $stmtStock = $conexion->prepare("
            INSERT INTO stock_actual
                (producto_id, ubicacion_id, cantidad)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE
                cantidad = VALUES(cantidad)
        ");
        $stmtStock->bind_param("iid", $producto_id, $ubicacion_id, $cantidad);
        $stmtStock->execute();

        /* MOVIMIENTO */
        if ($cantidad != $cantidad_anterior) {
            $stmtMov = $conexion->prepare("
                INSERT INTO movimientos_inventario
                    (producto_id, ubicacion_id, cantidad_anterior, cantidad_nueva, motivo, usuario_id)
                VALUES (?, ?, ?, ?, 'AJUSTE DIARIO', ?)
            ");
            $stmtMov->bind_param(
                "iiddi",
                $producto_id,
                $ubicacion_id,
                $cantidad_anterior,
                $cantidad,
                $user_id
            );
            $stmtMov->execute();
        }
    }

    $conexion->commit();

    echo json_encode([
        'ok' => true,
        'msg' => 'Inventario guardado correctamente'
    ]);
    exit;

} catch (Throwable $e) {

    $conexion->rollback();

    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'msg' => 'Error al guardar inventario'
    ]);
    exit;
}
