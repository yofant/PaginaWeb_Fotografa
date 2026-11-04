<?php
require_once __DIR__ . '/../php/config.php';
$autenticado = !empty($_SESSION['usuario_id']);
$nombre_usuario = $_SESSION['usuario_nombre'] ?? null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <meta name="theme-color" content="#0a0a0a">
  <title><?php echo isset($titulo_pagina) ? htmlspecialchars($titulo_pagina) : 'JENI HDZ'; ?> - Fotografía Moto Velocidad</title>
  <meta name="description" content="JENI HDZ - Fotógrafa especializada en moto velocidad. Galería, paquetes y contacto.">
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/responsive.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <noscript><div class="alerta alerta-error">JavaScript es requerido para usar esta página.</div></noscript>
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
        <span id="totalCarrito">COP $0,00</span>
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