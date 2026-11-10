/**
 * JENI HDZ - Scripts principales
 * Menú móvil, autenticación de fotógrafa, efectos generales
 */

(function () {
  'use strict';

  // Menú móvil
  const btnMenu = document.getElementById('mobileMenuBtn');
  const navLinks = document.getElementById('navLinks');

  if (btnMenu && navLinks) {
    btnMenu.addEventListener('click', function () {
      navLinks.classList.toggle('activo');
      btnMenu.setAttribute('aria-expanded', navLinks.classList.contains('activo'));
    });

    // Cerrar menú al hacer clic en un enlace
    navLinks.querySelectorAll('a').forEach((link) => {
      link.addEventListener('click', () => {
        navLinks.classList.remove('activo');
      });
    });
  }

  // Resaltar enlace activo según URL actual
  const rutaActual = window.location.pathname.split('/').pop();
  document.querySelectorAll('.nav-links a').forEach((link) => {
    const href = link.getAttribute('href');
    if (href && href === rutaActual) {
      link.classList.add('activo');
    }
  });

  // Autenticación de fotógrafa (login)
  const formLogin = document.getElementById('formLogin');
  if (formLogin) {
    formLogin.addEventListener('submit', async function (e) {
      e.preventDefault();
      const btn = formLogin.querySelector('button[type="submit"]');
      const textoOriginal = btn.textContent;
      btn.textContent = 'Iniciando...';
      btn.disabled = true;

      const email = document.getElementById('email').value;
      const password = document.getElementById('password').value;

      try {
        const respuesta = await fetch('php/auth.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          credentials: 'same-origin',
          body: JSON.stringify({ email, password })
        });
        const datos = await respuesta.json();

        if (datos.exito) {
          window.location.href = 'subir-fotos.php';
        } else {
          alert(datos.mensaje || 'Error al iniciar sesión');
        }
      } catch (err) {
        alert('Error de conexión');
      } finally {
        btn.textContent = textoOriginal;
        btn.disabled = false;
      }
    });
  }

  // Cerrar sesión
  window.cerrarSesion = async function () {
    if (!confirm('¿Cerrar sesión?')) return;
    try {
      await fetch('php/auth.php', { method: 'DELETE' });
    } catch (e) {}
    window.location.href = 'index.php';
  };

  // Verificar sesión para páginas protegidas
  async function verificarSesion() {
    try {
      const respuesta = await fetch('php/auth.php');
      const datos = await respuesta.json();
      if (!datos.autenticado) {
        // Si estamos en una página protegida y no hay sesión, redirigir
        if (window.location.pathname.includes('subir-fotos.php') || window.location.pathname.includes('login.php')) {
          // login.php puede estar sin sesión
          if (window.location.pathname.includes('subir-fotos.php')) {
            window.location.href = 'login.php';
          }
        }
      } else {
        // Si está autenticado y está en login, redirigir a subir-fotos
        if (window.location.pathname.includes('login.php')) {
          window.location.href = 'subir-fotos.php';
        }
      }
    } catch (e) {}
  }

  document.addEventListener('DOMContentLoaded', function () {
    verificarSesion();
  });

})();
