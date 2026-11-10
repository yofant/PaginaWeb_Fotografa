<?php
/**
 * Crear un nuevo evento de moto velocidad
 * Solo accesible para la fotógrafa autenticada (rol fotografa/admin)
 */
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['exito' => false, 'mensaje' => 'Método no permitido']);
    exit;
}

// Verificar autenticación
if (empty($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['exito' => false, 'mensaje' => 'No autenticado. Inicie sesión como fotógrafa.']);
    exit;
}

$rol = $_SESSION['usuario_rol'] ?? '';
if (!in_array($rol, ['fotografa', 'admin'], true)) {
    http_response_code(403);
    echo json_encode(['exito' => false, 'mensaje' => 'No tiene permisos para crear eventos.']);
    exit;
}

// Validar token CSRF
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

    $nombre = limpiar_entrada($datos['nombre'] ?? '');
    $fecha = !empty($datos['fecha']) ? $datos['fecha'] : null;
    $circuito = isset($datos['circuito']) ? limpiar_entrada($datos['circuito']) : null;
    $pais = isset($datos['pais']) ? limpiar_entrada($datos['pais']) : null;
    $descripcion = isset($datos['descripcion']) ? limpiar_entrada($datos['descripcion']) : null;
    $imagen_portada = isset($datos['imagen_portada']) ? limpiar_entrada($datos['imagen_portada']) : null;
    $activo = isset($datos['activo']) ? (int) $datos['activo'] : 1;

    if (empty($nombre) || !$fecha) {
        http_response_code(400);
        echo json_encode(['exito' => false, 'mensaje' => 'Nombre y fecha son requeridos.']);
        exit;
    }

    // Verificar si la columna imagen_portada existe
    $tiene_imagen_portada = false;
    try {
        $stmtCheck = $pdo->query("SHOW COLUMNS FROM eventos LIKE 'imagen_portada'");
        $tiene_imagen_portada = $stmtCheck->rowCount() > 0;
    } catch (Exception $e) {
        $tiene_imagen_portada = false;
    }

    if ($tiene_imagen_portada) {
        $stmt = $pdo->prepare("INSERT INTO eventos 
            (nombre, fecha, circuito, pais, descripcion, imagen_portada, activo, fecha_creacion) 
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([
            $nombre, $fecha, $circuito, $pais, $descripcion, $imagen_portada, $activo
        ]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO eventos 
            (nombre, fecha, circuito, pais, descripcion, activo, fecha_creacion) 
            VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([
            $nombre, $fecha, $circuito, $pais, $descripcion, $activo
        ]);
    }

    $id_evento = $pdo->lastInsertId();

    registrar_auditoria('crear_evento', $_SESSION['usuario_id']);

    echo json_encode([
        'exito' => true,
        'mensaje' => 'Evento creado correctamente.',
        'id' => $id_evento
    ]);

} catch (Exception $e) {
    error_log("Error create_event: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['exito' => false, 'mensaje' => 'Error al crear el evento']);
}

?>