<?php
require_once __DIR__ . '/includes/bootstrap.php';

if (sistema_sin_usuarios($conexion)) {
    redirigir('instalar.php');
}

if (hay_sesion()) {
    redirigir('index.php');
}

$error = null;
$usuarioForm = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verificar_token($_POST['csrf_token'] ?? null);

    $usuarioForm = trim($_POST['usuario'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($usuarioForm === '' || $password === '') {
        $error = 'Ingresa tu usuario y contraseña.';
    } elseif (intentar_login($conexion, $usuarioForm, $password)) {
        redirigir('index.php');
    } else {
        $error = 'Usuario o contraseña incorrectos.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Iniciar sesión · <?= escapar_texto(APP_NOMBRE) ?></title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="assets/css/estilo.css">
</head>
<body class="pagina-login">
<div class="caja-login">
    <div class="caja-login-marca">
        <i class="bi bi-shop"></i>
        <h1><?= escapar_texto(APP_NOMBRE) ?></h1>
    </div>
    <p class="text-center text-muted mb-4">Inicia sesión para continuar</p>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= escapar_texto($error) ?></div>
    <?php endif; ?>

    <?php $flash = flash_obtener(); if ($flash): ?>
        <div class="alert alert-<?= escapar_texto($flash['tipo']) ?>"><?= escapar_texto($flash['mensaje']) ?></div>
    <?php endif; ?>

    <form method="post" novalidate>
        <?php csrf_campo(); ?>
        <div class="mb-3">
            <label class="form-label">Usuario</label>
            <input type="text" name="usuario" class="form-control" value="<?= escapar_texto($usuarioForm) ?>" autofocus required>
        </div>
        <div class="mb-3">
            <label class="form-label">Contraseña</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">
            <i class="bi bi-box-arrow-in-right"></i> Ingresar
        </button>
    </form>
</div>
</body>
</html>
