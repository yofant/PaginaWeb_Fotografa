-- ============================================================================
-- BASE DE DATOS: Galería Fotógrafa Moto Velocidad
-- Proyecto: JENI-HDZ | Fotografía de Motovelocidad
-- Tecnologías: HTML, CSS, JavaScript, PHP, MySQL/MariaDB
-- ============================================================================

CREATE DATABASE IF NOT EXISTS jeni_hdz_gallery
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE jeni_hdz_gallery;

-- Tabla de usuarios (fotógrafa y administradores)
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    rol ENUM('fotografa','admin') DEFAULT 'fotografa',
    telefono VARCHAR(20),
    whatsapp VARCHAR(20),
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    activo TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de eventos de moto velocidad
CREATE TABLE eventos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    fecha DATE NOT NULL,
    circuito VARCHAR(150),
    pais VARCHAR(100),
    descripcion TEXT,
    activo TINYINT(1) DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de fotos subidas por la fotógrafa
CREATE TABLE fotos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_archivo VARCHAR(255) NOT NULL,
    ruta_archivo VARCHAR(500) NOT NULL,
    titulo VARCHAR(200),
    descripcion TEXT,
    color_predominante VARCHAR(50),
    numero_moto VARCHAR(20),
    numero_piloto VARCHAR(20),
    fecha_foto DATE,
    evento_id INT,
    precio DECIMAL(10,2) DEFAULT 0.00,
    disponible TINYINT(1) DEFAULT 1,
    vistas INT DEFAULT 0,
    fecha_subida TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    subido_por INT,
    FOREIGN KEY (evento_id) REFERENCES eventos(id) ON DELETE SET NULL,
    FOREIGN KEY (subido_por) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_color (color_predominante),
    INDEX idx_moto (numero_moto),
    INDEX idx_piloto (numero_piloto),
    INDEX idx_fecha (fecha_foto),
    INDEX idx_evento (evento_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de paquetes de la fotógrafa
CREATE TABLE paquetes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    descripcion TEXT,
    precio DECIMAL(10,2) NOT NULL,
    fotos_incluidas INT DEFAULT 0,
    entregas_dias INT DEFAULT 7,
    activo TINYINT(1) DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de compras / carrito (registros de intención de compra vía WhatsApp)
CREATE TABLE compras (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_cliente VARCHAR(150),
    email_cliente VARCHAR(150),
    telefono_cliente VARCHAR(50),
    whatsapp_cliente VARCHAR(50),
    fotos_seleccionadas JSON,
    total DECIMAL(10,2) DEFAULT 0.00,
    estado ENUM('pendiente','completado','cancelado') DEFAULT 'pendiente',
    mensaje_whatsapp TEXT,
    fecha_compra TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- DATOS DE EJEMPLO (SEED)
-- ============================================================================

INSERT INTO usuarios (nombre, email, password_hash, rol, telefono, whatsapp) VALUES
('JENI HDZ', 'jeni@jenihdz.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'fotografa', '+52 55 1234 5678', '+525512345678');

INSERT INTO eventos (nombre, fecha, circuito, pais, descripcion) VALUES
('Gran Premio de México 2026', '2026-10-25', 'Autódromo Hermanos Rodríguez', 'México', 'Evento de MotoGP en Ciudad de México'),
('Superbike España 2026', '2026-09-12', 'Circuito de Jerez', 'España', 'Campeonato Mundial de Superbike'),
('MotoGP Argentina 2026', '2026-08-15', 'Autódromo Termas de Río Hondo', 'Argentina', 'Fecha del calendario MotoGP');

INSERT INTO paquetes (nombre, descripcion, precio, fotos_incluidas, entregas_dias) VALUES
('Básico', '5 fotos editadas en alta resolución con marca de agua', 250.00, 5, 3),
('Estándar', '15 fotos editadas + 1 foto en canvas pequeña', 600.00, 15, 5),
('Premium', '30 fotos editadas + 2 canvas + álbum digital', 1200.00, 30, 7),
('Elite Velocidad', 'Todas las fotos del evento + impresión profesional', 2500.00, 999, 10);

-- ============================================================================
-- ÍNDICES DE SEGURIDAD Y AUDITORÍA (ISO/IEC 27001)
-- ============================================================================
CREATE TABLE auditoria_acceso (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    accion VARCHAR(100),
    ip VARCHAR(45),
    user_agent TEXT,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_fecha (fecha)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
