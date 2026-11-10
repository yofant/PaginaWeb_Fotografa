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

    // Verificar si la columna nombre_original existe
    $tiene_nombre_original = false;
    try {
        $stmtCheck = $pdo->query("SHOW COLUMNS FROM fotos LIKE 'nombre_original'");
        $tiene_nombre_original = $stmtCheck->rowCount() > 0;
    } catch (Exception $e) {
        $tiene_nombre_original = false;
    }

    if ($tiene_nombre_original) {
        $sql = "SELECT f.id, f.nombre_archivo, f.ruta_archivo, f.nombre_original, f.titulo, f.descripcion, 
                       f.color_predominante, f.numero_moto, f.numero_piloto, f.fecha_foto, 
                       f.precio, f.vistas, f.evento_id, f.subcarpeta_id, f.subido_por, e.nombre AS evento_nombre
                FROM fotos f
                LEFT JOIN eventos e ON f.evento_id = e.id
                WHERE f.disponible = 1";
    } else {
        $sql = "SELECT f.id, f.nombre_archivo, f.ruta_archivo, f.titulo, f.descripcion, 
                       f.color_predominante, f.numero_moto, f.numero_piloto, f.fecha_foto, 
                       f.precio, f.vistas, f.evento_id, f.subcarpeta_id, f.subido_por, e.nombre AS evento_nombre
                FROM fotos f
                LEFT JOIN eventos e ON f.evento_id = e.id
                WHERE f.disponible = 1";
    }

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

    // Filtro por subcarpeta
    if (!empty($_GET['subcarpeta'])) {
        $sql .= " AND f.subcarpeta_id = ?";
        $params[] = intval($_GET['subcarpeta']);
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
