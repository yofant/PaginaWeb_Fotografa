/**
 * ============================================================================
 * JENI HDZ - Fotografía Moto Velocidad
 * Controlador principal para Cloudflare Workers + D1 (SQLite)
 * Arquitectura Zero-Trust: Prepared Statements obligatorios, cero credenciales en código.
 * ============================================================================

interface Env {
  DB: D1Database;
  CSRF_TOKEN?: string;      // Se inyecta mediante wrangler secret put CSRF_TOKEN
  APP_NAME?: string;
  APP_ENV?: string;
}

/* -------------------------------------------------------------------------- */
/* Utilidades                                                                 */
/* -------------------------------------------------------------------------- */

const corsHeaders = {
  'Access-Control-Allow-Origin': '*',
  'Access-Control-Allow-Methods': 'GET, POST, PUT, DELETE, OPTIONS',
  'Access-Control-Allow-Headers': 'Content-Type, X-CSRF-Token',
};

function json(data: unknown, status = 200, extra?: Record<string, string>): Response {
  return new Response(JSON.stringify(data), {
    status,
    headers: { 'Content-Type': 'application/json; charset=utf-8', ...extra },
  });
}

function errorResponse(message: string, status = 400): Response {
  return json({ exito: false, mensaje: message }, status);
}

/* -------------------------------------------------------------------------- */
/* Middleware de CSRF                                                         */
/* -------------------------------------------------------------------------- */
async function validarCSRF(request: Request, env: Env): Promise<boolean> {
  const token = request.headers.get('X-CSRF-Token');
  const expected = env.CSRF_TOKEN;
  if (!expected) {
    // Si no hay CSRF_TOKEN configurado en secrets, permitir (modo desarrollo)
    return true;
  }
  return token === expected && token !== '';
}

/* -------------------------------------------------------------------------- */
/* Worker principal                                                           */
/* -------------------------------------------------------------------------- */

