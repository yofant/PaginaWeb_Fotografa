<?php
/**
 * Obtener lista de eventos para filtros y formularios
 */
require_once 'config.php';

header('Content-Type: application/json');

try {
    $pdo = db_connect();
    $stmt = $pdo->query("SELECT id, nombre, fecha, circuito FROM eventos WHERE activo = 1 ORDER BY fecha DESC");
    $eventos = $stmt->fetchAll();

    echo json_encode(['exito' => true, 'eventos' => $eventos]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['exito' => false, 'mensaje' => 'Error al obtener eventos']);
}
?>
