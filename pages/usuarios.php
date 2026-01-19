<?php
session_start();


require_once "../config/conexion.php";
require_once __DIR__ . '../../config.php'; // ← ajusta ruta
require_once BASE_PATH . 'includes/navbar.php';

/* =====================
   SEGURIDAD
===================== */
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit;
}
if (strtoupper($_SESSION['rol']) !== 'ADMIN') {
    die("Acceso solo para administradores");
}

/* =====================
   AJAX: OBTENER USUARIO
===================== */
if (
    isset($_POST['action']) &&
    $_POST['action'] === 'get_user' &&
    isset($_POST['id'])
) {
    $id = (int) $_POST['id'];

    $stmt = $conexion->prepare("
        SELECT id, nombre, email, rol_id, ubicacion_id, activo
        FROM usuarios
        WHERE id = ?
        LIMIT 1
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    header('Content-Type: application/json');
    echo json_encode($user ?: []);
    exit;
}

/* =====================
   CONSULTAS NORMALES
===================== */
$usuarios = $conexion->query("
    SELECT 
        u.id,
        u.nombre,
        u.email,
        u.activo,
        r.nombre AS rol,
        ub.nombre AS ubicacion
    FROM usuarios u
    JOIN roles r ON u.rol_id = r.id
    LEFT JOIN ubicaciones ub ON u.ubicacion_id = ub.id
    ORDER BY u.nombre
");

$roles = $conexion->query("SELECT id, nombre FROM roles");
$ubicaciones = $conexion->query("SELECT id, nombre FROM ubicaciones WHERE activo = 1");
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Usuarios | Inventarios</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">

    <style>
    body {
        font-family: 'Inter', sans-serif;
        background: #f6f8f7;
    }

    .card-frutas {
        background: rgba(255, 255, 255, .85);
        backdrop-filter: blur(14px);
        border-radius: 22px;
        box-shadow: 0 25px 60px rgba(0, 0, 0, .15);
        padding: 2rem;
    }

    .page-title {
        font-size: 32px;
        font-weight: 700;
        color: #2f9e44;
    }

    .btn-agregar {
        background: linear-gradient(135deg, #69db7c, #38d9a9);
        border: none;
        color: #fff;
        font-weight: 600;
        border-radius: 14px;
    }

    .btn-editar {
        background-color: #ffd43b;
        border: none;
    }
    </style>
</head>

<body>

    <?php renderNavbar('Usuarios');?>
    <div class="container my-4">
        <div class="card-frutas">

            <!-- HEADER -->
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                <div>
                    <div class="page-title">👥 Usuarios</div>
                    <small class="text-muted">Administración de usuarios del sistema</small>
                </div>

                <button class="btn btn-agregar px-4 py-2" onclick="nuevoUsuario()">
                    ➕ Agregar usuario
                </button>
            </div>

            <!-- TABLA -->
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-success">
                        <tr>
                            <th>#</th>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Rol</th>
                            <th>Ubicación</th>
                            <th>Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>

                        <?php while ($u = $usuarios->fetch_assoc()): ?>
                        <tr>
                            <td><?= $u['id'] ?></td>
                            <td><?= htmlspecialchars($u['nombre']) ?></td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td><?= htmlspecialchars($u['rol']) ?></td>
                            <td><?= $u['ubicacion'] ? htmlspecialchars($u['ubicacion']) : '—' ?></td>
                            <td>
                                <?= $u['activo']
        ? '<span class="badge bg-success">Activo</span>'
        : '<span class="badge bg-danger">Inactivo</span>' ?>
                            </td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-editar me-1" onclick="editarUsuario(<?= $u['id'] ?>)">
                                    ✏️
                                </button>

                                <button class="btn btn-sm btn-danger" onclick="eliminarUsuario(<?= $u['id'] ?>)">
                                    🗑️
                                </button>
                            </td>

                        </tr>
                        <?php endwhile; ?>

                    </tbody>
                </table>
            </div>

        </div>
    </div>

    <!-- MODAL USUARIO -->
    <div class="modal fade" id="modalUsuario" tabindex="-1">
        <div class="modal-dialog">
            <form id="formUsuario" class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">🍍 Usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" name="id" id="user_id">

                    <div class="mb-2">
                        <label>Nombre</label>
                        <input type="text" name="nombre" class="form-control" required>
                    </div>

                    <div class="mb-2">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>

                    <div class="mb-2">
                        <label>Contraseña</label>
                        <input type="password" name="password" class="form-control">
                        <small class="text-muted">Déjala vacía para no cambiarla</small>
                    </div>

                    <div class="mb-2">
                        <label>Rol</label>
                        <select name="rol_id" class="form-select" required>
                            <?php while ($r = $roles->fetch_assoc()): ?>
                            <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['nombre']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="mb-2">
                        <label>Ubicación</label>
                        <select name="ubicacion_id" class="form-select">
                            <option value="">—</option>
                            <?php while ($ub = $ubicaciones->fetch_assoc()): ?>
                            <option value="<?= $ub['id'] ?>"><?= htmlspecialchars($ub['nombre']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-2">
    <label>Estado</label>
    <select name="activo" class="form-select">
        <option value="1">🟢 Activo</option>
        <option value="0">🔴 Suspendido</option>
    </select>
</div>

                </div>
                <button type="submit" class="btn btn-success w-100">
                    💾 Guardar
                </button>


            </form>
        </div>
    </div>

    <!-- REGRESAR -->
    <button class="btn btn-secondary position-fixed bottom-0 start-0 m-3 shadow" onclick="history.back()">
        ⬅ Regresar
    </button>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
    const modal = new bootstrap.Modal(document.getElementById('modalUsuario'));

    /* NUEVO */
    function nuevoUsuario() {
        document.getElementById('formUsuario').reset();
        document.getElementById('user_id').value = '';
        modal.show();
    }

    /* EDITAR */
    async function editarUsuario(id) {

        const form = new FormData();
        form.append('action', 'get_user');
        form.append('id', id);

        const res = await fetch('usuarios.php', {
            method: 'POST',
            body: form
        });

        const u = await res.json();

        if (!u.id) {
            Swal.fire('Error', 'Usuario no encontrado', 'error');
            return;
        }

        document.getElementById('user_id').value = u.id;
        document.querySelector('[name="nombre"]').value = u.nombre;
        document.querySelector('[name="email"]').value = u.email;
        document.querySelector('[name="rol_id"]').value = u.rol_id;
        document.querySelector('[name="ubicacion_id"]').value = u.ubicacion_id ?? '';

        modal.show();
    }

   
      </script>
      <script>
         /* GUARDAR */
document.getElementById('formUsuario').addEventListener('submit', async e => {
    e.preventDefault();

    try {
        const res = await fetch('/inventariokikes/controllers/usuarios/agregarUsuario.php', {
            method: 'POST',
            body: new FormData(e.target)
        });

        const text = await res.text(); // 👈 DEBUG
        console.log(text);
     
        const data = JSON.parse(text);

        Swal.fire({
            icon: data.ok ? 'success' : 'error',
            title: data.ok ? 'Listo 🍏' : 'Error',
            text: data.msg
        }).then(() => {
            if (data.ok) location.reload();
        });

    } catch (err) {
        Swal.fire({
            icon: 'error',
            title: 'Error JS',
            text: err.message
        });
    }
});
</script>

    <script>
    async function eliminarUsuario(id) {

        const confirmacion = await Swal.fire({
            title: '¿Eliminar usuario?',
            text: 'Esta acción no se puede deshacer 🍎',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e03131',
            cancelButtonColor: '#adb5bd',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        });

        if (!confirmacion.isConfirmed) return;

        const form = new FormData();
        form.append('id', id);

        const res = await fetch('/inventariokikes/controllers/usuarios/eliminarUsuario.php', {
            method: 'POST',
            body: form
        });

        const data = await res.json();

        Swal.fire({
            icon: data.ok ? 'success' : 'error',
            title: data.ok ? 'Eliminado 🍏' : 'Error',
            text: data.msg
        }).then(() => {
            if (data.ok) location.reload();
        });
    }
    </script>


</body>

</html>