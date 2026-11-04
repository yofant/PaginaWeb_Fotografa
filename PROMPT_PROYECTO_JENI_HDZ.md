# PROMPT PROYECTO JENI HDZ - Fotografía Moto Velocidad

> Este documento contiene el prompt completo del proyecto para ser reutilizado en futuras ocasiones. Incluye todo lo solicitado, contexto, requerimientos, estructura, lenguajes, páginas, colores, diseños y tecnologías utilizadas.

---

## 1. LO SOLICITADO (Resumen del cliente)

El cliente solicitó una página web para una **fotógrafa de moto velocidad** con las siguientes características principales:

- **Páginas:** Bienvenida, Nosotros, Contacto y Paquetes.
- **Colores:** Negro y Morado.
- **Enfoque:** Moto velocidad.
- **Galería:** Apartado donde los usuarios puedan ver las fotos subidas por la fotógrafa.
- **Filtros de galería:** Por color, número de moto, número de piloto, fecha y evento.
- **Interfaz de subida:** La fotógrafa debe tener una interfaz para subir las fotos.
- **Compra:** Los usuarios seleccionan fotos y al terminar se redirigen a **WhatsApp** para finalizar la compra.
- **Responsividad:** La página debe ser responsive (móvil, tablet, escritorio).
- **Estándares de seguridad:**
  - No permitir descargas de fotos.
  - No permitir capturas de pantalla (anti-robo de fotos).
  - Las fotos de la galería deben aparecer con **marca de agua**.
- **Cumplimiento ISO:**
  - ISO/IEC/IEEE 23026:2015 (usabilidad sitios web informativos).
  - ISO/IEC 25000 (SQuaRE - calidad de software).
  - ISO 9001 (Gestión de la Calidad).
  - ISO/IEC 27001 (Seguridad de la Información - SGSI).
  - ISO/IEC 12207 (Procesos del ciclo de vida del software).
- **Fases SDLC:** Planificación, Análisis, Diseño, Implementación, Pruebas, Despliegue, Mantenimiento.
- **Términos y Condiciones:** Debe incluirse.
- **Tecnologías:** HTML, CSS, JavaScript, PHP para conexión a base de datos.
- **Documentación:** Flujos, procesos, etc.
- **Organización:** Debidamente organizado en carpetas.

### Modificaciones adicionales solicitadas por el cliente (segunda fase):

1. **Validación de colores:** Unificar colores en todas las páginas (que no haya variaciones).
2. **Validación de texto:** Todos los textos deben ser de color blanco para máxima legibilidad.
3. **Diseño novedoso y llamativo:** Mejorar el diseño visual para que sea atractivo y moderno.
4. **Nueva página de Eventos:** Cada evento registrado con imagen, fecha y lugar.
5. **Módulo de Registro Facial en Galería:** Opción de subir una foto o usar cámara para identificación automática de fotos del piloto.

---

## 2. CONTEXTO

- **Nombre del proyecto:** JENI HDZ - Fotografía Moto Velocidad.
- **Cliente:** Fotógrafa especializada en moto velocidad.
- **Fecha de desarrollo:** Octubre 2026.
- **Entorno de desarrollo:** Local (LAMP stack recomendado: Linux, Apache/Nginx, MySQL/MariaDB, PHP).
- **Objetivo:** Sitio web profesional, seguro, responsivo y visualmente atractivo para exhibir y vender fotografías de eventos de moto velocidad.
- **Público objetivo:** Pilotos, equipos, aficionados al motociclismo y clientes interesados en paquetes fotográficos.

---

## 3. REQUERIMIENTOS FUNCIONALES

