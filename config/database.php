<?php
/**
 * Conexión a MySQL usando la extensión mysqli, en estilo procedural
 * (sin clases): mysqli_connect() devuelve un identificador de conexión
 * que luego se pasa como primer argumento a cada función mysqli_*.
 */

require_once __DIR__ . '/config.php';

$conexion = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$conexion) {
    if (APP_DEBUG) {
        die('Error de conexión a la base de datos: ' . mysqli_connect_error());
    }
    die('No fue posible conectar con la base de datos. Intenta más tarde.');
}

mysqli_set_charset($conexion, DB_CHARSET);
