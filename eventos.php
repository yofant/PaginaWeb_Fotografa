<?php
$titulo_pagina = 'Eventos | JENI HDZ';
$meta_descripcion = 'Eventos de moto velocidad cubiertos por JENI HDZ. Consulta fechas, circuitos y galerías de fotos de cada carrera.';
require_once 'includes/header.php';

// Verificar si el usuario es fotógrafa/admin para mostrar opciones de administración
$esFotografa = !empty($_SESSION['usuario_id']) && in_array($_SESSION['usuario_rol'] ?? '', ['fotografa', 'admin'], true);
?>

<section class="section" style="padding-top:calc(var(--space-12) + 76px);">
  <div class="container">
    <h2 class="section-title">Eventos <span class="titulo-morado">de Moto Velocidad</span></h2>
    <p class="section-subtitle">Cada carrera capturada con precisión. Consulta nuestros eventos registrados y las fechas disponibles.</p>
    

    <div class="eventos-header">
      <?php if ($esFotografa): ?>
      <button class="btn btn--primary" id="btnNuevoEvento" title="Nuevo evento">+ Nuevo evento</button>
      <?php endif; ?>
    </div>

    <div class="eventos-grid" id="eventosGrid">
      <?php
      try {
          $pdo = db_connect();
          // Verificar si la columna imagen_portada existe
          $tiene_imagen_portada = false;
          try {
              $stmtCheck = $pdo->query("SHOW COLUMNS FROM eventos LIKE 'imagen_portada'");
              $tiene_imagen_portada = $stmtCheck->rowCount() > 0;
          } catch (Exception $e) {
              $tiene_imagen_portada = false;
          }
          if ($tiene_imagen_portada) {
              $stmt = $pdo->query("SELECT id, nombre, fecha, circuito, pais, descripcion, imagen_portada FROM eventos WHERE activo = 1 ORDER BY fecha DESC");
          } else {
              $stmt = $pdo->query("SELECT id, nombre, fecha, circuito, pais, descripcion FROM eventos WHERE activo = 1 ORDER BY fecha DESC");
          }
          $eventos = $stmt->fetchAll();
          foreach ($eventos as $ev):
      ?>
      <div class="evento-card" data-id="<?php echo $ev['id']; ?>" style="cursor:pointer;" onclick="if(!event.target.closest('.evento-acciones') && !event.target.closest('.btn-agregar-foto-evento')) window.location.href='evento.php?id=<?php echo $ev['id']; ?>'">
        <div class="evento-imagen">
          <img src="<?php echo !empty($ev['imagen_portada'] ?? '') ? 'assets/uploads/' . htmlspecialchars($ev['imagen_portada']) : 'assets/images/evento-' . $ev['id'] . '.jpg'; ?>" 
               alt="<?php echo htmlspecialchars($ev['nombre']); ?>" loading="lazy" 
               onerror="this.src='https://images.unsplash.com/photo-1577003724279-73e46c3b6ec0?w=600&q=80'">
          <span class="evento-badge"><?php echo htmlspecialchars($ev['pais']); ?></span>
          <?php if ($esFotografa): ?>
          <div class="evento-acciones">
            <button class="btn-editar-evento" data-id="<?php echo $ev['id']; ?>" title="Editar">✏️</button>
            <button class="btn-eliminar-evento" data-id="<?php echo $ev['id']; ?>" title="Eliminar">🗑️</button>
          </div>
          <?php endif; ?>
        </div>
        <div class="evento-info">
          <h3><?php echo htmlspecialchars($ev['nombre']); ?></h3>
          <div class="evento-fecha">
            <span><?php echo date('d \d\e F \d\e Y', strtotime($ev['fecha'])); ?></span>
          </div>
          <div class="evento-lugar">
            <span><?php echo htmlspecialchars($ev['circuito']); ?>, <?php echo htmlspecialchars($ev['pais']); ?></span>
          </div>
          <p class="evento-descripcion"><?php echo htmlspecialchars($ev['descripcion']); ?></p>
        </div>
      </div>
      <?php
          endforeach;
          if (empty($eventos)) {
              echo '<p style="color:var(--color-text-muted); text-align:center;">No hay eventos registrados.</p>';
          }
      } catch (Exception $e) {
          echo '<p style="color:var(--color-error); text-align:center;">Error al cargar eventos.</p>';
      }
      ?>
    </div>
  </div>
