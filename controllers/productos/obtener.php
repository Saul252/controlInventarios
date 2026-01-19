<?php
session_start();

header('Content-Type: application/json; charset=UTF-8');

ini_set('display_errors', 0);
error_reporting(0);


require_once "../../config/conexion.php";

/* =====================
   SEGURIDAD
===================== */
if (!isset($_SESSION['user_id'], $_SESSION['rol'])) {
    echo json_encode([
        'ok' => false,
        'msg' => 'Sesión inválida'
    ]);
    exit;
}

if (strtoupper($_SESSION['rol']) !== 'ADMIN') {
    echo json_encode([
        'ok' => false,
        'msg' => 'Permisos insuficientes'
    ]);
    exit;
}

/* =====================
   VALIDAR ID
===================== */
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    echo json_encode([
        'ok' => false,
        'msg' => 'ID inválido'
    ]);
    exit;
}

/* =====================
   PRODUCTO
===================== */
$stmt = $conexion->prepare("
    SELECT id, nombre, unidad, activo
    FROM productos
    WHERE id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    echo json_encode([
        'ok' => false,
        'msg' => 'Producto no encontrado'
    ]);
    exit;
}

$producto = $res->fetch_assoc();

/* =====================
   STOCK POR SUCURSAL
===================== */
$stock = [];

$q = $conexion->prepare("
    SELECT 
        u.id AS ubicacion_id,
        u.nombre AS ubicacion,
        IFNULL(s.cantidad, 0) AS cantidad
    FROM ubicaciones u
    LEFT JOIN stock_actual s 
        ON s.ubicacion_id = u.id 
        AND s.producto_id = ?
    WHERE u.activo = 1
    ORDER BY u.nombre
");

$q->bind_param("i", $id);
$q->execute();
$r = $q->get_result();

while ($row = $r->fetch_assoc()) {
    $stock[] = $row;
}

/* =====================
   RESPUESTA
===================== */
echo json_encode([
    'ok' => true,
    'producto' => [
        'id'     => (int)$producto['id'],
        'nombre' => $producto['nombre'],
        'unidad' => $producto['unidad'],
        'activo' => (int)$producto['activo'],
        'stock'  => $stock
    ]
]);
