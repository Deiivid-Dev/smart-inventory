<?php
require_once __DIR__ . '/includes/bootstrap.php';
requerir_login();

$titulo_pagina = 'Búsqueda de productos';
$pagina_activa = 'busqueda';

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

$js_extra = ['assets/js/busqueda.js'];
require __DIR__ . '/includes/header.php';
?>

<div class="panel">
    <div class="panel-encabezado">
        <h2><i class="bi bi-search"></i> Búsqueda de productos</h2>
    </div>

    <form method="get" class="mb-3" id="formBusqueda">
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input type="text" name="q" id="campoBusqueda" class="form-control" placeholder="Buscar por código, nombre o descripción..." value="<?= escapar_texto($q) ?>" autofocus autocomplete="off">
        </div>
    </form>

    <div id="resultadosBusqueda">
        <?php include __DIR__ . '/includes/_resultados_busqueda.php'; ?>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
