# JENI HDZ - Fotografía Moto Velocidad

Sitio web para fotógrafa especializada en moto velocidad. Desarrollado con **HTML, CSS, JavaScript y PHP** con base de datos **MySQL/MariaDB**.

## 📁 Estructura de carpetas

```
JENI-HDZ/
├── index.php              # Página de bienvenida
├── nosotros.php           # Nosotros
├── paquetes.php           # Paquetes fotográficos
├── galeria.php            # Galería pública con filtros
├── contacto.php           # Formulario de contacto
├── login.php              # Acceso de la fotógrafa
├── subir-fotos.php        # Panel privado de subida
├── docs/                  # Documentación (SDLC, ISO, flujos, términos)
│   ├── sdlc.md
│   ├── iso-compliance.md
│   ├── flows.md
│   └── terminos-condiciones.html
├── database.sql           # Esquema y datos de ejemplo
├── css/
│   ├── style.css
│   └── responsive.css
├── js/
│   ├── security.js        # Protección de imágenes (no clic derecho, no capturas)
│   ├── cart.js            # Carrito de compras y WhatsApp
│   ├── gallery.js         # Galería con filtros
│   ├── main.js            # Scripts principales y auth
│   └── upload.js          # Interfaz de subida de fotos
├── php/
│   ├── config.php         # Conexión BD y constantes de seguridad
│   ├── auth.php           # Autenticación de fotógrafa
│   ├── upload.php         # Subida de fotos con marca de agua (GD)
│   ├── get_photos.php     # Obtener fotos con filtros
│   ├── get_events.php     # Listado de eventos
│   └── save_purchase.php  # Guardar intención de compra
├── includes/
│   ├── header.php
│   └── footer.php
└── assets/
    ├── images/            # Imágenes estáticas
    └── uploads/           # Fotos subidas (requiere permisos de escritura)
```

## ⚡ Instalación rápida

1. **Clona o copia** los archivos en tu servidor web (Apache/Nginx + PHP + MySQL).
2. **Importa la base de datos:**
   ```bash
   mysql -u tu_usuario -p jeni_hdz_gallery < database.sql
   ```
3. **Configura la conexión** en `php/config.php` (host, usuario, contraseña de BD).
4. **Crea la carpeta de uploads** y dale permisos de escritura:
   ```bash
   mkdir -p assets/uploads
   sudo setfacl -m u:daemon:rwx assets/uploads  # Linux con XAMPP; usa el usuario de PHP/Apache de tu servidor
   ```
5. **Accede a** `index.php` desde tu navegador.

## 🧪 Prueba de subida de fotos

En un entorno de desarrollo con MySQL iniciado, la base importada y el usuario de prueba con id `1`, ejecuta desde la raíz del proyecto:

```bash
php test_upload.php
```

La prueba verifica el rechazo de usuarios no autenticados y realiza una subida JPEG real al endpoint. Requiere las extensiones PHP `curl`, `pdo_mysql` y `fileinfo`. El registro, los archivos y el registro de auditoría que crea se limpian al finalizar; no la ejecutes contra una base de datos de producción.

## 🔐 Credenciales de ejemplo (fotógrafa)

Después de importar `database.sql`, el usuario predeterminado es:

- **Email:** `jeni@jenihdz.com`
- **Contraseña:** `12345678`

*(Se recomienda cambiar la contraseña en producción.)*

## 🛡️ Seguridad implementada

- ✅ Sentencias preparadas con PDO (anti inyección SQL)
- ✅ Sanitización de entradas (anti XSS)
- ✅ Cookies de sesión seguras (httponly)
- ✅ Validación de archivos subidos (extensión y tamaño)
- ✅ Marca de agua automática en imágenes (librería GD de PHP)
- ✅ Desactivación de clic derecho, arrastre, PrintScreen y herramientas de desarrollador
- ✅ Registro de auditoría de accesos (ISO/IEC 27001)
- ✅ Validación obligatoria de Términos y Condiciones en formulario de contacto
- ✅ Validación obligatoria de declaración de piloto (modal inicial + checkbox en contacto)

## 📱 Características

- Diseño **responsivo** (móvil, tablet, escritorio)
- Tema **negro y morado**
- Galería con **filtros**: color, número de moto, número de piloto, fecha y evento
- **Carrito de compras** con redirección a **WhatsApp**
- Panel privado de **subida de fotos** con vista previa
- Páginas: Bienvenida, Nosotros, Paquetes, Galería, Contacto

## 📋 Cumplimiento normativo

Documentado en `docs/`:
- ISO/IEC/IEEE 23026:2015 (usabilidad web)
- ISO/IEC 25000 (SQuaRE - calidad de software)
- ISO 9001 (gestión de la calidad)
- ISO/IEC 27001 (seguridad de la información)
- ISO/IEC 12207 (ciclo de vida del software)
- 7 fases del SDLC

## 📄 Términos y condiciones

Disponible en `docs/terminos-condiciones.html`.

---

**Desarrollado para JENI HDZ - Fotografía Moto Velocidad**  
Colores: Negro `#0a0a0a` y Morado `#8b00ff`