| ID | Requerimiento | Descripción |
|----|---------------|-------------|
| RF-01 | Página de Bienvenida | Hero con llamada a la acción, presentación de la fotógrafa y enlaces a galería y paquetes. |
| RF-02 | Página Nosotros | Historia de la fotógrafa, compromiso y valores. |
| RF-03 | Página Paquetes | Catálogo de paquetes fotográficos (Básico, Estándar, Premium, Elite Velocidad) con precios y entregas. |
| RF-04 | Página Contacto | Formulario de contacto con validación de Términos y Condiciones y declaración de piloto. |
| RF-05 | Página Galería | Visualización de fotos con filtros por color, número de moto, número de piloto, fecha y evento. |
| RF-06 | Filtros de galería | Chips rápidos y selects desplegables para filtrar: color, moto, piloto, fecha, evento. |
| RF-07 | Carrito de compras | Selección múltiple de fotos, contador, panel lateral y redirección a WhatsApp para finalizar compra. |
| RF-08 | Panel de subida (fotógrafa) | Interfaz privada con login, drag & drop, vista previa, metadatos (título, color, moto, piloto, fecha, evento, precio) y subida con marca de agua automática. |
| RF-09 | Login de fotógrafa | Autenticación segura con email y contraseña (hash bcrypt). |
| RF-010 | Página de Eventos | Listado de eventos con imagen, fecha, lugar y descripción. |
| RF-011 | Registro Facial | Módulo en galería para capturar selfie o subir foto y simular identificación automática del piloto. |
| RF-012 | Términos y Condiciones | Página dedicada con declaración de piloto, propiedad intelectual y protección de imágenes. |
| RF-013 | Modal de aceptación de piloto | Al ingresar por primera vez, el usuario debe aceptar que es el piloto que comprará las fotos (guardado en localStorage). |
| RF-014 | WhatsApp checkout | Al finalizar compra, se abre WhatsApp con mensaje prellenado con datos del cliente y fotos seleccionadas. |

---

## 4. REQUERIMIENTOS NO FUNCIONALES

| ID | Requerimiento | Descripción |
|----|---------------|-------------|
| RNF-01 | Diseño visual | Colores Negro (`#050505`) y Morado (`#8b00ff`) unificados en todas las páginas. |
| RNF-02 | Texto blanco | Todo el texto visible debe ser blanco o tonos claros (`#ffffff`, `#e2e8f0`) para legibilidad sobre fondo oscuro. |
| RNF-03 | Responsividad | Diseño adaptable a móvil, tablet y escritorio (breakpoints: 480px, 768px, 1024px). |
| RNF-04 | Anti-descargas | Desactivar clic derecho, arrastre de imágenes y menú contextual. |
| RNF-05 | Anti-capturas | Bloquear PrintScreen, F12, Ctrl+Shift+I/J/C, Ctrl+C/U/S/P con aviso visual de bloqueo. |
| RNF-06 | Marca de agua | Todas las fotos de la galería deben llevar marca de agua repetitiva "JENI HDZ - FOTOGRAFÍA MOTO VELOCIDAD" generada en servidor con PHP GD. |
| RNF-07 | Seguridad de archivos | Solo extensiones .jpg, .jpeg, .png, .webp; tamaño máximo 10 MB; validación con MIME type. |
| RNF-08 | Sesiones seguras | Cookies HttpOnly, Secure (en producción con HTTPS), uso_only_cookies, regeneración de ID de sesión. |
| RNF-09 | Prevención SQL Injection | Sentencias preparadas con PDO en todas las consultas a base de datos. |
| RNF-010 | Prevención XSS | Sanitización de entradas con htmlspecialchars, trim, stripslashes. |
| RNF-011 | Auditoría | Tabla `auditoria_acceso` registrando usuario, acción, IP, user-agent y fecha. |
| RNF-012 | Cumplimiento ISO | Alineación con ISO/IEC/IEEE 23026:2015, ISO/IEC 25000, ISO 9001, ISO/IEC 27001, ISO/IEC 12207. |
| RNF-013 | Fases SDLC | Documentación de las 7 fases del ciclo de vida del software. |
| RNF-014 | Accesibilidad | Skip link, semántica HTML, focus visible, contraste WCAG AA, prefers-reduced-motion. |
| RNF-015 | Rendimiento | Carga asíncrona de galería (AJAX/Fetch), imágenes con lazy-load, CSS y JS optimizados. |

---

## 5. ESTRUCTURA DE CARPETAS

