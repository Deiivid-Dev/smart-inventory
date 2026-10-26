<?php
require_once __DIR__ . '/includes/bootstrap.php';
requerir_login();

$titulo_pagina = 'Entradas de inventario';
$pagina_activa = 'entradas';

$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verificar_token($_POST['csrf_token'] ?? null);

    $productoId = (int) ($_POST['producto_id'] ?? 0);
    $cantidad = (int) ($_POST['cantidad'] ?? 0);
    $motivo = trim($_POST['motivo'] ?? '');

    if ($productoId <= 0) {
        $errores[] = 'Selecciona un producto.';
    }
    if ($cantidad <= 0) {
        $errores[] = 'La cantidad debe ser mayor que cero.';
    }

    if (!$errores) {
        mysqli_begin_transaction($conexion);

        $stmt = mysqli_prepare($conexion, 'SELECT id, nombre, stock FROM productos WHERE id = ? AND activo = 1 FOR UPDATE');
        mysqli_stmt_bind_param($stmt, 'i', $productoId);
        mysqli_stmt_execute($stmt);
        $resultado = mysqli_stmt_get_result($stmt);
        $producto = mysqli_fetch_assoc($resultado);

        if (!$producto) {
            mysqli_rollback($conexion);
            $errores[] = 'El producto seleccionado no existe o fue eliminado.';
        } else {
            $stockAnterior = (int) $producto['stock'];
            $stockNuevo = $stockAnterior + $cantidad;

            $stmtActualizar = mysqli_prepare($conexion, 'UPDATE productos SET stock = ? WHERE id = ?');
            mysqli_stmt_bind_param($stmtActualizar, 'ii', $stockNuevo, $productoId);
            mysqli_stmt_execute($stmtActualizar);

            $usuario = usuario_actual();
            $usuarioId = $usuario['id'] ?? null;
            $motivoGuardar = $motivo ?: null;

            $stmtMovimiento = mysqli_prepare(
                $conexion,
                'INSERT INTO movimientos (producto_id, usuario_id, tipo, cantidad, motivo, stock_anterior, stock_nuevo)
                 VALUES (?, ?, "entrada", ?, ?, ?, ?)'
            );
            mysqli_stmt_bind_param(
                $stmtMovimiento,
                'iiisii',
                $productoId,
                $usuarioId,
                $cantidad,
                $motivoGuardar,
                $stockAnterior,
                $stockNuevo
            );
            mysqli_stmt_execute($stmtMovimiento);

            mysqli_commit($conexion);
            flash_establecer('success', 'Entrada registrada: +' . $cantidad . ' ' . $producto['nombre'] . ' (stock actual: ' . $stockNuevo . ').');
            redirigir('entradas.php');
        }
    }
}

$resultadoProductos = mysqli_query(
    $conexion,
    'SELECT id, codigo, nombre, stock, unidad FROM productos WHERE activo = 1 ORDER BY nombre ASC'
);
$productos = mysqli_fetch_all($resultadoProductos, MYSQLI_ASSOC);

$resultadoUltimasEntradas = mysqli_query(
    $conexion,
    'SELECT m.cantidad, m.motivo, m.creado_en, p.nombre AS producto, u.nombre AS usuario
     FROM movimientos m
     JOIN productos p ON p.id = m.producto_id
     LEFT JOIN usuarios u ON u.id = m.usuario_id
     WHERE m.tipo = "entrada"
     ORDER BY m.creado_en DESC
     LIMIT 10'
);
$ultimasEntradas = mysqli_fetch_all($resultadoUltimasEntradas, MYSQLI_ASSOC);

$js_extra = ['assets/js/movimientos.js'];
require __DIR__ . '/includes/header.php';
?>

<div class="row g-3">
    <div class="col-lg-5">
        <div class="panel">
            <div class="panel-encabezado">
                <h2><i class="bi bi-box-arrow-in-down text-success"></i> Registrar entrada</h2>
            </div>

            <?php if ($errores): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0"><?php foreach ($errores as $error): ?><li><?= escapar_texto($error) ?></li><?php endforeach; ?></ul>
                </div>
            <?php endif; ?>

            <form method="post" id="formMovimiento" data-autoguardado="entrada_borrador">
                <?php csrf_campo(); ?>
                <div class="mb-3">
                    <label class="form-label">Producto *</label>
                    <select name="producto_id" id="selectProducto" class="form-select" required>
                        <option value="">-- Selecciona un producto --</option>
                        <?php foreach ($productos as $p): ?>
                            <option value="<?= (int) $p['id'] ?>" data-stock="<?= (int) $p['stock'] ?>" data-unidad="<?= escapar_texto($p['unidad']) ?>">
                                <?= escapar_texto($p['codigo']) ?> · <?= escapar_texto($p['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text" id="infoStockActual"></div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Cantidad a ingresar *</label>
                    <input type="number" name="cantidad" id="campoCantidad" class="form-control" min="1" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Motivo / observación</label>
                    <input type="text" name="motivo" class="form-control" placeholder="Compra a proveedor, devolución, ajuste...">
                </div>
                <button type="submit" class="btn btn-success w-100">
                    <i class="bi bi-plus-circle"></i> Registrar entrada
                </button>
            </form>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="panel">
            <div class="panel-encabezado">
                <h2><i class="bi bi-list-ul"></i> Últimas entradas</h2>
            </div>
            <?php if (!$ultimasEntradas): ?>
                <p class="text-muted mb-0">Aún no se han registrado entradas.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead>
                            <tr><th>Producto</th><th>Cantidad</th><th>Motivo</th><th>Usuario</th><th>Fecha</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ultimasEntradas as $m): ?>
                                <tr>
                                    <td><?= escapar_texto($m['producto']) ?></td>
                                    <td class="text-success fw-semibold">+<?= (int) $m['cantidad'] ?></td>
                                    <td><?= escapar_texto($m['motivo'] ?? '-') ?></td>
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
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
