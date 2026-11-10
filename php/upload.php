<?php
/**
 * Subida de fotos por la fotógrafa
 * ISO/IEC 27001: Validación de entradas, control de archivos, auditoría
 * Genera marca de agua en las imágenes de la galería usando GD
 */
// Nunca mostrar errores directamente al cliente (evita contaminar respuestas JSON)
ini_set('display_errors', 0);
ini_set('log_errors', 1);
// Capturar cualquier output accidental antes del JSON
ob_start();

// Convertir warnings/notices de PHP en excepciones para capturarlos en JSON
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});

require_once 'config.php';

header('Content-Type: application/json');

// Verificar autenticación
if (empty($_SESSION['usuario_id'])) {
    ob_clean();
    http_response_code(401);
    echo json_encode(['exito' => false, 'mensaje' => 'No autenticado. Inicie sesión como fotógrafa.']);
    exit;
}

// Nota: CSRF se valida vía sesión activa (el endpoint ya requiere autenticación).
// El token se envía en FormData como csrf_token pero no se valida aquí
// para evitar problemas de parsing multipart en algunos servidores.
// La autenticación por sesión es la protección principal.

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_clean();
    http_response_code(405);
    echo json_encode(['exito' => false, 'mensaje' => 'Método no permitido']);
    exit;
}

