<?php
/**
 * Funciones auxiliares de uso general.
 */

/** Escapa texto para salida segura en HTML. */
function escapar_texto(?string $texto): string
{
    return htmlspecialchars($texto ?? '', ENT_QUOTES, 'UTF-8');
}

/** Formatea un número como moneda local (COP, sin decimales). */
function formato_moneda($numero): string
{
    return '$' . number_format((float) $numero, 0, ',', '.');
}

/** Formatea una fecha/hora de MySQL a formato legible. */
function formato_fecha(?string $fecha, string $formato = 'd/m/Y h:i A'): string
{
    if (empty($fecha)) {
        return '-';
    }
    $ts = strtotime($fecha);
    return $ts ? date($formato, $ts) : '-';
}

/** Redirige a otra URL dentro de la app y detiene la ejecución. */
function redirigir(string $url): void
{
    header('Location: ' . $url);
    exit;
}

/** Guarda un mensaje flash en sesión para mostrarlo tras una redirección. */
function flash_establecer(string $tipo, string $mensaje): void
{
    $_SESSION['flash'] = ['tipo' => $tipo, 'mensaje' => $mensaje];
}

/** Recupera y limpia el mensaje flash actual (si existe). */
function flash_obtener(): ?array
{
    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/** Responde en formato JSON y termina la ejecución. */
function json_responder(array $datos, int $codigo = 200): void
{
    http_response_code($codigo);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($datos, JSON_UNESCAPED_UNICODE);
    exit;
}

/** Cuenta cuántos productos activos están por debajo de su stock mínimo. */
function contar_alertas_stock($conexion): int
{
    $resultado = mysqli_query(
        $conexion,
        'SELECT COUNT(*) AS total FROM productos WHERE activo = 1 AND stock < stock_minimo'
    );
    $fila = mysqli_fetch_assoc($resultado);
    return (int) $fila['total'];
}

/** Obtiene la lista de categorías ordenadas por nombre. */
function obtener_categorias($conexion): array
{
    $resultado = mysqli_query($conexion, 'SELECT id, nombre FROM categorias ORDER BY nombre ASC');
    return mysqli_fetch_all($resultado, MYSQLI_ASSOC);
}
