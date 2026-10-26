<?php
/**
 * Endpoint AJAX: devuelve HTML con los resultados de búsqueda,
 * para actualizar la tabla de busqueda.php sin recargar la página.
 */
require_once __DIR__ . '/../includes/bootstrap.php';

if (!hay_sesion()) {
    http_response_code(401);
    exit('No autorizado');
}

$q = trim($_GET['q'] ?? '');
$resultados = [];

if ($q !== '') {
    $buscado = '%' . $q . '%';
    $stmt = mysqli_prepare(
        $conexion,
        'SELECT p.id, p.codigo, p.nombre, p.stock, p.stock_minimo, p.precio_venta, c.nombre AS categoria_nombre
         FROM productos p
         LEFT JOIN categorias c ON c.id = p.categoria_id
         WHERE p.activo = 1 AND (p.codigo LIKE ? OR p.nombre LIKE ? OR p.descripcion LIKE ?)
         ORDER BY p.nombre ASC
         LIMIT 50'
    );
    mysqli_stmt_bind_param($stmt, 'sss', $buscado, $buscado, $buscado);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    $resultados = mysqli_fetch_all($resultado, MYSQLI_ASSOC);
}

require __DIR__ . '/../includes/_resultados_busqueda.php';
