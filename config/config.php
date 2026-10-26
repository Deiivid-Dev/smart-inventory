<?php
/**
 * Configuración general de la aplicación.
 * Ajusta estos valores según tu entorno (XAMPP/MAMP/Laragon/etc).
 */

// --- Base de datos ---
define('DB_HOST', 'localhost');
define('DB_NAME', 'smart_inventory');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// --- Aplicación ---
define('APP_NOMBRE', 'Smart Inventory');
define('STOCK_MINIMO_DEFECTO', 5);

// --- Sesión ---
define('SESSION_NAME', 'smart_inventory_sid');

// Mostrar errores en desarrollo. Poner en 0 en producción.
define('APP_DEBUG', 0);

if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

date_default_timezone_set('America/Bogota');