</section>

<?php if ($esFotografa): ?>
<!-- Modal: Crear / Editar Evento -->
<div id="modalEvento" class="modal-evento" style="display:none;">
  <div class="modal-evento-contenido">
    <button class="cerrar-modal-evento" id="cerrarModalEvento">&times;</button>
    <h3 id="tituloModalEvento">Nuevo Evento</h3>
    <form id="formEvento">
      <input type="hidden" id="eventoId" name="id">
      <input type="hidden" id="eventoAccion" name="accion" value="crear">
      
      <div class="form-group">
        <label for="eventoNombre">Nombre del evento</label>
        <input type="text" id="eventoNombre" name="nombre" required placeholder="Ej: Gran Premio de México 2026">
      </div>
      
      <div class="form-group">
        <label for="eventoFecha">Fecha</label>
        <input type="date" id="eventoFecha" name="fecha" required>
      </div>
      
      <div class="form-group">
        <label for="eventoCircuito">Circuito</label>
        <input type="text" id="eventoCircuito" name="circuito" placeholder="Ej: Autódromo Hermanos Rodríguez">
      </div>
      
      <div class="form-group">
        <label for="eventoPais">País</label>
        <input type="text" id="eventoPais" name="pais" placeholder="Ej: México">
      </div>
      
      <div class="form-group">
        <label for="eventoDescripcion">Descripción</label>
        <textarea id="eventoDescripcion" name="descripcion" rows="3" placeholder="Describe el evento..."></textarea>
      </div>
      
      <div class="form-group">
        <label for="eventoImagenPortada">Imagen de portada</label>
        <input type="file" id="eventoImagenPortada" name="imagen_portada" accept="image/jpeg, image/png, image/webp">
        <input type="hidden" id="eventoImagenPortadaNombre" name="imagen_portada_nombre" value="">
        <div id="vistaPreviaPortada" style="margin-top:var(--space-3);"></div>
        <small style="color:var(--color-text-muted);">Formatos: JPG, PNG, WEBP. Máx. 10 MB.</small>
      </div>
      
      <div class="form-group">
        <label class="checkbox-label" style="display:flex; align-items:center; gap:var(--space-2);">
          <input type="checkbox" id="eventoActivo" name="activo" value="1" checked>
          <span>Evento activo</span>
        </label>
      </div>
      
      <div id="mensajeEvento" class="alerta" style="display:none; margin-top:var(--space-3);"></div>
      
      <div style="display:flex; gap:var(--space-3); margin-top:var(--space-5); justify-content:flex-end;">
        <button type="button" id="btnCancelarEvento" class="btn btn--ghost">Cancelar</button>
        <button type="submit" class="btn btn--primary" id="btnGuardarEvento">Guardar evento</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal: Agregar foto a evento -->
