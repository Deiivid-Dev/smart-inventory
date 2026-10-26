<?php
require_once __DIR__ . '/includes/bootstrap.php';
requerir_login();

$titulo_pagina = 'Estadísticas';
$pagina_activa = 'estadisticas';

$totalProductos = (int) mysqli_fetch_assoc(
    mysqli_query($conexion, 'SELECT COUNT(*) AS t FROM productos WHERE activo = 1')
)['t'];
$valorInventario = (float) mysqli_fetch_assoc(
    mysqli_query($conexion, 'SELECT COALESCE(SUM(stock * precio_venta), 0) AS t FROM productos WHERE activo = 1')
)['t'];
$totalEntradas = (int) mysqli_fetch_assoc(
    mysqli_query($conexion, "SELECT COALESCE(SUM(cantidad), 0) AS t FROM movimientos WHERE tipo = 'entrada'")
)['t'];
$totalSalidas = (int) mysqli_fetch_assoc(
    mysqli_query($conexion, "SELECT COALESCE(SUM(cantidad), 0) AS t FROM movimientos WHERE tipo = 'salida'")
)['t'];

$js_extra = [
    'https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js',
    'assets/js/estadisticas.js',
];
require __DIR__ . '/includes/header.php';
?>

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="tarjeta-metrica">
            <div class="tarjeta-metrica-icono bg-primary-subtle text-primary"><i class="bi bi-box-seam-fill"></i></div>
            <div>
                <div class="tarjeta-metrica-valor"><?= number_format($totalProductos, 0, ',', '.') ?></div>
                <div class="tarjeta-metrica-etiqueta">Productos activos</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="tarjeta-metrica">
            <div class="tarjeta-metrica-icono bg-info-subtle text-info"><i class="bi bi-cash-stack"></i></div>
            <div>
                <div class="tarjeta-metrica-valor"><?= formato_moneda($valorInventario) ?></div>
                <div class="tarjeta-metrica-etiqueta">Valor del inventario</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="tarjeta-metrica">
            <div class="tarjeta-metrica-icono bg-success-subtle text-success"><i class="bi bi-box-arrow-in-down"></i></div>
            <div>
                <div class="tarjeta-metrica-valor"><?= number_format($totalEntradas, 0, ',', '.') ?></div>
                <div class="tarjeta-metrica-etiqueta">Unidades ingresadas (total)</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="tarjeta-metrica">
            <div class="tarjeta-metrica-icono bg-warning-subtle text-warning"><i class="bi bi-box-arrow-up"></i></div>
            <div>
                <div class="tarjeta-metrica-valor"><?= number_format($totalSalidas, 0, ',', '.') ?></div>
                <div class="tarjeta-metrica-etiqueta">Unidades retiradas (total)</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="panel">
            <div class="panel-encabezado"><h2><i class="bi bi-graph-up"></i> Movimientos de los últimos 14 días</h2></div>
            <div class="contenedor-grafica">
                <canvas id="graficoMovimientos"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="panel">
            <div class="panel-encabezado"><h2><i class="bi bi-pie-chart-fill"></i> Productos por categoría</h2></div>
            <div class="contenedor-grafica">
                <canvas id="graficoCategorias"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-12">
        <div class="panel">
            <div class="panel-encabezado"><h2><i class="bi bi-trophy-fill"></i> Top 5 productos con más salidas</h2></div>
            <div class="contenedor-grafica contenedor-grafica-ancha">
                <canvas id="graficoTopProductos"></canvas>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
