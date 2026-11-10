<?php
/**
 * Prueba de integración de la subida de fotos.
 * Ejecutar desde la raíz del proyecto: php test_upload.php
 * Requiere MySQL con el esquema del proyecto y un usuario con id 1.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

ob_start();

$errores = [];
$servidor = null;
$pdo = null;
$archivoTemporal = null;
$sessionId = bin2hex(random_bytes(16));
$tituloPrueba = 'Prueba de subida ' . bin2hex(random_bytes(8));
$userAgentPrueba = 'JENI-HDZ-upload-test-' . bin2hex(random_bytes(8));

function comprobar(bool $condicion, string $mensaje): void
{
    global $errores;

    if ($condicion) {
        echo "[OK] $mensaje\n";
        return;
    }

    $errores[] = $mensaje;
    echo "[ERROR] $mensaje\n";
}

function solicitar(string $url, array $campos = [], ?string $cookie = null, string $userAgent = ''): array
{
    $curl = curl_init($url);
    $opciones = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => false,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_USERAGENT => $userAgent,
    ];

    if ($cookie !== null) {
        $opciones[CURLOPT_COOKIE] = $cookie;
    }

    if ($campos !== []) {
        $opciones[CURLOPT_POST] = true;
        $opciones[CURLOPT_POSTFIELDS] = $campos;
    }

    curl_setopt_array($curl, $opciones);
    $contenido = curl_exec($curl);
    $estado = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
    $error = curl_error($curl);
    curl_close($curl);

    if ($contenido === false) {
        throw new RuntimeException('Falló la petición HTTP: ' . $error);
    }

    return [$estado, $contenido];
}

function limpiar_prueba(?PDO $pdo, string $titulo, string $userAgent): void
{
    if ($pdo === null) {
        return;
    }

    $buscar = $pdo->prepare(
        'SELECT id, nombre_archivo, ruta_archivo FROM fotos WHERE titulo = ?'
    );
    $buscar->execute([$titulo]);

    foreach ($buscar->fetchAll() as $foto) {
        $archivos = array_unique([
            basename($foto['nombre_archivo']),
            basename($foto['ruta_archivo']),
        ]);

        foreach ($archivos as $nombre) {
            $ruta = UPLOAD_DIR . $nombre;
            if (is_file($ruta)) {
                unlink($ruta);
            }
        }

        $borrar = $pdo->prepare('DELETE FROM fotos WHERE id = ?');
        $borrar->execute([$foto['id']]);
    }

    $borrarAuditoria = $pdo->prepare(
        'DELETE FROM auditoria_acceso WHERE accion = ? AND user_agent = ?'
    );
    $borrarAuditoria->execute(['subida_foto', $userAgent]);
}

echo "=== Prueba de integración: subir foto ===\n";

foreach (['curl', 'pdo_mysql', 'fileinfo'] as $extension) {
    comprobar(extension_loaded($extension), "Extensión PHP $extension disponible");
}

if ($errores !== []) {
    exit(1);
}

session_id($sessionId);
require_once __DIR__ . '/php/config.php';
$_SESSION['usuario_id'] = 1;
$_SESSION['usuario_nombre'] = 'Usuario de prueba';
$_SESSION['usuario_rol'] = 'fotografa';
session_write_close();

$sessionPath = session_save_path();
if (strpos($sessionPath, ';') !== false) {
    $sessionPath = substr($sessionPath, strrpos($sessionPath, ';') + 1);
}
if ($sessionPath === '') {
    $sessionPath = sys_get_temp_dir();
}
$archivoSesion = rtrim($sessionPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'sess_' . $sessionId;

try {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    $usuario = $pdo->query('SELECT id FROM usuarios WHERE id = 1')->fetch();
    comprobar((bool) $usuario, 'Existe el usuario de prueba con id 1');
    if (!$usuario) {
        throw new RuntimeException('Importa database.sql o configura un usuario de prueba con id 1.');
    }

    $archivoTemporal = tempnam(sys_get_temp_dir(), 'jeni-upload-test-');
    $jpeg = base64_decode(
        '/9j/4AAQSkZJRgABAQEAYABgAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRofHh0aHBwgJC4nICIsIxwcKDcpLDAxNDQ0Hyc5PTgyPC4zNDL/2wBDAQkJCQwLDBgNDRgyIRwhMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjL/wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAn/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFQEBAQAAAAAAAAAAAAAAAAAAAAX/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwCdABmX/9k=',
        true
    );
    if ($jpeg === false || file_put_contents($archivoTemporal, $jpeg) === false) {
        throw new RuntimeException('No se pudo preparar la imagen JPEG de prueba.');
    }
    comprobar((new finfo(FILEINFO_MIME_TYPE))->file($archivoTemporal) === 'image/jpeg', 'La imagen temporal es un JPEG válido');

    $socket = stream_socket_server('tcp://127.0.0.1:0', $errno, $error);
    if ($socket === false) {
        throw new RuntimeException("No se pudo reservar un puerto local: $error ($errno)");
    }
    $direccion = stream_socket_get_name($socket, false);
    fclose($socket);

    $servidor = proc_open(
        [PHP_BINARY, '-S', $direccion, '-t', __DIR__],
        [
            0 => ['file', '/dev/null', 'r'],
            1 => ['file', '/dev/null', 'a'],
            2 => ['file', '/dev/null', 'a'],
        ],
        $pipes,
        __DIR__
    );
    if (!is_resource($servidor)) {
        throw new RuntimeException('No se pudo iniciar el servidor PHP de prueba.');
    }

    $es_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    $url = ($es_https ? 'https://' : 'http://') . $direccion . '/php/upload.php';
    $servidorListo = false;
    for ($intento = 0; $intento < 30; $intento++) {
        usleep(100000);
        $estadoServidor = proc_get_status($servidor);
        if (!$estadoServidor['running']) {
            throw new RuntimeException('El servidor PHP de prueba se cerró inesperadamente.');
        }
        $conexion = @fsockopen('127.0.0.1', (int) substr(strrchr($direccion, ':'), 1), $errno, $error, 0.2);
        if (is_resource($conexion)) {
            fclose($conexion);
            $servidorListo = true;
            break;
        }
    }
    comprobar($servidorListo, 'Servidor PHP local iniciado');
    if (!$servidorListo) {
        throw new RuntimeException('El servidor PHP local no respondió.');
    }

    [$estado, $respuesta] = solicitar($url, ['titulo' => 'sin autenticar'], null, $userAgentPrueba);
    $json = json_decode($respuesta, true);
    comprobar($estado === 401 && ($json['exito'] ?? true) === false, 'El endpoint rechaza una subida sin autenticar');

    [$estado, $respuesta] = solicitar(
        $url,
        [
            'foto' => new CURLFile($archivoTemporal, 'image/jpeg', 'foto-prueba.jpg'),
            'titulo' => $tituloPrueba,
            'descripcion' => 'Imagen temporal generada por test_upload.php',
            'color_predominante' => 'Morado',
            'numero_moto' => '99',
            'numero_piloto' => '1',
            'fecha_foto' => '',
            'evento_id' => '',
            'precio' => '0',
        ],
        'PHPSESSID=' . $sessionId,
        $userAgentPrueba
    );

    $json = json_decode($respuesta, true);
    comprobar($estado === 200 && ($json['exito'] ?? false) === true, 'El endpoint acepta la foto JPEG autenticada');

    if (isset($json['id'])) {
        $buscarFoto = $pdo->prepare(
            'SELECT nombre_archivo, ruta_archivo, titulo FROM fotos WHERE id = ?'
        );
        $buscarFoto->execute([(int) $json['id']]);
        $foto = $buscarFoto->fetch();
        comprobar(
            $foto !== false
                && $foto['titulo'] === $tituloPrueba
                && is_file(UPLOAD_DIR . basename($foto['nombre_archivo']))
                && is_file(UPLOAD_DIR . basename($foto['ruta_archivo'])),
            'La foto y sus metadatos quedaron guardados'
        );
    } else {
        comprobar(false, 'La respuesta incluye el id de la foto creada');
    }
} catch (Throwable $e) {
    $mensaje = $e instanceof PDOException && strpos($e->getMessage(), '[2002]') !== false
        ? 'No se pudo conectar con MySQL. Inicia el servicio y verifica php/config.php.'
        : $e->getMessage();
    $errores[] = $mensaje;
    echo "[ERROR] $mensaje\n";
} finally {
    if (is_resource($servidor)) {
        proc_terminate($servidor);
        proc_close($servidor);
    }

    try {
        limpiar_prueba($pdo, $tituloPrueba, $userAgentPrueba);
    } catch (Throwable $e) {
        $errores[] = 'No se pudo completar la limpieza: ' . $e->getMessage();
        echo "[ERROR] No se pudo completar la limpieza: " . $e->getMessage() . "\n";
    }

    if ($archivoTemporal !== null && is_file($archivoTemporal)) {
        unlink($archivoTemporal);
    }
    if (isset($archivoSesion) && is_file($archivoSesion)) {
        unlink($archivoSesion);
    }
}

if ($errores !== []) {
    echo "\nResultado: FALLÓ (" . count($errores) . " problema(s))\n";
    exit(1);
}

echo "\nResultado: todas las pruebas pasaron.\n";
ob_end_flush();
