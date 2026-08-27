(function () {
  'use strict';

  var installCommands = {
    bash: {
      quick: 'bash -c "curl -fsSL https://raw.githubusercontent.com/emilymontec/misi-framework/main/install.sh | sh"',
      full: '# 0. instalar CLI global\nbash -c "curl -fsSL https://raw.githubusercontent.com/emilymontec/misi-framework/main/install.sh | sh"\n\n# 1. crear y entrar al proyecto\nmisi new mi-proyecto\ncd mi-proyecto\n\n# 2. preparar el entorno\ncp .env.example .env\nnano .env\n\n# 3-4. base de datos\nmisi migrate\nmisi db:seed\n\n# 5. arrancar\nmisi serve'
    },
    ps: {
      quick: 'Invoke-Expression (New-Object Net.WebClient).DownloadString("https://raw.githubusercontent.com/emilymontec/misi-framework/main/install.sh")',
      full: '# 0. instalar CLI global\nInvoke-Expression (New-Object Net.WebClient).DownloadString("https://raw.githubusercontent.com/emilymontec/misi-framework/main/install.sh")\n\n# 1. crear y entrar al proyecto\nmisi new mi-proyecto\ncd mi-proyecto\n\n# 2. preparar el entorno\ncopy .env.example .env\nnotepad .env\n\n# 3-4. base de datos\nmisi migrate\nmisi db:seed\n\n# 5. arrancar\nmisi serve'
    }
  };

  function applyInstallShell(shell) {
    var nodes, i, el, match;
    nodes = document.querySelectorAll('[data-install-tab]');
    for (i = 0; i < nodes.length; i++) {
      match = nodes[i].getAttribute('data-install-tab') === shell;
      nodes[i].classList.toggle('active', match);
      nodes[i].setAttribute('aria-selected', match ? 'true' : 'false');
    }
    nodes = document.querySelectorAll('[data-install-prompt]');
    for (i = 0; i < nodes.length; i++) {
      nodes[i].style.display = nodes[i].getAttribute('data-install-prompt') === shell ? '' : 'none';
    }
    nodes = document.querySelectorAll('[data-install-cmd]');
    for (i = 0; i < nodes.length; i++) {
      nodes[i].style.display = nodes[i].getAttribute('data-install-cmd') === shell ? '' : 'none';
    }
    var qbtn = document.getElementById('btnCopyQuick');
    if (qbtn) qbtn.setAttribute('data-copy', installCommands[shell].quick);
    var ibtn = document.getElementById('btnInstallCopy');
    if (ibtn) ibtn.setAttribute('data-copy', installCommands[shell].full);
  }

  function autoPickInstallShell() {
    var ua = navigator.userAgent || '';
    var isWindows = /Windows/i.test(ua) && !/Linux/i.test(ua) && !/Android/i.test(ua);
    applyInstallShell(isWindows ? 'ps' : 'bash');
  }

  function bootInstallTabs() {
    document.addEventListener('click', function (e) {
      var t = e.target;
      while (t && t !== document) {
        if (t.nodeType === 1 && t.hasAttribute && t.hasAttribute('data-install-tab')) {
          applyInstallShell(t.getAttribute('data-install-tab'));
          break;
        }
        t = t.parentNode;
      }
    });
    autoPickInstallShell();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootInstallTabs);
  } else {
    bootInstallTabs();
  }
})();