```
JENI-HDZ/
├── index.php              # Página de bienvenida (Hero)
├── nosotros.php           # Nosotros / Historia
├── paquetes.php           # Paquetes fotográficos
├── galeria.php            # Galería pública con filtros + registro facial
├── contacto.php           # Formulario de contacto
├── login.php              # Acceso de la fotógrafa
├── subir-fotos.php        # Panel privado de subida de fotos
├── eventos.php            # Página de eventos (imagen, fecha, lugar)
├── database.sql           # Esquema y datos de ejemplo (MySQL/MariaDB)
├── README.md              # Guía de instalación y uso
├── PROMPT_PROYECTO_JENI_HDZ.md  # Este archivo (prompt completo)
│
├── css/
│   ├── style.css          # Estilos principales (colores unificados, textos blancos, diseño novedoso)
│   └── responsive.css     # Ajustes responsive adicionales
│
├── js/
│   ├── security.js        # Protección de imágenes (anti clic derecho, capturas, arrastre)
│   ├── cart.js            # Carrito de compras y redirección a WhatsApp
│   ├── gallery.js         # Galería con filtros dinámicos (AJAX)
│   ├── main.js            # Scripts principales, menú móvil, autenticación
│   ├── upload.js          # Interfaz de subida de fotos (drag & drop, vista previa)
│   └── facial-recognition.js  # Módulo de registro facial (cámara/subida)
│
├── php/
│   ├── config.php         # Conexión BD, constantes de seguridad, configuración
│   ├── auth.php           # Autenticación de fotógrafa (login/logout/sesión)
│   ├── upload.php         # Subida de fotos con marca de agua (GD)
│   ├── get_photos.php     # Obtener fotos con filtros (JSON)
│   ├── get_events.php     # Listado de eventos (JSON)
│   └── save_purchase.php  # Guardar intención de compra y generar URL WhatsApp
│
├── includes/
│   ├── header.php         # Header compartido (navbar, carrito, modal piloto)
│   └── footer.php         # Footer compartido (enlaces, contacto, scripts)
│
├── assets/
│   ├── images/            # Imágenes estáticas (moto, fotógrafa, eventos)
│   └── uploads/           # Fotos subidas por la fotógrafa (requiere permisos de escritura)
│
└── docs/
    ├── sdlc.md            # 7 fases del SDLC
    ├── iso-compliance.md  # Cumplimiento de normas ISO
    ├── flows.md           # Flujos y procesos del sistema
    ├── terminos-condiciones.html  # Términos y Condiciones
    └── validacion-seguridad.md  # Checklist de pruebas de seguridad
```

---

## 6. LENGUAJES Y TECNOLOGÍAS UTILIZADAS

| Tecnología | Uso en el proyecto |
|------------|-------------------|
| **HTML5** | Estructura semántica de todas las páginas (`<header>`, `<nav>`, `<main>`, `<section>`, `<footer>`, `<form>`, etc.) |
| **CSS3** | Diseño visual, variables CSS, Flexbox, Grid, animaciones, responsive design, efectos de brillo morado |
| **JavaScript (ES6+)** | Interactividad: menú móvil, carrito, filtros de galería, subida de fotos, registro facial, seguridad (anti-capturas) |
| **PHP 8+** | Backend: autenticación, subida de archivos, conexión a BD con PDO, generación de marca de agua con GD, endpoints JSON |
| **MySQL / MariaDB** | Base de datos relacional para usuarios, eventos, fotos, paquetes, compras y auditoría |
| **Apache / Nginx** | Servidor web recomendado (LAMP stack) |

---

## 7. PÁGINAS DEL SITIO

| Página | Descripción | Archivo |
|--------|-------------|---------|
| **Bienvenida** | Hero con presentación, CTA a galería y paquetes | `index.php` |
| **Nosotros** | Historia de la fotógrafa y compromiso | `nosotros.php` |
| **Paquetes** | 4 paquetes fotográficos con precios y entregas | `paquetes.php` |
| **Galería** | Fotos con filtros (color, moto, piloto, fecha, evento) + registro facial | `galeria.php` |
| **Eventos** | Listado de eventos con imagen, fecha y lugar | `eventos.php` |
| **Contacto** | Formulario con validación de Términos y declaración de piloto | `contacto.php` |
| **Login** | Acceso privado de la fotógrafa | `login.php` |
| **Subir Fotos** | Panel privado para subir imágenes con metadatos y marca de agua | `subir-fotos.php` |
| **Términos y Condiciones** | Declración de piloto, propiedad intelectual, protección de imágenes | `docs/terminos-condiciones.html` |

