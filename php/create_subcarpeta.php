<?php
/**
 * Crear subcarpeta dentro de un evento
 * Solo accesible para fotógrafa/admin
 * POST JSON: { evento_id, nombre, descripcion? }
 */
require_once 'config.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['exito' => false, 'mensaje' => 'Método no permitido']);
    exit;
}

// Verificar autenticación
if (empty($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['exito' => false, 'mensaje' => 'No autenticado.']);
    exit;
}

$rol = $_SESSION['usuario_rol'] ?? '';
if (!in_array($rol, ['fotografa', 'admin'], true)) {
    http_response_code(403);
    echo json_encode(['exito' => false, 'mensaje' => 'No tiene permisos para crear subcarpetas.']);
    exit;
}

// Leer body
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

    $evento_id = isset($datos['evento_id']) ? (int) $datos['evento_id'] : 0;
    $nombre = limpiar_entrada($datos['nombre'] ?? '');
    $descripcion = limpiar_entrada($datos['descripcion'] ?? '');

    if (!$evento_id || empty($nombre)) {
        http_response_code(400);
        echo json_encode(['exito' => false, 'mensaje' => 'Evento y nombre son requeridos.']);
        exit;
    }

    // Verificar que el evento existe
    $stmt = $pdo->prepare("SELECT id FROM eventos WHERE id = ? AND activo = 1 LIMIT 1");
    $stmt->execute([$evento_id]);
    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode(['exito' => false, 'mensaje' => 'Evento no encontrado.']);
        exit;
    }

    // Verificar que no exista ya una subcarpeta con ese nombre en ese evento
    $stmt = $pdo->prepare("SELECT id FROM subcarpetas WHERE evento_id = ? AND nombre = ? AND activo = 1 LIMIT 1");
    $stmt->execute([$evento_id, $nombre]);
    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode(['exito' => false, 'mensaje' => 'Ya existe una subcarpeta con ese nombre en este evento.']);
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO subcarpetas (evento_id, nombre, descripcion, activo) VALUES (?, ?, ?, 1)");
    $stmt->execute([$evento_id, $nombre, $descripcion]);

    $id_subcarpeta = $pdo->lastInsertId();

    // Crear carpeta física
    $ruta_carpeta = UPLOAD_DIR . 'eventos/' . $evento_id . '/' . $id_subcarpeta . '/';
    if (!is_dir($ruta_carpeta)) {
        @mkdir($ruta_carpeta, 0775, true);
    }

    registrar_auditoria('crear_subcarpeta', $_SESSION['usuario_id']);

    echo json_encode([
        'exito' => true,
        'mensaje' => 'Subcarpeta creada correctamente.',
        'subcarpeta' => [
            'id' => $id_subcarpeta,
            'evento_id' => $evento_id,
            'nombre' => $nombre,
            'descripcion' => $descripcion
        ]
    ]);

} catch (Exception $e) {
    error_log("Error create_subcarpeta: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['exito' => false, 'mensaje' => 'Error al crear la subcarpeta']);
}

?>