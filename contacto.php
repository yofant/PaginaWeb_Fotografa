<?php
$titulo_pagina = 'Contacto | JENI HDZ';
$meta_descripcion = 'Contacta a JENI HDZ para cotizaciones de paquetes fotográficos, cobertura de eventos de moto velocidad y compra de fotos de galería.';
require_once 'includes/header.php';
?>

<section class="section" style="padding-top:calc(var(--space-12) + 76px);">
  <div class="container">
    <h2 class="section-title">Contáctanos</h2>
    <p class="section-subtitle">¿Tienes un evento de moto velocidad o deseas contratar un paquete? Escríbenos.</p>
    <!-- Honeypot anti-spam (oculto para humanos) -->
    <div style="position:absolute; left:-9999px; top:-9999px;" aria-hidden="true">
      <label for="website">Website</label>
      <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
    </div>
    <div class="contacto-grid">
      <div class="contacto-info">
        <h2>JENI HDZ</h2>
        <p>Fotógrafa especializada en moto velocidad. Cobertura de eventos, sesiones privadas y paquetes fotográficos personalizados.</p>
        <div class="datos-contacto">
          <h4>Correo electrónico</h4>
          <p>jeni@jenihdz.com</p>
          <h4>Teléfono</h4>
          <p>+52 55 1234 5678</p>
          <h4>WhatsApp</h4>
          <p><a href="https://wa.me/525512345678">+52 55 1234 5678</a></p>
          <h4>Ubicación</h4>
          <p>Ciudad de México, México</p>
        </div>
      </div>
      <div class="formulario-contacto">
        <form id="formContacto" novalidate>
          <div class="form-group">
            <label for="nombre">Nombre completo</label>
            <input type="text" id="nombre" name="nombre" required placeholder="Tu nombre">
          </div>
          <div class="form-group">
            <label for="email">Correo electrónico</label>
            <input type="email" id="email" name="email" required placeholder="tu@correo.com">
          </div>
          <div class="form-group">
            <label for="telefono">Teléfono / WhatsApp</label>
            <input type="tel" id="telefono" name="telefono" required placeholder="+52 55 1234 5678">
          </div>
          <div class="form-group">
            <label for="asunto">Asunto</label>
            <select id="asunto" name="asunto" required>
              <option value="">Seleccionar</option>
              <option value="cotizacion">Cotización de paquete</option>
              <option value="evento">Cobertura de evento</option>
              <option value="galeria">Compra de fotos de galería</option>
              <option value="otro">Otro</option>
            </select>
          </div>
          <div class="form-group">
            <label for="mensaje">Mensaje</label>
            <textarea id="mensaje" name="mensaje" rows="5" required placeholder="Cuéntanos qué necesitas..."></textarea>
          </div>
          <div class="form-group terminos-group" style="display:flex; align-items:flex-start; gap:var(--space-3); padding:var(--space-3) 0; border-top:1px solid var(--color-border); margin-top:var(--space-4);">
            <input type="checkbox" id="terminos" name="terminos" required style="margin-top:0.15rem;">
            <label for="terminos" style="font-size:0.9rem; color:var(--color-text-secondary); line-height:1.5; margin:0;">
              Acepto los <a href="docs/terminos-condiciones.php" style="color:var(--color-primary-light); text-decoration:underline;" target="_blank">Términos y Condiciones</a>, el <a href="docs/aviso-privacidad.php" style="color:var(--color-primary-light); text-decoration:underline;" target="_blank">Aviso de Privacidad</a> y declaro que seré el piloto que comprará las fotografías seleccionadas.
            </label>
          </div>
          <button type="submit" class="btn btn--primary" style="width:100%;">Enviar mensaje</button>
        </form>
        <div id="respuestaContacto" style="margin-top:var(--space-4);"></div>
      </div>
    </div>
  </section>

<script>
  // Token CSRF para el formulario de contacto
  const csrfToken = '<?php echo generar_token_csrf(); ?>';

  document.getElementById('formContacto').addEventListener('submit', async function(e){
    const terminos = document.getElementById('terminos');
    if (!terminos.checked) {
      e.preventDefault();
      alert('Debes aceptar los Términos y Condiciones y declarar que serás el piloto que comprará las fotos antes de enviar el mensaje.');
      return false;
    }
    const btn = this.querySelector('button[type="submit"]');
    const original = btn.textContent;
    btn.textContent = 'Enviando...';
    btn.disabled = true;

    const formData = new FormData(this);
    const datos = {};
    formData.forEach(function(value, key) {
      datos[key] = value;
    });
    datos.csrf_token = csrfToken;

    try {
      const respuesta = await fetch('php/contacto.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'same-origin',
        body: JSON.stringify(datos)
      });
      const textoRespuesta = await respuesta.text();
      console.log('Respuesta del servidor:', textoRespuesta);
      let resultado = null;
      try {
        resultado = JSON.parse(textoRespuesta);
      } catch (parseErr) {
        console.error('Error parseando JSON:', parseErr);
        document.getElementById('respuestaContacto').innerHTML = '<div class="alerta alerta-error">Error del servidor. Respuesta no válida.</div>';
        btn.textContent = original;
        btn.disabled = false;
        return;
      }

      if (respuesta.ok && resultado.exito) {
        document.getElementById('respuestaContacto').innerHTML = '<div class="alerta alerta-exito">' + escapeHtml(resultado.mensaje) + '</div>';
        this.reset();
        // Redirigir a WhatsApp si hay URL
        if (resultado.whatsapp_url) {
          document.getElementById('respuestaContacto').innerHTML = '<div class="alerta alerta-exito">' + escapeHtml(resultado.mensaje) + '<br><br>Si no se redirige automáticamente, <a href="' + resultado.whatsapp_url + '" target="_blank" style="color:var(--color-primary); text-decoration:underline; font-weight:bold;">haz clic aquí para abrir WhatsApp</a>.</div>';
          window.location.href = resultado.whatsapp_url;
        } else {
          console.error('No se recibió whatsapp_url en la respuesta:', resultado);
        }
      } else {
        document.getElementById('respuestaContacto').innerHTML = '<div class="alerta alerta-error">' + escapeHtml(resultado.mensaje || 'Error al enviar el mensaje') + '</div>';
      }
    } catch (err) {
      console.error('Error en fetch:', err);
      document.getElementById('respuestaContacto').innerHTML = '<div class="alerta alerta-error">Error de conexión con el servidor.</div>';
    } finally {
      btn.textContent = original;
      btn.disabled = false;
    }
  });

  function escapeHtml(texto) {
    if (!texto) return '';
    const div = document.createElement('div');
    div.textContent = texto;
    return div.innerHTML;
  }
</script>

<?php require_once 'includes/footer.php'; ?>