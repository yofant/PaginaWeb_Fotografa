<?php
/**
 * Formulario de contacto - JENI HDZ
 * Guarda el mensaje y genera enlace de WhatsApp para notificar a la fotógrafa
 */
require_once 'config.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['exito' => false, 'mensaje' => 'Método no permitido']);
    exit;
}

// Leer el body una sola vez
$raw_input = file_get_contents('php://input');
$datos = json_decode($raw_input, true);

// CSRF: validación opcional (el formulario es público, la protección principal es la validación de campos)
$token_csrf = $datos['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (!empty($token_csrf) && !validar_token_csrf($token_csrf)) {
    error_log("CSRF fail contacto. Received: " . $token_csrf . " | Session: " . ($_SESSION['csrf_token'] ?? 'NOT SET'));
    // No bloquear, solo registrar
}

try {
    $pdo = db_connect();

    // Honeypot anti-spam: si el campo 'website' está lleno, es un bot
    if (!empty($datos['website'])) {
        http_response_code(400);
        echo json_encode(['exito' => false, 'mensaje' => 'Error de validación']);
        exit;
    }

    $nombre = limpiar_entrada($datos['nombre'] ?? '');
    $email = limpiar_entrada($datos['email'] ?? '');
    $telefono = limpiar_entrada($datos['telefono'] ?? '');
    $asunto = limpiar_entrada($datos['asunto'] ?? '');
    $mensaje_cliente = limpiar_entrada($datos['mensaje'] ?? '');
    $terminos = !empty($datos['terminos']);

    if (empty($nombre) || empty($email) || empty($telefono) || empty($asunto) || empty($mensaje_cliente)) {
        http_response_code(400);
        echo json_encode(['exito' => false, 'mensaje' => 'Todos los campos son requeridos']);
        exit;
    }

    if (!$terminos) {
        http_response_code(400);
        echo json_encode(['exito' => false, 'mensaje' => 'Debes aceptar los Términos y Condiciones']);
        exit;
    }

    // Guardar en base de datos (si la tabla existe)
    $id_contacto = null;
    try {
        $stmt = $pdo->prepare("INSERT INTO contactos 
            (nombre, email, telefono, asunto, mensaje, terminos_aceptados, fecha_contacto) 
            VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$nombre, $email, $telefono, $asunto, $mensaje_cliente, $terminos ? 1 : 0]);
        $id_contacto = $pdo->lastInsertId();
    } catch (Exception $e) {
        error_log("Error guardando contacto en BD: " . $e->getMessage());
        // Continuar aunque falle el guardado en BD
    }

    // Mapear asunto a texto legible
    $asuntos = [
        'cotizacion' => 'Cotización de paquete',
        'evento' => 'Cobertura de evento',
        'galeria' => 'Compra de fotos de galería',
        'otro' => 'Otro'
    ];
    $asunto_texto = $asuntos[$asunto] ?? $asunto;

    // Fecha y hora del envío
    $fecha_envio = date('d/m/Y H:i:s');

    // Construir mensaje de WhatsApp con formato elegante
    $sep = "━━━━━━━━━━━━━━━━━━━━";
    $lineas = [];
    $lineas[] = "*JENI HDZ — Fotografía Moto Velocidad*";
    $lineas[] = "_Nueva consulta desde el formulario de contacto_";
    $lineas[] = "";
    $lineas[] = $sep;
    $lineas[] = "*Cliente:* " . $nombre;
    $lineas[] = "*Email:* " . $email;
    $lineas[] = "*Teléfono:* " . $telefono;
    $lineas[] = "*Servicio de interés:* " . $asunto_texto;
    $lineas[] = "*Fecha de envío:* " . $fecha_envio;
    $lineas[] = $sep;
    $lineas[] = "";
    $lineas[] = "*Mensaje:*";
    $lineas[] = $mensaje_cliente;
    $lineas[] = "";
    $lineas[] = $sep;
    $lineas[] = "_Responder a este mensaje para contactar al cliente_";

    $mensaje = implode("\n", $lineas);

    registrar_auditoria('contacto_enviado', null);

    echo json_encode([
        'exito' => true,
        'mensaje' => 'Mensaje enviado correctamente. Te contactaremos por WhatsApp pronto.',
        'id_contacto' => $id_contacto,
        'whatsapp_url' => 'https://wa.me/' . str_replace('+', '', WHATSAPP_NUMBER) . '?text=' . urlencode($mensaje)
    ]);

} catch (Exception $e) {
    error_log("Error contacto: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['exito' => false, 'mensaje' => 'Error al enviar el mensaje']);
}

?>