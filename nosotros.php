<?php
$titulo_pagina = 'Nosotros | JENI HDZ';
require_once 'includes/header.php';
?>

<section class="section" style="padding-top:calc(var(--space-12) + 76px);">
  <div class="container">
    <h2 class="section-title">Nosotros</h2>
    <p class="section-subtitle">Pasión por la moto velocidad, lente tras lente.</p>
    <div class="nosotros-grid">
      <div class="nosotros-img">
        <img src="assets/images/fotografa.jpg" alt="Fotógrafa JENI HDZ" loading="lazy" onerror="this.src='https://images.unsplash.com/photo-1516035069371-29a1b244cc32?w=800&q=80'">
        <div class="nosotros-overlay">JENI HDZ</div>
      </div>
      <div class="nosotros-texto">
        <h2>Nuestra historia</h2>
        <p>JENI HDZ nace del amor por las dos ruedas y la fotografía. Cada carrera, cada entrenamiento y cada instante sobre la pista merece ser recordado con la mejor calidad visual.</p>
        <br>
        <h2>Nuestro compromiso</h2>
        <ul style="color:var(--color-text-secondary); margin-left:0; line-height:2;">
          <li>Fotografía profesional de moto velocidad</li>
          <li>Edición de color y retoque de alta calidad</li>
          <li>Imágenes protegidas con marca de agua</li>
          <li>Entrega oportuna y segura</li>
          <li>Atención personalizada vía WhatsApp</li>
        </ul>
      </div>
    </div>
  </div>
</section>

<?php require_once 'includes/footer.php'; ?>