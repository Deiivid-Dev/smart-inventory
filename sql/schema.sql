-- ============================================================
-- Smart Inventory - Esquema de base de datos (MySQL)
-- ============================================================

CREATE DATABASE IF NOT EXISTS smart_inventory
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE smart_inventory;

-- ------------------------------------------------------------
-- Usuarios del sistema (login)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS usuarios (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Categorías de productos
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS categorias (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

INSERT INTO categorias (nombre) VALUES
    ('Abarrotes'),
    ('Bebidas'),
    ('Lácteos y huevos'),
    ('Panadería'),
    ('Carnes y embutidos'),
    ('Frutas y verduras'),
    ('Aseo y limpieza'),
    ('Cuidado personal'),
    ('Snacks y dulces'),
    ('Otros')
ON DUPLICATE KEY UPDATE nombre = nombre;

-- ------------------------------------------------------------
-- Productos
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS productos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(50) NOT NULL UNIQUE,
    nombre VARCHAR(150) NOT NULL,
    descripcion VARCHAR(255) NULL,
    categoria_id INT UNSIGNED NULL,
    unidad VARCHAR(30) NOT NULL DEFAULT 'unidad',
    precio_compra DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    precio_venta DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    stock INT NOT NULL DEFAULT 0,
    stock_minimo INT NOT NULL DEFAULT 5,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_productos_categoria FOREIGN KEY (categoria_id)
        REFERENCES categorias(id) ON DELETE SET NULL,
    INDEX idx_productos_nombre (nombre),
    INDEX idx_productos_stock (stock)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Movimientos de inventario (entradas y salidas)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS movimientos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    producto_id INT UNSIGNED NOT NULL,
    usuario_id INT UNSIGNED NULL,
    tipo ENUM('entrada', 'salida') NOT NULL,
    cantidad INT UNSIGNED NOT NULL,
    motivo VARCHAR(255) NULL,
    stock_anterior INT NOT NULL,
    stock_nuevo INT NOT NULL,
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_movimientos_producto FOREIGN KEY (producto_id)
        REFERENCES productos(id) ON DELETE CASCADE,
    CONSTRAINT fk_movimientos_usuario FOREIGN KEY (usuario_id)
        REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_movimientos_fecha (creado_en),
    INDEX idx_movimientos_tipo (tipo)
) ENGINE=InnoDB;