<div id="modalAgregarFotoEvento" class="modal-evento" style="display:none;">
  <div class="modal-evento-contenido">
    <button class="cerrar-modal-evento" id="cerrarModalAgregarFoto">&times;</button>
    <h3 id="tituloModalAgregarFoto">Agregar Foto al Evento</h3>
    <form id="formAgregarFotoEvento">
      <input type="hidden" id="fotoEventoId" name="evento_id">
      
      <div class="form-group">
        <label for="agregarFotoArchivo">Seleccionar imagen</label>
        <input type="file" id="agregarFotoArchivo" name="foto" accept="image/jpeg, image/png, image/webp" required>
      </div>
      
      <div class="form-group">
        <label for="agregarFotoTitulo">Título de la foto</label>
        <input type="text" id="agregarFotoTitulo" name="titulo" required placeholder="Título de la foto">
      </div>
      
      <div class="form-group">
        <label for="agregarFotoDescripcion">Descripción</label>
        <textarea id="agregarFotoDescripcion" name="descripcion" rows="2" placeholder="Describe la foto..."></textarea>
      </div>
      
      <div class="form-group">
        <label for="agregarFotoColor">Color predominante</label>
        <input type="text" id="agregarFotoColor" name="color_predominante" placeholder="Ej: Rojo, Negro">
      </div>
      
      <div class="form-group">
        <label for="agregarFotoNumeroMoto">Número de moto</label>
        <input type="text" id="agregarFotoNumeroMoto" name="numero_moto" placeholder="Ej: 12">
      </div>
      
      <div class="form-group">
        <label for="agregarFotoNumeroPiloto">Número de piloto</label>
        <input type="text" id="agregarFotoNumeroPiloto" name="numero_piloto" placeholder="Ej: 93">
      </div>
      
      <div class="form-group">
        <label for="agregarFotoFecha">Fecha de la foto</label>
        <input type="date" id="agregarFotoFecha" name="fecha_foto">
      </div>
      
      <div class="form-group">
        <label for="agregarFotoPrecio">Precio (COP)</label>
        <input type="number" id="agregarFotoPrecio" name="precio" min="0" step="0.01" placeholder="0.00">
      </div>
      
      <div id="mensajeAgregarFoto" class="alerta" style="display:none; margin-top:var(--space-3);"></div>
      
      <div style="display:flex; gap:var(--space-3); margin-top:var(--space-5); justify-content:flex-end;">
        <button type="button" id="btnCancelarAgregarFoto" class="btn btn--ghost">Cancelar</button>
        <button type="submit" class="btn btn--primary">Subir foto al evento</button>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

<script>
// Token CSRF para eventos
const csrfTokenEventos = '<?php echo generar_token_csrf(); ?>';
const esFotografa = <?php echo $esFotografa ? 'true' : 'false'; ?>;

