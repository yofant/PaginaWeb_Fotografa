<?php
/**
 * Obtener subcarpetas de un evento (o todas las subcarpetas activas)
 * GET ?evento_id=X  → subcarpetas de un evento específico
 * GET (sin params)  → todas las subcarpetas activas con info del evento
 */
require_once 'config.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $pdo = db_connect();

    if (!empty($_GET['evento_id'])) {
        $evento_id = (int) $_GET['evento_id'];
        $stmt = $pdo->prepare("SELECT id, evento_id, nombre, descripcion, activo, fecha_creacion 
                               FROM subcarpetas WHERE evento_id = ? AND activo = 1 ORDER BY nombre ASC");
        $stmt->execute([$evento_id]);
    } else {
        $stmt = $pdo->query("SELECT s.id, s.evento_id, s.nombre, s.descripcion, s.activo, s.fecha_creacion, e.nombre AS evento_nombre
                             FROM subcarpetas s
                             INNER JOIN eventos e ON s.evento_id = e.id
                             WHERE s.activo = 1 AND e.activo = 1
                             ORDER BY e.fecha DESC, s.nombre ASC");
    }

    $subcarpetas = $stmt->fetchAll();

    echo json_encode(['exito' => true, 'subcarpetas' => $subcarpetas]);

} catch (Exception $e) {
    error_log("Error get_subcarpetas: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['exito' => false, 'mensaje' => 'Error al obtener subcarpetas']);
}

?>