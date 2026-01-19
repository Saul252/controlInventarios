<?php
session_start();
header('Content-Type: application/json');

require_once "../../config/conexion.php";

/* =====================
   SEGURIDAD
===================== */
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'ok' => false,
        'msg' => 'Sesión no válida'
    ]);
    exit;
}

/* =====================
   DATOS
===================== */
$id     = (int)($_POST['id'] ?? 0);
$nombre = trim($_POST['nombre'] ?? '');
$email  = trim($_POST['email'] ?? '');
$rol_id = (int)($_POST['rol_id'] ?? 0);
$activo = isset($_POST['activo']) ? (int)$_POST['activo'] : 1;

$password = $_POST['password'] ?? '';

$ubicacion_id = ($_POST['ubicacion_id'] ?? '') !== ''
    ? (int)$_POST['ubicacion_id']
    : null;

/* =====================
   VALIDACIONES
===================== */
if ($nombre === '' || $email === '' || $rol_id <= 0) {
    echo json_encode([
        'ok' => false,
        'msg' => 'Nombre, email y rol son obligatorios'
    ]);
    exit;
}

/* =====================
   CREAR USUARIO
===================== */
if ($id === 0) {

    if ($password === '') {
        echo json_encode([
            'ok' => false,
            'msg' => 'La contraseña es obligatoria al crear el usuario'
        ]);
        exit;
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conexion->prepare("
        INSERT INTO usuarios
        (nombre, email, password, rol_id, ubicacion_id, activo)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "sssiii",
        $nombre,
        $email,
        $hash,
        $rol_id,
        $ubicacion_id,
        $activo
    );

    if ($stmt->execute()) {
        echo json_encode([
            'ok' => true,
            'msg' => 'Usuario creado correctamente'
        ]);
    } else {
        echo json_encode([
            'ok' => false,
            'msg' => 'Error al crear usuario'
        ]);
    }

    exit;
}

/* =====================
   EDITAR USUARIO
===================== */
if ($password !== '') {

    $hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conexion->prepare("
        UPDATE usuarios SET
            nombre = ?,
            email = ?,
            password = ?,
            rol_id = ?,
            ubicacion_id = ?,
            activo = ?
        WHERE id = ?
    ");

    $stmt->bind_param(
        "sssiiii",
        $nombre,
        $email,
        $hash,
        $rol_id,
        $ubicacion_id,
        $activo,
        $id
    );

} else {

    $stmt = $conexion->prepare("
        UPDATE usuarios SET
            nombre = ?,
            email = ?,
            rol_id = ?,
            ubicacion_id = ?,
            activo = ?
        WHERE id = ?
    ");

    $stmt->bind_param(
        "ssiiii",
        $nombre,
        $email,
        $rol_id,
        $ubicacion_id,
        $activo,
        $id
    );
}

if ($stmt->execute()) {
    echo json_encode([
        'ok' => true,
        'msg' => 'Usuario actualizado correctamente'
    ]);
} else {
    echo json_encode([
        'ok' => false,
        'msg' => 'Error al actualizar usuario'
    ]);
}
