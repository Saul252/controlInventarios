<?php
require "config/conexion.php";

$plainPassword = 'admin123';
$hash = password_hash($plainPassword, PASSWORD_DEFAULT);

$sql = "UPDATE usuarios 
        SET password = ?, activo = 1 
        WHERE email = 'admin@inventarios.com'";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("s", $hash);
$stmt->execute();

echo "Password reseteado<br>";
echo "HASH GUARDADO:<br>";
var_dump($hash);
echo "<br>LONGITUD: " . strlen($hash);
