<?php
/**
 * Endpoint AJAX: elimina (desactiva) un producto.
 * Se hace "soft delete" (activo = 0) para no perder el historial de movimientos.
 */
require_once __DIR__ . '/../includes/bootstrap.php';

if (!hay_sesion()) {
    json_responder(['ok' => false, 'mensaje' => 'No autorizado.'], 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_responder(['ok' => false, 'mensaje' => 'Método no permitido.'], 405);
}

$datos = json_decode(file_get_contents('php://input'), true) ?: $_POST;

if (!csrf_valido($datos['csrf_token'] ?? null)) {
    json_responder(['ok' => false, 'mensaje' => 'Token de seguridad inválido.'], 400);
}

$id = (int) ($datos['id'] ?? 0);
if ($id <= 0) {
    json_responder(['ok' => false, 'mensaje' => 'Producto inválido.'], 422);
}

$stmt = mysqli_prepare($conexion, 'SELECT id, nombre FROM productos WHERE id = ? AND activo = 1 LIMIT 1');
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
$producto = mysqli_fetch_assoc($resultado);

if (!$producto) {
    json_responder(['ok' => false, 'mensaje' => 'El producto no existe o ya fue eliminado.'], 404);
}

$stmtEliminar = mysqli_prepare($conexion, 'UPDATE productos SET activo = 0 WHERE id = ?');
mysqli_stmt_bind_param($stmtEliminar, 'i', $id);
mysqli_stmt_execute($stmtEliminar);

json_responder(['ok' => true, 'mensaje' => 'Producto "' . $producto['nombre'] . '" eliminado correctamente.']);
