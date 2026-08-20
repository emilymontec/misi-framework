/**
 * Misi — cliente API en JavaScript vanilla (Fase 13).
 *
 * Sin frameworks ni dependencias (nada de axios): fetch() nativo es
 * suficiente. Maneja JSON, errores, CSRF y estados HTTP automáticamente,
 * usando el formato de respuesta estándar de Misi (ver docs/http.md):
 *   { success, data, message, errors? }
 *
 * apiFetch() es el wrapper de bajo nivel; `api` es la fachada con verbos
 * HTTP que la mayoría del código debería usar.
 */

/**
 * @param {string} url
 * @param {RequestInit} [options]
 * @returns {Promise<any>} el campo "data" de la respuesta si success=true
 * @throws {Error} con .status (código HTTP) y .errors (si vino de una
 *   ValidationException, ver docs/validation.md) cuando success=false
 *   o la respuesta no es JSON válido
 */
async function apiFetch(url, options = {}) {
  const headers = Object.assign(
    { Accept: 'application/json' },
    options.headers || {}
  );

  const isMutating = !['GET', 'HEAD', 'OPTIONS'].includes((options.method || 'GET').toUpperCase());

  if (options.body !== undefined && !(options.body instanceof FormData)) {
    headers['Content-Type'] = 'application/json';
  }

  if (isMutating) {
    headers['X-CSRF-Token'] = await apiFetch.getCsrfToken();
  }

  const response = await fetch(url, Object.assign({ credentials: 'same-origin' }, options, { headers }));

  let payload = null;
  try {
    payload = await response.json();
  } catch (parseError) {
    // Respuesta sin body JSON (ej. 204, o un error 500 en HTML de un
    // proxy intermedio) — se sigue tratando el status HTTP como fuente
    // de verdad más abajo.
  }

  if (!response.ok || (payload && payload.success === false)) {
    const message = (payload && payload.message) || `Error ${response.status}`;
    const error = new Error(message);
    error.status = response.status;
    error.errors = payload ? payload.errors : undefined;
    error.payload = payload;
    throw error;
  }

  return payload ? payload.data : null;
}

/**
 * Token CSRF cacheado en memoria (una sola petición al backend por carga
 * de página, no una por cada request mutante). Configurable con
 * apiFetch.csrfTokenUrl si tu proyecto usa otra ruta.
 */
apiFetch.csrfTokenUrl = '/api/csrf-token';
apiFetch._csrfTokenPromise = null;

apiFetch.getCsrfToken = function getCsrfToken() {
  if (!apiFetch._csrfTokenPromise) {
    apiFetch._csrfTokenPromise = fetch(apiFetch.csrfTokenUrl, { credentials: 'same-origin' })
      .then((res) => res.json())
      .then((json) => json.data.token)
      .catch((err) => {
        apiFetch._csrfTokenPromise = null; // reintentar en el siguiente request
        throw err;
      });
  }
  return apiFetch._csrfTokenPromise;
};

/** Fachada con verbos HTTP — lo que la mayoría del código debería usar. */
var api = {
  get(url) {
    return apiFetch(url, { method: 'GET' });
  },
  post(url, body) {
    return apiFetch(url, { method: 'POST', body: JSON.stringify(body || {}) });
  },
  put(url, body) {
    return apiFetch(url, { method: 'PUT', body: JSON.stringify(body || {}) });
  },
  patch(url, body) {
    return apiFetch(url, { method: 'PATCH', body: JSON.stringify(body || {}) });
  },
  delete(url) {
    return apiFetch(url, { method: 'DELETE' });
  },
  /** Subida de archivos: FormData, sin forzar Content-Type (el navegador pone el boundary). */
  upload(url, formData) {
    return apiFetch(url, { method: 'POST', body: formData });
  },
};
