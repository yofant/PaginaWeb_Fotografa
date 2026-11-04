<?php
$titulo_pagina = 'Bienvenida | JENI HDZ';
require_once 'includes/header.php';
?>

<section class="hero">
  <div class="container">
    <div class="hero-contenido">
      <h1>Capturando la <span>velocidad</span> en cada curva</h1>
      <p>Soy JENI HDZ, fotógrafa especializada en moto velocidad. Inmortalizo la adrenalina, el sonido y la pasión del motociclismo de competición.</p>
      <div class="hero-buttons">
        <a href="galeria.php" class="btn btn--primary">Ver Galería</a>
        <a href="paquetes.php" class="btn btn--ghost">Conocer Paquetes</a>
      </div>
    </div>
  </div>
</section>

<section class="section" style="padding-top:var(--space-8);">
  <div class="container">
    <div class="nosotros-grid">
      <div class="nosotros-img">
        <img src="assets/images/moto-velocidad.jpg" alt="Moto velocidad en pista" loading="lazy" onerror="this.src='https://images.unsplash.com/photo-1577003724279-73e46c3b6ec0?w=800&q=80'">
        <div class="nosotros-overlay">JENI HDZ</div>
      </div>
      <div class="nosotros-texto">
        <h2>¿Quién es JENI HDZ?</h2>
        <p>Fotógrafa profesional con años de experiencia capturando los mejores momentos del mundo de la moto velocidad. Desde MotoGP hasta superbikes nacionales, cada imagen cuenta una historia de velocidad y precisión.</p>
        <p>Mis trabajos destacan por el enfoque en el detalle, la composición dinámica y la entrega de imágenes de alta calidad protegidas con marca de agua.</p>
        <a href="nosotros.php" class="btn btn--primary">Conocer más</a>
      </div>
    </div>
  </div>
</section>

<section class="section">
  <div class="container">
    <h2 class="section-title">Nuestros <span class="titulo-morado">Paquetes</span></h2>
    <p class="section-subtitle">Elige el paquete que mejor se adapte a tu evento o necesidad fotográfica.</p>
    <div class="paquetes-grid">
      <div class="paquete">
        <h3>Básico</h3>
        <span class="precio">COP $250</span>
        <ul>
          <li>5 fotos editadas</li>
          <li>Alta resolución</li>
          <li>Marca de agua</li>
          <li>Entrega en 3 días</li>
        </ul>
        <a href="contacto.php" class="btn btn--ghost">Contratar</a>
      </div>
      <div class="paquete">
        <h3>Estándar</h3>
        <span class="precio">COP $600</span>
        <ul>
          <li>15 fotos editadas</li>
          <li>1 canvas pequeña</li>
          <li>Galería privada</li>
          <li>Entrega en 5 días</li>
        </ul>
        <a href="contacto.php" class="btn btn--ghost">Contratar</a>
      </div>
      <div class="paquete">
        <h3>Premium</h3>
        <span class="precio">COP $1.200</span>
        <ul>
          <li>30 fotos editadas</li>
          <li>2 canvas + álbum</li>
          <li>Galería privada</li>
          <li>Entrega en 7 días</li>
        </ul>
        <a href="contacto.php" class="btn btn--ghost">Contratar</a>
      </div>
    </div>
  </div>
</section>

<?php require_once 'includes/footer.php'; ?>