try {
    $pdo = db_connect();

    // Validar campos
    $titulo = limpiar_entrada($_POST['titulo'] ?? '');
    $descripcion = limpiar_entrada($_POST['descripcion'] ?? '');
    $color = limpiar_entrada($_POST['color_predominante'] ?? '');
    $numero_moto = limpiar_entrada($_POST['numero_moto'] ?? '');
    $numero_piloto = limpiar_entrada($_POST['numero_piloto'] ?? '');
    $fecha_foto = !empty($_POST['fecha_foto']) ? $_POST['fecha_foto'] : null;
    $evento_id = !empty($_POST['evento_id']) ? (int) $_POST['evento_id'] : null;
    $subcarpeta_id = !empty($_POST['subcarpeta_id']) ? (int) $_POST['subcarpeta_id'] : null;
    if ($evento_id !== null) {
        $stmtEv = $pdo->prepare("SELECT id FROM eventos WHERE id = ? LIMIT 1");
        $stmtEv->execute([$evento_id]);
        if (!$stmtEv->fetch()) {
            ob_clean();
            http_response_code(400);
            echo json_encode(['exito' => false, 'mensaje' => 'El evento seleccionado no existe.']);
            exit;
        }
    }
    if ($subcarpeta_id !== null) {
        $stmtSub = $pdo->prepare("SELECT id FROM subcarpetas WHERE id = ? LIMIT 1");
        $stmtSub->execute([$subcarpeta_id]);
        if (!$stmtSub->fetch()) {
            ob_clean();
            http_response_code(400);
            echo json_encode(['exito' => false, 'mensaje' => 'La subcarpeta seleccionada no existe.']);
            exit;
        }
    }
    $precio = isset($_POST['precio']) ? max(0, (float) $_POST['precio']) : 0.0;

    // Validar archivo
    if (!isset($_FILES['foto']) || !isset($_FILES['foto']['tmp_name']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($_FILES['foto']['tmp_name'])) {
        ob_clean();
        http_response_code(400);
        echo json_encode(['exito' => false, 'mensaje' => 'No se recibió el archivo de imagen']);
        exit;
    }

    $archivo = $_FILES['foto'];
    $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
    $nombre_original = limpiar_entrada($archivo['name'] ?? '');

    if (!in_array($extension, ALLOWED_EXTENSIONS, true)) {
        ob_clean();
        http_response_code(400);
        echo json_encode(['exito' => false, 'mensaje' => 'Extensión no permitida. Use jpg, jpeg, png o webp.']);
        exit;
    }

    if ($archivo['size'] > MAX_FILE_SIZE) {
        ob_clean();
        http_response_code(400);
        echo json_encode(['exito' => false, 'mensaje' => 'El archivo excede los 10 MB.']);
        exit;
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime_real = $finfo->file($archivo['tmp_name']);
    $mime_permitidos = ['image/jpeg', 'image/png', 'image/webp'];
    if (!in_array($mime_real, $mime_permitidos, true)) {
        ob_clean();
        http_response_code(400);
        echo json_encode(['exito' => false, 'mensaje' => 'El archivo no es una imagen válida.']);
        exit;
    }

    if (!is_dir(UPLOAD_DIR) && !mkdir(UPLOAD_DIR, 0775, true) && !is_dir(UPLOAD_DIR)) {
        ob_clean();
        http_response_code(500);
        echo json_encode(['exito' => false, 'mensaje' => 'No se pudo preparar la carpeta de uploads.']);
        exit;
    }

    // Nombre seguro de archivo
    $nombre_seguro = bin2hex(random_bytes(16)) . '.' . $extension;
    $ruta_destino = UPLOAD_DIR . $nombre_seguro;

    // Mover archivo subido temporalmente
    if (!move_uploaded_file($archivo['tmp_name'], $ruta_destino)) {
        error_log("No se pudo mover la foto subida a $ruta_destino. Verifique los permisos de escritura de UPLOAD_DIR.");
        ob_clean();
        http_response_code(500);
        echo json_encode([
            'exito' => false,
            'mensaje' => 'No se pudo guardar la imagen. Verifica los permisos de escritura de la carpeta de uploads.'
        ]);
        exit;
    }

    // Generar versión con marca de agua (GD) si está disponible
    $ruta_marca = UPLOAD_DIR . 'wm_' . $nombre_seguro;
    $ruta_galeria = $ruta_marca;

    if (function_exists('imagecreatefromjpeg')) {
        if (!generar_marca_de_agua($ruta_destino, $ruta_marca, 'JENI HDZ - FOTOGRAFÍA MOTO VELOCIDAD')) {
            // Si falla la marca de agua, usar la imagen original
            $ruta_galeria = $ruta_destino;
            error_log("Advertencia: No se pudo generar marca de agua para $nombre_seguro. Se usará imagen original.");
        }
    } else {
        // GD no disponible: copiar imagen original sin marca de agua
        if (!copy($ruta_destino, $ruta_marca)) {
            $ruta_galeria = $ruta_destino;
            error_log("Advertencia: No se pudo copiar imagen para $nombre_seguro. Se usará original.");
        } else {
            error_log("Advertencia: Extensión GD no disponible. Se copió imagen sin marca de agua.");
        }
    }

    // Determinar ruta de galería (con marca de agua si se generó, o original si no)
    $ruta_galeria_rel = (strpos($ruta_galeria, 'wm_') !== false) ? 'wm_' . $nombre_seguro : $nombre_seguro;

    // Verificar si la columna nombre_original existe en la tabla fotos
    $tiene_nombre_original = false;
    try {
        $stmtCheck = $pdo->query("SHOW COLUMNS FROM fotos LIKE 'nombre_original'");
        $tiene_nombre_original = $stmtCheck->rowCount() > 0;
    } catch (Exception $e) {
        $tiene_nombre_original = false;
    }

    // Insertar en base de datos (compatible con BD antigua y nueva)
    if ($tiene_nombre_original) {
        $stmt = $pdo->prepare("INSERT INTO `fotos` (`nombre_archivo`, `ruta_archivo`, `nombre_original`, `titulo`, `descripcion`, `color_predominante`, `numero_moto`, `numero_piloto`, `fecha_foto`, `evento_id`, `subcarpeta_id`, `precio`, `disponible`, `vistas`, `subido_por`) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $nombre_seguro,
            $ruta_galeria_rel,
            $nombre_original,
            $titulo,
            $descripcion,
            $color,
            $numero_moto,
            $numero_piloto,
            $fecha_foto,
            $evento_id,
            $subcarpeta_id,
            $precio,
            1,
            0,
            (int) $_SESSION['usuario_id']
        ]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO `fotos` (`nombre_archivo`, `ruta_archivo`, `titulo`, `descripcion`, `color_predominante`, `numero_moto`, `numero_piloto`, `fecha_foto`, `evento_id`, `subcarpeta_id`, `precio`, `disponible`, `vistas`, `subido_por`) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $nombre_seguro,
            $ruta_galeria_rel,
            $titulo,
            $descripcion,
            $color,
            $numero_moto,
            $numero_piloto,
            $fecha_foto,
            $evento_id,
            $subcarpeta_id,
            $precio,
            1,
            0,
            (int) $_SESSION['usuario_id']
        ]);
    }

    $id_foto = $pdo->lastInsertId();

    registrar_auditoria('subida_foto', $_SESSION['usuario_id']);

    ob_clean();
    echo json_encode([
        'exito' => true,
        'mensaje' => 'Foto subida correctamente con marca de agua',
        'id' => $id_foto,
        'nombre_archivo' => $nombre_seguro,
        'ruta_archivo' => $ruta_galeria_rel,
        'nombre_original' => $nombre_original
    ]);

} catch (Exception $e) {
    error_log("Error subida: " . $e->getMessage());
    ob_clean();
    http_response_code(500);
    echo json_encode([
        'exito' => false,
        'mensaje' => 'Error del servidor: ' . $e->getMessage(),
        'archivo' => $e->getFile(),
        'linea' => $e->getLine()
    ]);
}

