/**
 * Interfaz de subida de fotos para la fotógrafa - JENI HDZ
 * Drag & drop, vista previa y envío a PHP con marca de agua
 */

(function () {
  'use strict';

  const dropZone = document.getElementById('dropZone');
  const inputArchivo = document.getElementById('inputArchivo');
  const btnSeleccionar = document.getElementById('btnSeleccionar');
  const vistaPrevia = document.getElementById('vistaPrevia');
  const formSubida = document.getElementById('formSubida');
  const mensajeSubida = document.getElementById('mensajeSubida');

  let archivosSeleccionados = [];

  // Abrir selector de archivo
  if (btnSeleccionar) {
    btnSeleccionar.addEventListener('click', () => inputArchivo.click());
  }

  if (inputArchivo) {
    inputArchivo.addEventListener('change', function () {
      manejarArchivos(this.files);
    });
  }

  // Drag & drop
  if (dropZone) {
    ['dragenter', 'dragover'].forEach((tipo) => {
      dropZone.addEventListener(tipo, (e) => {
        e.preventDefault();
        dropZone.classList.add('drag-over');
      });
    });

    ['dragleave', 'drop'].forEach((tipo) => {
      dropZone.addEventListener(tipo, (e) => {
        e.preventDefault();
        dropZone.classList.remove('drag-over');
      });
    });

    dropZone.addEventListener('drop', (e) => {
      manejarArchivos(e.dataTransfer.files);
    });

    dropZone.addEventListener('click', () => inputArchivo.click());
  }

  function manejarArchivos(files) {
    const nuevos = Array.from(files).filter((f) => {
      const ext = f.name.split('.').pop().toLowerCase();
      return ['jpg', 'jpeg', 'png', 'webp'].includes(ext);
    });

    archivosSeleccionados = [...archivosSeleccionados, ...nuevos];
    renderizarPrevisualizaciones();
  }

  function renderizarPrevisualizaciones() {
    if (!vistaPrevia) return;
    vistaPrevia.innerHTML = '';

    archivosSeleccionados.forEach((archivo, index) => {
      const url = URL.createObjectURL(archivo);
      const div = document.createElement('div');
      div.className = 'miniatura-subida';
      div.innerHTML = `
        <img src="${url}" alt="Vista previa" loading="lazy">
        <button type="button" onclick="eliminarPrevia(${index})" style="position:absolute; top:4px; right:4px; background:#ef4444; color:#fff; border:none; border-radius:4px; width:24px; height:24px; cursor:pointer;">×</button>
      `;
      vistaPrevia.appendChild(div);
    });
  }

  window.eliminarPrevia = function (index) {
    archivosSeleccionados.splice(index, 1);
    renderizarPrevisualizaciones();
  };

  // Envío del formulario
  if (formSubida) {
    formSubida.addEventListener('submit', async function (e) {
      e.preventDefault();

      if (archivosSeleccionados.length === 0) {
        mostrarMensaje('Seleccione al menos una imagen', 'error');
        return;
      }

      const btn = formSubida.querySelector('button[type="submit"]');
      const textoOriginal = btn.textContent;
      btn.textContent = 'Subiendo...';
      btn.disabled = true;

      let exitosas = 0;
      let fallidas = 0;
      const errores = [];

      for (const archivo of archivosSeleccionados) {
        const formData = new FormData();
        formData.append('foto', archivo);
        formData.append('titulo', document.getElementById('titulo').value || archivo.name);
        formData.append('descripcion', document.getElementById('descripcion').value || '');
        formData.append('color_predominante', document.getElementById('color').value || '');
        formData.append('numero_moto', document.getElementById('numero_moto').value || '');
        formData.append('numero_piloto', document.getElementById('numero_piloto').value || '');
        formData.append('fecha_foto', document.getElementById('fecha_foto').value || '');
        const eventoVal = document.getElementById('evento_id').value;
        if (eventoVal) {
          formData.append('evento_id', eventoVal);
        }
        formData.append('precio', document.getElementById('precio').value || 0);

        try {
          const respuesta = await fetch('php/upload.php', {
            method: 'POST',
            body: formData
          });
          const contenido = await respuesta.text();
          let datos = null;
          try {
            datos = JSON.parse(contenido);
          } catch (error) {
            console.error('Respuesta no válida del servidor al subir la foto:', contenido);
          }

          if (respuesta.ok && datos?.exito === true) {
            exitosas++;
          } else {
            fallidas++;
            errores.push(
              typeof datos?.mensaje === 'string'
                ? datos.mensaje
                : `Respuesta no válida del servidor (HTTP ${respuesta.status})`
            );
          }
        } catch (err) {
          fallidas++;
          console.error('Error al enviar la foto:', err);
          errores.push('No se pudo conectar con el servidor. Verifica tu conexión e inténtalo de nuevo.');
        }
      }

      btn.textContent = textoOriginal;
      btn.disabled = false;

      const detalleError = errores.length > 0
        ? ` Detalle: ${errores[0]}${errores.length > 1 ? ` (y ${errores.length - 1} más)` : ''}`
        : '';
      mostrarMensaje(
        `Subida completada: ${exitosas} exitosa(s), ${fallidas} fallida(s).${detalleError}`,
        exitosas > 0 ? 'exito' : 'error'
      );

      if (exitosas > 0) {
        archivosSeleccionados = [];
        renderizarPrevisualizaciones();
        formSubida.reset();
      }
    });
  }

  function mostrarMensaje(texto, tipo) {
    if (!mensajeSubida) return;
    mensajeSubida.textContent = texto;
    mensajeSubida.className = 'alerta alerta-' + tipo;
    mensajeSubida.style.display = 'block';
    setTimeout(() => { mensajeSubida.style.display = 'none'; }, 6000);
  }

  // Cargar eventos en el select
  async function cargarEventos() {
    const select = document.getElementById('evento_id');
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

  document.addEventListener('DOMContentLoaded', cargarEventos);

})();
