<?php
require_once __DIR__ . '/includes/bootstrap.php';
requerir_login();

$titulo_pagina = 'Panel principal';
$pagina_activa = 'inicio';

$totalProductos = (int) mysqli_fetch_assoc(
    mysqli_query($conexion, 'SELECT COUNT(*) AS t FROM productos WHERE activo = 1')
)['t'];
$totalUnidades = (int) mysqli_fetch_assoc(
    mysqli_query($conexion, 'SELECT COALESCE(SUM(stock), 0) AS t FROM productos WHERE activo = 1')
)['t'];
$valorInventario = (float) mysqli_fetch_assoc(
    mysqli_query($conexion, 'SELECT COALESCE(SUM(stock * precio_venta), 0) AS t FROM productos WHERE activo = 1')
)['t'];
$totalAlertasStock = contar_alertas_stock($conexion);

$resultadoMovimientosHoy = mysqli_query(
    $conexion,
    "SELECT tipo, COUNT(*) AS total FROM movimientos WHERE DATE(creado_en) = CURDATE() GROUP BY tipo"
);
$movimientosHoy = mysqli_fetch_all($resultadoMovimientosHoy, MYSQLI_ASSOC);
$entradasHoy = 0;
$salidasHoy = 0;
foreach ($movimientosHoy as $fila) {
    if ($fila['tipo'] === 'entrada') {
        $entradasHoy = (int) $fila['total'];
    } else {
        $salidasHoy = (int) $fila['total'];
    }
}

$resultadoUltimosMovimientos = mysqli_query(
    $conexion,
    'SELECT m.tipo, m.cantidad, m.motivo, m.creado_en, p.nombre AS producto, u.nombre AS usuario
     FROM movimientos m
     JOIN productos p ON p.id = m.producto_id
     LEFT JOIN usuarios u ON u.id = m.usuario_id
     ORDER BY m.creado_en DESC
     LIMIT 8'
);
$ultimosMovimientos = mysqli_fetch_all($resultadoUltimosMovimientos, MYSQLI_ASSOC);

$resultadoBajoStock = mysqli_query(
    $conexion,
    'SELECT id, codigo, nombre, stock, stock_minimo
     FROM productos
     WHERE activo = 1 AND stock < stock_minimo
     ORDER BY (stock - stock_minimo) ASC
     LIMIT 6'
);
$productosBajoStock = mysqli_fetch_all($resultadoBajoStock, MYSQLI_ASSOC);

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
            <div class="tarjeta-metrica-icono bg-success-subtle text-success"><i class="bi bi-stack"></i></div>
            <div>
                <div class="tarjeta-metrica-valor"><?= number_format($totalUnidades, 0, ',', '.') ?></div>
                <div class="tarjeta-metrica-etiqueta">Unidades en stock</div>
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
        <a href="alertas.php" class="text-decoration-none">
            <div class="tarjeta-metrica">
                <div class="tarjeta-metrica-icono bg-danger-subtle text-danger"><i class="bi bi-exclamation-triangle-fill"></i></div>
                <div>
                    <div class="tarjeta-metrica-valor"><?= number_format($totalAlertasStock, 0, ',', '.') ?></div>
                    <div class="tarjeta-metrica-etiqueta">Alertas de stock bajo</div>
                </div>
            </div>
        </a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-sm-6">
        <div class="tarjeta-metrica">
            <div class="tarjeta-metrica-icono bg-success-subtle text-success"><i class="bi bi-box-arrow-in-down"></i></div>
            <div>
                <div class="tarjeta-metrica-valor"><?= $entradasHoy ?></div>
                <div class="tarjeta-metrica-etiqueta">Entradas registradas hoy</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="tarjeta-metrica">
            <div class="tarjeta-metrica-icono bg-warning-subtle text-warning"><i class="bi bi-box-arrow-up"></i></div>
            <div>
                <div class="tarjeta-metrica-valor"><?= $salidasHoy ?></div>
                <div class="tarjeta-metrica-etiqueta">Salidas registradas hoy</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="panel">
            <div class="panel-encabezado">
                <h2><i class="bi bi-clock-history"></i> Actividad reciente</h2>
                <a href="historial.php" class="btn btn-sm btn-outline-secondary">Ver historial</a>
            </div>
            <?php if (!$ultimosMovimientos): ?>
                <p class="text-muted mb-0">Todavía no hay movimientos registrados.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Tipo</th>
                                <th>Producto</th>
                                <th>Cantidad</th>
                                <th>Usuario</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ultimosMovimientos as $m): ?>
                                <tr>
                                    <td>
                                        <?php if ($m['tipo'] === 'entrada'): ?>
                                            <span class="badge text-bg-success"><i class="bi bi-box-arrow-in-down"></i> Entrada</span>
                                        <?php else: ?>
                                            <span class="badge text-bg-warning"><i class="bi bi-box-arrow-up"></i> Salida</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= escapar_texto($m['producto']) ?></td>
                                    <td><?= (int) $m['cantidad'] ?></td>
                                    <td><?= escapar_texto($m['usuario'] ?? 'Sistema') ?></td>
                                    <td><?= formato_fecha($m['creado_en']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="panel">
            <div class="panel-encabezado">
                <h2><i class="bi bi-exclamation-triangle-fill text-danger"></i> Stock bajo</h2>
                <a href="alertas.php" class="btn btn-sm btn-outline-secondary">Ver todas</a>
            </div>
            <?php if (!$productosBajoStock): ?>
                <p class="text-muted mb-0">No hay productos con stock bajo. ¡Todo en orden!</p>
            <?php else: ?>
                <ul class="lista-alertas">
                    <?php foreach ($productosBajoStock as $p): ?>
                        <li>
                            <div>
                                <strong><?= escapar_texto($p['nombre']) ?></strong>
                                <div class="text-muted small"><?= escapar_texto($p['codigo']) ?></div>
                            </div>
                            <span class="badge text-bg-danger">
                                <?= (int) $p['stock'] ?> / mín. <?= (int) $p['stock_minimo'] ?>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
