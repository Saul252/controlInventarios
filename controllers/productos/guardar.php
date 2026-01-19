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
$id     = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$nombre = trim($_POST['nombre'] ?? '');
$unidad = trim($_POST['unidad'] ?? '');
$stock  = $_POST['stock'] ?? [];

if ($nombre === '' || $unidad === '') {
    echo json_encode(['ok' => false, 'msg' => 'Datos incompletos']);
    exit;
}

/* =====================
   TRANSACCIÓN
===================== */
$conexion->begin_transaction();

try {

    /* =====================
       NUEVO PRODUCTO
    ===================== */
    if ($id === 0) {

        $stmt = $conexion->prepare("
            INSERT INTO productos (nombre, unidad)
            VALUES (?, ?)
        ");
        $stmt->bind_param("ss", $nombre, $unidad);
        $stmt->execute();

        $id = $conexion->insert_id;
    }
    /* =====================
       EDITAR PRODUCTO
    ===================== */
    else {

        $stmt = $conexion->prepare("
            UPDATE productos
            SET nombre = ?, unidad = ?
            WHERE id = ?
        ");
        $stmt->bind_param("ssi", $nombre, $unidad, $id);
        $stmt->execute();
    }

    /* =====================
       STOCK POR SUCURSAL
    ===================== */
    foreach ($stock as $ubicacion_id => $cantidad_nueva) {

        $ubicacion_id = (int)$ubicacion_id;
        $cantidad_nueva = (float)$cantidad_nueva;

        /* STOCK ACTUAL */
        $q = $conexion->prepare("
            SELECT cantidad
            FROM stock_actual
            WHERE producto_id = ? AND ubicacion_id = ?
        ");
        $q->bind_param("ii", $id, $ubicacion_id);
        $q->execute();
        $r = $q->get_result();

        if ($r->num_rows > 0) {

            $row = $r->fetch_assoc();
            $cantidad_anterior = (float)$row['cantidad'];

            if ($cantidad_anterior != $cantidad_nueva) {

                /* UPDATE STOCK */
                $u = $conexion->prepare("
                    UPDATE stock_actual
                    SET cantidad = ?
                    WHERE producto_id = ? AND ubicacion_id = ?
                ");
                $u->bind_param("dii", $cantidad_nueva, $id, $ubicacion_id);
                $u->execute();

                /* MOVIMIENTO */
                $m = $conexion->prepare("
                    INSERT INTO movimientos_inventario
                    (producto_id, ubicacion_id, cantidad_anterior, cantidad_nueva, motivo, usuario_id)
                    VALUES (?, ?, ?, ?, 'EDICION PRODUCTO', ?)
                ");
                $m->bind_param(
                    "iiddi",
                    $id,
                    $ubicacion_id,
                    $cantidad_anterior,
                    $cantidad_nueva,
                    $usuario_id
                );
                $m->execute();
            }

        } else {

            /* INSERT STOCK */
            $i = $conexion->prepare("
                INSERT INTO stock_actual (producto_id, ubicacion_id, cantidad)
                VALUES (?, ?, ?)
            ");
            $i->bind_param("iid", $id, $ubicacion_id, $cantidad_nueva);
            $i->execute();

            /* MOVIMIENTO */
            $m = $conexion->prepare("
                INSERT INTO movimientos_inventario
                (producto_id, ubicacion_id, cantidad_anterior, cantidad_nueva, motivo, usuario_id)
                VALUES (?, ?, 0, ?, 'ALTA PRODUCTO', ?)
            ");
            $m->bind_param(
                "iidi",
                $id,
                $ubicacion_id,
                $cantidad_nueva,
                $usuario_id
            );
            $m->execute();
        }
    }

    $conexion->commit();

    echo json_encode([
        'ok' => true,
        'msg' => $id ? 'Producto guardado correctamente' : 'Producto creado correctamente'
    ]);

} catch (Exception $e) {

    $conexion->rollback();

    echo json_encode([
        'ok' => false,
        'msg' => 'Error al guardar producto'
    ]);
}
