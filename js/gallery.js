/**
 * Galería - JENI HDZ
 * Carga fotos desde PHP con filtros: color, número de moto, número de piloto, fecha, evento
 * Permite editar y eliminar fotos si el usuario es la fotógrafa autenticada.
 */

(function () {
  'use strict';

  const galeriaGrid = document.getElementById('galeriaGrid');
  const contenedorVacio = document.getElementById('contenedorVacio');
  const btnFiltros = document.querySelectorAll('.filtro-chip');
  const selectColor = document.getElementById('filtroColor');
  const selectMoto = document.getElementById('filtroMoto');
  const selectPiloto = document.getElementById('filtroPiloto');
  const selectFecha = document.getElementById('filtroFecha');
  const selectEvento = document.getElementById('filtroEvento');
  const btnLimpiar = document.getElementById('btnLimpiarFiltros');

  let fotos = [];
  let usuarioAutenticado = false;
  let usuarioRol = null;
  let usuarioId = null;
  const csrfMeta = document.querySelector('meta[name="csrf-token"]');
  const csrfToken = csrfMeta ? csrfMeta.content : '';

  // Leer filtros iniciales desde la URL (útil para "motos similares" tras subir una foto)
  const paramsIniciales = new URLSearchParams(window.location.search);
  const filtrosIniciales = {};
  if (paramsIniciales.get('color')) filtrosIniciales.color = paramsIniciales.get('color');
  if (paramsIniciales.get('numero_moto')) filtrosIniciales.numero_moto = paramsIniciales.get('numero_moto');
  if (paramsIniciales.get('numero_piloto')) filtrosIniciales.numero_piloto = paramsIniciales.get('numero_piloto');
  if (paramsIniciales.get('fecha')) filtrosIniciales.fecha = paramsIniciales.get('fecha');
  if (paramsIniciales.get('evento')) filtrosIniciales.evento = paramsIniciales.get('evento');

  // Si estamos en la página de detalle de evento, filtrar por ese evento
  if (galeriaGrid && galeriaGrid.dataset.eventoId) {
    filtrosIniciales.evento = galeriaGrid.dataset.eventoId;
  }

  async function verificarSesion() {
    try {
      const respuesta = await fetch('php/auth.php', { credentials: 'same-origin' });
      const datos = await respuesta.json();
      if (datos.autenticado) {
        usuarioAutenticado = true;
        usuarioRol = (datos.usuario && datos.usuario.rol) ? datos.usuario.rol : null;
        usuarioId = (datos.usuario && datos.usuario.id) ? datos.usuario.id : null;
      }
    } catch (e) {
      console.error('Error verificando sesión:', e);
    }
  }

  async function cargarFotos(params = {}) {
    if (!galeriaGrid) return;

    try {
      const query = new URLSearchParams(params).toString();
      const url = 'php/get_photos.php' + (query ? '?' + query : '');

      const respuesta = await fetch(url, { credentials: 'same-origin' });
      const datos = await respuesta.json();

      if (!datos.exito) throw new Error(datos.mensaje || 'Error al cargar fotos');

      fotos = datos.fotos;
      renderizar(fotos);
      actualizarContadorFiltros(datos.total);
    } catch (error) {
      console.error(error);
      if (galeriaGrid) galeriaGrid.innerHTML = '<div class="vacio">Error al cargar la galería. Inténtelo más tarde.</div>';
    }
  }

  function renderizar(lista) {
    if (!galeriaGrid) return;
    galeriaGrid.innerHTML = '';
    if (!lista) lista = [];
    if (contenedorVacio) {
      contenedorVacio.style.display = lista.length ? 'none' : 'block';
    }

    lista.forEach((foto) => {
      const item = document.createElement('div');
      item.className = 'galeria-item';
      item.dataset.id = foto.id;
      item.dataset.color = foto.color_predominante || '';
      item.dataset.moto = foto.numero_moto || '';
      item.dataset.piloto = foto.numero_piloto || '';
      item.dataset.fecha = foto.fecha_foto || '';
      item.dataset.evento = foto.evento_id || '';
      item.dataset.evento_id = foto.evento_id || '';

      const nombreOriginal = foto.nombre_original || foto.nombre_archivo || '';
      let botonesAdmin = '';
      if (usuarioAutenticado && (usuarioRol === 'fotografa' || usuarioRol === 'admin')) {
        botonesAdmin = `
          <div class="galeria-acciones">
            <button class="btn-editar-foto" data-id="${foto.id}" title="Editar foto">&#9998;</button>
            <button class="btn-eliminar-foto" data-id="${foto.id}" title="Eliminar foto">&#128465;</button>
          </div>
        `;
      }

      item.innerHTML = `
        <img src="${escapeHtml(foto.url_imagen)}" alt="${escapeHtml(foto.titulo || 'Foto moto velocidad')}" loading="lazy">
        <div class="watermark-overlay">JENI HDZ</div>
        <div class="etiqueta-info">
          <strong>${escapeHtml(foto.titulo || 'Sin título')}</strong><br>
          <small>Moto #${escapeHtml(foto.numero_moto || '-')} | Piloto #${escapeHtml(foto.numero_piloto || '-')} | ${escapeHtml(foto.fecha_foto || '-')}</small>
          ${nombreOriginal ? '<br><small style="opacity:0.7; font-size:0.75rem;">&#128206; ' + escapeHtml(nombreOriginal) + '</small>' : ''}
        </div>
        <button class="btn-agregar" data-id="${foto.id}" onclick="agregarFotoDesdeGaleria(${foto.id})">+</button>
        ${botonesAdmin}
      `;
      galeriaGrid.appendChild(item);
    });

    // Vincular clic en la tarjeta para agregar (excepto botones de acción)
    galeriaGrid.querySelectorAll('.galeria-item').forEach((el) => {
      el.addEventListener('click', function (e) {
        if (e.target.classList.contains('btn-agregar') || e.target.closest('.btn-agregar')) return;
        if (e.target.classList.contains('btn-editar-foto') || e.target.closest('.btn-editar-foto')) return;
        if (e.target.classList.contains('btn-eliminar-foto') || e.target.closest('.btn-eliminar-foto')) return;
        const id = parseInt(this.dataset.id);
        const foto = fotos.find((f) => f.id === id);
        if (foto) window.agregarAlCarrito(foto);
      });
    });

    // Eventos botones editar
    galeriaGrid.querySelectorAll('.btn-editar-foto').forEach((btn) => {
      btn.addEventListener('click', function (e) {
        e.stopPropagation();
        const id = parseInt(this.dataset.id);
        const foto = fotos.find((f) => f.id === id);
        if (foto) abrirModalEdicionFoto(foto);
      });
    });

    // Eventos botones eliminar
    galeriaGrid.querySelectorAll('.btn-eliminar-foto').forEach((btn) => {
      btn.addEventListener('click', function (e) {
        e.stopPropagation();
        const id = parseInt(this.dataset.id);
        eliminarFoto(id);
      });
    });
  }

  function actualizarContadorFiltros(total) {
    // opcional: mostrar cantidad de fotos filtradas
  }

  function aplicarFiltros() {
    const params = {};
    const valores = {};

    btnFiltros.forEach((btn) => {
      if (btn.classList.contains('activo')) {
        const tipo = btn.dataset.tipo;
        const val = btn.dataset.valor;
        if (tipo === 'color') valores.color = val;
        if (tipo === 'moto') valores.numero_moto = val;
        if (tipo === 'piloto') valores.numero_piloto = val;
      }
    });

    if (selectColor && selectColor.value) valores.color = selectColor.value;
    if (selectMoto && selectMoto.value) valores.numero_moto = selectMoto.value;
    if (selectPiloto && selectPiloto.value) valores.numero_piloto = selectPiloto.value;
    if (selectFecha && selectFecha.value) valores.fecha = selectFecha.value;
    if (selectEvento && selectEvento.value) valores.evento = selectEvento.value;

    cargarFotos(valores);
  }

  btnFiltros.forEach((btn) => {
    btn.addEventListener('click', function () {
      const tipo = this.dataset.tipo;
      btnFiltros.forEach((b) => {
        if (b.dataset.tipo === tipo) b.classList.remove('activo');
      });
      this.classList.toggle('activo');
      aplicarFiltros();
    });
  });

  [selectColor, selectMoto, selectPiloto, selectFecha, selectEvento].forEach((sel) => {
    if (sel) sel.addEventListener('change', aplicarFiltros);
  });

  if (btnLimpiar) {
    btnLimpiar.addEventListener('click', function () {
      btnFiltros.forEach((b) => b.classList.remove('activo'));
      [selectColor, selectMoto, selectPiloto, selectFecha, selectEvento].forEach((sel) => {
        if (sel) sel.selectedIndex = 0;
      });
      cargarFotos();
    });
  }

  window.agregarFotoDesdeGaleria = function (id) {
    const foto = fotos.find((f) => f.id === id);
    if (foto) window.agregarAlCarrito(foto);
  };

  /* ============ EDICIÓN DE FOTOS (MODAL) ============ */

  function crearModalEdicionFoto() {
    if (document.getElementById('modalEdicionFoto')) return;

    const modal = document.createElement('div');
    modal.id = 'modalEdicionFoto';
    modal.className = 'modal-edicion';
    modal.style.display = 'none';
    modal.innerHTML = `
      <div class="modal-edicion-contenido">
        <button class="cerrar-modal-edicion" id="cerrarModalEdicionFoto">&times;</button>
        <h3>Editar Foto</h3>
        <form id="formEdicionFoto">
          <input type="hidden" id="editFotoId" name="id">
          <div class="form-group">
            <label for="editFotoTitulo">Título</label>
            <input type="text" id="editFotoTitulo" name="titulo" required>
          </div>
          <div class="form-group">
            <label for="editFotoDescripcion">Descripción</label>
            <textarea id="editFotoDescripcion" name="descripcion" rows="2"></textarea>
          </div>
          <div class="form-group">
            <label for="editFotoColor">Color predominante</label>
            <input type="text" id="editFotoColor" name="color_predominante">
          </div>
          <div class="form-group">
            <label for="editFotoNumeroMoto">Número de moto</label>
            <input type="text" id="editFotoNumeroMoto" name="numero_moto">
          </div>
          <div class="form-group">
            <label for="editFotoNumeroPiloto">Número de piloto</label>
            <input type="text" id="editFotoNumeroPiloto" name="numero_piloto">
          </div>
          <div class="form-group">
            <label for="editFotoFecha">Fecha de la foto</label>
            <input type="date" id="editFotoFecha" name="fecha_foto">
          </div>
          <div class="form-group">
            <label for="editFotoEvento">Evento</label>
            <select id="editFotoEvento" name="evento_id">
              <option value="">Sin evento</option>
            </select>
          </div>
          <div class="form-group">
            <label for="editFotoPrecio">Precio (COP)</label>
            <input type="number" id="editFotoPrecio" name="precio" min="0" step="0.01">
          </div>
          <div id="mensajeEdicionFoto" class="alerta" style="display:none; margin-top:var(--space-3);"></div>
          <div style="display:flex; gap:var(--space-3); margin-top:var(--space-5); justify-content:flex-end;">
            <button type="button" id="btnCancelarEdicionFoto" class="btn btn--ghost">Cancelar</button>
            <button type="submit" class="btn btn--primary">Guardar cambios</button>
          </div>
        </form>
      </div>
    `;
    document.body.appendChild(modal);

    // Cargar eventos en el select
    async function cargarEventosModal() {
      const select = document.getElementById('editFotoEvento');
      if (!select) return;
      try {
        const resp = await fetch('php/get_events.php');
        const datos = await resp.json();
        if (datos.exito) {
          datos.eventos.forEach((e) => {
            const op = document.createElement('option');
            op.value = e.id;
            op.textContent = e.nombre + ' (' + e.fecha + ')';
            select.appendChild(op);
          });
        }
      } catch (err) {
        console.error(err);
      }
    }
    cargarEventosModal();

    // Cerrar modal
    document.getElementById('cerrarModalEdicionFoto').addEventListener('click', cerrarModalEdicionFoto);
    document.getElementById('btnCancelarEdicionFoto').addEventListener('click', cerrarModalEdicionFoto);
    modal.addEventListener('click', function (e) {
      if (e.target === modal) cerrarModalEdicionFoto();
    });

    // Enviar formulario de edición
    document.getElementById('formEdicionFoto').addEventListener('submit', async function (e) {
      e.preventDefault();
      const btnSubmit = this.querySelector('button[type="submit"]');
      const textoOriginal = btnSubmit.textContent;
      btnSubmit.textContent = 'Guardando...';
      btnSubmit.disabled = true;
      const mensajeDiv = document.getElementById('mensajeEdicionFoto');
      mensajeDiv.style.display = 'none';

      const datos = {
        id: parseInt(document.getElementById('editFotoId').value),
        titulo: document.getElementById('editFotoTitulo').value,
        descripcion: document.getElementById('editFotoDescripcion').value,
        color_predominante: document.getElementById('editFotoColor').value,
        numero_moto: document.getElementById('editFotoNumeroMoto').value,
        numero_piloto: document.getElementById('editFotoNumeroPiloto').value,
        fecha_foto: document.getElementById('editFotoFecha').value || null,
        evento_id: document.getElementById('editFotoEvento').value || null,
        precio: document.getElementById('editFotoPrecio').value || 0,
        csrf_token: csrfToken
      };

      try {
        const respuesta = await fetch('php/edit_photo.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          credentials: 'same-origin',
          body: JSON.stringify(datos)
        });
        const resultado = await respuesta.json();

        if (respuesta.ok && resultado.exito) {
          cerrarModalEdicionFoto();
          cargarFotos();
        } else {
          mensajeDiv.textContent = resultado.mensaje || 'Error al editar la foto.';
          mensajeDiv.className = 'alerta alerta-error';
          mensajeDiv.style.display = 'block';
        }
      } catch (err) {
        mensajeDiv.textContent = 'Error de conexión.';
        mensajeDiv.className = 'alerta alerta-error';
        mensajeDiv.style.display = 'block';
      } finally {
        btnSubmit.disabled = false;
        btnSubmit.textContent = textoOriginal;
      }
    });
  }

  function abrirModalEdicionFoto(foto) {
    crearModalEdicionFoto();
    const modal = document.getElementById('modalEdicionFoto');
    document.getElementById('editFotoId').value = foto.id;
    document.getElementById('editFotoTitulo').value = foto.titulo || '';
    document.getElementById('editFotoDescripcion').value = foto.descripcion || '';
    document.getElementById('editFotoColor').value = foto.color_predominante || '';
    document.getElementById('editFotoNumeroMoto').value = foto.numero_moto || '';
    document.getElementById('editFotoNumeroPiloto').value = foto.numero_piloto || '';
    document.getElementById('editFotoFecha').value = foto.fecha_foto || '';
    document.getElementById('editFotoEvento').value = foto.evento_id || '';
    document.getElementById('editFotoPrecio').value = foto.precio || '';
    modal.style.display = 'flex';
  }

  function cerrarModalEdicionFoto() {
    const modal = document.getElementById('modalEdicionFoto');
    if (modal) modal.style.display = 'none';
  }

  /* ============ ELIMINACIÓN DE FOTOS ============ */

  async function eliminarFoto(id) {
    if (!confirm('¿Está seguro de que desea eliminar esta foto? Esta acción no se puede deshacer.')) return;

    try {
      const respuesta = await fetch('php/delete_photo.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        credentials: 'same-origin',
        body: JSON.stringify({ id, csrf_token: csrfToken })
      });
      const resultado = await respuesta.json();

      if (respuesta.ok && resultado.exito) {
        cargarFotos();
      } else {
        alert(resultado.mensaje || 'No se pudo eliminar la foto.');
      }
    } catch (err) {
      alert('Error de conexión.');
    }
  }

  async function cargarOpcionesFiltros() {
    try {
      // Colores
      if (selectColor) {
        const colores = await obtenerValoresUnicos('color_predominante');
        llenarSelect(selectColor, colores);
      }
      // Números de moto
      if (selectMoto) {
        const motos = await obtenerValoresUnicos('numero_moto');
        llenarSelect(selectMoto, motos);
      }
      // Números de piloto
      if (selectPiloto) {
        const pilotos = await obtenerValoresUnicos('numero_piloto');
        llenarSelect(selectPiloto, pilotos);
      }
      // Eventos
      if (selectEvento) {
        const resp = await fetch('php/get_events.php', { credentials: 'same-origin' });
        const datos = await resp.json();
        llenarSelect(selectEvento, datos.eventos || [], 'nombre', 'id');
      }
    } catch (e) {
      console.error('Error cargando opciones de filtro:', e);
    }
  }

  async function obtenerValoresUnicos(campo) {
    const resp = await fetch('php/get_photos.php', { credentials: 'same-origin' });
    const datos = await resp.json();
    const valores = [...new Set((datos.fotos || []).map((f) => f[campo]).filter(Boolean))];
    return valores.sort();
  }

  function llenarSelect(select, items, labelField, valueField) {
    select.innerHTML = '<option value="">Seleccionar...</option>';
    items.forEach((item) => {
      const opcion = document.createElement('option');
      opcion.value = valueField ? item[valueField] : item;
      opcion.textContent = labelField ? item[labelField] : item;
      select.appendChild(opcion);
    });
  }

  // Cargar al inicio
  document.addEventListener('DOMContentLoaded', async function () {
    await verificarSesion();

    await cargarOpcionesFiltros();

    if (Object.keys(filtrosIniciales).length > 0) {
      btnFiltros.forEach((btn) => {
        const tipo = btn.dataset.tipo;
        const val = btn.dataset.valor;
        if (filtrosIniciales[tipo] && filtrosIniciales[tipo] === val) {
          btn.classList.add('activo');
        }
      });
      if (selectColor && filtrosIniciales.color) selectColor.value = filtrosIniciales.color;
      if (selectMoto && filtrosIniciales.numero_moto) selectMoto.value = filtrosIniciales.numero_moto;
      if (selectPiloto && filtrosIniciales.numero_piloto) selectPiloto.value = filtrosIniciales.numero_piloto;
      if (selectFecha && filtrosIniciales.fecha) selectFecha.value = filtrosIniciales.fecha;
      if (selectEvento && filtrosIniciales.evento) selectEvento.value = filtrosIniciales.evento;
    }

    cargarFotos(filtrosIniciales);
  });

})();