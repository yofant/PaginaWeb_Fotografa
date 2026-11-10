<?php
require_once __DIR__ . '/../php/config.php';
$autenticado = !empty($_SESSION['usuario_id']);
$nombre_usuario = $_SESSION['usuario_nombre'] ?? null;

// Evitar caché del navegador para que los cambios se reflejen inmediatamente
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="theme-color" content="#0a0a0a">
  <title><?php echo isset($titulo_pagina) ? htmlspecialchars($titulo_pagina) : 'JENI HDZ'; ?> - Fotografía Moto Velocidad</title>
  <meta name="description" content="<?php echo isset($meta_descripcion) ? htmlspecialchars($meta_descripcion) : 'JENI HDZ - Fotógrafa especializada en moto velocidad. Galería, paquetes y contacto.'; ?>">
  <meta name="csrf-token" content="<?php echo generar_token_csrf(); ?>">
  <!-- Open Graph -->
  <meta property="og:title" content="<?php echo isset($titulo_pagina) ? htmlspecialchars($titulo_pagina) : 'JENI HDZ'; ?> - Fotografía Moto Velocidad">
  <meta property="og:description" content="<?php echo isset($meta_descripcion) ? htmlspecialchars($meta_descripcion) : 'JENI HDZ - Fotógrafa especializada en moto velocidad. Galería, paquetes y contacto.'; ?>">
  <meta property="og:type" content="website">
  <meta property="og:url" content="<?php echo htmlspecialchars(((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://') . ($_SERVER['HTTP_HOST'] ?? 'localhost') . ($_SERVER['REQUEST_URI'] ?? '')); ?>">
  <meta property="og:image" content="<?php echo htmlspecialchars(APP_URL . '/assets/images/og-image.jpg'); ?>">
  <meta property="og:locale" content="es_CO">
  <!-- Twitter Card -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="<?php echo isset($titulo_pagina) ? htmlspecialchars($titulo_pagina) : 'JENI HDZ'; ?> - Fotografía Moto Velocidad">
  <meta name="twitter:description" content="<?php echo isset($meta_descripcion) ? htmlspecialchars($meta_descripcion) : 'JENI HDZ - Fotógrafa especializada en moto velocidad.'; ?>">
  <!-- Favicon (SVG inline) -->
  <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🏍️</text></svg>">
  <link rel="apple-touch-icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🏍️</text></svg>">
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/responsive.css">
  <noscript><div class="alerta alerta-error">JavaScript es requerido para usar esta página.</div></noscript>
  <!-- Google Analytics (reemplazar G-XXXXXXXXXX con tu ID real) -->
  <!-- <script async src="https://www.googletagmanager.com/gtag/js?id=G-XXXXXXXXXX"></script>
  <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', 'G-XXXXXXXXXX');
  </script> -->
  <!-- Schema.org JSON-LD -->
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "LocalBusiness",
    "name": "JENI HDZ - Fotografía Moto Velocidad",
    "description": "Fotógrafa especializada en moto velocidad. Cobertura de eventos, sesiones privadas y paquetes fotográficos.",
    "url": "<?php echo htmlspecialchars(APP_URL); ?>",
    "telephone": "+573125879821",
    "email": "jeni@jenihdz.com",
    "address": {
      "@type": "PostalAddress",
      "addressCountry": "CO"
    },
    "image": "<?php echo htmlspecialchars(APP_URL . '/assets/images/og-image.jpg'); ?>",
    "priceRange": "$$"
  }
  </script>
</head>
<body class="no-seleccion">

  <a href="#contenido-principal" class="skip-link">Saltar al contenido principal</a>

  <div class="bloqueo-pantalla" id="bloqueoPantalla">
    <span>📸</span>
    <div>
      <strong>Capturas de pantalla desactivadas</strong>
      <p style="margin:0; font-size:0.95rem; opacity:0.9;">Las imágenes están protegidas con marca de agua</p>
    </div>
  </div>

  <header>
    <div class="nav-wrap">
      <a href="index.php" class="logo">JENI<span>HDZ</span></a>
      <ul class="nav-links" id="navLinks">
        <li><a href="index.php">Bienvenida</a></li>
        <li><a href="nosotros.php">Nosotros</a></li>
        <li><a href="paquetes.php">Paquetes</a></li>
        <li><a href="galeria.php">Galería</a></li>
        <li><a href="eventos.php">Eventos</a></li>
        <li><a href="contacto.php">Contacto</a></li>
        <?php if ($autenticado): ?>
          <li><a href="subir-fotos.php" class="btn btn--primary" style="padding:var(--space-2) var(--space-5); font-size:0.9rem;">Subir Fotos</a></li>
          <li><a href="#" onclick="cerrarSesion()" class="btn btn--danger" style="padding:var(--space-2) var(--space-5); font-size:0.9rem;">Cerrar sesión</a></li>
        <?php else: ?>
          <li><a href="login.php" class="btn btn--primary" style="padding:var(--space-2) var(--space-5); font-size:0.9rem;">Iniciar sesión</a></li>
        <?php endif; ?>
      </ul>
      <button class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Menú móvil" aria-expanded="false">
        <span></span><span></span><span></span>
      </button>
    </div>
  </header>

  <!-- Carrito flotante -->
  <div class="carrito-fijo" id="btnCarrito" title="Ver carrito">
    <span class="carrito-icono">🛒</span>
    <span class="carrito-contador" id="contadorCarrito">0</span>
  </div>

  <!-- Panel carrito -->
  <div class="panel-carrito" id="panelCarrito">
    <div class="panel-header">
      <h3>Tu selección</h3>
      <button class="cerrar-panel" id="cerrarCarrito">&times;</button>
    </div>
    <div class="lista-carrito" id="listaCarrito">
      <p style="color:var(--color-text-muted); text-align:center;">No hay fotos seleccionadas</p>
    </div>
    <div class="panel-footer">
      <div class="total-carrito">
        <span>Total:</span>
        <span id="totalCarrito">COP $0,000</span>
      </div>
      <button class="btn-whatsapp" id="btnWhatsApp">
        <span>💬</span> Finalizar en WhatsApp
      </button>
      <form id="formFinalizarCompra" hidden>
        <div class="form-group">
          <label for="nombreCliente">Nombre</label>
          <input type="text" id="nombreCliente" name="nombre" required autocomplete="name">
        </div>
        <div class="form-group">
          <label for="emailCliente">Correo electrónico (opcional)</label>
          <input type="email" id="emailCliente" name="email" autocomplete="email">
        </div>
        <div class="form-group">
          <label for="whatsappCliente">Número de WhatsApp</label>
          <input type="tel" id="whatsappCliente" name="whatsapp" required autocomplete="tel">
        </div>
        <p id="errorFinalizarCompra" class="alerta alerta-error" role="alert" hidden></p>
        <button type="submit" class="btn-whatsapp">Continuar a WhatsApp</button>
      </form>
    </div>
  </div>

  <main id="contenido-principal">