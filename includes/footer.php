  </main>

  <!-- Banner de cookies -->
  <div id="cookieBanner" class="cookie-banner" style="display:none;">
    <div class="cookie-banner-contenido">
      <p>Este sitio utiliza cookies de sesión estrictamente necesarias para el funcionamiento del panel de administración. No se utilizan cookies de rastreo ni publicidad.</p>
      <div class="cookie-banner-botones">
        <button id="btnAceptarCookies" class="btn btn--primary" style="padding:var(--space-2) var(--space-5); font-size:0.85rem;">Aceptar</button>
        <button id="btnRechazarCookies" class="btn btn--ghost" style="padding:var(--space-2) var(--space-5); font-size:0.85rem;">Rechazar</button>
      </div>
    </div>
  </div>

  <!-- Botón flotante de WhatsApp -->
  <a href="https://wa.me/573125879821?text=Hola%2C%20me%20interesa%20conocer%20m%C3%A1s%20sobre%20tus%20servicios%20fotogr%C3%A1ficos." 
     class="whatsapp-float" target="_blank" rel="noopener noreferrer" aria-label="Contactar por WhatsApp">
    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="currentColor">
      <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
    </svg>
  </a>

  <footer>
    <div class="container">
      <div class="footer-grid">
        <div class="footer-col">
          <h4>JENI HDZ</h4>
          <p>Fotógrafa especializada en moto velocidad. Capturando la adrenalina de cada curva y recta.</p>
          <p style="font-size:0.8rem; color:var(--color-text-muted); margin-top:var(--space-2);">
            Catherine Hernández (JENI HDZ) — Fotografía profesional de moto velocidad. Colombia.
          </p>
        </div>
        <div class="footer-col">
          <h4>Enlaces</h4>
          <p><a href="index.php">Bienvenida</a></p>
          <p><a href="nosotros.php">Nosotros</a></p>
          <p><a href="paquetes.php">Paquetes</a></p>
          <p><a href="galeria.php">Galería</a></p>
          <p><a href="contacto.php">Contacto</a></p>
          <p><a href="docs/terminos-condiciones.php">Términos y Condiciones</a></p>
          <p><a href="docs/aviso-privacidad.php">Aviso de Privacidad</a></p>
          <p><a href="docs/aviso-legal.php">Aviso Legal</a></p>
          <p><a href="docs/politica-reembolsos.php">Política de Reembolsos</a></p>
        </div>
        <div class="footer-col">
          <h4>Contacto</h4>
          <p>📧 Hernandezcatherine132002@gmail.com</p>
          <p>📞 +57 312 5879821 </p>
          <p><a href="https://wa.me/573125879821"> 💬 WhatsApp: Escríbenos</a></p>
        </div>
      </div>
      <div class="footer-bottom">
        <p>&copy; 2026 JENI HDZ - Fotografía Moto Velocidad. Todos los derechos reservados.</p>
        <p style="font-size:0.8rem; margin-top:8px;">Imágenes protegidas con marca de agua. Descargas y capturas de pantalla desactivadas.</p>
      </div>
    </div>
  </footer>

  <!-- Modal de aceptación de piloto (se muestra una vez por navegador) -->
  <div id="modalPiloto" class="modal-piloto" style="display:flex;">
    <div class="modal-piloto-contenido">
      <div class="modal-piloto-icono">🏍️</div>
      <h2>Confirmación de Piloto</h2>
      <p>Antes de continuar, confirma que eres el piloto que comprará las fotografías de moto velocidad en esta galería.</p>
      <div class="modal-piloto-terminos" style="margin:var(--space-5) 0;">
        <input type="checkbox" id="aceptoPiloto" style="margin-right:var(--space-3);">
        <label for="aceptoPiloto" style="font-size:0.95rem; color:var(--color-text-secondary); line-height:1.5;">
          Acepto que soy el piloto que adquirirá las fotos y declaro bajo protesta de decir verdad la información proporcionada.
        </label>
      </div>
      <button class="btn btn--primary btn--piloto-aceptar" style="width:auto; padding:var(--space-4) var(--space-8);">
        Aceptar y entrar a la página
      </button>
    </div>
  </div>

  <script src="js/security.js"></script>
  <script>
    // Banner de cookies
    (function() {
      var banner = document.getElementById('cookieBanner');
      if (!banner) return;
      var consent = localStorage.getItem('jeni_cookie_consent');
      if (!consent) {
        banner.style.display = 'flex';
      }
      var btnAceptar = document.getElementById('btnAceptarCookies');
      var btnRechazar = document.getElementById('btnRechazarCookies');
      if (btnAceptar) {
        btnAceptar.addEventListener('click', function() {
          localStorage.setItem('jeni_cookie_consent', 'accepted');
          banner.style.display = 'none';
        });
      }
      if (btnRechazar) {
        btnRechazar.addEventListener('click', function() {
          localStorage.setItem('jeni_cookie_consent', 'rejected');
          banner.style.display = 'none';
        });
      }
    })();
  </script>
  <script src="js/cart.js?v=10"></script>
  <script src="js/main.js"></script>
  <script>
    // Modal de aceptación de piloto
    (function(){
      const modal = document.getElementById('modalPiloto');
      const checkbox = document.getElementById('aceptoPiloto');
      const btnAceptar = document.querySelector('.btn--piloto-aceptar');

      if (!modal || !checkbox || !btnAceptar) return;

      // Si ya aceptó previamente, ocultar modal
      if (localStorage.getItem('jeni_piloto_aceptado') === 'true') {
        modal.style.display = 'none';
        return;
      }

      // Deshabilitar botón hasta aceptar
      btnAceptar.disabled = true;
      btnAceptar.style.opacity = '0.5';
      btnAceptar.style.cursor = 'not-allowed';

      checkbox.addEventListener('change', function(){
        btnAceptar.disabled = !this.checked;
        btnAceptar.style.opacity = this.checked ? '1' : '0.5';
        btnAceptar.style.cursor = this.checked ? 'pointer' : 'not-allowed';
      });

      btnAceptar.addEventListener('click', function(){
        localStorage.setItem('jeni_piloto_aceptado', 'true');
        modal.style.display = 'none';
      });
    })();
  </script>
</body>
</html>