document.addEventListener('DOMContentLoaded', function() {
  const btnNuevoEvento = document.getElementById('btnNuevoEvento');
  const modalEvento = document.getElementById('modalEvento');
  const cerrarModalEvento = document.getElementById('cerrarModalEvento');
  const formEvento = document.getElementById('formEvento');
  const btnCancelarEvento = document.getElementById('btnCancelarEvento');
  const mensajeEvento = document.getElementById('mensajeEvento');
  const btnGuardarEvento = document.getElementById('btnGuardarEvento');
  const eventosGrid = document.getElementById('eventosGrid');

  // Abrir modal nuevo evento
  if (btnNuevoEvento) {
    btnNuevoEvento.addEventListener('click', function() {
      document.getElementById('tituloModalEvento').textContent = 'Nuevo Evento';
      document.getElementById('eventoAccion').value = 'crear';
      formEvento.reset();
      document.getElementById('eventoId').value = '';
      document.getElementById('eventoActivo').checked = true;
      document.getElementById('eventoImagenPortadaNombre').value = '';
      document.getElementById('vistaPreviaPortada').innerHTML = '';
      document.getElementById('eventoImagenPortada').value = '';
      mensajeEvento.style.display = 'none';
      modalEvento.style.display = 'flex';
    });
  }

  // Cerrar modal evento
  function cerrarModalEventoFn() {
    if (modalEvento) modalEvento.style.display = 'none';
  }
  if (cerrarModalEvento) cerrarModalEvento.addEventListener('click', cerrarModalEventoFn);
  if (btnCancelarEvento) btnCancelarEvento.addEventListener('click', cerrarModalEventoFn);
  if (modalEvento) modalEvento.addEventListener('click', function(e) { if (e.target === modalEvento) cerrarModalEventoFn(); });

  // Editar evento
  eventosGrid.addEventListener('click', function(e) {
    const btnEditar = e.target.closest('.btn-editar-evento');
    const btnEliminar = e.target.closest('.btn-eliminar-evento');

    if (btnEditar) {
      e.preventDefault();
      e.stopPropagation();
      const id = btnEditar.getAttribute('data-id');
      cargarEventoParaEditar(id);
    }

    if (btnEliminar) {
      e.preventDefault();
      e.stopPropagation();
      const id = btnEliminar.getAttribute('data-id');
      eliminarEvento(id);
    }
  });

  async function cargarEventoParaEditar(id) {
    try {
      const respuesta = await fetch('php/get_events.php?id=' + id);
      const datos = await respuesta.json();
      if (datos.exito && datos.eventos && datos.eventos[0]) {
        const ev = datos.eventos[0];
        document.getElementById('tituloModalEvento').textContent = 'Editar Evento';
        document.getElementById('eventoAccion').value = 'editar';
        document.getElementById('eventoId').value = ev.id;
        document.getElementById('eventoNombre').value = ev.nombre;
        document.getElementById('eventoFecha').value = ev.fecha;
        document.getElementById('eventoCircuito').value = ev.circuito || '';
        document.getElementById('eventoPais').value = ev.pais || '';
        document.getElementById('eventoDescripcion').value = ev.descripcion || '';
        document.getElementById('eventoImagenPortadaNombre').value = ev.imagen_portada || '';
        document.getElementById('eventoImagenPortada').value = '';
        const prevPortada = document.getElementById('vistaPreviaPortada');
        if (ev.imagen_portada) {
          prevPortada.innerHTML = '<img src="assets/uploads/' + ev.imagen_portada + '" alt="Portada actual" style="max-width:100%; max-height:200px; border-radius:var(--radius-md); border:2px solid var(--color-border); display:block; margin:0 auto;">';
        } else {
          prevPortada.innerHTML = '';
        }
        document.getElementById('eventoActivo').checked = ev.activo == 1;
        mensajeEvento.style.display = 'none';
        modalEvento.style.display = 'flex';
      }
    } catch (err) {
      console.error(err);
    }
  }

  async function eliminarEvento(id) {
    if (!confirm('¿Está seguro de eliminar este evento? Las fotos asociadas se desvincularán.')) return;
    try {
      const respuesta = await fetch('php/delete_event.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        credentials: 'same-origin',
        body: JSON.stringify({ id, csrf_token: csrfTokenEventos })
      });
      const resultado = await respuesta.json();
      if (respuesta.ok && resultado.exito) {
        location.reload();
      } else {
        alert(resultado.mensaje || 'No se pudo eliminar el evento.');
      }
    } catch (err) {
      alert('Error de conexión.');
    }
  }

  // Guardar evento (crear o editar)
  if (formEvento) {
    formEvento.addEventListener('submit', async function(e) {
    e.preventDefault();
    btnGuardarEvento.textContent = 'Guardando...';
    btnGuardarEvento.disabled = true;
    mensajeEvento.style.display = 'none';

    const accion = document.getElementById('eventoAccion').value;
    const datos = {
      id: document.getElementById('eventoId').value || null,
      nombre: document.getElementById('eventoNombre').value,
      fecha: document.getElementById('eventoFecha').value,
      circuito: document.getElementById('eventoCircuito').value,
      pais: document.getElementById('eventoPais').value,
      descripcion: document.getElementById('eventoDescripcion').value,
      imagen_portada: document.getElementById('eventoImagenPortadaNombre').value,
      activo: document.getElementById('eventoActivo').checked ? 1 : 0,
      csrf_token: csrfTokenEventos
    };

    try {
      const url = accion === 'editar' ? 'php/edit_event.php' : 'php/create_event.php';
      const respuesta = await fetch(url, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        credentials: 'same-origin',
        body: JSON.stringify(datos)
      });
      const resultado = await respuesta.json();

      if (respuesta.ok && resultado.exito) {
        cerrarModalEventoFn();
        location.reload();
      } else {
        mensajeEvento.textContent = resultado.mensaje || 'Error al guardar el evento.';
        mensajeEvento.className = 'alerta alerta-error';
        mensajeEvento.style.display = 'block';
      }
    } catch (err) {
      mensajeEvento.textContent = 'Error de conexión con el servidor.';
      mensajeEvento.className = 'alerta alerta-error';
      mensajeEvento.style.display = 'block';
    } finally {
      btnGuardarEvento.textContent = 'Guardar evento';
      btnGuardarEvento.disabled = false;
    }
  });
  }

  /* ============ AGREGAR FOTO DESDE EVENTOS ============ */
  const modalAgregarFoto = document.getElementById('modalAgregarFotoEvento');
  const cerrarModalAgregarFoto = document.getElementById('cerrarModalAgregarFoto');
  const btnCancelarAgregarFoto = document.getElementById('btnCancelarAgregarFoto');
  const formAgregarFoto = document.getElementById('formAgregarFotoEvento');
  const mensajeAgregarFoto = document.getElementById('mensajeAgregarFoto');

  function cerrarModalAgregarFotoFn() {
    if (modalAgregarFoto) modalAgregarFoto.style.display = 'none';
  }
  if (cerrarModalAgregarFoto) cerrarModalAgregarFoto.addEventListener('click', cerrarModalAgregarFotoFn);
  if (btnCancelarAgregarFoto) btnCancelarAgregarFoto.addEventListener('click', cerrarModalAgregarFotoFn);
  if (modalAgregarFoto) modalAgregarFoto.addEventListener('click', function(e) { if (e.target === modalAgregarFoto) cerrarModalAgregarFotoFn(); });

  // Botón para agregar foto desde el card del evento (agregar después de renderizar)
  function agregarBotonesAgregarFoto() {
    document.querySelectorAll('.evento-card').forEach(card => {
      if (card.querySelector('.btn-agregar-foto-evento')) return;
      const btn = document.createElement('button');
      btn.className = 'btn-agregar-foto-evento';
      btn.textContent = '+ Foto';
      btn.title = 'Agregar foto a este evento';
      btn.style.cssText = 'margin-top:var(--space-3); padding:var(--space-2) var(--space-4); background:var(--color-primary); color:#fff; border:none; border-radius:var(--radius-md); cursor:pointer; font-size:0.85rem;';
      btn.addEventListener('click', function(e) {
        e.stopPropagation();
        const id = card.getAttribute('data-id');
        document.getElementById('fotoEventoId').value = id;
        document.getElementById('tituloModalAgregarFoto').textContent = 'Agregar Foto al Evento #' + id;
        formAgregarFoto.reset();
        mensajeAgregarFoto.style.display = 'none';
        modalAgregarFoto.style.display = 'flex';
      });
      card.appendChild(btn);
    });
  }
  // Ejecutar tras carga inicial
  if (esFotografa) {
    setTimeout(agregarBotonesAgregarFoto, 100);

    // Abrir modal de edición si viene ?editar=X en la URL
    const paramsUrl = new URLSearchParams(window.location.search);
    const editarId = paramsUrl.get('editar');
    if (editarId) {
      cargarEventoParaEditar(editarId);
    }
  }

  if (formAgregarFoto) {
    formAgregarFoto.addEventListener('submit', async function(e) {
    e.preventDefault();
    const btnSubmit = this.querySelector('button[type="submit"]');
    const textoOriginal = btnSubmit.textContent;
    btnSubmit.textContent = 'Subiendo...';
    btnSubmit.disabled = true;
    mensajeAgregarFoto.style.display = 'none';

    const archivo = document.getElementById('agregarFotoArchivo').files[0];
    if (!archivo) {
      mensajeAgregarFoto.textContent = 'Seleccione una imagen.';
      mensajeAgregarFoto.className = 'alerta alerta-error';
      mensajeAgregarFoto.style.display = 'block';
      btnSubmit.disabled = false;
      btnSubmit.textContent = textoOriginal;
      return;
    }

    const formData = new FormData();
    formData.append('foto', archivo);
    formData.append('evento_id', document.getElementById('fotoEventoId').value);
    formData.append('titulo', document.getElementById('agregarFotoTitulo').value);
    formData.append('descripcion', (document.getElementById('agregarFotoDescripcion') || {}).value || '');
    formData.append('color_predominante', document.getElementById('agregarFotoColor').value);
    formData.append('numero_moto', document.getElementById('agregarFotoNumeroMoto').value);
    formData.append('numero_piloto', document.getElementById('agregarFotoNumeroPiloto').value);
    formData.append('fecha_foto', document.getElementById('agregarFotoFecha').value || '');
    formData.append('precio', document.getElementById('agregarFotoPrecio').value || 0);

    try {
      const respuesta = await fetch('php/upload.php', {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
      });
      const resultado = await respuesta.json();

      if (respuesta.ok && resultado.exito) {
        cerrarModalAgregarFotoFn();
        location.reload();
      } else {
        mensajeAgregarFoto.textContent = resultado.mensaje || 'Error al subir la foto.';
        mensajeAgregarFoto.className = 'alerta alerta-error';
        mensajeAgregarFoto.style.display = 'block';
      }
    } catch (err) {
      mensajeAgregarFoto.textContent = 'Error de conexión.';
      mensajeAgregarFoto.className = 'alerta alerta-error';
      mensajeAgregarFoto.style.display = 'block';
    } finally {
      btnSubmit.disabled = false;
      btnSubmit.textContent = textoOriginal;
    }
  });
  }

  /* ============ SUBIDA DE IMAGEN DE PORTADA ============ */
  const inputPortada = document.getElementById('eventoImagenPortada');
  const vistaPreviaPortada = document.getElementById('vistaPreviaPortada');
  const inputPortadaNombre = document.getElementById('eventoImagenPortadaNombre');

  if (inputPortada) {
    inputPortada.addEventListener('change', async function() {
      const archivo = this.files[0];
      if (!archivo) return;

      // Validar extensión
      const ext = archivo.name.split('.').pop().toLowerCase();
      if (!['jpg', 'jpeg', 'png', 'webp'].includes(ext)) {
        alert('Solo se permiten imágenes JPG, PNG o WEBP.');
        this.value = '';
        return;
      }

      // Mostrar preview inmediato
      const url = URL.createObjectURL(archivo);
      vistaPreviaPortada.innerHTML = '<img src="' + url + '" alt="Preview portada" style="max-width:100%; max-height:200px; border-radius:var(--radius-md); border:2px solid var(--color-primary); display:block; margin:0 auto;">';

      // Obtener evento_id actual (si es edición) o null
      const eventoId = document.getElementById('eventoId').value || null;

      const formData = new FormData();
      formData.append('foto', archivo);
      formData.append('titulo', 'Portada evento');
      formData.append('descripcion', 'Imagen de portada de evento');
      formData.append('color_predominante', '');
      formData.append('numero_moto', '');
      formData.append('numero_piloto', '');
      formData.append('fecha_foto', '');
      formData.append('evento_id', eventoId || '');
      formData.append('precio', '0');

      try {
        const respuesta = await fetch('php/upload.php?csrf_token=' + encodeURIComponent(csrfTokenEventos), {
          method: 'POST',
          body: formData,
          credentials: 'same-origin'
        });
        const resultado = await respuesta.json();

        if (respuesta.ok && resultado.exito) {
          // upload.php retorna nombre_archivo y ruta_archivo en la respuesta
          inputPortadaNombre.value = resultado.ruta_archivo || resultado.nombre_archivo || '';
          
        } else {
          alert(resultado.mensaje || 'Error al subir la imagen de portada.');
          vistaPreviaPortada.innerHTML = '';
          this.value = '';
        }
      } catch (err) {
        alert('Error de conexión al subir la portada.');
        vistaPreviaPortada.innerHTML = '';
        this.value = '';
      }
    });
  }
});
</script>

<?php require_once 'includes/footer.php'; ?>