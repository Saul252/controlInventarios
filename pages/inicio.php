<?php
session_start();

require_once __DIR__ . '../../config.php'; // ← ajusta ruta
require_once BASE_PATH . 'includes/navbar.php';
// if (!isset($_SESSION['usuario'])) {
//     header("Location: index.php");
//     exit;
// }
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Inicio | Inventarios</title>

<!-- Bootstrap -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Fuente -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">

<style>
/* =====================
   BASE
===================== */
body {
    font-family: 'Inter', sans-serif;
    min-height: 100vh;
    background: #f6f8f7;
    margin: 0;
}

/* =====================
   FONDO
===================== */
.bg-illustration {
    position: fixed;
    inset: 0;
    background-color: #f6f8f7;
    z-index: -2;
    overflow: hidden;
}

.shape {
    position: absolute;
    opacity: 0.22;
}

.watermelon {
    width: 260px;
    height: 260px;
    background: radial-gradient(circle at center, #ff6b6b 60%, #2ecc71 61%);
    border-radius: 50%;
    top: -80px;
    left: -100px;
}

.carrot {
    width: 130px;
    height: 330px;
    background: linear-gradient(#ff922b, #ff6f00);
    border-radius: 70px;
    transform: rotate(25deg);
    bottom: -110px;
    right: -60px;
}

/* =====================
   HEADER
===================== */
.header {
    padding: 2.5rem 1rem 1rem;
    text-align: center;
}

.header h1 {
    font-size: 42px;
    font-weight: 700;
    color: #2f9e44;
}

.header p {
    color: #555;
    margin-top: .5rem;
}

/* =====================
   DASHBOARD
===================== */
.dashboard {
    padding: 2rem 1rem 4rem;
}

.card-glass {
    background: rgba(255,255,255,0.75);
    backdrop-filter: blur(14px);
    border-radius: 22px;
    box-shadow: 0 25px 60px rgba(0,0,0,0.15);
    padding: 2.5rem 2rem;
    text-align: center;
    transition: transform .25s ease, box-shadow .25s ease;
    cursor: pointer;
    height: 100%;
}

.card-glass:hover {
    transform: translateY(-6px);
    box-shadow: 0 35px 70px rgba(0,0,0,0.2);
}

/* =====================
   ICONOS
===================== */
.card-icon {
    font-size: 52px;
    margin-bottom: 1rem;
}

.card-title {
    font-size: 22px;
    font-weight: 600;
    color: #2f9e44;
}

.card-text {
    font-size: 14px;
    color: #666;
    margin-top: .5rem;
}

/* =====================
   LINKS
===================== */
.card-link {
    text-decoration: none;
    color: inherit;
}

/* =====================
   FOOTER
===================== */
.footer {
    text-align: center;
    font-size: 12px;
    color: #777;
    padding-bottom: 1rem;
}
</style>
</head>

<body>
<?php

renderNavbar('Inicio');
?>
<!-- FONDO -->
<div class="bg-illustration">
    <div class="shape watermelon"></div>
    <div class="shape carrot"></div>
</div>

<!-- HEADER -->
<div class="header">
    <h1>Inventarios</h1>
    <p>Panel de control del sistema</p>
</div>

<!-- DASHBOARD -->
<div class="container dashboard">
    <div class="row g-4 justify-content-center">

        <!-- INVENTARIO DIARIO -->
        <div class="col-md-4">
            <a href="inventarios.php" class="card-link">
                <div class="card-glass">
                    <div class="card-icon">📦</div>
                    <div class="card-title">Inventario diario</div>
                    <div class="card-text">
                        Registro y control de existencias por día
                    </div>
                </div>
            </a>
        </div>

        <!-- USUARIOS -->
        <div class="col-md-4">
            <a href="usuarios.php" class="card-link">
                <div class="card-glass">
                    <div class="card-icon">👥</div>
                    <div class="card-title">Usuarios</div>
                    <div class="card-text">
                        Administración de usuarios y permisos
                    </div>
                </div>
            </a>
        </div>

        <!-- HISTORIAL -->
        <div class="col-md-4">
            <a href="historial.php" class="card-link">
                <div class="card-glass">
                    <div class="card-icon">🕘</div>
                    <div class="card-title">Historial</div>
                    <div class="card-text">
                        Movimientos y ajustes de inventario
                    </div>
                </div>
            </a>
        </div>
<!-- PRODUCTOS -->
<div class="col-md-4">
    <a href="productos.php" class="card-link">
        <div class="card-glass">
            <div class="card-icon">🍎</div>
            <div class="card-title">Productos</div>
            <div class="card-text">
                Alta, edición y control de productos del inventario
            </div>
        </div>
    </a>
</div>

    </div>
</div>

<!-- FOOTER -->
<div class="footer">
    Sistema interno • Gestión de inventarios
</div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
