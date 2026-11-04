/**
 * JENI HDZ - Medidas de seguridad para proteger las fotografías
 * - Desactiva clic derecho
 * - Desactiva arrastre de imágenes
 * - Desactiva teclas de captura (PrintScreen, etc.)
 * - Bloquea atajos de teclado (Ctrl+C, Ctrl+U, F12, etc.)
 * - Muestra aviso de bloqueo al intentar captura
 * Nota: Estas son medidas disuasorias; la protección real se refuerza con marca de agua en servidor.
 */

(function () {
  'use strict';

  const bloqueo = document.getElementById('bloqueoPantalla');

  function mostrarBloqueo() {
    if (bloqueo) bloqueo.classList.add('mostrar');
    setTimeout(() => {
      if (bloqueo) bloqueo.classList.remove('mostrar');
    }, 2500);
  }

  // 1. Desactivar menú contextual (clic derecho)
  document.addEventListener('contextmenu', function (e) {
    e.preventDefault();
    mostrarBloqueo();
    return false;
  }, { passive: false });

  // 2. Desactivar arrastre de imágenes y elementos
  document.addEventListener('dragstart', function (e) {
    if (e.target.tagName === 'IMG' || e.target.closest('.galeria-item, .miniatura-subida')) {
      e.preventDefault();
      return false;
    }
  }, { passive: false });

  // 3. Desactivar selección de texto sobre imágenes
  document.querySelectorAll('img, .galeria-item').forEach(function (el) {
    el.setAttribute('draggable', 'false');
    el.classList.add('no-seleccion');
  });

  // 4. Bloquear teclas de captura y herramientas de desarrollador
  document.addEventListener('keydown', function (e) {
    // PrintScreen
    if (e.key === 'PrintScreen' || e.key === 'SysRq') {
      e.preventDefault();
      mostrarBloqueo();
      return false;
    }

    // Ctrl + C, Ctrl + X, Ctrl + U, Ctrl + S, Ctrl + P, F12, Ctrl+Shift+I/J/C, Ctrl+Shift+Print
    if (
      (e.ctrlKey && ['c', 'x', 'u', 's', 'p'].includes(e.key.toLowerCase())) ||
      e.key === 'F12' ||
      (e.ctrlKey && e.shiftKey && ['i', 'j', 'c'].includes(e.key.toLowerCase())) ||
      (e.ctrlKey && e.key === 'PrintScreen')
    ) {
      e.preventDefault();
      mostrarBloqueo();
      return false;
    }
  });

  // 5. Desactivar menú contextual en imágenes específicamente
  document.querySelectorAll('img').forEach(function (img) {
    img.addEventListener('contextmenu', function (e) {
      e.preventDefault();
      mostrarBloqueo();
      return false;
    }, { passive: false });
  });

  // 6. Evitar guardar imagen mediante "guardar enlace como" no aplicable directamente,
  // pero ocultamos src absoluto en el inspector lo máximo posible vía CSS (background) en galería.

})();
