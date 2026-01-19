<?php session_start(); ?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | Inventarios</title>

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
        margin: 0;
        background: #f6f8f7;
        overflow: hidden;
    }

    /* =====================
   FONDO ILUSTRADO
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
        opacity: 0.25;
    }

    /* Sandía */
    .watermelon {
        width: 280px;
        height: 280px;
        background: radial-gradient(circle at center, #ff6b6b 60%, #2ecc71 61%);
        border-radius: 50%;
        top: -80px;
        left: -100px;
    }

    /* Zanahoria */
    .carrot {
        width: 140px;
        height: 340px;
        background: linear-gradient(#ff922b, #ff6f00);
        border-radius: 70px;
        transform: rotate(20deg);
        bottom: -100px;
        right: -60px;
    }

    /* Pepinos */
    .cucumber {
        width: 90px;
        height: 240px;
        background: linear-gradient(#2f9e44, #51cf66);
        border-radius: 50px;
    }

    .cucumber.one {
        top: 25%;
        right: 12%;
    }

    .cucumber.two {
        bottom: 18%;
        left: 12%;
    }

    /* =====================
   LAYOUT
===================== */
    .login-wrapper {
        display: flex;
        min-height: 100vh;
    }

    /* =====================
   VISUAL IZQUIERDO
===================== */
    .login-visual {
        flex: 1;
        padding: 4rem;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1;
    }

    .login-visual h1 {
        font-size: 56px;
        font-weight: 700;
        color: #2f9e44;
    }

    .login-visual p {
        font-size: 18px;
        color: #555;
        max-width: 420px;
    }

    /* =====================
   FORMULARIO
===================== */
    .login-form {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem;
        z-index: 2;
    }

    /* =====================
   GLASS CARD
===================== */
    .glass {
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(14px);
        border-radius: 22px;
        box-shadow: 0 25px 60px rgba(0, 0, 0, 0.15);
        padding: 2.5rem;
        max-width: 420px;
        width: 100%;
    }

    /* =====================
   TEXTOS
===================== */
    .brand {
        font-size: 36px;
        font-weight: 700;
        color: #2f9e44;
    }

    .subtitle {
        font-size: 14px;
        color: #666;
    }

    .form-label {
        font-size: 13px;
        color: #555;
    }

    /* =====================
   INPUTS
===================== */
    .form-control {
        border-radius: 14px;
        padding: 14px;
        font-size: 15px;
    }

    .form-control:focus {
        box-shadow: 0 0 0 3px rgba(47, 158, 68, .25);
    }

    /* =====================
   BOTÓN
===================== */
    .btn-login {
        background: linear-gradient(135deg, #2f9e44, #51cf66);
        border: none;
        border-radius: 16px;
        padding: 14px;
        font-weight: 600;
        font-size: 16px;
        color: #fff;
    }

    .btn-login:hover {
        opacity: 0.92;
    }

    /* =====================
   FOOTER
===================== */
    .footer-text {
        font-size: 12px;
        color: #777;
    }

    /* =====================
   TABLET
===================== */
    @media (max-width: 991px) {
        .login-visual h1 {
            font-size: 44px;
        }
    }

    /* =====================
   MÓVIL
===================== */
    @media (max-width: 767px) {
        .login-wrapper {
            flex-direction: column;
        }

        .login-form {
            padding: 1.5rem;
        }

        .glass {
            padding: 2rem 1.5rem;
        }

        .brand {
            font-size: 32px;
        }
    }
    </style>
</head>

<body>

    <!-- FONDO -->
    <div class="bg-illustration">
        <div class="shape watermelon"></div>
        <div class="shape carrot"></div>
        <div class="shape cucumber one"></div>
        <div class="shape cucumber two"></div>
    </div>

    <div class="login-wrapper">

        <!-- VISUAL (Tablet / Escritorio) -->
        <div class="login-visual d-none d-md-flex">
            <div>
                <h1>Inventarios</h1>
                <p>
                    Control diario de frutas y verduras<br>
                    en todas tus ubicaciones.
                </p>
            </div>
        </div>

        <!-- LOGIN -->
        <div class="login-form">
            <div class="glass">

                <!-- Branding móvil -->
                <div class="text-center mb-4 d-md-none">
                    <div class="brand">Inventarios</div>
                    <div class="subtitle mt-2">Control diario de frutas y verduras</div>
                </div>

                <form method="POST" action="validar_login.php">
                    <div class="mb-3">
                        <label class="form-label">Usuario</label>
                        <input type="email" name="usuario" class="form-control"  placeholder="ejempĺo@ejemplo.com" required
                        >
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Contraseña</label>

                        <div class="input-group">
                            <input type="password" id="password" name="password" class="form-control"
                                placeholder="*****" required>

                            <button type="button" class="btn btn-outline-secondary" id="togglePassword" tabindex="-1">
                                🔒
                            </button>
                        </div>
                    </div>


                    <button class="btn btn-login w-100">
                        🥬 Ingresar
                    </button>
                </form>

                <div class="text-center mt-4 footer-text">
                    Sistema interno • Acceso restringido
                </div>

            </div>
        </div>

    </div>
    <script>
    const input = document.getElementById('password');
    const btn = document.getElementById('togglePassword');

    btn.addEventListener('click', () => {
        const isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';
        btn.textContent = isPassword ? '🔓' : '🔒';
    });
    </script>

</body>

</html>