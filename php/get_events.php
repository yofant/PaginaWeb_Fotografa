<?php
/**
 * Obtener lista de eventos para filtros y formularios
 * Soporta filtro por ID individual: ?id=1
 */
require_once 'config.php';

header('Content-Type: application/json');

try {
    $pdo = db_connect();

    // Verificar si la columna imagen_portada existe
    $tiene_imagen_portada = false;
    try {
        $stmtCheck = $pdo->query("SHOW COLUMNS FROM eventos LIKE 'imagen_portada'");
        $tiene_imagen_portada = $stmtCheck->rowCount() > 0;
    } catch (Exception $e) {
        $tiene_imagen_portada = false;
    }

    if ($tiene_imagen_portada) {
        $sql = "SELECT id, nombre, fecha, circuito, pais, descripcion, imagen_portada, activo 
                FROM eventos WHERE 1=1";
    } else {
        $sql = "SELECT id, nombre, fecha, circuito, pais, descripcion, activo 
                FROM eventos WHERE 1=1";
    }
    $params = [];

    if (!empty($_GET['id'])) {
        $sql .= " AND id = ?";
        $params[] = (int) $_GET['id'];
    } else {
        $sql .= " AND activo = 1";
    }

    $sql .= " ORDER BY fecha DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $eventos = $stmt->fetchAll();

    // Asegurar que imagen_portada exista en la respuesta
    foreach ($eventos as &$ev) {
        if (!isset($ev['imagen_portada'])) {
            $ev['imagen_portada'] = null;
        }
    }
    unset($ev);

    echo json_encode(['exito' => true, 'eventos' => $eventos]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['exito' => false, 'mensaje' => 'Error al obtener eventos']);
}

?>