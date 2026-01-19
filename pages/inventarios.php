
<?php
session_start();

require_once "../config/conexion.php";
require_once __DIR__ . '../../config.php';
require_once BASE_PATH . 'includes/navbar.php';

/* =====================
   ZONA HORARIA
===================== */
date_default_timezone_set('America/Mexico_City');

/* =====================
   SEGURIDAD
===================== */
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$nombreUsuario = $_SESSION['nombre'] ?? 'Usuario';
$user_id  = (int)$_SESSION['user_id'];
$userUbic = $_SESSION['ubicacion_id'] ?? null;

/* INVENTARIO SOLO DEL DÍA ACTUAL */
$fecha = date('Y-m-d');

/* =====================
   UBICACIONES SEGÚN USUARIO
   - ubicacion_id NULL  → TODAS
   - ubicacion_id != NULL → SOLO LA SUYA
===================== */
if ($userUbic === null) {

    // Usuario global (admin)
    $ubicaciones = $conexion->query("
        SELECT id, nombre
        FROM ubicaciones
        WHERE activo = 1
        ORDER BY nombre
    ");

} else {

    // Usuario con sucursal asignada
    $stmt = $conexion->prepare("
        SELECT id, nombre
        FROM ubicaciones
        WHERE id = ?
          AND activo = 1
        LIMIT 1
    ");
    $stmt->bind_param("i", $userUbic);
    $stmt->execute();
    $ubicaciones = $stmt->get_result();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Inventario diario</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">

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

.card-inventario {
    height: 70vh;
    display: flex;
    flex-direction: column;
}

.store-title {
    font-size: 20px;
    font-weight: 700;
    color: #2f9e44;
}

.table-scroll {
    flex-grow: 1;
    max-height: 55vh;
    overflow-y: auto;
}

.stock-inicial {
    font-weight: 600;
    color: #666;
}

.btn-save {
    background: linear-gradient(135deg, #2f9e44, #51cf66);
    border: none;
    font-weight: 600;
}

/* BOTÓN EXPORTAR FLOTANTE */
.btn-exportar {
    position: fixed;
    bottom: 24px;
    right: 24px;
    z-index: 999;
    padding: 12px 20px;
    border-radius: 50px;
    font-weight: 600;
    box-shadow: 0 12px 30px rgba(47,158,68,.35);
}

.btn-exportar:hover {
    transform: translateY(-2px);
    box-shadow: 0 16px 40px rgba(47,158,68,.45);
}

@media (max-width: 768px) {
    .btn-exportar {
        bottom: 16px;
        right: 16px;
        padding: 10px 16px;
        font-size: 14px;
    }
}
</style>
</head>

<body>

<?php renderNavbar('Inventarios'); ?>

<div class="container my-4">

    <div class="glass p-4 mb-4">
        <h3 class="fw-bold text-success mb-0">
            📦 Inventario diario — <?= date('d/m/Y') ?>
        </h3>
    </div>

    <div class="row g-4">

<?php while ($u = $ubicaciones->fetch_assoc()): ?>

        <div class="col-lg-6 col-xl-4">
            <div class="glass p-4 card-inventario">

                <div class="store-title mb-2">
                    🏬 <?= htmlspecialchars($u['nombre']) ?>
                </div>

                <form class="form-inventario"
                      action="/inventariokikes/controllers/inventario/actualizarInventario.php">

                    <input type="hidden" name="ubicacion_id" value="<?= (int)$u['id'] ?>">

                    <div class="table-scroll">
                        <table class="table table-sm align-middle">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Unidad</th>
                                    <th width="80">Inicial</th>
                                    <th width="90">Actual</th>
                                </tr>
                            </thead>
                            <tbody>

<?php
$stmtProd = $conexion->prepare("
    SELECT p.id, p.nombre, p.unidad
    FROM ubicacion_productos up
    JOIN productos p ON p.id = up.producto_id
    WHERE up.ubicacion_id = ?
      AND up.activo = 1
      AND p.activo = 1
    ORDER BY p.nombre
");
$stmtProd->bind_param("i", $u['id']);
$stmtProd->execute();
$productos = $stmtProd->get_result();

while ($p = $productos->fetch_assoc()):

    /* INVENTARIO DEL DÍA */
    $stmtInv = $conexion->prepare("
        SELECT cantidad
        FROM inventario_diario
        WHERE producto_id = ?
          AND ubicacion_id = ?
          AND fecha = ?
    ");
    $stmtInv->bind_param("iis", $p['id'], $u['id'], $fecha);
    $stmtInv->execute();
    $rowInv = $stmtInv->get_result()->fetch_assoc();

    /* STOCK ACTUAL REAL */
    $stmtStock = $conexion->prepare("
        SELECT cantidad
        FROM stock_actual
        WHERE producto_id = ?
          AND ubicacion_id = ?
    ");
    $stmtStock->bind_param("ii", $p['id'], $u['id']);
    $stmtStock->execute();
    $rowStock = $stmtStock->get_result()->fetch_assoc();

    $stockInicial = $rowStock['cantidad'] ?? 0;
    $stockActual  = $rowInv['cantidad'] ?? $stockInicial;
?>

<tr>
    <td><?= htmlspecialchars($p['nombre']) ?></td>
    <td><?= htmlspecialchars($p['unidad']) ?></td>
    <td class="stock-inicial"><?= $stockInicial ?></td>
    <td>
        <input type="number"
               step="0.01"
               class="form-control form-control-sm"
               name="stock_actual[<?= (int)$p['id'] ?>]"
               value="<?= $stockActual ?>">
    </td>
</tr>

<?php endwhile; ?>

                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        <button class="btn btn-save w-100 text-white">
                            💾 Guardar cambios
                        </button>
                    </div>

                </form>

            </div>
        </div>

<?php endwhile; ?>

    </div>

    <a href="/inventariokikes/controllers/inventario/exportar_stock_actual.csv.php"
       class="btn btn-success btn-exportar shadow">
        📤 Exportar inventario
    </a>

</div>

<button class="btn btn-secondary position-fixed bottom-0 start-0 m-3 shadow"
        onclick="history.back()">
    ⬅ Regresar
</button>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.querySelectorAll('.form-inventario').forEach(form => {

    form.addEventListener('submit', async e => {
        e.preventDefault();

        Swal.fire({
            title: 'Guardando inventario',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        try {
            const res = await fetch(form.action, {
                method: 'POST',
                body: new FormData(form)
            });

            const data = await res.json();
            if (!data.ok) throw new Error(data.msg);

            Swal.fire({
                icon: 'success',
                title: 'Inventario actualizado',
                timer: 1500,
                showConfirmButton: false
            });

        } catch (err) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: err.message
            });
        }
    });

});
</script>

</body>
</html>
