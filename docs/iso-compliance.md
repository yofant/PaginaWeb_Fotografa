# Cumplimiento de Normas ISO - JENI HDZ

Este documento detalla cómo el proyecto se alinea con las normas ISO solicitadas.

---

## 1. ISO/IEC/IEEE 23026:2015 - Usabilidad de sitios web informativos

| Requisito de la norma | Aplicación en JENI HDZ |
|-----------------------|------------------------|
| Localización de información oportuna | Galería con filtros por color, número de moto, número de piloto, fecha y evento. Búsqueda rápida de fotos. |
| Mantenimiento eficiente | Código modular en carpetas (`php/`, `css/`, `js/`, `includes/`), base de datos normalizada, panel de subida sencillo para la fotógrafa. |
| Gestión de la seguridad | Validación de entradas, marca de agua, desactivación de clic derecho y capturas, registro de auditoría. |
| Contenido informativo claro | Páginas de Bienvenida, Nosotros, Paquetes y Contacto bien estructuradas y responsivas. |

---

## 2. ISO/IEC 25000 (SQuaRE) - Calidad del producto de software

| Característica de calidad | Implementación |
|---------------------------|----------------|
| **Rendimiento** | Carga asíncrona de galería (AJAX/Fetch), imágenes optimizadas, consultas indexadas en MySQL. |
| **Seguridad** | Sentencias preparadas (PDO), sanitización de datos, sesión segura, validación de archivos subidos, marca de agua, bloqueo de capturas. |
| **Fiabilidad** | Manejo de errores con `try-catch`, respuestas JSON consistentes, registro de auditoría en tabla `auditoria_acceso`. |
| **Mantenibilidad** | Código comentado, separación de responsabilidades (vista, controlador, datos), documentación completa. |
| **Portabilidad** | Diseño responsivo que funciona en móviles, tablets y escritorio; estándares web abiertos. |
| **Usabilidad** | Interfaz limpia, navegación intuitiva, formularios con validación y mensajes de retroalimentación. |

---

## 3. ISO 9001 - Gestión de la Calidad

| Proceso ISO 9001 | Aplicación en el proyecto |
|------------------|---------------------------|
| Análisis de requisitos | Documentado en `docs/sdlc.md` (fase 2). |
| Diseño | Arquitectura, base de datos y wireframes definidos antes de codificar. |
| Codificación | Estándares de PHP, HTML semántico, CSS organizado, JavaScript modular. |
| Pruebas | Plan de pruebas definido (unidad, integración, sistema, seguridad, usabilidad). |
| Entrega | Despliegue documentado con pasos claros a producción. |
| Mejora continua | Retroalimentación de la fotógrafa y usuarios para nuevas versiones. |

---

## 4. ISO/IEC 27001 - Seguridad de la Información (SGSI)

| Control de seguridad | Implementación en JENI HDZ |
|----------------------|----------------------------|
| Control de acceso | Login obligatorio para subir fotos; solo usuarios autenticados acceden a `subir-fotos.php`. |
| Criptografía | Contraseñas hasheadas con `password_hash` (bcrypt); cookies de sesión seguras. |
| Gestión de incidentes | Tabla `auditoria_acceso` registra IP, user-agent, acción y fecha de cada acceso. |
| Validación de entrada | Sanitización de todos los inputs (XSS, inyección SQL). |
| Protección de archivos | Solo extensiones `.jpg`, `.jpeg`, `.png`, `.webp`; tamaño máximo 10 MB. |
| Protección de imágenes | Marca de agua automática en servidor (GD), desactivación de clic derecho, arrastre, PrintScreen y herramientas de desarrollador. |
| Clasificación de información | Fotos marcadas como `disponible` o no; compras con estado `pendiente/completado/cancelado`. |

---

## 5. ISO/IEC 12207 - Procesos del ciclo de vida del software

| Proceso | Estado en JENI HDZ |
|---------|--------------------|
| Desarrollo | Implementado con HTML/CSS/JS/PHP y MySQL. |
| Operación | Sitio listo para despliegue en servidor web. |
| Mantenimiento | Documentado en fase 7 del SDLC. |
| Aseguramiento de la calidad | Pruebas definidas y cumplimiento de normas ISO. |
| Gestión de configuración | Archivos versionados por carpeta y documento de configuración (`php/config.php`). |

---

**Conclusión:** El proyecto JENI HDZ fue diseñado y desarrollado considerando los lineamientos de las normas ISO/IEC/IEEE 23026:2015, ISO/IEC 25000, ISO 9001, ISO/IEC 27001 e ISO/IEC 12207, así como las 7 fases del SDLC.
