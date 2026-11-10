<?php
require_once __DIR__ . '/php/config.php';
$titulo_pagina = 'Subir Fotos | JENI HDZ';
$meta_descripcion = 'Panel privado de subida de fotos para la fotógrafa JENI HDZ. Sube imágenes con marca de agua automática.';

if (empty($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'includes/header.php';
?>

<section class="section" style="padding-top:calc(var(--space-12) + 76px);">
  <div class="container">
    <h2 class="section-title">Subir <span class="titulo-morado">Fotos</span></h2>
    <p class="section-subtitle">Interfaz exclusiva de la fotógrafa. Las imágenes se guardan con marca de agua automática.</p>
    <div id="mensajeSubida" class="alerta" style="display:none;"></div>
    <div class="subida-area">
      <div class="drop-zone" id="dropZone">
        <p>Arrastra tus fotos aquí</p>
        <p><span>o haz clic para seleccionar</span></p>
      </div>
      <input type="file" id="inputArchivo" multiple accept="image/jpeg, image/png, image/webp" style="display:none;">
      <button type="button" id="btnSeleccionar" class="btn btn--primary btn-subir" style="margin-bottom:var(--space-6);">Seleccionar imágenes</button>

      <form id="formSubida">
        <div class="campos-foto">
          <div class="form-group">
            <label for="titulo">Título</label>
            <input type="text" id="titulo" name="titulo" placeholder="Título de la foto">
          </div>
          <div class="form-group">
            <label for="color">Color predominante</label>
            <input type="text" id="color" name="color_predominante" placeholder="Ej: Rojo, Negro, Azul">
          </div>
          <div class="form-group">
            <label for="numero_moto">Número de moto</label>
            <input type="text" id="numero_moto" name="numero_moto" placeholder="Ej: 12">
          </div>
          <div class="form-group">
            <label for="numero_piloto">Número de piloto</label>
            <input type="text" id="numero_piloto" name="numero_piloto" placeholder="Ej: 93">
          </div>
          <div class="form-group">
            <label for="fecha_foto">Fecha de la foto</label>
            <input type="date" id="fecha_foto" name="fecha_foto">
          </div>
          <div class="form-group">
            <label for="evento_id">Evento</label>
            <select id="evento_id" name="evento_id">
              <option value="">Seleccionar evento</option>
            </select>
          </div>
          <div class="form-group">
            <label for="subcarpeta_id">Subcarpeta</label>
            <select id="subcarpeta_id" name="subcarpeta_id" disabled>
              <option value="">Primero selecciona un evento</option>
            </select>
          </div>
          <div class="form-group">
            <label for="precio">Precio (COP)</label>
            <input type="number" id="precio" name="precio" min="0" step="0.01" placeholder="0.000">
          </div>
        </div>
        <div class="form-group" style="grid-column:1/-1;">
          <label for="descripcion">Descripción</label>
          <textarea id="descripcion" name="descripcion" rows="3" placeholder="Describe la foto..."></textarea>
        </div>
        <h4 style="margin:var(--space-6) 0 var(--space-3); color:var(--color-text);">Vista previa</h4>
        <div class="vista-previa-subida" id="vistaPrevia"></div>
        <button type="submit" class="btn btn--primary btn-subir">Subir foto(s) con marca de agua</button>
      </form>
    </div>
  </div>
</section>

<script>window.csrfToken = '<?php echo generar_token_csrf(); ?>';</script>
<script src="js/upload.js?v=5"></script>

<?php require_once 'includes/footer.php'; ?>