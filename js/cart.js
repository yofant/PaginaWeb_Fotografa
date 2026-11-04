/**
 * Carrito de compras - JENI HDZ
 * Almacenamiento en localStorage y redirección a WhatsApp
 */

(function () {
  'use strict';

  let carrito = JSON.parse(localStorage.getItem('jeni_carrito') || '[]');

  const btnCarrito = document.getElementById('btnCarrito');
  const panelCarrito = document.getElementById('panelCarrito');
  const cerrarCarrito = document.getElementById('cerrarCarrito');
  const listaCarrito = document.getElementById('listaCarrito');
  const totalCarrito = document.getElementById('totalCarrito');
  const contadorCarrito = document.getElementById('contadorCarrito');
  const btnWhatsApp = document.getElementById('btnWhatsApp');
  const formFinalizarCompra = document.getElementById('formFinalizarCompra');
  const errorFinalizarCompra = document.getElementById('errorFinalizarCompra');

  function guardar() {
    localStorage.setItem('jeni_carrito', JSON.stringify(carrito));
    actualizarContador();
    renderizar();
  }

  function actualizarContador() {
    if (contadorCarrito) {
      contadorCarrito.textContent = carrito.length;
      contadorCarrito.style.display = carrito.length ? 'inline-block' : 'none';
    }
  }

  function renderizar() {
    if (!listaCarrito) return;
    listaCarrito.innerHTML = '';
    let total = 0;

    if (carrito.length === 0) {
      listaCarrito.innerHTML = '<p style="color:#64748b; text-align:center;">No hay fotos seleccionadas</p>';
    } else {
      carrito.forEach((item, index) => {
        total += parseFloat(item.precio || 0);
        const el = document.createElement('div');
        el.className = 'item-carrito';
        const basePath = window.location.pathname.slice(0, window.location.pathname.lastIndexOf('/'));
        const urlImagen = item.ruta_archivo
          ? `${basePath}/assets/uploads/${item.ruta_archivo}`
          : item.url_imagen;
        el.innerHTML = `
          <img src="${escapeHtml(urlImagen)}" alt="${escapeHtml(item.titulo)}" loading="lazy">
          <div class="item-info">
            <h4>${escapeHtml(item.titulo || 'Foto #' + item.id)}</h4>
            <p>#${escapeHtml(item.numero_moto || '-')} • Piloto #${escapeHtml(item.numero_piloto || '-')}</p>
            <p>COP $${parseFloat(item.precio || 0).toFixed(2).replace('.', ',')}</p>
          </div>
          <button class="eliminar-item" onclick="eliminarDelCarrito(${index})">&times;</button>
        `;
        listaCarrito.appendChild(el);
      });
    }

    if (totalCarrito) totalCarrito.textContent = 'COP $' + total.toFixed(2).replace('.', ',');

  }

  window.agregarAlCarrito = function (foto) {
    // Evitar duplicados
    const existe = carrito.some((c) => c.id == foto.id);
    if (!existe) {
      carrito.push(foto);
      guardar();
      const btn = document.querySelector('.btn-agregar[data-id="' + foto.id + '"]');
      if (btn) {
        btn.classList.add('agregado');
        btn.textContent = '✓';
        setTimeout(() => {
          btn.classList.remove('agregado');
          btn.textContent = '+';
        }, 1200);
      }
    } else {
      alert('Esta foto ya está en su selección.');
    }
  };

  window.eliminarDelCarrito = function (index) {
    carrito.splice(index, 1);
    guardar();
  };

  window.limpiarCarrito = function () {
    carrito = [];
    guardar();
  };

  if (btnWhatsApp) {
    btnWhatsApp.addEventListener('click', function () {
      if (carrito.length === 0) {
        if (errorFinalizarCompra) {
          errorFinalizarCompra.textContent = 'Seleccione al menos una foto antes de continuar.';
          errorFinalizarCompra.hidden = false;
        }
        return;
      }

      if (formFinalizarCompra) {
        formFinalizarCompra.hidden = false;
        document.getElementById('nombreCliente')?.focus();
      }
    });
  }

  if (formFinalizarCompra) {
    formFinalizarCompra.addEventListener('submit', async function (event) {
      event.preventDefault();
      const datosFormulario = new FormData(formFinalizarCompra);
      const btnEnviar = formFinalizarCompra.querySelector('button[type="submit"]');
      btnEnviar.disabled = true;
      btnEnviar.textContent = 'Conectando con WhatsApp...';
      if (errorFinalizarCompra) errorFinalizarCompra.hidden = true;

      await finalizarCompra({
        nombre: datosFormulario.get('nombre'),
        email: datosFormulario.get('email'),
        whatsapp: datosFormulario.get('whatsapp')
      });

      btnEnviar.disabled = false;
      btnEnviar.textContent = 'Continuar a WhatsApp';
    });
  }

  async function finalizarCompra(cliente) {
    try {
      const respuesta = await fetch('php/save_purchase.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          nombre: cliente.nombre,
          email: cliente.email,
          telefono: cliente.whatsapp,
          whatsapp: cliente.whatsapp,
          fotos: carrito.map((c) => c.id),
          total: cliente.total
        })
      });

      const resultado = await respuesta.json();

      if (respuesta.ok && resultado.exito && resultado.whatsapp_url) {
        window.location.assign(resultado.whatsapp_url);
      } else {
        mostrarErrorFinalizarCompra(resultado.mensaje || 'No se pudo registrar la compra. Intente nuevamente.');
      }
    } catch (error) {
      console.error(error);
      mostrarErrorFinalizarCompra('Error de conexión con el servidor. Intente nuevamente.');
    }
  }

  function mostrarErrorFinalizarCompra(mensaje) {
    if (!errorFinalizarCompra) return;
    errorFinalizarCompra.textContent = mensaje;
    errorFinalizarCompra.hidden = false;
  }

  // Abrir / cerrar panel carrito
  if (btnCarrito) {
    btnCarrito.addEventListener('click', function () {
      panelCarrito.classList.toggle('abierto');
    });
  }

  if (cerrarCarrito) {
    cerrarCarrito.addEventListener('click', function () {
      panelCarrito.classList.remove('abierto');
    });
  }

  // Clic fuera del panel para cerrar
  document.addEventListener('click', function (e) {
    if (panelCarrito && panelCarrito.classList.contains('abierto')) {
      if (!panelCarrito.contains(e.target) && !btnCarrito.contains(e.target)) {
        panelCarrito.classList.remove('abierto');
      }
    }
  });

  // Inicializar
  actualizarContador();
  renderizar();

})();

function escapeHtml(texto) {
  if (!texto) return '';
  const div = document.createElement('div');
  div.textContent = texto;
  return div.innerHTML;
}
