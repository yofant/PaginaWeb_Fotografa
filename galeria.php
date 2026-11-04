<?php
$titulo_pagina = 'Galería | JENI HDZ';
require_once 'includes/header.php';
?>

<section class="section" style="padding-top:calc(var(--space-12) + 76px);">
  <div class="container">
    <h2 class="section-title">Galería <span class="titulo-morado">de Moto Velocidad</span></h2>
    <p class="section-subtitle">Selecciona las fotos que deseas comprar. Al finalizar serás redirigido/a a WhatsApp.</p>

    <!-- Módulo de Registro Facial para identificación automática -->
    <div class="registro-facial-panel">
      <h3>🔐 Identificación por Rostro</h3>
      <p>Sube una selfie o usa tu cámara para identificar automáticamente las fotos tuyas en la galería.</p>
      <div class="video-container" id="videoContainer">
        <video id="video" autoplay playsinline muted></video>
      </div>
      <button type="button" id="btnCapturar" class="btn-capturar">Capturar Foto</button>
      <div class="status-facial info" id="statusFacial">Listo para capturar</div>
      <div style="margin-top:var(--space-4); font-size:0.85rem; color:var(--color-text-muted);">
        Esta función es solo para identificación local. Las imágenes faciales no se almacenan en servidores.
      </div>
    </div>

    <div class="filtros-panel">
      <h3>Filtrar fotos</h3>
      <div class="filtros-chip-group">
        <button type="button" class="filtro-chip activo" data-tipo="color" data-valor="">Todos los colores</button>
        <button type="button" class="filtro-chip" data-tipo="color" data-valor="Rojo">Rojo</button>
        <button type="button" class="filtro-chip" data-tipo="color" data-valor="Negro">Negro</button>
        <button type="button" class="filtro-chip" data-tipo="color" data-valor="Blanco">Blanco</button>
        <button type="button" class="filtro-chip" data-tipo="color" data-valor="Azul">Azul</button>
        <button type="button" class="filtro-chip" data-tipo="color" data-valor="Amarillo">Amarillo</button>
        <button type="button" class="filtro-chip" data-tipo="moto" data-valor="1">Moto #1</button>
        <button type="button" class="filtro-chip" data-tipo="moto" data-valor="12">Moto #12</button>
        <button type="button" class="filtro-chip" data-tipo="piloto" data-valor="1">Piloto #1</button>
        <button type="button" class="filtro-chip" data-tipo="piloto" data-valor="93">Piloto #93</button>
      </div>
      <div class="filtros-selects">
        <select id="filtroColor" class="select-filtro"><option value="">Filtrar por color</option></select>
        <select id="filtroMoto" class="select-filtro"><option value="">Número de moto</option></select>
        <select id="filtroPiloto" class="select-filtro"><option value="">Número de piloto</option></select>
        <select id="filtroFecha" class="select-filtro"><option value="">Fecha</option></select>
        <select id="filtroEvento" class="select-filtro"><option value="">Evento</option></select>
        <button type="button" id="btnLimpiarFiltros" class="btn-limpiar-filtros">Limpiar filtros</button>
      </div>
    </div>

    <div class="galeria-grid" id="galeriaGrid"></div>
    <div id="contenedorVacio" class="vacio" style="display:none;">No se encontraron fotos con esos filtros.</div>
  </div>
</section>

<script src="js/gallery.js"></script>
<script src="js/facial-recognition.js"></script>

<?php require_once 'includes/footer.php'; ?>