(function () {
'use strict';
  try {
    var cubeField = document.getElementById('cubeField');
if (cubeField && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
  const cubes = cubeField.querySelectorAll('.cube');
  cubeField.parentElement.addEventListener('mousemove', (e) => {
    const rect = cubeField.parentElement.getBoundingClientRect();
    const px = (e.clientX - rect.left) / rect.width - 0.5;
    const py = (e.clientY - rect.top) / rect.height - 0.5;
    cubes.forEach((cube, i) => {
      const depth = (i % 4) + 1;
      cube.style.marginLeft = `${px * depth * 14}px`;
      cube.style.marginTop = `${py * depth * 14}px`;
    });
  });
}

// Toggle para menú responsive
const navToggle = document.getElementById('navToggle');
const navLinks = document.getElementById('navLinks');
if (navToggle && navLinks) {
  navToggle.addEventListener('click', () => {
    const isOpen = navLinks.classList.toggle('open');
    navToggle.setAttribute('aria-expanded', isOpen);
    navToggle.setAttribute('aria-label', isOpen ? 'Cerrar menú de navegación' : 'Abrir menú de navegación');
  });
}

// IntersectionObserver para navegación activa
const sections = ['inicio', 'caracteristicas', 'instalacion', 'ejemplo', 'cli']
  .map((id) => document.getElementById(id))
  .filter(Boolean);
const navAnchors = Array.from(navLinks ? navLinks.querySelectorAll('a[href^="#"]') : []);

if (sections.length && navAnchors.length) {
  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (!entry.isIntersecting) return;
      navAnchors.forEach((a) => a.classList.remove('active'));
      const match = navAnchors.find((a) => a.getAttribute('href') === `#${entry.target.id}`);
      if (match) match.classList.add('active');
    });
  }, { rootMargin: '-40% 0px -55% 0px' });

  sections.forEach((section) => observer.observe(section));
}

// Funcionalidad de copiado con feedback visual y SVG icon
const checkIconSvg = `<svg class="icon" viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>`;

function setupCopyButtons() {
  const copyButtons = document.querySelectorAll('[data-copy], .btn-terminal-copy, .btn-copy-quick');
  copyButtons.forEach((btn) => {
    btn.addEventListener('click', async () => {
      const textToCopy = btn.getAttribute('data-copy') || btn.closest('.terminal')?.querySelector('.terminal-body')?.innerText || '';
      if (!textToCopy) return;

      try {
        await navigator.clipboard.writeText(textToCopy);
        const originalHTML = btn.innerHTML;
        btn.innerHTML = `${checkIconSvg} <span>¡Copiado!</span>`;
        setTimeout(() => {
          btn.innerHTML = originalHTML;
        }, 2000);
      } catch (err) {
        console.error('Error al copiar:', err);
      }
    });
  });
}
setupCopyButtons();

