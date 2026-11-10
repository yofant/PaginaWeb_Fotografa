const API_CONFIG = {
  BASE_URL: 'https://compel-remake-bubble.ngrok-free.dev/PaginaWeb_Fotografa',
};

async function apiFetch(endpoint, options = {}) {
  const url = `${API_CONFIG.BASE_URL}${endpoint}`;
  const headers = {
    'Content-Type': 'application/json',
    ...options.headers,
  };

  try {
    const response = await fetch(url, { ...options, headers });

    if (!response.ok) {
      const errorData = await response.json().catch(() => ({}));
      throw new Error(errorData.mensaje || `Error ${response.status}`);
    }

    return await response.json();
  } catch (error) {
    console.error('API Error:', error);
    throw error;
  }
}

const api = {
  getEventos: () => apiFetch('/api/eventos'),
  getEvento: (id) => apiFetch(`/api/eventos/${id}`),
  crearEvento: (data) => apiFetch('/api/eventos', {
    method: 'POST',
    body: JSON.stringify(data),
  }),
  editarEvento: (id, data) => apiFetch(`/api/eventos/${id}`, {
    method: 'PUT',
    body: JSON.stringify(data),
  }),
  eliminarEvento: (id) => apiFetch(`/api/eventos/${id}`, {
    method: 'DELETE',
  }),

  getSubcarpetas: (eventoId) => apiFetch(`/api/subcarpetas?evento_id=${eventoId}`),
  crearSubcarpeta: (data) => apiFetch('/api/subcarpetas', {
    method: 'POST',
    body: JSON.stringify(data),
  }),
  eliminarSubcarpeta: (id) => apiFetch(`/api/subcarpetas/${id}`, {
    method: 'DELETE',
  }),

  getFotos: ({ evento, subcarpeta }) => {
    let url = '/api/fotos';
    const params = [];
    if (evento) params.push(`evento=${evento}`);
    if (subcarpeta) params.push(`subcarpeta=${subcarpeta}`);
    if (params.length) url += '?' + params.join('&');
    return apiFetch(url);
  },
  subirFoto: (formData) => {
    return fetch(`${API_CONFIG.BASE_URL}/api/fotos`, {
      method: 'POST',
      body: formData,
    }).then(async (res) => {
      if (!res.ok) {
        const err = await res.json().catch(() => ({}));
        throw new Error(err.mensaje || `Error ${res.status}`);
      }
      return await res.json();
    });
  },

  crearContacto: (data) => apiFetch('/api/contacto', {
    method: 'POST',
    body: JSON.stringify(data),
  }),

  crearCompra: (data) => apiFetch('/api/compras', {
    method: 'POST',
    body: JSON.stringify(data),
  }),

  getAuditoria: () => apiFetch('/api/auditoria'),
};