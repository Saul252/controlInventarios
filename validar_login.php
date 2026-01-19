<?php
session_start();
require "config/conexion.php";

$usuario  = trim($_POST['usuario'] ?? '');
$password = trim($_POST['password'] ?? '');

if ($usuario === '' || $password === '') {
    die("<script>alert('Complete todos los campos');window.location='index.php'</script>");
}

$sql = "SELECT u.id, u.nombre, u.email, u.password, u.rol_id, r.nombre AS rol
        FROM usuarios u
        INNER JOIN roles r ON u.rol_id = r.id
        WHERE u.email = ?
          AND u.activo = 1
        LIMIT 1";

$stmt = $conexion->prepare($sql);
if (!$stmt) {
    die("Error al preparar la consulta");
}

$stmt->bind_param("s", $usuario);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    die("<script>alert('Usuario no existe o está inactivo');window.location='index.php'</script>");
}

$row = $resultado->fetch_assoc();

$hashLimpio = trim($row['password']);

if (!password_verify($password, trim($row['password']))) {
    die("PASSWORD NO COINCIDE");
}



/* 🔐 SESIÓN */
$_SESSION['user_id'] = $row['id'];
$_SESSION['nombre']  = $row['nombre'];
$_SESSION['email']   = $row['email'];
$_SESSION['rol_id']  = $row['rol_id'];
$_SESSION['rol']     = $row['rol'];
$_SESSION['login']   = true;

header("Location: pages/inicio.php");
exit;
