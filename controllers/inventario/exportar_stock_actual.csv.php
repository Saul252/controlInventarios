<?php
session_start();
require_once "../../config/conexion.php";

/* =====================
   SEGURIDAD
===================== */
if (!isset($_SESSION['user_id'], $_SESSION['rol'])) {
    exit;
}

$rol      = strtoupper(trim($_SESSION['rol']));
$userUbic = $_SESSION['ubicacion_id'] ?? null;

/* =====================
   UBICACIONES SEGÚN ROL
===================== */
$ubicaciones = [];

if ($rol === 'ADMIN') {

    $res = $conexion->query("
        SELECT id, nombre
        FROM ubicaciones
        WHERE activo = 1
        ORDER BY nombre
    ");

} else {

    if ($userUbic === null) {
        exit;
    }

    $stmt = $conexion->prepare("
        SELECT id, nombre
        FROM ubicaciones
        WHERE id = ? AND activo = 1
    ");
    $stmt->bind_param("i", $userUbic);
    $stmt->execute();
    $res = $stmt->get_result();
}

while ($u = $res->fetch_assoc()) {
    $ubicaciones[$u['id']] = $u['nombre'];
}

/* =====================
   PRODUCTOS + STOCK ACTUAL
===================== */
$data = [];

foreach ($ubicaciones as $ubicacion_id => $ubicacion_nombre) {

    $stmt = $conexion->prepare("
        SELECT 
            p.nombre AS producto,
            IFNULL(sa.cantidad, 0) AS cantidad
        FROM ubicacion_productos up
        JOIN productos p ON p.id = up.producto_id
        LEFT JOIN stock_actual sa
               ON sa.producto_id = up.producto_id
              AND sa.ubicacion_id = up.ubicacion_id
        WHERE up.ubicacion_id = ?
          AND up.activo = 1
          AND p.activo = 1
        ORDER BY p.nombre
    ");
    $stmt->bind_param("i", $ubicacion_id);
    $stmt->execute();
    $resProd = $stmt->get_result();

    while ($row = $resProd->fetch_assoc()) {
        $data[$row['producto']][$ubicacion_nombre] = $row['cantidad'];
    }
}

/* =====================
   EXPORTAR CSV
===================== */
$filename = "stock_actual_" . date('Y-m-d') . ".csv";

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="'.$filename.'"');

$out = fopen('php://output', 'w');

/* BOM UTF-8 (Excel) */
fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

/* Header */
fputcsv($out, array_merge(['Producto'], array_values($ubicaciones)));

/* Filas */
foreach ($data as $producto => $stocks) {
    $row = [$producto];
    foreach ($ubicaciones as $nombre) {
        $row[] = $stocks[$nombre] ?? 0;
    }
    fputcsv($out, $row);
}

fclose($out);
exit;
