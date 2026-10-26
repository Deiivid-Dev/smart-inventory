<?php
require_once __DIR__ . '/includes/bootstrap.php';
requerir_login();

$titulo_pagina = 'Alertas de inventario bajo';
$pagina_activa = 'alertas';

$resultado = mysqli_query(
    $conexion,
    'SELECT p.id, p.codigo, p.nombre, p.stock, p.stock_minimo, p.unidad, c.nombre AS categoria_nombre
     FROM productos p
     LEFT JOIN categorias c ON c.id = p.categoria_id
     WHERE p.activo = 1 AND p.stock < p.stock_minimo
     ORDER BY (p.stock - p.stock_minimo) ASC'
);
$productos = mysqli_fetch_all($resultado, MYSQLI_ASSOC);

require __DIR__ . '/includes/header.php';
?>

<div class="panel">
    <div class="panel-encabezado">
        <h2><i class="bi bi-exclamation-triangle-fill text-danger"></i> Productos con stock bajo</h2>
        <span class="badge text-bg-danger"><?= count($productos) ?> alerta(s)</span>
    </div>

    <?php if (!$productos): ?>
        <p class="text-muted mb-0">No hay productos por debajo de su stock mínimo. ¡Todo en orden!</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Nombre</th>
                        <th>Categoría</th>
                        <th class="text-end">Stock actual</th>
                        <th class="text-end">Stock mínimo</th>
                        <th class="text-end">Faltan</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($productos as $p):
                        $faltan = max(0, (int) $p['stock_minimo'] - (int) $p['stock']);
                    ?>
                        <tr class="<?= (int) $p['stock'] === 0 ? 'fila-stock-critico' : '' ?>">
                            <td><?= escapar_texto($p['codigo']) ?></td>
                            <td><?= escapar_texto($p['nombre']) ?></td>
                            <td><?= escapar_texto($p['categoria_nombre'] ?? 'Sin categoría') ?></td>
                            <td class="text-end"><span class="badge text-bg-danger"><?= (int) $p['stock'] ?> <?= escapar_texto($p['unidad']) ?></span></td>
                            <td class="text-end"><?= (int) $p['stock_minimo'] ?></td>
                            <td class="text-end fw-semibold"><?= $faltan ?></td>
                            <td class="text-end">
                                <a href="entradas.php" class="btn btn-sm btn-success">
                                    <i class="bi bi-box-arrow-in-down"></i> Reabastecer
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
