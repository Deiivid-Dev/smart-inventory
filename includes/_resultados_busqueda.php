<?php
/**
 * Plantilla reutilizable: tabla de resultados de búsqueda de productos.
 */
?>
<?php if ($q === ''): ?>
    <p class="text-muted mb-0">Escribe un código, nombre o descripción para buscar productos.</p>
<?php elseif (!$resultados): ?>
    <p class="text-muted mb-0">No se encontraron productos para "<?= escapar_texto($q) ?>".</p>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Nombre</th>
                    <th>Categoría</th>
                    <th class="text-end">Precio venta</th>
                    <th class="text-end">Stock</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($resultados as $p): ?>
                    <tr>
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
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
