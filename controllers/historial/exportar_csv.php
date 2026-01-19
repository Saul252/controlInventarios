<?php
session_start();

require_once "../../config/conexion.php";

date_default_timezone_set('America/Mexico_City');

/* =====================
   SEGURIDAD
===================== */
if (!isset($_SESSION['user_id'], $_SESSION['rol'])) {
    die("Acceso no autorizado");
}

if (strtoupper($_SESSION['rol']) !== 'ADMIN') {
    die("Acceso solo para administradores");
}

/* =====================
   FECHA
===================== */
$fecha = $_GET['fecha'] ?? date('Y-m-d');
$hoy   = date('Y-m-d');

/* =====================
   HEADERS CSV
===================== */
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=historial_inventario_' . $fecha . '.csv');

$output = fopen('php://output', 'w');

/* =====================
   UBICACIONES
===================== */
$ubicaciones = [];
$resU = $conexion->query("
    SELECT id, nombre
    FROM ubicaciones
    WHERE activo = 1
    ORDER BY nombre
");
while ($u = $resU->fetch_assoc()) {
    $ubicaciones[] = $u;
}

/* =====================
   ENCABEZADO CSV
===================== */
$header = ['Producto', 'Unidad'];
foreach ($ubicaciones as $u) {
    $header[] = $u['nombre'];
}
fputcsv($output, $header);

/* =====================
   PRODUCTOS
===================== */
$productos = $conexion->query("
    SELECT id, nombre, unidad
    FROM productos
    WHERE activo = 1
    ORDER BY nombre
");

/* =====================
   STOCK
===================== */
$stock = [];

if ($fecha === $hoy) {

    $res = $conexion->query("
        SELECT producto_id, ubicacion_id, cantidad
        FROM stock_actual
    ");

} else {

    $stmt = $conexion->prepare("
        SELECT producto_id, ubicacion_id, cantidad
        FROM inventario_diario
        WHERE fecha = ?
    ");
    $stmt->bind_param("s", $fecha);
    $stmt->execute();
    $res = $stmt->get_result();
}

while ($r = $res->fetch_assoc()) {
    $stock[$r['producto_id']][$r['ubicacion_id']] = $r['cantidad'];
}

/* =====================
   FILAS CSV
===================== */
while ($p = $productos->fetch_assoc()) {

    $fila = [
        $p['nombre'],
        $p['unidad']
    ];

    foreach ($ubicaciones as $u) {
        $fila[] = $stock[$p['id']][$u['id']] ?? 0;
    }

    fputcsv($output, $fila);
}

fclose($output);
exit;