// Tabs del CLI interactivo
const cliSnippets = {
  all: {
    title: 'misi cli — referencia completa',
    copy: `$ misi serve
$ misi doctor
$ misi db migrate
$ misi db status
$ misi db fresh
$ misi route:list
$ misi config:list

$ misi make controller Customer
$ misi make repository Product
$ misi make module Inventory
$ misi create business catalog
$ misi new mi-proyecto`,
    html: `<span class="t-prompt">$</span> misi serve
<span class="t-comment">  # servidor de desarrollo (alias: run)</span>
<span class="t-prompt">$</span> misi doctor
<span class="t-comment">  # diagnóstico del entorno (extensiones, .env, permisos)</span>
<span class="t-prompt">$</span> misi db migrate
<span class="t-comment">  # ejecuta migraciones pendientes</span>
<span class="t-prompt">$</span> misi db status
<span class="t-comment">  # qué corrió, qué falta</span>
<span class="t-prompt">$</span> misi db fresh
<span class="t-comment">  # recrea la base desde cero + seed</span>
<span class="t-prompt">$</span> misi route:list
<span class="t-comment">  # todas las rutas registradas</span>
<span class="t-prompt">$</span> misi config:list
<span class="t-comment">  # configuración cargada, por archivo</span>

<span class="t-prompt">$</span> misi make controller Customer
<span class="t-comment">  # app/Http/Controllers/CustomerController.php</span>
<span class="t-prompt">$</span> misi make repository Product
<span class="t-comment">  # app/Repositories/ProductRepository.php</span>
<span class="t-prompt">$</span> misi make module Inventory
<span class="t-comment">  # modules/Inventory/ completo</span>
<span class="t-prompt">$</span> misi create business catalog
<span class="t-comment">  # Business Core + módulo Catalog listos</span>
<span class="t-prompt">$</span> misi new mi-proyecto
<span class="t-comment">  # nuevo proyecto a partir de Misi</span>`
  },
  server: {
    title: 'misi cli — servidor y diagnóstico',
    copy: `$ misi serve
$ misi doctor
$ misi route:list
$ misi config:list`,
    html: `<span class="t-prompt">$</span> misi serve
<span class="t-comment">  # levanta el servidor local en http://127.0.0.1:8000</span>
<span class="t-prompt">$</span> misi serve --port=8080
<span class="t-comment">  # levanta en puerto personalizado</span>

<span class="t-prompt">$</span> misi doctor
<span class="t-comment">  # valida versión de PHP (8.1+), extensiones (pdo_mysql, mbstring, etc.), .env y permisos</span>

<span class="t-prompt">$</span> misi route:list
<span class="t-comment">  # tabla con métodos HTTP, URIs, controladores y middlewares activos</span>`
  },
  db: {
    title: 'misi cli — migraciones y base de datos',
    copy: `$ misi db migrate
$ misi db status
$ misi db rollback
$ misi db seed
$ misi db fresh`,
    html: `<span class="t-prompt">$</span> misi db migrate
<span class="t-comment">  # ejecuta migraciones pendientes con lock seguro</span>

<span class="t-prompt">$</span> misi db status
<span class="t-comment">  # muestra el estado de cada archivo de migración</span>

<span class="t-prompt">$</span> misi db rollback
<span class="t-comment">  # revierte el último lote de migraciones</span>

<span class="t-prompt">$</span> misi db seed
<span class="t-comment">  # inserta datos iniciales / usuarios demo</span>

<span class="t-prompt">$</span> misi db fresh
<span class="t-comment">  # recrea todas las tablas desde cero y corre seeds</span>`
  },
  make: {
    title: 'misi cli — generadores de código',
    copy: `$ misi make controller Customer
$ misi make model Customer
$ misi make repository Customer
$ misi make service Customer
$ misi make migration create_orders_table
$ misi make module Inventory`,
    html: `<span class="t-prompt">$</span> misi make controller Customer
<span class="t-comment">  # crea app/Http/Controllers/CustomerController.php</span>

<span class="t-prompt">$</span> misi make repository Product
<span class="t-comment">  # crea app/Repositories/ProductRepository.php</span>

<span class="t-prompt">$</span> misi make service Order
<span class="t-comment">  # crea app/Services/OrderService.php</span>

<span class="t-prompt">$</span> misi make migration create_orders_table
<span class="t-comment">  # crea database/migrations/NNN_create_orders_table.php</span>

<span class="t-prompt">$</span> misi make module Inventory
<span class="t-comment">  # estructura completa en modules/Inventory/ (rutas, controllers, migraciones)</span>`
  },
  business: {
    title: 'misi cli — Business Core y proyectos',
    copy: `$ misi create business catalog
$ misi create business customers
$ misi new mi-sistema-admin`,
    html: `<span class="t-prompt">$</span> misi create business catalog
<span class="t-comment">  # instala el módulo Business Core de catálogo + panel de administración con RBAC</span>

<span class="t-prompt">$</span> misi new mi-sistema-admin
<span class="t-comment">  # inicializa un nuevo proyecto listo para desarrollo con toda la estructura de Misi</span>`
  }
};

const cliTabButtons = document.querySelectorAll('.cli-tab-btn');
const cliCodeBlock = document.getElementById('cliCodeBlock');
const cliTerminalTitle = document.getElementById('cliTerminalTitle');
const btnCliCopy = document.getElementById('btnCliCopy');

if (cliTabButtons.length && cliCodeBlock && cliTerminalTitle && btnCliCopy) {
  cliTabButtons.forEach((tab) => {
    tab.addEventListener('click', () => {
      cliTabButtons.forEach((t) => {
        t.classList.remove('active');
        t.setAttribute('aria-selected', 'false');
      });
      tab.classList.add('active');
      tab.setAttribute('aria-selected', 'true');

      const tabKey = tab.getAttribute('data-tab');
      const data = cliSnippets[tabKey] || cliSnippets.all;

      cliTerminalTitle.textContent = data.title;
      cliCodeBlock.innerHTML = data.html;
      btnCliCopy.setAttribute('data-copy', data.copy);
    });
  });

  // Configurar copia inicial del bloque CLI
  btnCliCopy.setAttribute('data-copy', cliSnippets.all.copy);
}
})();
