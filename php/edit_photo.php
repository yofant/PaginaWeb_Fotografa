<?php
/**
 * Editar metadatos de una foto de la galería
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
    echo json_encode(['exito' => false, 'mensaje' => 'No tiene permisos para editar fotos.']);
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

    // Verificar que la foto exista
    $stmt = $pdo->prepare("SELECT subido_por FROM fotos WHERE id = ? AND disponible = 1 LIMIT 1");
    $stmt->execute([$id_foto]);
    $foto = $stmt->fetch();

    if (!$foto) {
        http_response_code(404);
        echo json_encode(['exito' => false, 'mensaje' => 'Foto no encontrada.']);
        exit;
    }

    // Solo puede editar sus propias fotos, salvo admin
    if ($rol !== 'admin' && (int) $foto['subido_por'] !== (int) $_SESSION['usuario_id']) {
        http_response_code(403);
        echo json_encode(['exito' => false, 'mensaje' => 'Solo puede editar fotos que usted subió.']);
        exit;
    }

    // Validar evento si se proporciona
    $evento_id = isset($datos['evento_id']) && $datos['evento_id'] !== '' ? (int) $datos['evento_id'] : null;
    if ($evento_id !== null) {
        $stmtEv = $pdo->prepare("SELECT id FROM eventos WHERE id = ? LIMIT 1");
        $stmtEv->execute([$evento_id]);
        if (!$stmtEv->fetch()) {
            http_response_code(400);
            echo json_encode(['exito' => false, 'mensaje' => 'El evento seleccionado no existe.']);
            exit;
        }
    }

    $campos = [];
    $valores = [];

    if (isset($datos['titulo'])) { $campos[] = "titulo = ?"; $valores[] = limpiar_entrada($datos['titulo']); }
    if (isset($datos['descripcion'])) { $campos[] = "descripcion = ?"; $valores[] = limpiar_entrada($datos['descripcion']); }
    if (isset($datos['color_predominante'])) { $campos[] = "color_predominante = ?"; $valores[] = limpiar_entrada($datos['color_predominante']); }
    if (isset($datos['numero_moto'])) { $campos[] = "numero_moto = ?"; $valores[] = limpiar_entrada($datos['numero_moto']); }
    if (isset($datos['numero_piloto'])) { $campos[] = "numero_piloto = ?"; $valores[] = limpiar_entrada($datos['numero_piloto']); }
    if (isset($datos['fecha_foto']) && $datos['fecha_foto'] !== '') { $campos[] = "fecha_foto = ?"; $valores[] = $datos['fecha_foto']; }
    elseif (isset($datos['fecha_foto']) && $datos['fecha_foto'] === '') { $campos[] = "fecha_foto = NULL"; }
    if (isset($datos['evento_id'])) { $campos[] = "evento_id = ?"; $valores[] = $evento_id; }
    if (isset($datos['precio'])) { $campos[] = "precio = ?"; $valores[] = max(0, (float) $datos['precio']); }

    if (empty($campos)) {
        http_response_code(400);
        echo json_encode(['exito' => false, 'mensaje' => 'No hay campos para actualizar.']);
        exit;
    }

    $valores[] = $id_foto;
    $sql = "UPDATE fotos SET " . implode(', ', $campos) . " WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($valores);

    registrar_auditoria('editar_foto', $_SESSION['usuario_id']);

    echo json_encode([
        'exito' => true,
        'mensaje' => 'Foto actualizada correctamente.',
        'id' => $id_foto
    ]);

} catch (Exception $e) {
    error_log("Error edit_photo: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['exito' => false, 'mensaje' => 'Error al editar la foto']);
}

?>