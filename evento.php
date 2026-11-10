<?php
/**
 * Detalle de Evento - JENI HDZ
 * Muestra la información de un evento y su galería de fotos asociadas
 */
require_once __DIR__ . '/php/config.php';
$titulo_pagina = 'Evento | JENI HDZ';
$meta_descripcion = 'Detalle de evento de moto velocidad. Fotografías, fecha, circuito y galería de imágenes.';

// Obtener ID del evento
$evento_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($evento_id <= 0) {
    header('Location: eventos.php');
    exit;
}

// Cargar evento
$evento = null;
try {
    $pdo = db_connect();
    $stmt = $pdo->prepare("SELECT id, nombre, fecha, circuito, pais, descripcion, imagen_portada, activo FROM eventos WHERE id = ? LIMIT 1");
    $stmt->execute([$evento_id]);
    $evento = $stmt->fetch();
} catch (Exception $e) {
    error_log("Error cargando evento: " . $e->getMessage());
}

if (!$evento || !$evento['activo']) {
    header('Location: eventos.php');
    exit;
}

require_once 'includes/header.php';

$esFotografa = !empty($_SESSION['usuario_id']) && in_array($_SESSION['usuario_rol'] ?? '', ['fotografa', 'admin'], true);
?>

<section class="section" style="padding-top:calc(var(--space-12) + 76px);">
  <div class="container">
    <div style="margin-bottom: var(--space-4);">
      <a href="eventos.php" class="btn btn--ghost" style="font-size:0.9rem;">← Volver a eventos</a>
    </div>
    <!-- Cabecera del evento -->
    <div class="evento-detalle-header">
      <div class="evento-detalle-imagen">
        <img src="<?php echo !empty($evento['imagen_portada']) ? 'assets/uploads/' . htmlspecialchars($evento['imagen_portada']) : 'assets/images/evento-' . $evento['id'] . '.jpg'; ?>" 
             alt="<?php echo htmlspecialchars($evento['nombre']); ?>" loading="lazy"
             onerror="this.src='https://images.unsplash.com/photo-1577003724279-73e46c3b6ec0?w=800&q=80'">
      </div>
      <div class="evento-detalle-info">
        <h2 class="section-title" style="text-align:left; width:auto; margin:0 0 var(--space-3) 0;">
          <?php echo htmlspecialchars($evento['nombre']); ?>
        </h2>
        <div class="evento-detalle-meta">
          <span class="evento-detalle-badge"><?php echo htmlspecialchars($evento['pais']); ?></span>
          <span>📅 <?php echo date('d \d\e F \d\e Y', strtotime($evento['fecha'])); ?></span>
          <span>🏁 <?php echo htmlspecialchars($evento['circuito']); ?></span>
        </div>
        <p class="evento-detalle-descripcion"><?php echo htmlspecialchars($evento['descripcion']); ?></p>
        <?php if ($esFotografa): ?>
        <div class="evento-detalle-acciones">
          <button class="btn btn--primary" id="btnEditarEventoDetalle" data-id="<?php echo $evento['id']; ?>">✏️ Editar evento</button>
          <button class="btn btn--ghost" id="btnAgregarFotoEvento" data-id="<?php echo $evento['id']; ?>">+ Agregar foto</button>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Subcarpetas del evento -->
    <?php
    // Cargar subcarpetas del evento
    $subcarpetas = [];
    try {
        $stmtSub = $pdo->prepare("SELECT id, nombre, descripcion FROM subcarpetas WHERE evento_id = ? AND activo = 1 ORDER BY nombre ASC");
        $stmtSub->execute([$evento_id]);
        $subcarpetas = $stmtSub->fetchAll();
    } catch (Exception $e) {}
    ?>

    <?php if (count($subcarpetas) > 0): ?>
    <h3 class="evento-galeria-titulo">Categorías del evento</h3>
    <div class="subcarpetas-grid" id="subcarpetasGrid">
      <button class="subcarpeta-btn activo" data-subcarpeta="" type="button">Todas las fotos</button>
      <?php foreach ($subcarpetas as $sub): ?>
      <button class="subcarpeta-btn" data-subcarpeta="<?php echo $sub['id']; ?>" type="button">
        <?php echo htmlspecialchars($sub['nombre']); ?>
      </button>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if ($esFotografa): ?>
    <!-- Gestión de subcarpetas (solo fotógrafa) -->
    <div class="gestion-subcarpetas" id="gestionSubcarpetas">
      <button class="btn btn--ghost" id="btnNuevaSubcarpeta" type="button">+ Nueva subcarpeta</button>
      <div id="formSubcarpeta" style="display:none; margin-top:var(--space-4); background:var(--color-surface); padding:var(--space-4); border-radius:var(--radius-md); border:1px solid var(--color-border);">
        <div class="form-group">
          <label for="nuevaSubcarpetaNombre">Nombre de la subcarpeta</label>
          <input type="text" id="nuevaSubcarpetaNombre" placeholder="Ej: Manga 1, Pits, Premiación">
        </div>
        <div class="form-group">
          <label for="nuevaSubcarpetaDescripcion">Descripción (opcional)</label>
          <input type="text" id="nuevaSubcarpetaDescripcion" placeholder="Descripción breve">
        </div>
        <div style="display:flex; gap:var(--space-3);">
          <button class="btn btn--primary" id="btnGuardarSubcarpeta" type="button">Crear subcarpeta</button>
          <button class="btn btn--ghost" id="btnCancelarSubcarpeta" type="button">Cancelar</button>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <!-- Galería de fotos del evento -->
    <h3 class="evento-galeria-titulo">Fotos del evento</h3>
    <div class="galeria-grid" id="galeriaGrid" data-evento-id="<?php echo $evento_id; ?>"></div>
    <div id="contenedorVacio" class="vacio" style="display:none;">Este evento aún no tiene fotos asignadas.</div>
  </div>
