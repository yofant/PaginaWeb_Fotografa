<?php
$titulo_pagina = 'Eventos | JENI HDZ';
require_once 'includes/header.php';
?>

<section class="section" style="padding-top:calc(var(--space-14) + 76px);">
  <div class="container">
    <h2 class="section-title">Eventos <span class="titulo-morado">de Moto Velocidad</span></h2>
    <p class="section-subtitle">Cada carrera capturada con precisión. Consulta nuestros eventos registrados y las fechas disponibles.</p>

    <div class="eventos-grid">
      <!-- Evento 1 -->
      <div class="evento-card">
        <div class="evento-imagen">
          <img src="assets/images/evento-motogp.jpg" alt="Gran Premio de México MotoGP" loading="lazy" onerror="this.src='https://images.unsplash.com/photo-1577003724279-73e46c3b6ec0?w=600&q=80'">
          <span class="evento-badge">MotoGP</span>
        </div>
        <div class="evento-info">
          <h3>Gran Premio de México 2026</h3>
          <div class="evento-fecha">
            <span>25 Octubre 2026</span>
          </div>
          <div class="evento-lugar">
            <span>Autódromo Hermanos Rodríguez, CDMX</span>
          </div>
          <p class="evento-descripcion">La máxima categoría del motociclismo mundial llega a México con los mejores pilotos y motos de velocidad del planeta.</p>
        </div>
      </div>

      <!-- Evento 2 -->
      <div class="evento-card">
        <div class="evento-imagen">
          <img src="assets/images/evento-superbike.jpg" alt="Superbike España" loading="lazy" onerror="this.src='https://images.unsplash.com/photo-1569443718744-68339283192e?w=600&q=80'">
          <span class="evento-badge">Superbike</span>
        </div>
        <div class="evento-info">
          <h3>Superbike España 2026</h3>
          <div class="evento-fecha">
            <span>12 Septiembre 2026</span>
          </div>
          <div class="evento-lugar">
            <span>Circuito de Jerez, España</span>
          </div>
          <p class="evento-descripcion">Campeonato Mundial de Superbike en uno de los circuitos más emblemáticos de Europa.</p>
        </div>
      </div>

      <!-- Evento 3 -->
      <div class="evento-card">
        <div class="evento-imagen">
          <img src="assets/images/evento-motogp-argentina.jpg" alt="MotoGP Argentina" loading="lazy" onerror="this.src='https://images.unsplash.com/photo-1543595023-a561a49819bb?w=600&q=80'">
          <span class="evento-badge">MotoGP</span>
        </div>
        <div class="evento-info">
          <h3>MotoGP Argentina 2026</h3>
          <div class="evento-fecha">
            <span>15 Agosto 2026</span>
          </div>
          <div class="evento-lugar">
            <span>Autódromo Termas de Río Hondo, Argentina</span>
          </div>
          <p class="evento-descripcion">Velocidad pura en el circuito sudamericano con los mejores pilotos del calendario MotoGP.</p>
        </div>
      </div>

      <!-- Evento 4 -->
      <div class="evento-card">
        <div class="evento-imagen">
          <img src="assets/images/evento-nacionales.jpg" alt="Campeonato Nacional de Velocidad" loading="lazy" onerror="this.src='https://images.unsplash.com/photo-1597584076968-78d0e106cb5e?w=600&q=80'">
          <span class="evento-badge">Nacional</span>
        </div>
        <div class="evento-info">
          <h3>Campeonato Nacional de Velocidad 2026</h3>
          <div class="evento-fecha">
            <span>05 Diciembre 2026</span>
          </div>
          <div class="evento-lugar">
            <span>Autódromo de los Reyes, México</span>
          </div>
          <p class="evento-descripcion">El mejor talento nacional de la moto velocidad compite en el cierre de temporada.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require_once 'includes/footer.php'; ?>