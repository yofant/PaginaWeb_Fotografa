-- ============================================================================
-- ESQUEMA INICIAL PARA CLOUDFLARE D1 (SQLITE)
-- Proyecto: JENI HDZ - Fotografía Moto Velocidad
-- Motor: SQLite (D1)
-- Dialecto estricto: SQLite con PRAGMA foreign_keys = ON
-- ============================================================================

-- Activar integridad referencial en cada conexión (D1 respeta PRAGMA por sesión)
PRAGMA foreign_keys = ON;

-- ============================================================================
-- TABLA: usuarios
-- ============================================================================
CREATE TABLE IF NOT EXISTS usuarios (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nombre TEXT NOT NULL CHECK (length(nombre) > 0),
    email TEXT NOT NULL UNIQUE CHECK (length(email) > 0 AND email LIKE '%@%.%'),
    password_hash TEXT NOT NULL CHECK (length(password_hash) > 0),
    rol TEXT NOT NULL CHECK (rol IN ('fotografa','admin')) DEFAULT 'fotografa',
    telefono TEXT,
    whatsapp TEXT,
    fecha_creacion TEXT NOT NULL DEFAULT (datetime('now')),
    activo INTEGER NOT NULL DEFAULT 1 CHECK (activo IN (0,1))
);

CREATE INDEX IF NOT EXISTS idx_usuarios_email ON usuarios(email);
CREATE INDEX IF NOT EXISTS idx_usuarios_activo ON usuarios(activo);

-- ============================================================================
-- TABLA: eventos
-- ============================================================================
CREATE TABLE IF NOT EXISTS eventos (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nombre TEXT NOT NULL CHECK (length(nombre) > 0),
    fecha TEXT NOT NULL CHECK (length(fecha) = 10), -- YYYY-MM-DD (ISO-8601)
    circuito TEXT,
    pais TEXT,
    descripcion TEXT,
    imagen_portada TEXT,
    activo INTEGER NOT NULL DEFAULT 1 CHECK (activo IN (0,1)),
    fecha_creacion TEXT NOT NULL DEFAULT (datetime('now'))
);

CREATE INDEX IF NOT EXISTS idx_eventos_fecha ON eventos(fecha);
CREATE INDEX IF NOT EXISTS idx_eventos_activo ON eventos(activo);

