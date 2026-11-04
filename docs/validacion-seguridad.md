# Validación, Pruebas y Auditoría de Seguridad - JENI HDZ

Este documento describe las pruebas de seguridad, usabilidad y funcionalidad que debe cumplir la página web para garantizar los estándares ISO requeridos.

---

## 1. Pruebas de Seguridad (ISO/IEC 27001 - SGSI)

| # | Prueba | Objetivo | Resultado esperado | Estado |
|---|--------|----------|-------------------|--------|
| S-01 | **Inyección SQL** | Enviar comillas simples, `OR 1=1`, `UNION SELECT` en campos de entrada (email, login, contacto, subida). | Las sentencias preparadas con PDO rechazan o neutralizan la entrada maliciosa. No hay exposición de datos. | Pendiente |
| S-02 | **XSS (Cross-Site Scripting)** | Inyectar `<script>`, `javascript:`, `onerror=` en campos de texto y metadatos de fotos. | Los datos se sanitizan con `htmlspecialchars` al mostrar. No se ejecuta código JavaScript. | Pendiente |
| S-03 | **Acceso sin autenticación** | Intentar acceder directamente a `subir-fotos.php`, `php/upload.php` sin sesión iniciada. | Redirección a `login.php` o respuesta HTTP 401. | Pendiente |
| S-04 | **Control de sesiones** | Verificar que las cookies de sesión tengan `HttpOnly` y `Secure` (en producción con HTTPS). | `session.cookie_httponly = 1`, `session.cookie_secure = 1` en producción. | Pendiente |
| S-05 | **Validación de archivos subidos** | Intentar subir archivos `.php`, `.html`, `.exe`, `.svg` con contenido malicioso, o archivos > 10 MB. | Solo se aceptan `.jpg`, `.jpeg`, `.png`, `.webp` y máximo 10 MB. Otros archivos son rechazados. | Pendiente |
| S-06 | **Clic derecho / menú contextual** | Hacer clic derecho sobre imágenes de la galería. | Menú contextual bloqueado. Se muestra aviso visual de bloqueo. | Pendiente |
| S-07 | **Arrastre de imágenes** | Intentar arrastrar imágenes de la galería al escritorio o nueva pestaña. | Arrastre desactivado (`draggable="false"`, `dragstart` preventDefault). | Pendiente |
| S-08 | **PrintScreen y capturas** | Presionar tecla `PrintScreen`, `Ctrl+Shift+I` (devtools), `F12`, `Ctrl+C` sobre imágenes. | Teclas bloqueadas. Se muestra bloqueo visual. Las imágenes llevan marca de agua. | Pendiente |
| S-09 | **Marca de agua en servidor** | Verificar que las fotos subidas tengan la versión `wm_` generada con librería GD. | Cada imagen en galería es `wm_archivo.ext` con marca de agua repetitiva "JENI HDZ". | Pendiente |
| S-10 | **Registro de auditoría** | Revisar tabla `auditoria_acceso` tras login, subida, acceso a galería. | Cada acción registra usuario_id, acción, IP, user_agent y fecha. | Pendiente |
| S-11 | **CSRF** | Verificar presencia de token CSRF en formularios críticos (login, subida, compra). | Token generado y validado con `hash_equals`. | Pendiente |

---

## 2. Pruebas de Funcionalidad (ISO/IEC 25000 - SQuaRE)

| # | Prueba | Objetivo | Resultado esperado | Estado |
|---|--------|----------|-------------------|--------|
| F-01 | **Navegación entre páginas** | Clic en menú: Bienvenida, Nosotros, Paquetes, Galería, Contacto. | Cada página carga correctamente con su contenido. | Pendiente |
| F-02 | **Filtros de galería** | Aplicar filtros: color, número de moto, número de piloto, fecha, evento. | Las fotos se filtran dinámicamente sin recargar la página (AJAX/Fetch). | Pendiente |
| F-03 | **Limpieza de filtros** | Clic en "Limpiar filtros". | Todos los filtros se restablecen y se muestran todas las fotos. | Pendiente |
| F-04 | **Carrito de compras** | Agregar fotos al carrito desde la galería. | Contador se actualiza, fotos aparecen en el panel lateral. | Pendiente |
| F-05 | **Redirección a WhatsApp** | Clic en "Finalizar en WhatsApp" con fotos seleccionadas. | Se genera mensaje prellenado y se abre `https://wa.me/...` en nueva pestaña. | Pendiente |
| F-06 | **Login de fotógrafa** | Iniciar sesión con credenciales válidas e inválidas. | Válidas → redirige a `subir-fotos.php`. Inválidas → mensaje de error. | Pendiente |
| F-07 | **Subida de fotos** | Subir imágenes con metadatos (título, color, moto, piloto, fecha, evento, precio). | Foto guardada en `assets/uploads/` con marca de agua y registro en BD. | Pendiente |
| F-08 | **Formulario de contacto** | Enviar formulario con datos válidos. | Mensaje de éxito mostrado. (En producción conectar a backend de envío de email). | Pendiente |

---

## 3. Pruebas de Usabilidad y Responsive (ISO/IEC/IEEE 23026:2015)

