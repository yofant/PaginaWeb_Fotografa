<?php
$titulo_pagina = 'Paquetes | JENI HDZ';
require_once 'includes/header.php';
?>

<section class="section" style="padding-top:calc(var(--space-12) + 76px);">
  <div class="container">
    <h2 class="section-title">Paquetes <span class="titulo-morado">Fotográficos</span></h2>
    <p class="section-subtitle">Todos los paquetes incluyen entrega digital con marca de agua y atención personalizada.</p>
    <div class="paquetes-grid">
      <div class="paquete">
        <h3>Básico</h3>
        <span class="precio">COP $250</span>
        <ul>
          <li>5 fotos editadas en alta resolución</li>
          <li>Marca de agua JENI HDZ</li>
          <li>Entrega en 3 días hábiles</li>
          <li>Ideal para una carrera o sesión corta</li>
        </ul>
        <a href="contacto.php" class="btn btn--ghost">Solicitar</a>
      </div>
      <div class="paquete">
        <h3>Estándar</h3>
        <span class="precio">COP $600</span>
        <ul>
          <li>15 fotos editadas en alta resolución</li>
          <li>1 impresión en canvas pequeña</li>
          <li>Galería privada de selección</li>
          <li>Entrega en 5 días hábiles</li>
        </ul>
        <a href="contacto.php" class="btn btn--ghost">Solicitar</a>
      </div>
      <div class="paquete">
        <h3>Premium</h3>
        <span class="precio">COP $1.200</span>
        <ul>
          <li>30 fotos editadas en alta resolución</li>
          <li>2 canvas + álbum digital</li>
          <li>Galería privada de selección</li>
          <li>Entrega en 7 días hábiles</li>
        </ul>
        <a href="contacto.php" class="btn btn--ghost">Solicitar</a>
      </div>
      <div class="paquete">
        <h3>Elite Velocidad</h3>
        <span class="precio">COP $2.500</span>
        <ul>
          <li>Todas las fotos del evento seleccionadas</li>
          <li>Impresión profesional</li>
          <li>Álbum físico premium</li>
          <li>Entrega en 10 días hábiles</li>
        </ul>
        <a href="contacto.php" class="btn btn--ghost">Solicitar</a>
      </div>
    </div>
  </div>
</section>

<?php require_once 'includes/footer.php'; ?>