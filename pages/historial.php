<?php
session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once "../config/conexion.php";
require_once __DIR__ . '../../config.php';
require_once BASE_PATH . 'includes/navbar.php';

date_default_timezone_set('America/Mexico_City');

/* =====================
   SEGURIDAD
===================== */
if (!isset($_SESSION['user_id'], $_SESSION['rol'])) {
    header("Location: index.php");
    exit;
}
if (strtoupper($_SESSION['rol']) !== 'ADMIN') {
    die("Acceso solo para administradores");
}

/* =====================
   FECHA SELECCIONADA
===================== */
$fecha = $_GET['fecha'] ?? date('Y-m-d');
$hoy   = date('Y-m-d');

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

    // STOCK ACTUAL
    $res = $conexion->query("
        SELECT producto_id, ubicacion_id, cantidad
        FROM stock_actual
    ");

} else {

    // HISTORIAL
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
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Historial de inventario</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">

<style>
body {
    font-family: 'Inter', sans-serif;
    background: #f6f8f7;
}
.glass {
    background: rgba(255,255,255,.8);
    backdrop-filter: blur(14px);
    border-radius: 22px;
    box-shadow: 0 25px 60px rgba(0,0,0,.15);
}
.table thead {
    background: #2f9e44;
    color: #fff;
}
.table-scroll {
    overflow-x: auto;
}
</style>
</head>

<body>

<?php renderNavbar('Historial'); ?>

<div class="container my-4">

    <div class="glass p-4 mb-4 d-flex flex-wrap gap-3 align-items-center justify-content-between">
        <h3 class="fw-bold text-success mb-0">📊 Historial de inventario</h3>

        <form method="GET" class="d-flex gap-2">
            <input type="date" name="fecha" value="<?= $fecha ?>" class="form-control">
            <button class="btn btn-success">Filtrar</button>
        </form>
    </div>

    <div class="glass p-4 table-scroll">
        <table class="table table-bordered table-sm align-middle">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Unidad</th>
                    <?php foreach ($ubicaciones as $u): ?>
                        <th><?= htmlspecialchars($u['nombre']) ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>

            <?php while ($p = $productos->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($p['nombre']) ?></td>
                    <td><?= $p['unidad'] ?></td>

                    <?php foreach ($ubicaciones as $u): ?>
                        <td class="text-end">
                            <?= number_format(
                                $stock[$p['id']][$u['id']] ?? 0,
                                2
                            ) ?>
                        </td>
                    <?php endforeach; ?>
                </tr>
            <?php endwhile; ?>

            </tbody>
        </table>
    </div>

    <a href="/inventariokikes/controllers/historial/exportar_csv.php?fecha=<?= $fecha ?>"
       class="btn btn-success position-fixed bottom-0 end-0 m-4 shadow">
        📥 Exportar CSV
    </a>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