---

## 8. COLORES Y DISEÑO

### Paleta unificada (Negro y Morado)

| Variable | Valor | Uso |
|----------|-------|-----|
| `--color-bg` | `#050505` | Fondo principal |
| `--color-surface` | `#0a0a0a` | Superficies de tarjetas y paneles |
| `--color-surface-hover` | `#141414` | Hover de superficies |
| `--color-primary` | `#8b00ff` | Morado principal (botones, acentos) |
| `--color-primary-hover` | `#a700ff` | Morado hover |
| `--color-primary-active` | `#6a00cc` | Morado activo |
| `--color-primary-glow` | `rgba(139, 0, 255, 0.5)` | Brillo/sombra morada |
| `--color-accent` | `#00d4ff` | Azul cian (acento secundario) |
| `--color-text` | `#ffffff` | Texto principal (BLANCO) |
| `--color-text-secondary` | `#e2e8f0` | Texto secundario |
| `--color-text-muted` | `#a5a5a5` | Texto atenuado |
| `--color-success` | `#10b981` | Éxito (WhatsApp, confirmaciones) |
| `--color-error` | `#ef4444` | Error |
| `--color-warning` | `#f59e0b` | Advertencia |

### Elementos de diseño novedoso
- Gradientes morados en botones y bordes de tarjetas.
- Sombras con brillo morado (`box-shadow` con rgba morado).
- Header vidrioso con `backdrop-filter: blur()`.
- Animaciones de entrada en hero y modales.
- Efecto flotante en iconos.
- Tarjetas con borde superior animado que se expande en hover.
- Botones con efecto de brillo deslizante en hover.

---

## 9. BASE DE DATOS (`database.sql`)

### Tablas principales

| Tabla | Campos principales | Descripción |
|-------|-------------------|-------------|
| `usuarios` | id, nombre, email, password_hash, rol, telefono, whatsapp, activo | Fotógrafa y administradores |
| `eventos` | id, nombre, fecha, circuito, pais, descripcion, activo | Eventos de moto velocidad |
| `fotos` | id, nombre_archivo, ruta_archivo, titulo, descripcion, color_predominante, numero_moto, numero_piloto, fecha_foto, evento_id, precio, disponible, vistas, subido_por | Fotos subidas con metadatos |
| `paquetes` | id, nombre, descripcion, precio, fotos_incluidas, entregas_dias, activo | Paquetes fotográficos |
| `compras` | id, nombre_cliente, email_cliente, telefono_cliente, whatsapp_cliente, fotos_seleccionadas, total, estado, mensaje_whatsapp, fecha_compra | Intenciones de compra |
| `auditoria_acceso` | id, usuario_id, accion, ip, user_agent, fecha | Registro de accesos (ISO 27001) |

### Credenciales de prueba
- **Email:** `jeni@jenihdz.com`
- **Contraseña:** `12345678` (hash bcrypt)

---

## 10. SEGURIDAD IMPLEMENTADA

1. **Sentencias preparadas con PDO** - Previene inyección SQL.
2. **Sanitización de entradas** - `htmlspecialchars`, `trim`, `stripslashes` (anti XSS).
3. **Cookies de sesión seguras** - `HttpOnly`, `Secure` (producción), `use_only_cookies`.
4. **Validación de archivos subidos** - Solo `.jpg`, `.jpeg`, `.png`, `.webp`; máximo 10 MB.
5. **Marca de agua automática** - Librería GD de PHP, versión `wm_archivo.ext` con marca repetitiva.
6. **Anti clic derecho** - `contextmenu` preventDefault con aviso visual.
7. **Anti arrastre** - `draggable="false"`, `dragstart` preventDefault.
8. **Anti capturas** - Bloqueo de `PrintScreen`, `F12`, `Ctrl+Shift+I/J/C`, `Ctrl+C/U/S/P`.
9. **Control de acceso** - Login obligatorio para subir fotos; redirección a `login.php` si no hay sesión.
10. **Registro de auditoría** - Tabla `auditoria_acceso` con IP, user-agent, acción y fecha.
11. **Validación de Términos** - Checkbox obligatorio en contacto y modal de piloto inicial.

