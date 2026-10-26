<?php
/**
 * Asistente de primer uso: crea el usuario administrador inicial.
 * Se desactiva automáticamente en cuanto exista al menos un usuario.
 */
require_once __DIR__ . '/includes/bootstrap.php';

if (!sistema_sin_usuarios($conexion)) {
    redirigir('login.php');
}

$errores = [];
$nombre = '';
$usuario = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verificar_token($_POST['csrf_token'] ?? null);

    $nombre = trim($_POST['nombre'] ?? '');
    $usuario = trim($_POST['usuario'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmar = $_POST['confirmar'] ?? '';

    if ($nombre === '' || $usuario === '' || $password === '') {
        $errores[] = 'Todos los campos son obligatorios.';
    }
    if (strlen($password) < 6) {
        $errores[] = 'La contraseña debe tener al menos 6 caracteres.';
    }
    if ($password !== $confirmar) {
        $errores[] = 'Las contraseñas no coinciden.';
    }

    if (!$errores) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = mysqli_prepare(
            $conexion,
            'INSERT INTO usuarios (nombre, usuario, password) VALUES (?, ?, ?)'
        );
        mysqli_stmt_bind_param($stmt, 'sss', $nombre, $usuario, $hash);
        mysqli_stmt_execute($stmt);

        flash_establecer('success', 'Usuario administrador creado. Ya puedes iniciar sesión.');
        redirigir('login.php');
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Configuración inicial · <?= escapar_texto(APP_NOMBRE) ?></title>
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
    <p class="text-center text-muted mb-4">Configuración inicial: crea el usuario administrador</p>

    <?php if ($errores): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errores as $error): ?>
                    <li><?= escapar_texto($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" novalidate>
        <?php csrf_campo(); ?>
        <div class="mb-3">
            <label class="form-label">Nombre completo</label>
            <input type="text" name="nombre" class="form-control" value="<?= escapar_texto($nombre) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Usuario</label>
            <input type="text" name="usuario" class="form-control" value="<?= escapar_texto($usuario) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Contraseña</label>
            <input type="password" name="password" class="form-control" minlength="6" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Confirmar contraseña</label>
            <input type="password" name="confirmar" class="form-control" minlength="6" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">
            <i class="bi bi-check-circle"></i> Crear administrador
        </button>
    </form>
</div>
</body>
</html>
