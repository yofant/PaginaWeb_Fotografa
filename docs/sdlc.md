# Ciclo de Vida del Software (SDLC) - JENI HDZ

Este documento describe las 7 fases principales del SDLC aplicadas al desarrollo de la página web de fotografía de moto velocidad.

---

## 1. Planificación y Alcance

**Objetivo:** Crear una página web para una fotógrafa de moto velocidad con galería protegida, carrito de compras con redirección a WhatsApp y panel privado de subida de fotos.

| Elemento | Descripción |
|----------|-------------|
| Alcance | 5 páginas públicas, galería con filtros, login fotógrafa, subida segura, carrito, WhatsApp checkout |
| Costo estimado | Desarrollo propio (tiempo-hombre) + hosting + dominio |
| Recursos | 1 desarrollador full-stack (HTML/CSS/JS/PHP), 1 diseñador UI, 1 fotógrafa (contenido) |
| Plazos | 4 semanas |
| Viabilidad técnica | Alta: stack ligero (LAMP), sin dependencias externas complejas |

**Entregable:** Documento de alcance y cronograma.

---

## 2. Análisis de Requisitos

### Requisitos Funcionales
1. Páginas: Bienvenida, Nosotros, Contacto, Paquetes, Galería.
2. Galería pública con filtros: color, número de moto, número de piloto, fecha y evento.
3. Carrito de selección de fotos y redirección a WhatsApp para finalizar compra.
4. Área privada de la fotógrafa para subir imágenes (login obligatorio).
5. Marca de agua automática en imágenes de la galería.
6. Diseño responsivo (móvil, tablet, escritorio).
7. Tema visual negro y morado, enfocado en moto velocidad.

### Requisitos No Funcionales
- **Seguridad:** No descarga directa, no capturas de pantalla, marca de agua, validación de entrada, sentencias preparadas (PDO).
- **Usabilidad:** Navegación clara, filtros intuitivos, carga rápida.
- **Rendimiento:** Carga de galería paginada/filtrable vía AJAX.
- **Disponibilidad:** 99% tiempo de funcionamiento en entorno de producción.

**Entregable:** Especificación de requisitos de software (SRS).

---

## 3. Diseño del Sistema

### Arquitectura
- **Frontend:** HTML5, CSS3 (Flexbox/Grid), JavaScript vanilla (ES6+).
- **Backend:** PHP 8+ con PDO para acceso a base de datos.
- **Base de datos:** MySQL / MariaDB.
- **Servidor:** Apache/Nginx (LAMP).

### Modelo de datos (resumido)
- `usuarios`: fotógrafa/administradores.
- `eventos`: carreras de moto velocidad.
- `fotos`: imágenes subidas con metadatos (color, moto, piloto, fecha, evento, precio, ruta con marca de agua).
- `paquetes`: planes fotográficos.
- `compras`: intención de compra vía WhatsApp.
- `auditoria_acceso`: registro de accesos (ISO 27001).

### Diseño de interfaces
- Wireframes de baja fidelidad para cada página.
- Sistema de diseño: variables CSS (colores negro/morado, tipografía, espaciado).

**Entregable:** Diagrama de base de datos, wireframes, modelo de componentes.

---

## 4. Implementación (Codificación)

Se desarrolló siguiendo estándares de programación:

- Sentencias preparadas con PDO para prevenir inyección SQL.
- Sanitización de entradas de usuario (`htmlspecialchars`, `trim`, `stripslashes`).
- Tokens CSRF para formularios críticos.
- Cookies de sesión seguras (`httponly`, `secure` en producción).
- Validación de archivos (extensión y tamaño) antes de subir.
- Generación de marca de agua con librería GD de PHP.

**Entregable:** Código fuente organizado en carpetas (`css/`, `js/`, `php/`, `includes/`, `assets/`).

---

## 5. Pruebas (Testing)

| Tipo de prueba | Descripción | Estado |
|----------------|-------------|--------|
| Pruebas unitarias | Validación de funciones PHP (conexión, carga de fotos, carrito) | Pendiente |
| Pruebas de integración | Flujo subida → galería → carrito → WhatsApp | Pendiente |
| Pruebas de sistema | Navegación completa en Chrome, Firefox, Safari, Edge | Pendiente |
| Pruebas de seguridad | Intento de clic derecho, PrintScreen, inyección SQL, acceso sin login | Pendiente |
| Pruebas de usabilidad | Navegación en móvil y tablet | Pendiente |

**Entregable:** Informe de pruebas y registro de defectos corregidos.

---

## 6. Despliegue (Puesta en producción)

1. Crear base de datos ejecutando `database.sql`.
2. Configurar `php/config.php` con credenciales de producción.
3. Subir archivos al servidor web (Apache/Nginx + PHP).
4. Dar permisos de escritura a `assets/uploads/` para subida de imágenes.
5. Activar HTTPS y marcar `session.cookie_secure = 1`.
6. Probar en entorno real antes de anunciar al público.

**Entregable:** Sitio publicado y operativo.

---

## 7. Mantenimiento y Operaciones

- Monitoreo de errores en `error_log` de PHP.
- Copias de seguridad semanales de base de datos y carpeta `assets/uploads/`.
- Actualización de parches de seguridad del servidor y PHP.
- Soporte a la fotógrafa para subida de nuevo contenido.
- Mejora continua basada en métricas de uso.

---

## Diagrama de flujo del SDLC

```
Planificación → Análisis → Diseño → Implementación → Pruebas → Despliegue → Mantenimiento
     ↓             ↓          ↓           ↓            ↓          ↓             ↓
 Alcance       Requisitos  Arquitectura Código       Corrección  Producción   Soporte
```
