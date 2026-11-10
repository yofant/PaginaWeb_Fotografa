<?php
$titulo_pagina = 'Página no encontrada | JENI HDZ';
http_response_code(404);
require_once 'includes/header.php';
?>
<section class="section" style="padding-top:calc(var(--space-12) + 76px); min-height: 60vh; display: flex; align-items: center; justify-content: center;">
  <div class="container" style="text-align: center;">
    <h1 style="font-size: 6rem; color: var(--color-primary); margin: 0; font-weight: 900;">404</h1>
    <h2 class="section-title" style="margin-bottom: var(--space-4);">Página no encontrada</h2>
    <p class="section-subtitle" style="margin-bottom: var(--space-6);">
      Lo sentimos, la página que buscas no existe o fue movida.
    </p>
    <div style="display: flex; gap: var(--space-3); justify-content: center; flex-wrap: wrap;">
      <a href="index.php" class="btn btn--primary">Ir al inicio</a>
      <a href="galeria.php" class="btn btn--ghost">Ver galería</a>
      <a href="contacto.php" class="btn btn--ghost">Contactarnos</a>
    </div>
  </div>
</section>
<?php require_once 'includes/footer.php'; ?>