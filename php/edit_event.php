<?php
/**
 * Editar un evento de moto velocidad
 * Solo accesible para la fotógrafa autenticada (rol fotografa/admin)
 */
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['exito' => false, 'mensaje' => 'Método no permitido']);
    exit;
}

if (empty($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['exito' => false, 'mensaje' => 'No autenticado.']);
    exit;
}

$rol = $_SESSION['usuario_rol'] ?? '';
if (!in_array($rol, ['fotografa', 'admin'], true)) {
    http_response_code(403);
    echo json_encode(['exito' => false, 'mensaje' => 'No tiene permisos para editar eventos.']);
    exit;
}

$raw_input = file_get_contents('php://input');
$datos = json_decode($raw_input, true);

$token_csrf = $datos['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (!validar_token_csrf($token_csrf)) {
    http_response_code(403);
    echo json_encode(['exito' => false, 'mensaje' => 'Token CSRF inválido']);
    exit;
}

try {
    $pdo = db_connect();

    $id_evento = isset($datos['id']) ? (int) $datos['id'] : null;
    if (!$id_evento) {
        http_response_code(400);
        echo json_encode(['exito' => false, 'mensaje' => 'ID de evento requerido.']);
        exit;
    }

    $nombre = isset($datos['nombre']) ? limpiar_entrada($datos['nombre']) : null;
    $fecha = isset($datos['fecha']) && $datos['fecha'] !== '' ? $datos['fecha'] : null;
    $circuito = isset($datos['circuito']) ? limpiar_entrada($datos['circuito']) : null;
    $pais = isset($datos['pais']) ? limpiar_entrada($datos['pais']) : null;
    $descripcion = isset($datos['descripcion']) ? limpiar_entrada($datos['descripcion']) : null;
    $imagen_portada = isset($datos['imagen_portada']) ? limpiar_entrada($datos['imagen_portada']) : null;
    $activo = isset($datos['activo']) ? (int) $datos['activo'] : null;

    $campos = [];
    $valores = [];

    if ($nombre !== null) { $campos[] = "nombre = ?"; $valores[] = $nombre; }
    if ($fecha !== null) { $campos[] = "fecha = ?"; $valores[] = $fecha; }
    if ($circuito !== null) { $campos[] = "circuito = ?"; $valores[] = $circuito; }
    if ($pais !== null) { $campos[] = "pais = ?"; $valores[] = $pais; }
    if ($descripcion !== null) { $campos[] = "descripcion = ?"; $valores[] = $descripcion; }
    // Verificar si la columna imagen_portada existe antes de incluirla
    $tiene_imagen_portada = false;
    try {
        $stmtCheck = $pdo->query("SHOW COLUMNS FROM eventos LIKE 'imagen_portada'");
        $tiene_imagen_portada = $stmtCheck->rowCount() > 0;
    } catch (Exception $e) {
        $tiene_imagen_portada = false;
    }

    if ($imagen_portada !== null && $tiene_imagen_portada) { $campos[] = "imagen_portada = ?"; $valores[] = $imagen_portada; }
    if ($activo !== null) { $campos[] = "activo = ?"; $valores[] = $activo; }

    if (empty($campos)) {
        http_response_code(400);
        echo json_encode(['exito' => false, 'mensaje' => 'No hay campos para actualizar.']);
        exit;
    }

    $valores[] = $id_evento;
    $sql = "UPDATE eventos SET " . implode(', ', $campos) . " WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($valores);

    registrar_auditoria('editar_evento', $_SESSION['usuario_id']);

    echo json_encode([
        'exito' => true,
        'mensaje' => 'Evento actualizado correctamente.',
        'id' => $id_evento
    ]);

} catch (Exception $e) {
    error_log("Error edit_event: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['exito' => false, 'mensaje' => 'Error al editar el evento']);
}

?>