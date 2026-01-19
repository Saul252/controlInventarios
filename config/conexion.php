<?php

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$host = "localhost";
$user = "root";
$pass = "";
$db   = "inventarios";

try {
    $conexion = new mysqli($host, $user, $pass, $db);
    $conexion->set_charset("utf8mb4");
} catch (mysqli_sql_exception $e) {
    // En producción esto debería ir a logs
    error_log($e->getMessage());
    die("Error al conectar con la base de datos.");
}
