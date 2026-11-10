<?php
/**
 * Eliminar subcarpeta (soft delete)
 * Solo accesible para fotógrafa/admin
 * POST JSON: { id }
 */
require_once 'config.php';

header('Content-Type: application/json; charset=utf-8');

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
    echo json_encode(['exito' => false, 'mensaje' => 'No tiene permisos para eliminar subcarpetas.']);
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

    $id_subcarpeta = isset($datos['id']) ? (int) $datos['id'] : 0;
    if (!$id_subcarpeta) {
        http_response_code(400);
        echo json_encode(['exito' => false, 'mensaje' => 'ID de subcarpeta requerido.']);
        exit;
    }

    // Verificar que existe
    $stmt = $pdo->prepare("SELECT id, evento_id, nombre FROM subcarpetas WHERE id = ? AND activo = 1 LIMIT 1");
    $stmt->execute([$id_subcarpeta]);
    $subcarpeta = $stmt->fetch();

    if (!$subcarpeta) {
        http_response_code(404);
        echo json_encode(['exito' => false, 'mensaje' => 'Subcarpeta no encontrada.']);
        exit;
    }

    // Soft delete de la subcarpeta
    $stmt = $pdo->prepare("UPDATE subcarpetas SET activo = 0 WHERE id = ?");
    $stmt->execute([$id_subcarpeta]);

    // Desvincular fotos de esta subcarpeta (quedan sin subcarpeta)
    $stmt = $pdo->prepare("UPDATE fotos SET subcarpeta_id = NULL WHERE subcarpeta_id = ?");
    $stmt->execute([$id_subcarpeta]);

    registrar_auditoria('eliminar_subcarpeta', $_SESSION['usuario_id']);

    echo json_encode([
        'exito' => true,
        'mensaje' => 'Subcarpeta eliminada correctamente.',
        'id' => $id_subcarpeta
    ]);

} catch (Exception $e) {
    error_log("Error delete_subcarpeta: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['exito' => false, 'mensaje' => 'Error al eliminar la subcarpeta']);
}

?>