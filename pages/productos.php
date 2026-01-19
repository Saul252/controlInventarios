<?php
session_start();

/* =====================
   ERRORES (TEMPORAL)
===================== */
ini_set('display_errors', 1);
error_reporting(E_ALL);

/* =====================
   CONFIGURACIÓN
===================== */

require_once "../config/conexion.php";
require_once __DIR__ . '../../config.php'; // ← ajusta ruta
require_once BASE_PATH . 'includes/navbar.php';
/* =====================
   ZONA HORARIA
===================== */
date_default_timezone_set('America/Mexico_City');

/* =====================
   SEGURIDAD
===================== */
if (!isset($_SESSION['user_id'], $_SESSION['rol'])) {
    header("Location: index.php");
    exit;
}

$rol = strtoupper(trim($_SESSION['rol']));
if ($rol !== 'ADMIN') {
    die("❌ Acceso solo para administradores");
}

/* =====================
   OBTENER PRODUCTOS
===================== */
$productos = $conexion->query("
    SELECT id, nombre, unidad, activo
    FROM productos
    ORDER BY nombre
");
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Productos</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
body {
    font-family: 'Inter', sans-serif;
    background: #f6f8f7;
}

.glass {
    background: rgba(255,255,255,.78);
    backdrop-filter: blur(14px);
    border-radius: 22px;
    box-shadow: 0 25px 60px rgba(0,0,0,.15);
}

.table thead {
    background: #2f9e44;
    color: #fff;
}
</style>
</head>

<body>

<?php renderNavbar('Productos'); ?>

<div class="container my-4">

    <div class="glass p-4 mb-4 d-flex justify-content-between align-items-center">
        <h3 class="fw-bold text-success mb-0">🍎 Productos</h3>
        <button class="btn btn-success" onclick="abrirModal()">+ Agregar producto</button>
    </div>

    <div class="glass p-4">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Unidad</th>
                    <th>Estado</th>
                    <th width="180">Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($p = $productos->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($p['nombre']) ?></td>
                    <td><?= htmlspecialchars($p['unidad']) ?></td>
                    <td>
                        <?= $p['activo']
                            ? '<span class="badge bg-success">Activo</span>'
                            : '<span class="badge bg-secondary">Inactivo</span>' ?>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-primary"
                            onclick='editarProducto(<?= json_encode($p, JSON_HEX_APOS) ?>)'>
                            Editar
                        </button>
                        <button class="btn btn-sm btn-danger"
                            onclick="eliminarProducto(<?= (int)$p['id'] ?>)">
                            Eliminar
                        </button>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL -->
<div class="modal fade" id="modalProducto" tabindex="-1">
    <div class="modal-dialog">
        <form id="formProducto" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Producto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <input type="hidden" name="id" id="producto_id">

                <div class="mb-3">
                    <label>Nombre</label>
                    <input type="text" name="nombre" id="nombre"
                           class="form-control" required>
                </div>

                <div class="mb-3">
                    <label>Unidad</label>
                    <select name="unidad" id="unidad"
                            class="form-select" required>
                        <option value="">Seleccione</option>
                        <option value="kg">Kg</option>
                        <option value="pieza">Pieza</option>
                        <option value="caja">Caja</option>
                    </select>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">
                    Cancelar
                </button>
                <button class="btn btn-success">
                    Guardar
                </button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
const modal = new bootstrap.Modal(document.getElementById('modalProducto'));

function abrirModal() {
    document.getElementById('formProducto').reset();
    document.getElementById('producto_id').value = '';
    modal.show();
}

function editarProducto(p) {
    document.getElementById('producto_id').value = p.id;
    document.getElementById('nombre').value = p.nombre;
    document.getElementById('unidad').value = p.unidad;
    modal.show();
}
</script>

</body>
</html>