</section>

<script src="js/gallery.js?v=4"></script>
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Event",
  "name": "<?php echo addslashes($evento['nombre']); ?>",
  "startDate": "<?php echo $evento['fecha']; ?>",
  "location": {
    "@type": "Place",
    "name": "<?php echo addslashes($evento['circuito']); ?>",
    "address": {
      "@type": "PostalAddress",
      "addressCountry": "<?php echo addslashes($evento['pais']); ?>"
    }
  },
  "description": "<?php echo addslashes($evento['descripcion']); ?>",
  "organizer": {
    "@type": "Person",
    "name": "JENI HDZ",
    "email": "jeni@jenihdz.com"
  }
}
</script>

<script>
// Token CSRF
const csrfTokenEvento = '<?php echo generar_token_csrf(); ?>';
const esFotografaEvento = <?php echo $esFotografa ? 'true' : 'false'; ?>;
const eventoIdActual = <?php echo $evento_id; ?>;

document.addEventListener('DOMContentLoaded', function() {
  // Botón editar evento (redirige a eventos.php con modal)
  const btnEditar = document.getElementById('btnEditarEventoDetalle');
  if (btnEditar) {
    btnEditar.addEventListener('click', function() {
      window.location.href = 'eventos.php?editar=' + this.getAttribute('data-id');
    });
  }

  // Botón agregar foto
  const btnAgregarFoto = document.getElementById('btnAgregarFotoEvento');
  if (btnAgregarFoto) {
    btnAgregarFoto.addEventListener('click', function() {
      const id = this.getAttribute('data-id');
      window.location.href = 'subir-fotos.php?evento_id=' + id;
    });
  }

  // ====== FILTRAR POR SUBCARPETA ======
  const subcarpetaBtns = document.querySelectorAll('.subcarpeta-btn');
  subcarpetaBtns.forEach(function(btn) {
    btn.addEventListener('click', function() {
      // Cambiar activo
      subcarpetaBtns.forEach(function(b) { b.classList.remove('activo'); });
      this.classList.add('activo');

      const subcarpetaId = this.getAttribute('data-subcarpeta');
      const galeriaGrid = document.getElementById('galeriaGrid');
      const contenedorVacio = document.getElementById('contenedorVacio');

      if (!galeriaGrid) return;

      // Construir URL con filtro
      let url = 'php/get_photos.php?evento=' + eventoIdActual;
      if (subcarpetaId) {
        url += '&subcarpeta=' + subcarpetaId;
      }

      // Mostrar cargando
      galeriaGrid.innerHTML = '<div class="vacio" style="grid-column:1/-1; text-align:center; padding:2rem; color:var(--color-text-muted);">Cargando fotos...</div>';

      fetch(url, { credentials: 'same-origin' })
        .then(function(resp) { return resp.json(); })
        .then(function(datos) {
          if (datos.exito && datos.fotos && datos.fotos.length > 0) {
            // Renderizar fotos usando la misma estructura que gallery.js
            galeriaGrid.innerHTML = '';
            if (contenedorVacio) contenedorVacio.style.display = 'none';

            datos.fotos.forEach(function(foto) {
              var item = document.createElement('div');
              item.className = 'galeria-item';
              item.innerHTML =
                '<img src="' + foto.url_imagen + '" alt="' + (foto.titulo || 'Foto') + '" loading="lazy">' +
                '<div class="watermark-overlay">JENI HDZ</div>' +
                '<div class="etiqueta-info">' +
                  '<strong>' + (foto.titulo || 'Sin título') + '</strong><br>' +
                  '<small>Moto #' + (foto.numero_moto || '-') + ' | Piloto #' + (foto.numero_piloto || '-') + ' | ' + (foto.fecha_foto || '-') + '</small>' +
                '</div>';
              galeriaGrid.appendChild(item);
            });
          } else {
            galeriaGrid.innerHTML = '';
            if (contenedorVacio) {
              contenedorVacio.style.display = 'block';
              contenedorVacio.textContent = 'No hay fotos en esta categoría.';
            }
          }
        })
        .catch(function(err) {
          console.error('Error cargando fotos:', err);
          galeriaGrid.innerHTML = '<div class="vacio" style="grid-column:1/-1; text-align:center; padding:2rem; color:var(--color-error);">Error al cargar las fotos.</div>';
        });
    });
  });

  // ====== GESTIÓN DE SUBCARPETAS ======
  const btnNuevaSubcarpeta = document.getElementById('btnNuevaSubcarpeta');
  const formSubcarpeta = document.getElementById('formSubcarpeta');
  const btnGuardarSubcarpeta = document.getElementById('btnGuardarSubcarpeta');
  const btnCancelarSubcarpeta = document.getElementById('btnCancelarSubcarpeta');

  if (btnNuevaSubcarpeta && formSubcarpeta) {
    btnNuevaSubcarpeta.addEventListener('click', function() {
      formSubcarpeta.style.display = 'block';
      document.getElementById('nuevaSubcarpetaNombre').focus();
    });
  }

  if (btnCancelarSubcarpeta && formSubcarpeta) {
    btnCancelarSubcarpeta.addEventListener('click', function() {
      formSubcarpeta.style.display = 'none';
    });
  }

  if (btnGuardarSubcarpeta) {
    btnGuardarSubcarpeta.addEventListener('click', function() {
      var nombre = document.getElementById('nuevaSubcarpetaNombre').value.trim();
      var descripcion = document.getElementById('nuevaSubcarpetaDescripcion').value.trim();

      if (!nombre) {
        alert('Ingresa un nombre para la subcarpeta.');
        return;
      }

      this.disabled = true;
      this.textContent = 'Creando...';

      var self = this;

      fetch('php/create_subcarpeta.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'same-origin',
        body: JSON.stringify({
          evento_id: eventoIdActual,
          nombre: nombre,
          descripcion: descripcion,
          csrf_token: csrfTokenEvento
        })
      })
      .then(function(resp) { return resp.json(); })
      .then(function(datos) {
        if (datos.exito) {
          location.reload();
        } else {
          alert(datos.mensaje || 'Error al crear la subcarpeta.');
        }
      })
      .catch(function(err) {
        console.error('Error:', err);
        alert('Error de conexión.');
      })
      .finally(function() {
        self.disabled = false;
        self.textContent = 'Crear subcarpeta';
      });
    });
  }
});
</script>

<?php require_once 'includes/footer.php'; ?>