-- ============================================================================
-- TABLA: subcarpetas (jerarquía 1:N con eventos)
-- ============================================================================
CREATE TABLE IF NOT EXISTS subcarpetas (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    evento_id INTEGER NOT NULL,
    nombre TEXT NOT NULL CHECK (length(nombre) > 0),
    descripcion TEXT,
    activo INTEGER NOT NULL DEFAULT 1 CHECK (activo IN (0,1)),
    fecha_creacion TEXT NOT NULL DEFAULT (datetime('now')),
    FOREIGN KEY (evento_id) REFERENCES eventos(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_subcarpetas_evento ON subcarpetas(evento_id);
CREATE INDEX IF NOT EXISTS idx_subcarpetas_activo ON subcarpetas(activo);

-- ============================================================================
-- TABLA: fotos
-- ============================================================================
CREATE TABLE IF NOT EXISTS fotos (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nombre_archivo TEXT NOT NULL CHECK (length(nombre_archivo) > 0),
    ruta_archivo TEXT NOT NULL CHECK (length(ruta_archivo) > 0),
    nombre_original TEXT,
    titulo TEXT,
    descripcion TEXT,
    color_predominante TEXT,
    numero_moto TEXT,
    numero_piloto TEXT,
    fecha_foto TEXT,
    evento_id INTEGER,
    subcarpeta_id INTEGER,
    precio REAL NOT NULL DEFAULT 0.0 CHECK (precio >= 0),
    disponible INTEGER NOT NULL DEFAULT 1 CHECK (disponible IN (0,1)),
    vistas INTEGER NOT NULL DEFAULT 0 CHECK (vistas >= 0),
    fecha_subida TEXT NOT NULL DEFAULT (datetime('now')),
    subido_por INTEGER,
    FOREIGN KEY (evento_id) REFERENCES eventos(id) ON DELETE SET NULL,
    FOREIGN KEY (subcarpeta_id) REFERENCES subcarpetas(id) ON DELETE SET NULL,
    FOREIGN KEY (subido_por) REFERENCES usuarios(id) ON DELETE SET NULL
);

CREATE INDEX IF NOT EXISTS idx_fotos_evento ON fotos(evento_id);
CREATE INDEX IF NOT EXISTS idx_fotos_subcarpeta ON fotos(subcarpeta_id);
CREATE INDEX IF NOT EXISTS idx_fotos_color ON fotos(color_predominante);
CREATE INDEX IF NOT EXISTS idx_fotos_moto ON fotos(numero_moto);
CREATE INDEX IF NOT EXISTS idx_fotos_piloto ON fotos(numero_piloto);
CREATE INDEX IF NOT EXISTS idx_fotos_disponible ON fotos(disponible);

-- ============================================================================
-- TABLA: paquetes
-- ============================================================================
CREATE TABLE IF NOT EXISTS paquetes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nombre TEXT NOT NULL CHECK (length(nombre) > 0),
    descripcion TEXT,
    precio REAL NOT NULL DEFAULT 0.0 CHECK (precio >= 0),
    fotos_incluidas INTEGER NOT NULL DEFAULT 0 CHECK (fotos_incluidas >= 0),
    entregas_dias INTEGER NOT NULL DEFAULT 7 CHECK (entregas_dias >= 0),
    activo INTEGER NOT NULL DEFAULT 1 CHECK (activo IN (0,1)),
    fecha_creacion TEXT NOT NULL DEFAULT (datetime('now'))
);

CREATE INDEX IF NOT EXISTS idx_paquetes_activo ON paquetes(activo);

-- ============================================================================
-- TABLA: compras
-- ============================================================================
CREATE TABLE IF NOT EXISTS compras (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nombre_cliente TEXT NOT NULL CHECK (length(nombre_cliente) > 0),
    email_cliente TEXT,
    telefono_cliente TEXT,
    whatsapp_cliente TEXT NOT NULL CHECK (length(whatsapp_cliente) > 0),
    fotos_seleccionadas TEXT, -- JSON array de IDs
    total REAL NOT NULL DEFAULT 0.0 CHECK (total >= 0),
    estado TEXT NOT NULL DEFAULT 'pendiente' CHECK (estado IN ('pendiente','completado','cancelado')),
    mensaje_whatsapp TEXT,
    fecha_compra TIMESTAMP NOT NULL DEFAULT (datetime('now'))
);

CREATE INDEX IF NOT EXISTS idx_compras_estado ON compras(estado);
CREATE INDEX IF NOT EXISTS idx_compras_fecha ON compras(fecha_compra);

-- ============================================================================
-- TABLA: contactos
-- ============================================================================
CREATE TABLE IF NOT EXISTS contactos (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nombre TEXT NOT NULL CHECK (length(nombre) > 0),
    email TEXT NOT NULL CHECK (length(email) > 0),
    telefono TEXT NOT NULL CHECK (length(telefono) > 0),
    asunto TEXT NOT NULL CHECK (length(asunto) > 0),
    mensaje TEXT NOT NULL CHECK (length(mensaje) > 0),
    terminos_aceptados INTEGER NOT NULL DEFAULT 0 CHECK (terminos_aceptados IN (0,1)),
    fecha_contacto TIMESTAMP NOT NULL DEFAULT (datetime('now'))
);

CREATE INDEX IF NOT EXISTS idx_contactos_fecha ON contactos(fecha_contacto);

-- ============================================================================
-- TABLA: auditoria_acceso
-- ============================================================================
CREATE TABLE IF NOT EXISTS auditoria_acceso (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    usuario_id INTEGER,
    accion TEXT NOT NULL CHECK (length(accion) > 0),
    ip TEXT,
    user_agent TEXT,
    fecha TIMESTAMP NOT NULL DEFAULT (datetime('now')),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
);

CREATE INDEX IF NOT EXISTS idx_auditoria_fecha ON auditoria_acceso(fecha);
CREATE INDEX IF NOT EXISTS idx_auditoria_usuario ON auditoria_acceso(usuario_id);

-- ============================================================================
-- FIN DEL ESQUEMA
-- ============================================================================
