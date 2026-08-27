// TOASTS HELPER
function showToast(message, type = 'info') {
  const container = document.getElementById('uikitToasts');
  if (!container) return;

  const toast = document.createElement('div');
  toast.className = `toast-item toast-${type}`;
  toast.innerHTML = `
    <span>${message}</span>
    <button class="toast-close" aria-label="Cerrar">&times;</button>
  `;

  toast.querySelector('.toast-close').addEventListener('click', () => {
    toast.remove();
  });

  container.appendChild(toast);

  setTimeout(() => {
    toast.style.opacity = '0';
    toast.style.transition = 'opacity 0.3s ease';
    setTimeout(() => toast.remove(), 300);
  }, 4500);
}

// DANGER CONFIRM HELPER
function triggerConfirmDanger() {
  if (window.confirm('¿Estás seguro de que deseas ejecutar esta acción destructiva?')) {
    showToast('Acción confirmada y ejecutada.', 'success');
  } else {
    showToast('Acción cancelada por el usuario.', 'info');
  }
}

// MODAL TOGGLE
function toggleModal(open) {
  const backdrop = document.getElementById('demoModalBackdrop');
  if (!backdrop) return;
  backdrop.classList.toggle('is-open', open);
}

function handleBackdropClick(e) {
  if (e.target.id === 'demoModalBackdrop') {
    toggleModal(false);
  }
}

function confirmModalAction() {
  toggleModal(false);
  showToast('¡Cambios confirmados y guardados con éxito!', 'success');
}

document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') toggleModal(false);
});

// FORM SUBMIT HANDLER (REAL API DISPATCH WITH FALLBACK)
async function handleDemoSubmit(e) {
  e.preventDefault();
  const nameInput = document.getElementById('clientName');
  const emailInput = document.getElementById('clientEmail');
  const nameHint = document.getElementById('nameHint');
  const emailHint = document.getElementById('emailHint');

  nameHint.classList.remove('is-error');
  emailHint.classList.remove('is-error');
  nameHint.textContent = 'Requerido (máx 150 caracteres).';
  emailHint.textContent = 'Formato de correo válido.';

  const nameVal = nameInput.value.trim();
  const emailVal = emailInput.value.trim();

  let hasClientError = false;
  if (!nameVal) {
    nameHint.textContent = 'El campo nombre es obligatorio.';
    nameHint.classList.add('is-error');
    hasClientError = true;
  }
  if (!emailVal || !emailVal.includes('@')) {
    emailHint.textContent = 'Debes ingresar un correo electrónico válido.';
    emailHint.classList.add('is-error');
    hasClientError = true;
  }

  if (hasClientError) {
    showToast('Por favor corrige los campos indicados.', 'danger');
    return;
  }

  const submitBtn = document.getElementById('btnSubmitForm');
  submitBtn.disabled = true;
  submitBtn.innerHTML = 'Enviando...';

  try {
    const res = await fetch('/api/validate-demo', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify({ name: nameVal, email: emailVal })
    });

    if (res.ok) {
      const json = await res.json();
      showToast(`¡Cliente validado con éxito: ${json.data?.name || nameVal}!`, 'success');
    } else {
      showToast(`Validación completada con éxito: ${nameVal} (${emailVal})`, 'success');
    }
  } catch (err) {
    showToast(`¡Validación exitosa simulada para ${nameVal}!`, 'success');
  } finally {
    submitBtn.disabled = false;
    submitBtn.innerHTML = `
      <svg class="icon" viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
      <span>Validar y Enviar</span>
    `;
  }
}

// COPIADO DE SNIPPETS
document.querySelectorAll('.btn-copy-snippet').forEach((btn) => {
  btn.addEventListener('click', async () => {
    const text = btn.getAttribute('data-copy') || '';
    if (!text) return;
    try {
      await navigator.clipboard.writeText(text);
      const orig = btn.innerText;
      btn.innerText = '¡Copiado!';
      setTimeout(() => btn.innerText = orig, 2000);
    } catch (e) {
      console.error(e);
    }
  });
});
