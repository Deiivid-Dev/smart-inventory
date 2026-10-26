<?php
/**
 * Protección CSRF básica para formularios y peticiones AJAX.
 */

/** Devuelve el token CSRF actual, generándolo si no existe. */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/** Imprime un input oculto con el token CSRF, listo para usar dentro de un <form>. */
function csrf_campo(): void
{
    echo '<input type="hidden" name="csrf_token" value="' . escapar_texto(csrf_token()) . '">';
}

/** Verifica el token recibido contra el de la sesión. */
function csrf_valido(?string $tokenRecibido): bool
{
    return !empty($tokenRecibido)
        && !empty($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $tokenRecibido);
}

/** Verifica el token o detiene la ejecución con un error. */
function csrf_verificar_token(?string $tokenRecibido): void
{
    if (!csrf_valido($tokenRecibido)) {
        http_response_code(400);
        die('Token de seguridad inválido o expirado. Vuelve a cargar la página e intenta de nuevo.');
    }
}
