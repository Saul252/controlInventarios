<?php
function renderNavbar(string $seccion): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $usuario = $_SESSION['nombre'] ?? 'Usuario';
    ?>

<style>
.navbar-frutas {
    background: linear-gradient(90deg, #e8f5e9, #fffde7);
}
.navbar-frutas .navbar-brand {
    color: #2f9e44;
    font-weight: 700;
}
.navbar-frutas .nav-link {
    color: #388e3c;
}
</style>

<nav class="navbar navbar-expand-lg navbar-frutas shadow-sm">
    <div class="container-fluid">

        <a class="navbar-brand" href="/inventariokikes/pages/inicio.php">
            🍍 Inventarios
        </a>

        <button class="navbar-toggler" type="button"
                data-bs-toggle="collapse"
                data-bs-target="#navbarFrutas">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarFrutas">

            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <span class="nav-link active fw-semibold">
                        📄 <?= htmlspecialchars($seccion) ?>
                    </span>
                </li>
            </ul>

            <ul class="navbar-nav">
                <li class="nav-item dropdown">

                    <a class="nav-link dropdown-toggle"
                       href="#"
                       data-bs-toggle="dropdown">
                        👤 <?= htmlspecialchars($usuario) ?>
                        <span class="d-lg-none text-danger ms-1">🚪</span>
                    </a>

                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item text-danger"
                               href="/inventariokikes/logout.php">
                                🚪 Cerrar sesión
                            </a>
                        </li>
                    </ul>

                </li>
            </ul>

        </div>
    </div>
</nav>

<?php
}