---

## 11. CUMPLIMIENTO ISO

| Norma | Aplicación |
|-------|------------|
| **ISO/IEC/IEEE 23026:2015** | Usabilidad web, localización de información, mantenimiento eficiente, gestión de seguridad |
| **ISO/IEC 25000 (SQuaRE)** | Rendimiento, seguridad, fiabilidad, mantenibilidad, portabilidad, usabilidad |
| **ISO 9001** | Análisis de requisitos, diseño, codificación, pruebas, entrega, mejora continua |
| **ISO/IEC 27001** | Control de acceso, criptografía (bcrypt), gestión de incidentes, validación de entrada, protección de archivos e imágenes |
| **ISO/IEC 12207** | Desarrollo, operación, mantenimiento, aseguramiento de calidad, gestión de configuración |

---

## 12. FASES SDLC DOCUMENTADAS

1. **Planificación y Alcance** - Objetivos, costos, recursos, plazos, viabilidad técnica.
2. **Análisis de Requisitos** - Requerimientos funcionales y no funcionales documentados.
3. **Diseño del Sistema** - Arquitectura, base de datos, interfaces, componentes.
4. **Implementación (Codificación)** - HTML, CSS, JS, PHP, MySQL siguiendo estándares.
5. **Pruebas (Testing)** - Unitarias, integración, sistema, seguridad, usabilidad.
6. **Despliegue** - Instalación en entorno de producción, configuración BD, permisos, HTTPS.
7. **Mantenimiento y Operaciones** - Soporte, actualizaciones, mejoras, copias de seguridad.

---

## 13. FLUJOS Y PROCESOS PRINCIPALES

### Flujo 1: Usuario público
Bienvenida → Nosotros/Paquetes/Galería/Contacto → Filtros → Seleccionar fotos → Carrito → WhatsApp checkout.

### Flujo 2: Fotógrafa (subida)
Login → Subir fotos (drag & drop + metadatos) → Validación PHP → Marca de agua GD → Guardar en BD → Disponible en galería.

### Flujo 3: Compra
Seleccionar fotos → localStorage → Panel carrito → Datos cliente → save_purchase.php → WhatsApp URL → Pago coordinado por WhatsApp.

### Flujo 4: Seguridad
Clic derecho → Bloqueo visual → PrintScreen → Bloqueo visual → Marca de agua en servidor → Protección incluso si se captura.

---

## 14. INSTALACIÓN RÁPIDA

1. Clonar/copiar archivos en servidor web (Apache/Nginx + PHP + MySQL).
2. Importar base de datos: `mysql -u usuario -p jeni_hdz_gallery < database.sql`.
3. Configurar `php/config.php` (host, usuario, contraseña BD, WhatsApp).
4. Crear carpeta `assets/uploads/` con permisos de escritura (`chmod 777` o usuario del servidor).
5. Activar HTTPS y `session.cookie_secure = 1` en producción.
6. Acceder a `index.php`.

---

## 15. ARCHIVOS CREADOS EN ESTE PROYECTO

**Páginas PHP:** `index.php`, `nosotros.php`, `paquetes.php`, `galeria.php`, `eventos.php`, `contacto.php`, `login.php`, `subir-fotos.php`.

**CSS:** `css/style.css`, `css/responsive.css`.

**JavaScript:** `js/security.js`, `js/cart.js`, `js/gallery.js`, `js/main.js`, `js/upload.js`, `js/facial-recognition.js`.

**PHP Backend:** `php/config.php`, `php/auth.php`, `php/upload.php`, `php/get_photos.php`, `php/get_events.php`, `php/save_purchase.php`.

**Includes:** `includes/header.php`, `includes/footer.php`.

**Base de datos:** `database.sql`.

**Documentación:** `docs/sdlc.md`, `docs/iso-compliance.md`, `docs/flows.md`, `docs/terminos-condiciones.html`, `docs/validacion-seguridad.md`, `README.md`, `PROMPT_PROYECTO_JENI_HDZ.md`.

**Assets:** `assets/images/`, `assets/uploads/`.

---

> Este archivo puede utilizarse como prompt base para continuar el proyecto, hacer modificaciones, o replicar la estructura en futuros desarrollos similares.