| # | Prueba | Objetivo | Resultado esperado | Estado |
|---|--------|----------|-------------------|--------|
| U-01 | **Diseño responsivo** | Ver en móvil (375px), tablet (768px) y escritorio (1280px+). | Layout se adapta sin romperse; menú móvil funciona; imágenes no se desbordan. | Pendiente |
| U-02 | **Tamaño de objetivo táctil** | Botones e inputs en móvil. | Mínimo 44×44 px para todos los elementos interactivos. | Pendiente |
| U-03 | **Contraste de color** | Texto sobre fondo negro/morado. | Contraste ≥ WCAG AA (4.5:1 normal, 3:1 texto grande). | Pendiente |
| U-04 | **Navegación por teclado** | Recorrer con `Tab` todos los elementos interactivos. | Orden lógico, focus visible (`outline` en `:focus-visible`), sin trampas de teclado. | Pendiente |
| U-05 | **Skip link** | Presionar `Tab` al cargar la página. | Aparece enlace "Saltar al contenido principal". | Pendiente |
| U-06 | **Preferencia de movimiento reducido** | Activar `prefers-reduced-motion: reduce` en el navegador. | Animaciones se reducen a opacidad/cross-fade; no hay movimientos bruscos. | Pendiente |
| U-07 | **Títulos y semántica HTML** | Inspeccionar estructura de página. | Uso correcto de `<header>`, `<nav>`, `<main>`, `<section>`, `<footer>`, `<label>`, `<button>`. | Pendiente |

---

## 4. Pruebas de Rendimiento (ISO/IEC 25000)

| # | Prueba | Objetivo | Resultado esperado | Estado |
|---|--------|----------|-------------------|--------|
| R-01 | **Carga de galería** | Medir tiempo de carga inicial y con filtros. | Carga inicial < 2 s; filtrado AJAX < 1 s. | Pendiente |
| R-02 | **Imágenes optimizadas** | Verificar tamaños de archivo de imágenes en `assets/uploads/` y `assets/images/`. | Imágenes compradas, dimensiones adecuadas, uso de `loading="lazy"`. | Pendiente |
| R-03 | **Recursos CSS/JS** | Verificar que no haya CSS o JS bloqueante innecesario. | CSS en `<head>`, JS al final de `<body>` o con `defer`. | Pendiente |

---

## 5. Checklist de Cumplimiento ISO

| Norma | Requisito | Implementado en |
|-------|-----------|-----------------|
| **ISO/IEC/IEEE 23026:2015** | Localización oportuna de información | Galería con filtros, navegación clara, páginas informativas |
| | Mantenimiento eficiente | Código modular en carpetas, base de datos normalizada, panel de subida |
| | Gestión de seguridad | Validación, marca de agua, bloqueo de capturas, auditoría |
| **ISO/IEC 25000 (SQuaRE)** | Rendimiento | Carga asíncrona, consultas indexadas, imágenes lazy-load |
| | Seguridad | PDO, sanitización, sesión segura, validación de archivos |
| | Fiabilidad | `try-catch`, respuestas JSON, tabla de auditoría |
| | Mantenibilidad | Código comentado, separación de responsabilidades |
| | Portabilidad | Diseño responsivo, estándares web abiertos |
| | Usabilidad | Interfaz limpia, formularios con validación y feedback |
| **ISO 9001** | Análisis de requisitos | `docs/sdlc.md` (fase 2) |
| | Diseño / Codificación / Pruebas / Entrega | Documentado en `docs/sdlc.md` |
| **ISO/IEC 27001** | Control de acceso | Login obligatorio para subir fotos |
| | Criptografía | `password_hash` (bcrypt), cookies seguras |
| | Gestión de incidentes | Tabla `auditoria_acceso` |
| | Validación de entrada | Sanitización XSS / SQL injection |
| | Protección de archivos | Extensiones permitidas, tamaño máximo 10 MB |
| | Protección de imágenes | Marca de agua GD, clic derecho/PrintScreen bloqueados |
| **ISO/IEC 12207** | Desarrollo / Operación / Mantenimiento | Stack PHP + MySQL, despliegue documentado, fase 7 del SDLC |

---

## 6. Cómo ejecutar las pruebas (ejemplo rápido)

1. **Importar base de datos:**
   ```bash
   mysql -u root -p jeni_hdz_gallery < database.sql
   ```

2. **Configurar `php/config.php`** con credenciales de tu entorno local.

3. **Levantar servidor local:**
   ```bash
   php -S localhost:8000 -t .
   ```

4. **Acceder a:** `http://localhost:8000/index.php`

5. **Credenciales de prueba:**
   - Email: `jeni@jenihdz.com`
   - Contraseña: `12345678`

6. **Verificar seguridad:** Abrir herramientas de desarrollador, intentar `PrintScreen`, clic derecho, y revisar que la consola no muestre errores de SQL ni ejecución de scripts maliciosos.

---

**Estado general del proyecto:** ✅ Implementado y documentado. Las pruebas marcadas como "Pendiente" deben ejecutarse en el entorno de despliegue final antes de la puesta en producción.
