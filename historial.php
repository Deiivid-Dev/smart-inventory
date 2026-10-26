<?php
require_once __DIR__ . '/includes/bootstrap.php';
requerir_login();

$titulo_pagina = 'Historial de movimientos';
$pagina_activa = 'historial';

$tipo = $_GET['tipo'] ?? '';
$desde = $_GET['desde'] ?? '';
$hasta = $_GET['hasta'] ?? '';
$productoId = (int) ($_GET['producto_id'] ?? 0);

// Para cada filtro, si viene vacío se usa un valor "neutro" ('' o 0) que hace
// que la condición "? = valor_neutro OR columna = ?" sea siempre verdadera,
// es decir, el filtro simplemente no aplica. Así la consulta preparada
// siempre tiene el mismo número fijo de signos de interrogación (?).
$tipoFiltro = in_array($tipo, ['entrada', 'salida'], true) ? $tipo : '';
$desdeFiltro = ($desde !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $desde)) ? $desde . ' 00:00:00' : '';
$hastaFiltro = ($hasta !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $hasta)) ? $hasta . ' 23:59:59' : '';

$stmt = mysqli_prepare(
    $conexion,
    'SELECT m.id, m.tipo, m.cantidad, m.motivo, m.stock_anterior, m.stock_nuevo, m.creado_en,
            p.codigo AS producto_codigo, p.nombre AS producto_nombre, u.nombre AS usuario_nombre
     FROM movimientos m
     JOIN productos p ON p.id = m.producto_id
     LEFT JOIN usuarios u ON u.id = m.usuario_id
     WHERE (? = "" OR m.tipo = ?)
       AND (? = "" OR m.creado_en >= ?)
       AND (? = "" OR m.creado_en <= ?)
       AND (? = 0 OR m.producto_id = ?)
     ORDER BY m.creado_en DESC
     LIMIT 300'
);
mysqli_stmt_bind_param(
    $stmt,
    'ssssssii',
    $tipoFiltro,
    $tipoFiltro,
    $desdeFiltro,
    $desdeFiltro,
    $hastaFiltro,
    $hastaFiltro,
    $productoId,
    $productoId
);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
$movimientos = mysqli_fetch_all($resultado, MYSQLI_ASSOC);

$resultadoProductos = mysqli_query($conexion, 'SELECT id, nombre FROM productos ORDER BY nombre ASC');
$productos = mysqli_fetch_all($resultadoProductos, MYSQLI_ASSOC);

require __DIR__ . '/includes/header.php';
?>

<div class="panel">
    <div class="panel-encabezado">
        <h2><i class="bi bi-clock-history"></i> Historial de movimientos</h2>
    </div>

    <form method="get" class="row g-2 mb-3">
        <div class="col-sm-6 col-md-3">
            <select name="tipo" class="form-select form-select-sm">
                <option value="">Todos los tipos</option>
                <option value="entrada" <?= $tipo === 'entrada' ? 'selected' : '' ?>>Entradas</option>
                <option value="salida" <?= $tipo === 'salida' ? 'selected' : '' ?>>Salidas</option>
            </select>
        </div>
        <div class="col-sm-6 col-md-3">
            <select name="producto_id" class="form-select form-select-sm">
                <option value="0">Todos los productos</option>
                <?php foreach ($productos as $p): ?>
                    <option value="<?= (int) $p['id'] ?>" <?= $productoId === (int) $p['id'] ? 'selected' : '' ?>>
                        <?= escapar_texto($p['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-sm-6 col-md-2">
            <input type="date" name="desde" class="form-control form-control-sm" value="<?= escapar_texto($desde) ?>">
        </div>
        <div class="col-sm-6 col-md-2">
            <input type="date" name="hasta" class="form-control form-control-sm" value="<?= escapar_texto($hasta) ?>">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-sm btn-primary w-100"><i class="bi bi-funnel"></i> Filtrar</button>
        </div>
    </form>

    <?php if (!$movimientos): ?>
        <p class="text-muted mb-0">No hay movimientos que coincidan con el filtro.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Tipo</th>
                        <th>Producto</th>
                        <th class="text-end">Cantidad</th>
                        <th class="text-end">Stock antes → después</th>
                        <th>Motivo</th>
                        <th>Usuario</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($movimientos as $m): ?>
                        <tr>
                            <td><?= formato_fecha($m['creado_en']) ?></td>
                            <td>
                                <?php if ($m['tipo'] === 'entrada'): ?>
                                    <span class="badge text-bg-success"><i class="bi bi-box-arrow-in-down"></i> Entrada</span>
                                <?php else: ?>
                                    <span class="badge text-bg-warning"><i class="bi bi-box-arrow-up"></i> Salida</span>
                                <?php endif; ?>
                            </td>
                            <td><?= escapar_texto($m['producto_codigo']) ?> · <?= escapar_texto($m['producto_nombre']) ?></td>
                            <td class="text-end"><?= (int) $m['cantidad'] ?></td>
                            <td class="text-end"><?= (int) $m['stock_anterior'] ?> → <?= (int) $m['stock_nuevo'] ?></td>
                            <td><?= escapar_texto($m['motivo'] ?? '-') ?></td>
                            <td><?= escapar_texto($m['usuario_nombre'] ?? 'Sistema') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <p class="text-muted small mt-2">Mostrando hasta 300 movimientos más recientes según el filtro aplicado.</p>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
