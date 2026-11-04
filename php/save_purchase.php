<?php
/**
 * Guardar intención de compra y generar mensaje de WhatsApp
 * Los usuarios son redirigidos a WhatsApp para finalizar la compra
 */
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['exito' => false, 'mensaje' => 'Método no permitido']);
    exit;
}

try {
    $pdo = db_connect();
    $datos = json_decode(file_get_contents('php://input'), true);

    $nombre = limpiar_entrada($datos['nombre'] ?? '');
    $email = limpiar_entrada($datos['email'] ?? '');
    $telefono = limpiar_entrada($datos['telefono'] ?? '');
    $whatsapp = limpiar_entrada($datos['whatsapp'] ?? $telefono);
    $fotos = $datos['fotos'] ?? []; // array de IDs de fotos
    $total = floatval($datos['total'] ?? 0);

    if (empty($nombre) || empty($whatsapp) || empty($fotos)) {
        http_response_code(400);
        echo json_encode(['exito' => false, 'mensaje' => 'Datos incompletos']);
        exit;
    }

    // Obtener detalles de las fotos seleccionadas
    $placeholders = implode(',', array_fill(0, count($fotos), '?'));
    $stmt = $pdo->prepare("SELECT id, titulo, precio, ruta_archivo FROM fotos WHERE id IN ($placeholders)");
    $stmt->execute($fotos);
    $fotos_seleccionadas = $stmt->fetchAll();

    $es_https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    $host = $_SERVER['HTTP_HOST'] ?? parse_url(APP_URL, PHP_URL_HOST);
    $ruta_aplicacion = rtrim(
        str_replace('\\', '/', dirname(dirname($_SERVER['SCRIPT_NAME'] ?? ''))),
        '/'
    );
    $url_aplicacion = ($es_https ? 'https://' : 'http://') . $host . $ruta_aplicacion;

    // Construir mensaje de WhatsApp
    $lineas = [];
    $lineas[] = "🏍️ *Nueva solicitud de compra - JENI HDZ Fotografía Moto Velocidad*";
    $lineas[] = "";
    $lineas[] = "👤 Cliente: " . $nombre;
    $lineas[] = "📧 Email: " . ($email ?: 'No proporcionado');
    $lineas[] = "📞 WhatsApp: " . $whatsapp;
    $lineas[] = "";
    $lineas[] = "📸 Fotos seleccionadas:";

    $total_calc = 0;
    foreach ($fotos_seleccionadas as $f) {
        $lineas[] = "• Foto #" . $f['id'] . " - " . ($f['titulo'] ?: 'Sin título') . " - COP $" . number_format($f['precio'], 2, ',', '.');
        $lineas[] = "  Ver foto: " . $url_aplicacion . '/assets/uploads/' . rawurlencode(basename($f['ruta_archivo']));
        $total_calc += floatval($f['precio']);
    }

    $lineas[] = "";
    $lineas[] = "💰 Total: COP $" . number_format($total_calc, 2, ',', '.');
    $lineas[] = "";
    $lineas[] = "🏁 Evento: Moto Velocidad";
    $lineas[] = "🔗 Página: " . $url_aplicacion;

    $mensaje = implode("\n", $lineas);

    // Guardar en base de datos
    $stmt = $pdo->prepare("INSERT INTO compras 
        (nombre_cliente, email_cliente, telefono_cliente, whatsapp_cliente, fotos_seleccionadas, total, mensaje_whatsapp) 
        VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $nombre,
        $email,
        $telefono,
        $whatsapp,
        json_encode($fotos_seleccionadas),
        $total_calc,
        $mensaje
    ]);

    $id_compra = $pdo->lastInsertId();

    registrar_auditoria('compra_solicitada', null);

    echo json_encode([
        'exito' => true,
        'mensaje' => 'Compra registrada. Redirigiendo a WhatsApp...',
        'id_compra' => $id_compra,
        'whatsapp_url' => 'https://wa.me/' . str_replace('+', '', WHATSAPP_NUMBER) . '?text=' . urlencode($mensaje),
        'total' => $total_calc
    ]);

} catch (Exception $e) {
    error_log("Error save_purchase: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['exito' => false, 'mensaje' => 'Error al procesar la compra']);
}
?>