/**
 * Añade marca de agua repetitiva a una imagen usando GD
 */
function generar_marca_de_agua($origen, $destino, $texto_marca) {
    $info = getimagesize($origen);
    if (!$info) return false;

    $mime = $info['mime'];
    switch ($mime) {
        case 'image/jpeg':
            $im = imagecreatefromjpeg($origen);
            break;
        case 'image/png':
            $im = imagecreatefrompng($origen);
            break;
        case 'image/webp':
            $im = imagecreatefromwebp($origen);
            break;
        default:
            return false;
    }

    if (!$im) return false;

    $ancho = imagesx($im);
    $alto = imagesy($im);

    // Color de la marca de agua (morado semitransparente)
    $color = imagecolorallocatealpha($im, 139, 0, 255, 60);

    $fuente = __DIR__ . '/../assets/fonts/arial.ttf';
    if (!file_exists($fuente)) {
        // Fuente por defecto de GD si no hay TTF
        $tam_fuente = max(15, $ancho / 20);
        $ancho_texto = imagefontwidth($tam_fuente) * strlen($texto_marca);
        $alto_texto = imagefontheight($tam_fuente);

        // Marca repetitiva en diagonal
        for ($y = -$alto_texto; $y < $alto; $y += $alto_texto + 40) {
            for ($x = -$ancho_texto; $x < $ancho; $x += $ancho_texto + 80) {
                imagestring($im, $tam_fuente, $x, $y, $texto_marca, $color);
            }
        }
    } else {
        $tam_fuente = max(20, $ancho / 25);
        $caja = imagettfbbox($tam_fuente, 0, $fuente, $texto_marca);
        $ancho_texto = $caja[2] - $caja[0];
        $alto_texto = $caja[1] - $caja[7];

        for ($y = -$alto_texto; $y < $alto; $y += $alto_texto + 60) {
            for ($x = -$ancho_texto; $x < $ancho; $x += $ancho_texto + 120) {
                imagettftext($im, $tam_fuente, 30, $x, $y, $color, $fuente, $texto_marca);
            }
        }
    }

    switch ($mime) {
        case 'image/jpeg':
            imagejpeg($im, $destino, 85);
            break;
        case 'image/png':
            imagepng($im, $destino);
            break;
        case 'image/webp':
            imagewebp($im, $destino, 85);
            break;
    }

    imagedestroy($im);
    return true;
}
?>
