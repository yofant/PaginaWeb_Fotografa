/**
 * Galería - JENI HDZ
 * Carga fotos desde PHP con filtros: color, número de moto, número de piloto, fecha, evento
 */

(function () {
  'use strict';

  const galeriaGrid = document.getElementById('galeriaGrid');
  const contenedorVacio = document.getElementById('contenedorVacio');
  const btnFiltros = document.querySelectorAll('.filtro-btn');
  const selectColor = document.getElementById('filtroColor');
  const selectMoto = document.getElementById('filtroMoto');
  const selectPiloto = document.getElementById('filtroPiloto');
  const selectFecha = document.getElementById('filtroFecha');
  const selectEvento = document.getElementById('filtroEvento');
  const btnLimpiar = document.getElementById('btnLimpiarFiltros');

  let fotos = [];

  async function cargarFotos(params = {}) {
    if (!galeriaGrid) return;

    try {
      const query = new URLSearchParams(params).toString();
      const url = 'php/get_photos.php' + (query ? '?' + query : '');

      const respuesta = await fetch(url);
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
    contenedorVacio.style.display = lista.length ? 'none' : 'block';

    lista.forEach((foto) => {
      const item = document.createElement('div');
      item.className = 'galeria-item';
      item.dataset.id = foto.id;
      item.dataset.color = foto.color_predominante || '';
      item.dataset.moto = foto.numero_moto || '';
      item.dataset.piloto = foto.numero_piloto || '';
      item.dataset.fecha = foto.fecha_foto || '';
      item.dataset.evento = foto.evento_id || '';

      item.innerHTML = `
        <img src="${escapeHtml(foto.url_imagen)}" alt="${escapeHtml(foto.titulo || 'Foto moto velocidad')}" loading="lazy">
        <div class="watermark-overlay">JENI HDZ</div>
        <div class="etiqueta-info">
          <strong>${escapeHtml(foto.titulo || 'Sin título')}</strong><br>
          <small>Moto #${escapeHtml(foto.numero_moto || '-')} | Piloto #${escapeHtml(foto.numero_piloto || '-')} | ${escapeHtml(foto.fecha_foto || '-')}</small>
        </div>
        <button class="btn-agregar" data-id="${foto.id}" onclick="agregarFotoDesdeGaleria(${foto.id})">+</button>
      `;
      galeriaGrid.appendChild(item);
    });

    // Vincular clic en la tarjeta para agregar (excepto el botón +)
    galeriaGrid.querySelectorAll('.galeria-item').forEach((el) => {
      el.addEventListener('click', function (e) {
        if (e.target.classList.contains('btn-agregar') || e.target.closest('.btn-agregar')) return;
        const id = parseInt(this.dataset.id);
        const foto = fotos.find((f) => f.id === id);
        if (foto) window.agregarAlCarrito(foto);
      });
    });
  }

  function actualizarContadorFiltros(total) {
    // opcional: mostrar cantidad de fotos filtradas
  }

  function aplicarFiltros() {
    const params = {};
    const valores = {};

    // Botones de color rápidos
    btnFiltros.forEach((btn) => {
      if (btn.classList.contains('activo')) {
        const tipo = btn.dataset.tipo;
        const val = btn.dataset.valor;
        if (tipo === 'color') valores.color = val;
        if (tipo === 'moto') valores.numero_moto = val;
        if (tipo === 'piloto') valores.numero_piloto = val;
      }
    });

    // Selects
    if (selectColor && selectColor.value) valores.color = selectColor.value;
    if (selectMoto && selectMoto.value) valores.numero_moto = selectMoto.value;
    if (selectPiloto && selectPiloto.value) valores.numero_piloto = selectPiloto.value;
    if (selectFecha && selectFecha.value) valores.fecha = selectFecha.value;
    if (selectEvento && selectEvento.value) valores.evento = selectEvento.value;

    cargarFotos(valores);
  }

  // Eventos filtros rápidos
  btnFiltros.forEach((btn) => {
    btn.addEventListener('click', function () {
      // Toggle solo dentro del mismo grupo (data-tipo)
      const tipo = this.dataset.tipo;
      btnFiltros.forEach((b) => {
        if (b.dataset.tipo === tipo) b.classList.remove('activo');
      });
      this.classList.toggle('activo');
      aplicarFiltros();
    });
  });

  // Eventos selects
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

  // Cargar al inicio
  document.addEventListener('DOMContentLoaded', function () {
    // Cargar opciones de los selects dinámicamente
    cargarOpcionesFiltros();
    cargarFotos();
  });

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
        const resp = await fetch('php/get_events.php');
        const datos = await resp.json();
        llenarSelect(selectEvento, datos.eventos || [], 'nombre', 'id');
      }
    } catch (e) {
      console.error('Error cargando opciones de filtro:', e);
    }
  }

  async function obtenerValoresUnicos(campo) {
    const resp = await fetch('php/get_photos.php');
    const datos = await resp.json();
    const valores = [...new Set((datos.fotos || []).map((f) => f[campo]).filter(Boolean))];
    return valores.sort();
  }

  function llenarSelect(select, items, labelField = null, valueField = null) {
    select.innerHTML = '<option value="">Seleccionar...</option>';
    items.forEach((item) => {
      const opcion = document.createElement('option');
      opcion.value = valueField ? item[valueField] : item;
      opcion.textContent = labelField ? item[labelField] : item;
      select.appendChild(opcion);
    });
  }

  function escapeHtml(texto) {
    if (!texto) return '';
    const div = document.createElement('div');
    div.textContent = texto;
    return div.innerHTML;
  }

})();
