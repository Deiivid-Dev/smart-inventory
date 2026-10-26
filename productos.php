<?php
require_once __DIR__ . '/includes/bootstrap.php';
requerir_login();

$titulo_pagina = 'Productos';
$pagina_activa = 'productos';

$errores = [];

// ------------------------------------------------------------
// Crear / actualizar producto (formulario autoenviado)
// ------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verificar_token($_POST['csrf_token'] ?? null);
    $accion = $_POST['accion'] ?? '';

    $id = (int) ($_POST['id'] ?? 0);
    $codigo = trim($_POST['codigo'] ?? '');
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $categoriaId = (int) ($_POST['categoria_id'] ?? 0) ?: null;
    $unidad = trim($_POST['unidad'] ?? 'unidad');
    $precioCompra = (float) ($_POST['precio_compra'] ?? 0);
    $precioVenta = (float) ($_POST['precio_venta'] ?? 0);
    $stock = (int) ($_POST['stock'] ?? 0);
    $stockMinimo = (int) ($_POST['stock_minimo'] ?? STOCK_MINIMO_DEFECTO);

    if ($codigo === '' || $nombre === '') {
        $errores[] = 'El código y el nombre del producto son obligatorios.';
    }
    if ($stock < 0 || $stockMinimo < 0 || $precioCompra < 0 || $precioVenta < 0) {
        $errores[] = 'Los valores numéricos no pueden ser negativos.';
    }

    $descripcionGuardar = $descripcion ?: null;
    $unidadGuardar = $unidad ?: 'unidad';

    if (!$errores && $accion === 'crear') {
        $stmt = mysqli_prepare($conexion, 'SELECT id FROM productos WHERE codigo = ? LIMIT 1');
        mysqli_stmt_bind_param($stmt, 's', $codigo);
        mysqli_stmt_execute($stmt);
        $resultado = mysqli_stmt_get_result($stmt);

        if (mysqli_fetch_assoc($resultado)) {
            $errores[] = 'Ya existe un producto con ese código.';
        } else {
            $stmt = mysqli_prepare(
                $conexion,
                'INSERT INTO productos
                    (codigo, nombre, descripcion, categoria_id, unidad, precio_compra, precio_venta, stock, stock_minimo)
                 VALUES
                    (?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            mysqli_stmt_bind_param(
                $stmt,
                'sssisddii',
                $codigo,
                $nombre,
                $descripcionGuardar,
                $categoriaId,
                $unidadGuardar,
                $precioCompra,
                $precioVenta,
                $stock,
                $stockMinimo
            );
            mysqli_stmt_execute($stmt);

            // Si el producto se crea con stock inicial, se registra como entrada
            // para que el historial de movimientos quede completo.
            if ($stock > 0) {
                $nuevoId = (int) mysqli_insert_id($conexion);
                $usuario = usuario_actual();
                $usuarioId = $usuario['id'] ?? null;

                $stmtMov = mysqli_prepare(
                    $conexion,
                    'INSERT INTO movimientos (producto_id, usuario_id, tipo, cantidad, motivo, stock_anterior, stock_nuevo)
                     VALUES (?, ?, "entrada", ?, "Stock inicial", 0, ?)'
                );
                mysqli_stmt_bind_param($stmtMov, 'iiii', $nuevoId, $usuarioId, $stock, $stock);
                mysqli_stmt_execute($stmtMov);
            }

            flash_establecer('success', 'Producto "' . $nombre . '" creado correctamente.');
            redirigir('productos.php');
        }
    } elseif (!$errores && $accion === 'actualizar' && $id > 0) {
        $stmt = mysqli_prepare($conexion, 'SELECT id FROM productos WHERE codigo = ? AND id <> ? LIMIT 1');
        mysqli_stmt_bind_param($stmt, 'si', $codigo, $id);
        mysqli_stmt_execute($stmt);
        $resultado = mysqli_stmt_get_result($stmt);

        if (mysqli_fetch_assoc($resultado)) {
            $errores[] = 'Ya existe otro producto con ese código.';
        } else {
            // El stock NO se modifica aquí a propósito: solo cambia a través de
            // Entradas/Salidas, para que el historial de movimientos sea siempre fiel al stock real.
            $stmt = mysqli_prepare(
                $conexion,
                'UPDATE productos SET
                    codigo = ?, nombre = ?, descripcion = ?,
                    categoria_id = ?, unidad = ?,
                    precio_compra = ?, precio_venta = ?,
                    stock_minimo = ?
                 WHERE id = ?'
            );
            mysqli_stmt_bind_param(
                $stmt,
                'sssisddii',
                $codigo,
                $nombre,
                $descripcionGuardar,
                $categoriaId,
                $unidadGuardar,
                $precioCompra,
                $precioVenta,
                $stockMinimo,
                $id
            );
            mysqli_stmt_execute($stmt);
            flash_establecer('success', 'Producto "' . $nombre . '" actualizado correctamente.');
            redirigir('productos.php');
        }
    }
}

// ------------------------------------------------------------
// Listado con filtro opcional por categoría
// ------------------------------------------------------------
$categoriaFiltro = (int) ($_GET['categoria'] ?? 0);

if ($categoriaFiltro > 0) {
    $stmt = mysqli_prepare(
        $conexion,
        'SELECT p.*, c.nombre AS categoria_nombre
         FROM productos p
         LEFT JOIN categorias c ON c.id = p.categoria_id
         WHERE p.activo = 1 AND p.categoria_id = ?
         ORDER BY p.nombre ASC'
    );
    mysqli_stmt_bind_param($stmt, 'i', $categoriaFiltro);
    mysqli_stmt_execute($stmt);
    $resultadoProductos = mysqli_stmt_get_result($stmt);
} else {
    $resultadoProductos = mysqli_query(
        $conexion,
        'SELECT p.*, c.nombre AS categoria_nombre
         FROM productos p
         LEFT JOIN categorias c ON c.id = p.categoria_id
         WHERE p.activo = 1
         ORDER BY p.nombre ASC'
    );
}
$productos = mysqli_fetch_all($resultadoProductos, MYSQLI_ASSOC);

$categorias = obtener_categorias($conexion);

$js_extra = ['assets/js/productos.js'];
require __DIR__ . '/includes/header.php';
?>

<div class="panel">
    <div class="panel-encabezado">
        <h2><i class="bi bi-box-seam-fill"></i> Productos</h2>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalProducto" id="btnNuevoProducto">
            <i class="bi bi-plus-lg"></i> Nuevo producto
        </button>
    </div>

    <?php if ($errores): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errores as $error): ?><li><?= escapar_texto($error) ?></li><?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="get" class="row g-2 mb-3">
        <div class="col-auto">
            <select name="categoria" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="0">Todas las categorías</option>
                <?php foreach ($categorias as $cat): ?>
                    <option value="<?= (int) $cat['id'] ?>" <?= $categoriaFiltro === (int) $cat['id'] ? 'selected' : '' ?>>
                        <?= escapar_texto($cat['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-hover align-middle" id="tablaProductos">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Nombre</th>
                    <th>Categoría</th>
                    <th class="text-end">Precio venta</th>
                    <th class="text-end">Stock</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$productos): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">No hay productos registrados todavía.</td></tr>
                <?php endif; ?>
                <?php foreach ($productos as $p): ?>
                    <tr id="fila-producto-<?= (int) $p['id'] ?>"
                        data-id="<?= (int) $p['id'] ?>"
                        data-codigo="<?= escapar_texto($p['codigo']) ?>"
                        data-nombre="<?= escapar_texto($p['nombre']) ?>"
                        data-descripcion="<?= escapar_texto($p['descripcion'] ?? '') ?>"
                        data-categoria-id="<?= (int) $p['categoria_id'] ?>"
                        data-unidad="<?= escapar_texto($p['unidad']) ?>"
                        data-precio-compra="<?= escapar_texto((string) $p['precio_compra']) ?>"
                        data-precio-venta="<?= escapar_texto((string) $p['precio_venta']) ?>"
                        data-stock="<?= (int) $p['stock'] ?>"
                        data-stock-minimo="<?= (int) $p['stock_minimo'] ?>">
                        <td><?= escapar_texto($p['codigo']) ?></td>
                        <td><?= escapar_texto($p['nombre']) ?></td>
                        <td><?= escapar_texto($p['categoria_nombre'] ?? 'Sin categoría') ?></td>
                        <td class="text-end"><?= formato_moneda($p['precio_venta']) ?></td>
                        <td class="text-end">
                            <?php if ((int) $p['stock'] < (int) $p['stock_minimo']): ?>
                                <span class="badge text-bg-danger"><?= (int) $p['stock'] ?></span>
                            <?php else: ?>
                                <span class="badge text-bg-light text-dark border"><?= (int) $p['stock'] ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <button type="button" class="btn btn-sm btn-outline-secondary btn-editar-producto" title="Editar">
                                <i class="bi bi-pencil-square"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger btn-eliminar-producto" title="Eliminar">
                                <i class="bi bi-trash3"></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal crear/editar producto -->
<div class="modal fade" id="modalProducto" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="post" id="formProducto">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalProductoTitulo">Nuevo producto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <?php csrf_campo(); ?>
                    <input type="hidden" name="accion" id="campoAccion" value="crear">
                    <input type="hidden" name="id" id="campoId" value="">

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Código *</label>
                            <input type="text" name="codigo" id="campoCodigo" class="form-control" required>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Nombre *</label>
                            <input type="text" name="nombre" id="campoNombre" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Descripción</label>
                            <input type="text" name="descripcion" id="campoDescripcion" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Categoría</label>
                            <select name="categoria_id" id="campoCategoria" class="form-select">
                                <option value="0">Sin categoría</option>
                                <?php foreach ($categorias as $cat): ?>
                                    <option value="<?= (int) $cat['id'] ?>"><?= escapar_texto($cat['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Unidad</label>
                            <input type="text" name="unidad" id="campoUnidad" class="form-control" value="unidad" placeholder="unidad, kg, caja...">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Precio compra</label>
                            <input type="number" step="0.01" min="0" name="precio_compra" id="campoPrecioCompra" class="form-control" value="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Precio venta</label>
                            <input type="number" step="0.01" min="0" name="precio_venta" id="campoPrecioVenta" class="form-control" value="0">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label" id="etiquetaStock">Stock inicial</label>
                            <input type="number" min="0" name="stock" id="campoStock" class="form-control" value="0">
                            <div class="form-text d-none" id="ayudaStock">Para cambiar el stock usa Entradas/Salidas.</div>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Stock mínimo</label>
                            <input type="number" min="0" name="stock_minimo" id="campoStockMinimo" class="form-control" value="<?= (int) STOCK_MINIMO_DEFECTO ?>">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
