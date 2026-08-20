/**
 * Misi — utilidades UI en JavaScript vanilla (Fase 13).
 *
 * showAlert(), confirmAction(), modal(), formSubmit(). Sin dependencias,
 * sin frameworks. Requiere public/css/misi.css cargado (usa las clases
 * .sd-*) y, si usas formSubmit(), también public/js/api.js.
 */

/**
 * Muestra una alerta flotante que se autodescarta.
 *
 * @param {string} message
 * @param {'success'|'danger'|'warning'|'info'} [type]
 * @param {{duration?: number}} [options] duration en ms, 0 = no autodescartar
 */
function showAlert(message, type, options) {
  type = type || 'info';
  options = options || {};
  var duration = options.duration === undefined ? 5000 : options.duration;

  var container = document.querySelector('.sd-alerts');
  if (!container) {
    container = document.createElement('div');
    container.className = 'sd-alerts';
    document.body.appendChild(container);
  }

  var alertEl = document.createElement('div');
  alertEl.className = 'sd-alert sd-alert-' + type;
  alertEl.setAttribute('role', 'alert');

  var text = document.createElement('span');
  text.textContent = message;
  alertEl.appendChild(text);

  var closeBtn = document.createElement('button');
  closeBtn.type = 'button';
  closeBtn.className = 'sd-alert-close';
  closeBtn.setAttribute('aria-label', 'Cerrar');
  closeBtn.textContent = '\u00D7';
  closeBtn.addEventListener('click', function () {
    alertEl.remove();
  });
  alertEl.appendChild(closeBtn);

  container.appendChild(alertEl);

  if (duration > 0) {
    setTimeout(function () {
      alertEl.remove();
    }, duration);
  }

  return alertEl;
}

/**
 * Confirmación antes de una acción destructiva. Envuelve el confirm()
 * nativo del navegador — simple y funciona en todos lados sin CSS
 * adicional. Si tu proyecto necesita un modal de confirmación con estilo
 * propio, constrúyelo sobre modal() (abajo) y reemplaza esta función.
 *
 * @param {string} message
 * @returns {boolean}
 */
function confirmAction(message) {
  return window.confirm(message);
}

/**
 * Abre, cierra o alterna un modal (estructura .sd-modal-backdrop, ver
 * docs/frontend.md para el markup completo).
 *
 * @param {string|HTMLElement} target id del backdrop (con o sin '#') o el elemento mismo
 * @param {'open'|'close'|'toggle'} [action]
 */
function modal(target, action) {
  action = action || 'toggle';

  var el = typeof target === 'string'
    ? document.getElementById(target.replace(/^#/, ''))
    : target;

  if (!el) {
    return;
  }

  var shouldOpen = action === 'toggle' ? !el.classList.contains('is-open') : action === 'open';

  el.classList.toggle('is-open', shouldOpen);

  if (shouldOpen) {
    document.addEventListener('keydown', modal._escHandler = modal._escHandler || function (event) {
      if (event.key === 'Escape') {
        modal(el, 'close');
      }
    });
  } else {
    document.removeEventListener('keydown', modal._escHandler);
  }
}

// Cerrar el modal al hacer click en el backdrop (fuera de .sd-modal).
document.addEventListener('click', function (event) {
  if (event.target.classList && event.target.classList.contains('sd-modal-backdrop')) {
    modal(event.target, 'close');
  }
});

// Cualquier elemento con data-modal-close="idDelModal" lo cierra al hacer click.
document.addEventListener('click', function (event) {
  var trigger = event.target.closest('[data-modal-close]');
  if (trigger) {
    modal(trigger.getAttribute('data-modal-close'), 'close');
  }
});

/**
 * Envía un <form> vía fetch (api.post/put/patch según el método del
 * form o data-method), en vez de la navegación tradicional del navegador.
 * Junta los campos con FormData -> objeto plano; si el form tiene un
 * <input type="file">, se manda como FormData real (multipart), igual
 * que un envío normal.
 *
 * Uso:
 *   formSubmit(document.querySelector('#customer-form'), {
 *     onSuccess: (data) => showAlert('Guardado', 'success'),
 *     onError: (err) => showAlert(err.message, 'danger'),
 *   });
 *
 * @param {HTMLFormElement} form
 * @param {{onSuccess?: (data: any) => void, onError?: (err: Error) => void}} [handlers]
 */
function formSubmit(form, handlers) {
  handlers = handlers || {};

  form.addEventListener('submit', function (event) {
    event.preventDefault();

    var method = (form.getAttribute('data-method') || form.method || 'POST').toUpperCase();
    var url = form.getAttribute('action') || window.location.pathname;
    var hasFile = form.querySelector('input[type="file"]');

    var submitBtn = form.querySelector('[type="submit"]');
    if (submitBtn) {
      submitBtn.disabled = true;
    }

    var request;
    if (hasFile) {
      request = apiFetch(url, { method: method, body: new FormData(form) });
    } else {
      var data = {};
      new FormData(form).forEach(function (value, key) {
        data[key] = value;
      });
      var verb = method.toLowerCase();
      request = (api[verb] || api.post)(url, data);
    }

    request
      .then(function (responseData) {
        if (handlers.onSuccess) {
          handlers.onSuccess(responseData);
        }
      })
      .catch(function (error) {
        if (handlers.onError) {
          handlers.onError(error);
        } else {
          showAlert(error.message, 'danger');
        }
      })
      .finally(function () {
        if (submitBtn) {
          submitBtn.disabled = false;
        }
      });
  });
}
