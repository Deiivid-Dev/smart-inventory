<?php

/**
 * Encabezado HTML común + barra lateral de navegación.
 * Variables esperadas antes de incluir este archivo:
 *   $titulo_pagina (string) - título a mostrar en <title> y en el encabezado.
 *   $pagina_activa (string) - clave de la página actual, para resaltar el menú.
 */

requerir_login();

$titulo_pagina = $titulo_pagina ?? APP_NOMBRE;
$pagina_activa = $pagina_activa ?? '';
$usuario = usuario_actual();
$totalAlertas = contar_alertas_stock($conexion);
$flash = flash_obtener();

$menu = [
    'inicio' => ['url' => 'index.php', 'icono' => 'bi-house-door-fill', 'texto' => 'Panel principal'],
    'productos' => ['url' => 'productos.php', 'icono' => 'bi-box-seam-fill', 'texto' => 'Productos'],
    'entradas' => ['url' => 'entradas.php', 'icono' => 'bi-box-arrow-in-down', 'texto' => 'Entradas'],
    'salidas' => ['url' => 'salidas.php', 'icono' => 'bi-box-arrow-up', 'texto' => 'Salidas'],
    'busqueda' => ['url' => 'busqueda.php', 'icono' => 'bi-search', 'texto' => 'Búsqueda'],
    'alertas' => ['url' => 'alertas.php', 'icono' => 'bi-exclamation-triangle-fill', 'texto' => 'Alertas'],
    'historial' => ['url' => 'historial.php', 'icono' => 'bi-clock-history', 'texto' => 'Historial'],
    'estadisticas' => ['url' => 'estadisticas.php', 'icono' => 'bi-bar-chart-fill', 'texto' => 'Estadísticas'],
];
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= escapar_texto($titulo_pagina) ?> · <?= escapar_texto(APP_NOMBRE) ?></title>
    <meta name="csrf-token" content="<?= escapar_texto(csrf_token()) ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/estilo.css">
</head>

<body>
    <div class="app-wrapper">
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-marca">
                <i class="bi bi-shop"></i>
                <span><?= escapar_texto(APP_NOMBRE) ?></span>
            </div>
            <nav class="sidebar-nav">
                <?php foreach ($menu as $clave => $item): ?>
                    <a href="<?= escapar_texto($item['url']) ?>" class="sidebar-link <?= $pagina_activa === $clave ? 'activo' : '' ?>">
                        <i class="bi <?= escapar_texto($item['icono']) ?>"></i>
                        <span><?= escapar_texto($item['texto']) ?></span>
                        <?php if ($clave === 'alertas' && $totalAlertas > 0): ?>
                            <span class="badge text-bg-danger ms-auto"><?= (int) $totalAlertas ?></span>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </nav>
            <div class="sidebar-footer">
                <a href="logout.php" class="sidebar-link" id="enlaceCerrarSesion">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Cerrar sesión</span>
                </a>
            </div>
        </aside>

        <div class="app-main">
            <header class="topbar">
                <button class="btn btn-sm btn-outline-secondary d-lg-none" id="btnToggleSidebar" type="button">
                    <i class="bi bi-list"></i>
                </button>
                <h1 class="topbar-titulo"><?= escapar_texto($titulo_pagina) ?></h1>
                <div class="topbar-usuario">
                    <i class="bi bi-person-circle"></i>
                    <span><?= escapar_texto($usuario['nombre'] ?? '') ?></span>
                </div>
            </header>

            <main class="contenido">
                <?php if ($flash): ?>
                    <div class="alert alert-<?= escapar_texto($flash['tipo']) ?> alert-dismissible fade show" role="alert">
                        <?= escapar_texto($flash['mensaje']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                    </div>
                <?php endif; ?>