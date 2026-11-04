<?php
/**
 * Obtener fotos de la galería con filtros
 * ISO/IEC 23026: Localización oportuna de información
 */
require_once 'config.php';

header('Content-Type: application/json');

try {
    $pdo = db_connect();

    $filtros = [];
    $params = [];

    $sql = "SELECT f.id, f.nombre_archivo, f.ruta_archivo, f.titulo, f.descripcion, 
                   f.color_predominante, f.numero_moto, f.numero_piloto, f.fecha_foto, 
                   f.precio, f.vistas, e.nombre AS evento_nombre
            FROM fotos f
            LEFT JOIN eventos e ON f.evento_id = e.id
            WHERE f.disponible = 1";

    // Filtro por color
    if (!empty($_GET['color'])) {
        $sql .= " AND f.color_predominante = ?";
        $params[] = limpiar_entrada($_GET['color']);
    }

    // Filtro por número de moto
    if (!empty($_GET['numero_moto'])) {
        $sql .= " AND f.numero_moto = ?";
        $params[] = limpiar_entrada($_GET['numero_moto']);
    }

    // Filtro por número de piloto
    if (!empty($_GET['numero_piloto'])) {
        $sql .= " AND f.numero_piloto = ?";
        $params[] = limpiar_entrada($_GET['numero_piloto']);
    }

    // Filtro por fecha
    if (!empty($_GET['fecha'])) {
        $sql .= " AND f.fecha_foto = ?";
        $params[] = limpiar_entrada($_GET['fecha']);
    }

    // Filtro por evento
    if (!empty($_GET['evento'])) {
        $sql .= " AND f.evento_id = ?";
        $params[] = intval($_GET['evento']);
    }

    $sql .= " ORDER BY f.fecha_subida DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $fotos = $stmt->fetchAll();

    // Formatear URLs de imagen
    $ruta_aplicacion = rtrim(
        str_replace('\\', '/', dirname(dirname($_SERVER['SCRIPT_NAME'] ?? ''))),
        '/'
    );
    foreach ($fotos as &$foto) {
        $foto['url_imagen'] = $ruta_aplicacion . '/assets/uploads/' . $foto['ruta_archivo'];
        $foto['url_miniatura'] = $foto['url_imagen'];
    }
    unset($foto);

    echo json_encode([
        'exito' => true,
        'total' => count($fotos),
        'fotos' => $fotos
    ]);

} catch (Exception $e) {
    error_log("Error get_photos: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['exito' => false, 'mensaje' => 'Error al obtener fotos']);
}
?>
