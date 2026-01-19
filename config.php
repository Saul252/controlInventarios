<?php
// Ruta física del proyecto (portable)
define(
    'BASE_PATH',
    rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/inventariokikes/'
);

// URL base (opcional pero recomendable)
define(
    'BASE_URL',
    'http://' . $_SERVER['HTTP_HOST'] . '/inventariokikes/'
);
