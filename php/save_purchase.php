<?php
/**
 * Guardar intención de compra y generar mensaje de WhatsApp
 * Los usuarios son redirigidos a WhatsApp para finalizar la compra
 */
require_once 'config.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['exito' => false, 'mensaje' => 'Método no permitido']);
    exit;
}

try {
    // Leer el body una sola vez
    $raw_input = file_get_contents('php://input');
    $datos = json_decode($raw_input, true);

    // Validar token CSRF
    $token_csrf = $datos['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!validar_token_csrf($token_csrf)) {
        http_response_code(403);
        echo json_encode(['exito' => false, 'mensaje' => 'Token CSRF inválido']);
        exit;
    }

    $pdo = db_connect();

    $nombre = limpiar_entrada($datos['nombre'] ?? '');
    $email = limpiar_entrada($datos['email'] ?? '');
    $telefono = limpiar_entrada($datos['telefono'] ?? '');
    $whatsapp = limpiar_entrada($datos['whatsapp'] ?? $telefono);
    $fotos = $datos['fotos'] ?? []; // array de IDs de fotos
    // El total se recalcula desde la base de datos (se ignora el enviado por el cliente)
    $total = 0;

    if (empty($nombre) || empty($whatsapp) || empty($fotos)) {
        http_response_code(400);
        echo json_encode(['exito' => false, 'mensaje' => 'Datos incompletos']);
        exit;
    }

    // Obtener detalles de las fotos seleccionadas (incluyendo nombre original)
    $placeholders = implode(',', array_fill(0, count($fotos), '?'));
    $stmt = $pdo->prepare("SELECT id, titulo, precio, ruta_archivo, nombre_original, nombre_archivo, evento_id FROM fotos WHERE id IN ($placeholders)");
    $stmt->execute($fotos);
    $fotos_seleccionadas = $stmt->fetchAll();

    $es_https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    $host = $_SERVER['HTTP_HOST'] ?? parse_url(APP_URL, PHP_URL_HOST);
    $ruta_aplicacion = rtrim(
        str_replace('\\', '/', dirname(dirname($_SERVER['SCRIPT_NAME'] ?? ''))),
        '/'
    );
    $url_aplicacion = ($es_https ? 'https://' : 'http://') . $host . $ruta_aplicacion;

    // Construir mensaje de WhatsApp con formato elegante y profesional (sin emojis)
    $sep = "━━━━━━━━━━━━━━━━━━━━";
    $lineas = [];
    $lineas[] = "*JENI HDZ — Fotografía Moto Velocidad*";
    $lineas[] = "_Nueva solicitud de compra_";
    $lineas[] = "";
    $lineas[] = $sep;
    $lineas[] = "*Cliente:* " . $nombre;
    $lineas[] = "*Email:* " . ($email ?: 'No proporcionado');
    $lineas[] = "*WhatsApp:* " . $whatsapp;
    $lineas[] = $sep;
    $lineas[] = "";
    $lineas[] = "*Fotos seleccionadas (" . count($fotos_seleccionadas) . "):*";
    $lineas[] = "";

    $total_calc = 0;
    foreach ($fotos_seleccionadas as $f) {
        $titulo_foto = $f['titulo'] ?: 'Sin título';
        $nombre_archivo = $f['nombre_original'] ?: $f['nombre_archivo'];
        $precio_fmt = "COP $" . number_format($f['precio'], 3, ',', '.');

        $lineas[] = "• *Foto #" . $f['id'] . "* — " . $titulo_foto;
        $lineas[] = "  Archivo: " . $nombre_archivo;
        $lineas[] = "  Precio: " . $precio_fmt;
        $lineas[] = "";
        $total_calc += floatval($f['precio']);
    }

    $lineas[] = $sep;
    $lineas[] = "*Total: COP $" . number_format($total_calc, 3, ',', '.') . "*";
    $lineas[] = "";
    $lineas[] = "Evento: Moto Velocidad";
    $lineas[] = "Página: " . $url_aplicacion;

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