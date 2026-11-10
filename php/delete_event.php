<?php
/**
 * Eliminar un evento de moto velocidad
 * Solo accesible para la fotógrafa autenticada (rol fotografa/admin)
 */
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'DELETE') {
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
    echo json_encode(['exito' => false, 'mensaje' => 'No tiene permisos para eliminar eventos.']);
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

    // Verificar que el evento exista y obtener su imagen de portada
    $stmt = $pdo->prepare("SELECT id, imagen_portada FROM eventos WHERE id = ? LIMIT 1");
    $stmt->execute([$id_evento]);
    $evento = $stmt->fetch();
    if (!$evento) {
        http_response_code(404);
        echo json_encode(['exito' => false, 'mensaje' => 'Evento no encontrado.']);
        exit;
    }

    $stmt = $pdo->prepare("DELETE FROM eventos WHERE id = ?");
    $stmt->execute([$id_evento]);

    // Borrar imagen de portada del disco
    if (!empty($evento['imagen_portada'])) {
        $ruta_portada = UPLOAD_DIR . $evento['imagen_portada'];
        if (file_exists($ruta_portada)) {
            @unlink($ruta_portada);
        }
    }

    registrar_auditoria('eliminar_evento', $_SESSION['usuario_id']);

    echo json_encode([
        'exito' => true,
        'mensaje' => 'Evento eliminado correctamente.',
        'id' => $id_evento
    ]);

} catch (Exception $e) {
    error_log("Error delete_event: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['exito' => false, 'mensaje' => 'Error al eliminar el evento']);
}

?>