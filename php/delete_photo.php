<?php
/**
 * Eliminar una foto de la galería (soft-delete)
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
    echo json_encode(['exito' => false, 'mensaje' => 'No tiene permisos para eliminar fotos.']);
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
    $id_foto = isset($datos['id']) ? (int) $datos['id'] : null;

    if (!$id_foto) {
        http_response_code(400);
        echo json_encode(['exito' => false, 'mensaje' => 'ID de foto requerido.']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT ruta_archivo, subido_por FROM fotos WHERE id = ? AND disponible = 1 LIMIT 1");
    $stmt->execute([$id_foto]);
    $foto = $stmt->fetch();

    if (!$foto) {
        http_response_code(404);
        echo json_encode(['exito' => false, 'mensaje' => 'Foto no encontrada.']);
        exit;
    }

    if ($rol !== 'admin' && (int) $foto['subido_por'] !== (int) $_SESSION['usuario_id']) {
        http_response_code(403);
        echo json_encode(['exito' => false, 'mensaje' => 'Solo puede eliminar fotos que usted subió.']);
        exit;
    }

    // Soft delete
    $stmt = $pdo->prepare("UPDATE fotos SET disponible = 0 WHERE id = ?");
    $stmt->execute([$id_foto]);

    // Borrar archivos físicos
    $ruta_archivo = UPLOAD_DIR . $foto['ruta_archivo'];
    if (file_exists($ruta_archivo)) {
        @unlink($ruta_archivo);
    }
    // Borrar también la versión original si existe
    $ruta_original = UPLOAD_DIR . basename($foto['ruta_archivo']);
    if ($ruta_original !== $ruta_archivo && file_exists($ruta_original)) {
        @unlink($ruta_original);
    }

    registrar_auditoria('eliminar_foto', $_SESSION['usuario_id']);

    echo json_encode([
        'exito' => true,
        'mensaje' => 'Foto eliminada correctamente.',
        'id' => $id_foto
    ]);

} catch (Exception $e) {
    error_log("Error delete_photo: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['exito' => false, 'mensaje' => 'Error al eliminar la foto']);
}

?>