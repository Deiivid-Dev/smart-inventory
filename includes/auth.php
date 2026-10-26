<?php
/**
 * Autenticación y control de acceso basado en sesión.
 */

/** Devuelve el usuario autenticado actual, o null si no hay sesión. */
function usuario_actual(): ?array
{
    return $_SESSION['usuario'] ?? null;
}

function hay_sesion(): bool
{
    return usuario_actual() !== null;
}

/**
 * Exige sesión iniciada y que ese usuario todavía exista y esté activo
 * en la base de datos; si no, cierra la sesión y redirige al login.
 * Esto evita que una sesión "vieja" (de un usuario ya eliminado, por
 * ejemplo tras recargar la base de datos) provoque errores más adelante
 * al intentar guardar movimientos con un usuario_id que ya no existe.
 */
function requerir_login(): void
{
    global $conexion;

    if (!hay_sesion()) {
        redirigir('login.php');
    }

    $usuario = usuario_actual();
    $stmt = mysqli_prepare($conexion, 'SELECT id FROM usuarios WHERE id = ? AND activo = 1 LIMIT 1');
    mysqli_stmt_bind_param($stmt, 'i', $usuario['id']);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);

    if (!mysqli_fetch_assoc($resultado)) {
        cerrar_sesion();
        redirigir('login.php');
    }
}

/**
 * Intenta autenticar con usuario y contraseña.
 * Devuelve true y guarda la sesión si es correcto, o false si no.
 */
function intentar_login($conexion, string $usuario, string $password): bool
{
    $stmt = mysqli_prepare(
        $conexion,
        'SELECT id, nombre, usuario, password FROM usuarios WHERE usuario = ? AND activo = 1 LIMIT 1'
    );
    mysqli_stmt_bind_param($stmt, 's', $usuario);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    $fila = mysqli_fetch_assoc($resultado);

    if (!$fila || !password_verify($password, $fila['password'])) {
        return false;
    }

    session_regenerate_id(true);
    $_SESSION['usuario'] = [
        'id' => (int) $fila['id'],
        'nombre' => $fila['nombre'],
        'usuario' => $fila['usuario'],
    ];

    return true;
}

/** Cierra la sesión actual. */
function cerrar_sesion(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $parametros = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $parametros['path'],
            $parametros['domain'],
            $parametros['secure'],
            $parametros['httponly']
        );
    }
    session_destroy();
}

/** True si todavía no existe ningún usuario registrado (primer uso del sistema). */
function sistema_sin_usuarios($conexion): bool
{
    $resultado = mysqli_query($conexion, 'SELECT COUNT(*) AS total FROM usuarios');
    $fila = mysqli_fetch_assoc($resultado);
    return ((int) $fila['total']) === 0;
}
