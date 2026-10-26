-- ============================================================
-- Datos de ejemplo (OPCIONAL) - solo para pruebas/demostración
-- Ejecutar después de schema.sql si quieres ver la app con datos.
-- ============================================================

USE smart_inventory;

INSERT INTO productos (codigo, nombre, descripcion, categoria_id, unidad, precio_compra, precio_venta, stock, stock_minimo) VALUES
('AB-001', 'Arroz 1kg', 'Arroz blanco grano largo', 1, 'unidad', 2500, 3200, 40, 10),
('AB-002', 'Aceite vegetal 1L', 'Aceite vegetal mezcla', 1, 'unidad', 7800, 9900, 8, 10),
('BE-001', 'Gaseosa Cola 1.5L', 'Bebida gaseosa sabor cola', 2, 'unidad', 3200, 4500, 25, 12),
('BE-002', 'Agua embotellada 600ml', 'Agua sin gas', 2, 'unidad', 900, 1500, 60, 20),
('LA-001', 'Leche entera 1L', 'Leche entera UHT', 3, 'unidad', 2900, 3800, 5, 15),
('PA-001', 'Pan tajado blanco', 'Paquete de pan tajado 500g', 4, 'unidad', 3500, 4800, 14, 8),
('AS-001', 'Detergente líquido 1L', 'Detergente para ropa', 7, 'unidad', 6500, 8900, 3, 10),
('SN-001', 'Papas fritas 150g', 'Snack de papa sabor natural', 9, 'unidad', 2200, 3300, 30, 15)
ON DUPLICATE KEY UPDATE nombre = nombre;
