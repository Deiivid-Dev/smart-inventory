<?php
/**
 * Endpoint AJAX: datos agregados en JSON para las gráficas de estadisticas.php.
 */
require_once __DIR__ . '/../includes/bootstrap.php';

if (!hay_sesion()) {
    json_responder(['ok' => false, 'mensaje' => 'No autorizado.'], 401);
}

// Movimientos (entradas vs salidas) de los últimos 14 días.
$dias = [];
$entradasPorDia = [];
$salidasPorDia = [];
for ($i = 13; $i >= 0; $i--) {
    $fecha = date('Y-m-d', strtotime("-$i day"));
    $dias[$fecha] = $fecha;
    $entradasPorDia[$fecha] = 0;
    $salidasPorDia[$fecha] = 0;
}

$desdeHace14Dias = date('Y-m-d 00:00:00', strtotime('-13 day'));
$stmt = mysqli_prepare(
    $conexion,
    "SELECT DATE(creado_en) AS dia, tipo, SUM(cantidad) AS total
     FROM movimientos
     WHERE creado_en >= ?
     GROUP BY DATE(creado_en), tipo"
);
mysqli_stmt_bind_param($stmt, 's', $desdeHace14Dias);
mysqli_stmt_execute($stmt);
$resultadoPorDia = mysqli_stmt_get_result($stmt);
foreach (mysqli_fetch_all($resultadoPorDia, MYSQLI_ASSOC) as $fila) {
    $dia = $fila['dia'];
    if (!isset($dias[$dia])) {
        continue;
    }
    if ($fila['tipo'] === 'entrada') {
        $entradasPorDia[$dia] = (int) $fila['total'];
    } else {
        $salidasPorDia[$dia] = (int) $fila['total'];
    }
}

// Top 5 productos con más salidas (más "vendidos").
$resultadoTop = mysqli_query(
    $conexion,
    "SELECT p.nombre, SUM(m.cantidad) AS total
     FROM movimientos m
     JOIN productos p ON p.id = m.producto_id
     WHERE m.tipo = 'salida'
     GROUP BY m.producto_id, p.nombre
     ORDER BY total DESC
     LIMIT 5"
);
$topProductos = mysqli_fetch_all($resultadoTop, MYSQLI_ASSOC);

// Productos por categoría.
$resultadoCategoria = mysqli_query(
    $conexion,
    "SELECT COALESCE(c.nombre, 'Sin categoría') AS categoria, COUNT(*) AS total
     FROM productos p
     LEFT JOIN categorias c ON c.id = p.categoria_id
     WHERE p.activo = 1
     GROUP BY categoria
     ORDER BY total DESC"
);
$porCategoria = mysqli_fetch_all($resultadoCategoria, MYSQLI_ASSOC);

json_responder([
    'ok' => true,
    'movimientos_por_dia' => [
        'etiquetas' => array_keys($dias),
        'entradas' => array_values($entradasPorDia),
        'salidas' => array_values($salidasPorDia),
    ],
    'top_productos' => [
        'etiquetas' => array_column($topProductos, 'nombre'),
        'valores' => array_map('intval', array_column($topProductos, 'total')),
    ],
    'por_categoria' => [
        'etiquetas' => array_column($porCategoria, 'categoria'),
        'valores' => array_map('intval', array_column($porCategoria, 'total')),
    ],
]);
