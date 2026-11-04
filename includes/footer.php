  </main>

  <footer>
    <div class="container">
      <div class="footer-grid">
        <div class="footer-col">
          <h4>JENI HDZ</h4>
          <p>Fotógrafa especializada en moto velocidad. Capturando la adrenalina de cada curva y recta.</p>
        </div>
        <div class="footer-col">
          <h4>Enlaces</h4>
          <p><a href="index.php">Bienvenida</a></p>
          <p><a href="nosotros.php">Nosotros</a></p>
          <p><a href="paquetes.php">Paquetes</a></p>
          <p><a href="galeria.php">Galería</a></p>
          <p><a href="contacto.php">Contacto</a></p>
          <p><a href="docs/terminos-condiciones.html">Términos y Condiciones</a></p>
        </div>
        <div class="footer-col">
          <h4>Contacto</h4>
          <p>📧 jeni@jenihdz.com</p>
          <p>📞 +52 55 1234 5678</p>
          <p>💬 WhatsApp: <a href="https://wa.me/525512345678" style="color:var(--color-primary-light);">Escríbenos</a></p>
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
  <script src="js/cart.js?v=4"></script>
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