export default {
  async fetch(request: Request, env: Env): Promise<Response> {
    const url = new URL(request.url);
    const path = url.pathname;
    const method = request.method;

    // Preflight CORS
    if (method === 'OPTIONS') {
      return new Response(null, { headers: corsHeaders });
    }

    // ========================================================================
    // RUTAS: EVENTOS
    // ========================================================================

    if (path === '/api/eventos' && method === 'GET') {
      const { results } = await env.DB.prepare(
        'SELECT * FROM eventos WHERE activo = 1 ORDER BY fecha DESC'
      ).all();
      return json({ exito: true, eventos: results }, 200, corsHeaders);
    }

    const eventoIdMatch = path.match(/^\/api\/eventos\/(\d+)$/);
    if (eventoIdMatch && method === 'GET') {
      const id = Number(eventoIdMatch[1]);
      const evento = await env.DB.prepare(
        'SELECT * FROM eventos WHERE id = ? AND activo = 1'
      ).bind(id).first();
      if (!evento) return errorResponse('Evento no encontrado', 404);
      return json({ exito: true, evento }, 200, corsHeaders);
    }

    if (path === '/api/eventos' && method === 'POST') {
      if (!(await validarCSRF(request, env))) {
        return errorResponse('Token CSRF inválido', 403);
      }
      const body = await request.json() as Record<string, unknown>;
      const { nombre, fecha, circuito, pais, descripcion, imagen_portada, activo = 1 } = body;
      if (!nombre || !fecha) {
        return errorResponse('Nombre y fecha son requeridos', 400);
      }
      const info = await env.DB.prepare(
        `INSERT INTO eventos (nombre, fecha, circuito, pais, descripcion, imagen_portada, activo)
         VALUES (?, ?, ?, ?, ?, ?, ?)`
      ).bind(nombre, fecha, circuito ?? null, pais ?? null, descripcion ?? null, imagen_portada ?? null, activo).run();
      return json({ exito: true, id: info.meta.last_row_id, mensaje: 'Evento creado' }, 201, corsHeaders);
    }

    if (eventoIdMatch && method === 'PUT') {
      if (!(await validarCSRF(request, env))) {
        return errorResponse('Token CSRF inválido', 403);
      }
      const id = Number(eventoIdMatch[1]);
      const body = await request.json() as Record<string, unknown>;
      const { nombre, fecha, circuito, pais, descripcion, imagen_portada, activo } = body;
      const sets: string[] = [];
      const params: unknown[] = [];
      if (nombre !== undefined) { sets.push('nombre = ?'); params.push(nombre); }
      if (fecha !== undefined) { sets.push('fecha = ?'); params.push(fecha); }
      if (circuito !== undefined) { sets.push('circuito = ?'); params.push(circuito); }
      if (pais !== undefined) { sets.push('pais = ?'); params.push(pais); }
      if (descripcion !== undefined) { sets.push('descripcion = ?'); params.push(descripcion); }
      if (imagen_portada !== undefined) { sets.push('imagen_portada = ?'); params.push(imagen_portada); }
      if (activo !== undefined) { sets.push('activo = ?'); params.push(activo); }
      if (sets.length === 0) return errorResponse('No hay campos para actualizar', 400);
      params.push(id);
      const sql = `UPDATE eventos SET ${sets.join(', ')} WHERE id = ?`;
      await env.DB.prepare(sql).bind(...params).run();
      return json({ exito: true, mensaje: 'Evento actualizado' }, 200, corsHeaders);
    }

    if (eventoIdMatch && method === 'DELETE') {
      if (!(await validarCSRF(request, env))) {
        return errorResponse('Token CSRF inválido', 403);
      }
      const id = Number(eventoIdMatch[1]);
      await env.DB.prepare('DELETE FROM eventos WHERE id = ?').bind(id).run();
      return json({ exito: true, mensaje: 'Evento eliminado' }, 200, corsHeaders);
    }

    // ========================================================================
    // RUTAS: SUBCARPETAS
    // ========================================================================

    if (path === '/api/subcarpetas' && method === 'GET') {
      const eventoId = url.searchParams.get('evento_id');
      if (!eventoId) {
        return errorResponse('evento_id es requerido', 400);
      }
      const { results } = await env.DB.prepare(
        'SELECT * FROM subcarpetas WHERE evento_id = ? AND activo = 1 ORDER BY nombre ASC'
      ).bind(Number(eventoId)).all();
      return json({ exito: true, subcarpetas: results }, 200, corsHeaders);
    }

    if (path === '/api/subcarpetas' && method === 'POST') {
      if (!(await validarCSRF(request, env))) {
        return errorResponse('Token CSRF inválido', 403);
      }
      const body = await request.json() as Record<string, unknown>;
      const { evento_id, nombre, descripcion } = body;
      if (!evento_id || !nombre) {
        return errorResponse('evento_id y nombre son requeridos', 400);
      }
      const info = await env.DB.prepare(
        'INSERT INTO subcarpetas (evento_id, nombre, descripcion) VALUES (?, ?, ?)'
      ).bind(evento_id, nombre, descripcion ?? null).run();
      return json({ exito: true, id: info.meta.last_row_id, mensaje: 'Subcarpeta creada' }, 201, corsHeaders);
    }

    const subIdMatch = path.match(/^\/api\/subcarpetas\/(\d+)$/);
    if (subIdMatch && method === 'DELETE') {
      if (!(await validarCSRF(request, env))) {
        return errorResponse('Token CSRF inválido', 403);
      }
      const id = Number(subIdMatch[1]);
      await env.DB.prepare('UPDATE subcarpetas SET activo = 0 WHERE id = ?').bind(id).run();
      return json({ exito: true, mensaje: 'Subcarpeta desactivada' }, 200, corsHeaders);
    }

    // ========================================================================
    // RUTAS: FOTOS
    // ========================================================================

    if (path === '/api/fotos' && method === 'GET') {
      const eventoId = url.searchParams.get('evento');
      const subcarpetaId = url.searchParams.get('subcarpeta');
      const color = url.searchParams.get('color');
      let sql = 'SELECT * FROM fotos WHERE disponible = 1';
      const params: unknown[] = [];
      if (eventoId) { sql += ' AND evento_id = ?'; params.push(eventoId); }
      if (subcarpetaId) { sql += ' AND subcarpeta_id = ?'; params.push(subcarpetaId); }
      if (color) { sql += ' AND color_predominante = ?'; params.push(color); }
      sql += ' ORDER BY fecha_subida DESC';
      const { results } = await env.DB.prepare(sql).bind(...params).all();
      return json({ exito: true, fotos: results, total: results.length }, 200, corsHeaders);
    }

    if (path === '/api/fotos' && method === 'POST') {
      if (!(await validarCSRF(request, env))) {
        return errorResponse('Token CSRF inválido', 403);
      }
      const body = await request.json() as Record<string, unknown>;
      const {
        nombre_archivo, ruta_archivo, nombre_original, titulo, descripcion,
        color_predominante, numero_moto, numero_piloto, fecha_foto,
        evento_id, subcarpeta_id, precio, subido_por,
      } = body;
      if (!nombre_archivo || !ruta_archivo) {
        return errorResponse('nombre_archivo y ruta_archivo son requeridos', 400);
      }
      const info = await env.DB.prepare(
        `INSERT INTO fotos
         (nombre_archivo, ruta_archivo, nombre_original, titulo, descripcion,
          color_predominante, numero_moto, numero_piloto, fecha_foto,
          evento_id, subcarpeta_id, precio, subido_por)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`
      ).bind(
        nombre_archivo, ruta_archivo, nombre_original ?? null, titulo ?? null,
        descripcion ?? null, color_predominante ?? null, numero_moto ?? null,
        numero_piloto ?? null, fecha_foto ?? null, evento_id ?? null,
        subcarpeta_id ?? null, precio ?? 0, subido_por ?? null
      ).run();
      return json({ exito: true, id: info.meta.last_row_id, mensaje: 'Foto registrada' }, 201, corsHeaders);
    }

    // ========================================================================
    // RUTAS: COMPRAS
    // ========================================================================

    if (path === '/api/compras' && method === 'POST') {
      if (!(await validarCSRF(request, env))) {
        return errorResponse('Token CSRF inválido', 403);
      }
      const body = await request.json() as Record<string, unknown>;
      const {
        nombre_cliente, email_cliente, telefono_cliente, whatsapp_cliente,
        fotos_seleccionadas, total, mensaje_whatsapp,
      } = body;
      if (!nombre_cliente || !whatsapp_cliente) {
        return errorResponse('Nombre y WhatsApp son requeridos', 400);
      }
      const fotosJson = Array.isArray(fotos_seleccionadas) ? JSON.stringify(fotos_seleccionadas) : String(fotos_seleccionadas ?? '[]');
      const info = await env.DB.prepare(
        `INSERT INTO compras
         (nombre_cliente, email_cliente, telefono_cliente, whatsapp_cliente,
          fotos_seleccionadas, total, mensaje_whatsapp)
         VALUES (?, ?, ?, ?, ?, ?, ?)`
      ).bind(
        nombre_cliente, email_cliente ?? null, telefono_cliente ?? null,
        whatsapp_cliente, fotosJson, total ?? 0, mensaje_whatsapp ?? null
      ).run();
      return json({ exito: true, id: info.meta.last_row_id, mensaje: 'Compra registrada' }, 201, corsHeaders);
    }

    // ========================================================================
    // RUTAS: CONTACTO
    // ========================================================================

    if (path === '/api/contacto' && method === 'POST') {
      if (!(await validarCSRF(request, env))) {
        return errorResponse('Token CSRF inválido', 403);
      }
      const body = await request.json() as Record<string, unknown>;
      const { nombre, email, telefono, asunto, mensaje, terminos } = body;
      if (!nombre || !email || !telefono || !asunto || !mensaje) {
        return errorResponse('Todos los campos son requeridos', 400);
      }
      if (!terminos) {
        return errorResponse('Debe aceptar los términos y condiciones', 400);
      }
      const info = await env.DB.prepare(
        `INSERT INTO contactos
         (nombre, email, telefono, asunto, mensaje, terminos_aceptados)
         VALUES (?, ?, ?, ?, ?, ?)`
      ).bind(nombre, email, telefono, asunto, mensaje, terminos ? 1 : 0).run();
      return json({ exito: true, id: info.meta.last_row_id, mensaje: 'Mensaje enviado' }, 201, corsHeaders);
    }

    // ========================================================================
    // RUTA: AUDITORÍA (solo lectura)
    // ========================================================================

    if (path === '/api/auditoria' && method === 'GET') {
      const { results } = await env.DB.prepare(
        'SELECT * FROM auditoria_acceso ORDER BY fecha DESC LIMIT 50'
      ).all();
      return json({ exito: true, auditoria: results }, 200, corsHeaders);
    }

    // ========================================================================
    // RUTA NO ENCONTRADA
    // ========================================================================

    return json({ exito: false, mensaje: 'Ruta no encontrada' }, 404, corsHeaders);
  },
} satisfies ExportedHandler<Env>;
