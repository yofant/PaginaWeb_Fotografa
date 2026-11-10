<?php
$titulo_pagina = 'Galería | JENI HDZ';
$meta_descripcion = 'Galería de fotografías de moto velocidad de JENI HDZ. Filtra por color, número de moto, piloto, fecha y evento. Compra tus fotos favoritas.';
require_once 'includes/header.php';
?>

<section class="section" style="padding-top:calc(var(--space-12) + 76px);">
  <div class="container">
    <h2 class="section-title">Galería <span class="titulo-morado">de Moto Velocidad</span></h2>
    <p class="section-subtitle">Selecciona las fotos que deseas comprar. Al finalizar serás redirigido/a a WhatsApp.</p>
    

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

<script src="js/gallery.js?v=4"></script>

<?php require_once 'includes/footer.php'; ?>