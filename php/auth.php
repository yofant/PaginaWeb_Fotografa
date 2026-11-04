<?php
/**
 * Autenticación de la fotógrafa
 * ISO/IEC 27001: Control de acceso y gestión de identidades
 */
require_once 'config.php';

header('Content-Type: application/json');

$metodo = $_SERVER['REQUEST_METHOD'];

if ($metodo === 'POST') {
    $datos = json_decode(file_get_contents('php://input'), true);
    $email = limpiar_entrada($datos['email'] ?? '');
    $password = $datos['password'] ?? '';

    if (empty($email) || empty($password)) {
        http_response_code(400);
        echo json_encode(['exito' => false, 'mensaje' => 'Email y contraseña requeridos']);
        exit;
    }

    try {
        $pdo = db_connect();
        $stmt = $pdo->prepare("SELECT id, nombre, email, password_hash, rol, activo FROM usuarios WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch();

        if ($usuario && password_verify($password, $usuario['password_hash'])) {
            if (!$usuario['activo']) {
                http_response_code(403);
                echo json_encode(['exito' => false, 'mensaje' => 'Usuario inactivo']);
                exit;
            }
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nombre'] = $usuario['nombre'];
            $_SESSION['usuario_rol'] = $usuario['rol'];
            $_SESSION['usuario_email'] = $usuario['email'];
            registrar_auditoria('login', $usuario['id']);
            echo json_encode([
                'exito' => true,
                'usuario' => [
                    'id' => $usuario['id'],
                    'nombre' => $usuario['nombre'],
                    'rol' => $usuario['rol']
                ]
            ]);
        } else {
            http_response_code(401);
            echo json_encode(['exito' => false, 'mensaje' => 'Credenciales incorrectas']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['exito' => false, 'mensaje' => 'Error del servidor']);
    }
} elseif ($metodo === 'GET') {
    // Verificar sesión activa
    if (!empty($_SESSION['usuario_id'])) {
        echo json_encode([
            'exito' => true,
            'autenticado' => true,
            'usuario' => [
                'id' => $_SESSION['usuario_id'],
                'nombre' => $_SESSION['usuario_nombre'],
                'rol' => $_SESSION['usuario_rol']
            ]
        ]);
    } else {
        echo json_encode(['exito' => true, 'autenticado' => false]);
    }
} elseif ($metodo === 'DELETE') {
    // Logout
    session_destroy();
    echo json_encode(['exito' => true, 'mensaje' => 'Sesión cerrada']);
    exit;
} else {
    http_response_code(405);
    echo json_encode(['exito' => false, 'mensaje' => 'Método no permitido']);
}
?>
