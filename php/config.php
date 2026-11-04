<?php
/**
 * Configuración de conexión a base de datos
 * Proyecto: JENI-HDZ - Fotografía Moto Velocidad
 * Estándar: ISO/IEC 27001 - Gestión de la Seguridad de la Información
 */

// Credenciales de base de datos (en producción usar variables de entorno)
define('DB_HOST', 'localhost');
define('DB_NAME', 'jeni_hdz_gallery');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Configuración de seguridad
define('APP_NAME', 'JENI HDZ - Fotografía Moto Velocidad');
define('APP_URL', 'http://localhost/JENI-HDZ');
define('WHATSAPP_NUMBER', '+525512345678'); // Número de WhatsApp de la fotógrafa
define('SESSION_TIMEOUT', 1800); // 30 minutos de inactividad

// Extensiones de archivo permitidas para subida
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'webp']);
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10 MB

// Carpeta de uploads
define('UPLOAD_DIR', __DIR__ . '/../assets/uploads/');

/**
 * Conexión a la base de datos con PDO (previene inyección SQL - ISO 27001)
 */
function db_connect() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        return new PDO($dsn, DB_USER, DB_PASS, $options);
    } catch (PDOException $e) {
        error_log("Error de conexión DB: " . $e->getMessage());
        http_response_code(503);
        die("Servicio temporalmentente no disponible. Inténtelo más tarde.");
    }
}

/**
 * Registra auditoría de acceso (ISO/IEC 27001 - Control de acceso y registro)
 */
function registrar_auditoria($accion, $usuario_id = null) {
    try {
        $pdo = db_connect();
        $stmt = $pdo->prepare("INSERT INTO auditoria_acceso (usuario_id, accion, ip, user_agent) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $usuario_id,
            $accion,
            $_SERVER['REMOTE_ADDR'] ?? 'desconocida',
            $_SERVER['HTTP_USER_AGENT'] ?? 'desconocido'
        ]);
    } catch (Exception $e) {
        error_log("Error auditoría: " . $e->getMessage());
    }
}

/**
 * Sanitiza entrada de usuario
 */
function limpiar_entrada($datos) {
    $datos = trim($datos);
    $datos = stripslashes($datos);
    $datos = htmlspecialchars($datos, ENT_QUOTES, 'UTF-8');
    return $datos;
}

/**
 * Genera token CSRF simple
 */
function generar_token_csrf() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validar_token_csrf($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Iniciar sesión si no está activa
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 0); // Poner 1 en producción con HTTPS
    ini_set('session.use_only_cookies', 1);
    session_start();
}
?>
