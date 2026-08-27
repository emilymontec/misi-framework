    (function () {
      'use strict';

      var installCommands = {
        bash: {
          quick: 'bash -c "curl -fsSL https://raw.githubusercontent.com/emilymontec/misi-framework/main/install.sh | sh"',
          full: '# 0. instalar CLI global\nbash -c "curl -fsSL https://raw.githubusercontent.com/emilymontec/misi-framework/main/install.sh | sh"\n\n# 1. crear y entrar al proyecto\nmisi new mi-proyecto\ncd mi-proyecto\n\n# 2. preparar el entorno\ncp .env.example .env\nnano .env\n\n# 3-4. base de datos\nmisi migrate\nmisi db:seed\n\n# 5. arrancar\nmisi serve'
        },
        ps: {
          quick: 'irm https://raw.githubusercontent.com/emilymontec/misi-framework/main/install.sh | iex',
          full: '# 0. instalar CLI global\nirm https://raw.githubusercontent.com/emilymontec/misi-framework/main/install.sh | iex\n\n# 1. crear y entrar al proyecto\nmisi new mi-proyecto\ncd mi-proyecto\n\n# 2. preparar el entorno\ncopy .env.example .env\nnotepad .env\n\n# 3-4. base de datos\nmisi migrate\nmisi db:seed\n\n# 5. arrancar\nmisi serve'
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
          var cubes = cubeField.querySelectorAll('.cube');
          cubeField.parentElement.addEventListener('mousemove', function (e) {
            var rect = cubeField.parentElement.getBoundingClientRect();
            var px = (e.clientX - rect.left) / rect.width - 0.5;
            var py = (e.clientY - rect.top) / rect.height - 0.5;
            cubes.forEach(function (cube, i) {
              var depth = (i % 4) + 1;
              cube.style.marginLeft = (px * depth * 14) + 'px';
              cube.style.marginTop = (py * depth * 14) + 'px';
            });
          });
        }

        var navToggle = document.getElementById('navToggle');
        var navLinks = document.getElementById('navLinks');
        if (navToggle && navLinks) {
          navToggle.addEventListener('click', function () {
            var isOpen = navLinks.classList.toggle('open');
            navToggle.setAttribute('aria-expanded', isOpen);
            navToggle.setAttribute('aria-label', isOpen ? 'Cerrar menú de navegación' : 'Abrir menú de navegación');
          });
        }

        var sections = ['inicio', 'caracteristicas', 'instalacion', 'ejemplo', 'cli']
          .map(function (id) { return document.getElementById(id); })
          .filter(Boolean);
        var navAnchors = Array.from(navLinks ? navLinks.querySelectorAll('a[href^="#"]') : []);

        if (sections.length && navAnchors.length) {
          var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
              if (!entry.isIntersecting) return;
              navAnchors.forEach(function (a) { a.classList.remove('active'); });
              var match = navAnchors.find(function (a) { return a.getAttribute('href') === '#' + entry.target.id; });
              if (match) match.classList.add('active');
            });
          }, { rootMargin: '-40% 0px -55% 0px' });

          sections.forEach(function (section) { observer.observe(section); });
        }

        var checkIconSvg = '<svg class="icon" viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>';

        function setupCopyButtons() {
          var copyButtons = document.querySelectorAll('[data-copy], .btn-terminal-copy, .btn-copy-quick');
          copyButtons.forEach(function (btn) {
            btn.addEventListener('click', async function () {
              var textToCopy = btn.getAttribute('data-copy') || (btn.closest && btn.closest('.terminal')?.querySelector('.terminal-body')?.innerText) || '';
              if (!textToCopy) return;
              try {
                await navigator.clipboard.writeText(textToCopy);
                var originalHTML = btn.innerHTML;
                btn.innerHTML = checkIconSvg + ' <span>¡Copiado!</span>';
                setTimeout(function () { btn.innerHTML = originalHTML; }, 2000);
              } catch (err) {
                console.error('Error al copiar:', err);
              }
            });
          });
        }
        if (document.readyState === 'loading') {
          document.addEventListener('DOMContentLoaded', setupCopyButtons);
        } else {
          setupCopyButtons();
        }
      } catch (e) {
        console.error('Misi landing: error en UI features (cubos/nav/copy/intersection).', e);
      }
    })();

    (function () {
      'use strict';
      try {
        var cliSnippets = {
          all: {
            title: 'misi cli — referencia completa',
            copy: '$ misi serve\n$ misi doctor\n$ misi db migrate\n$ misi db status\n$ misi db fresh\n$ misi route:list\n$ misi config:list\n\n$ misi make controller Customer\n$ misi make repository Product\n$ misi make module Inventory\n$ misi create business catalog\n$ misi new mi-proyecto',
            html: '<span class="t-prompt">$</span> misi serve\n<span class="t-comment">  # servidor de desarrollo (alias: run)</span>\n<span class="t-prompt">$</span> misi doctor\n<span class="t-comment">  # diagnóstico del entorno (extensiones, .env, permisos)</span>\n<span class="t-prompt">$</span> misi db migrate\n<span class="t-comment">  # ejecuta migraciones pendientes</span>\n<span class="t-prompt">$</span> misi db status\n<span class="t-comment">  # qué corrió, qué falta</span>\n<span class="t-prompt">$</span> misi db fresh\n<span class="t-comment">  # recrea la base desde cero + seed</span>\n<span class="t-prompt">$</span> misi route:list\n<span class="t-comment">  # todas las rutas registradas</span>\n<span class="t-prompt">$</span> misi config:list\n<span class="t-comment">  # configuración cargada, por archivo</span>\n\n<span class="t-prompt">$</span> misi make controller Customer\n<span class="t-comment">  # app/Http/Controllers/CustomerController.php</span>\n<span class="t-prompt">$</span> misi make repository Product\n<span class="t-comment">  # app/Repositories/ProductRepository.php</span>\n<span class="t-prompt">$</span> misi make module Inventory\n<span class="t-comment">  # modules/Inventory/ completo</span>\n<span class="t-prompt">$</span> misi create business catalog\n<span class="t-comment">  # Business Core + módulo Catalog listos</span>\n<span class="t-prompt">$</span> misi new mi-proyecto\n<span class="t-comment">  # nuevo proyecto a partir de Misi</span>'
          },
          server: {
            title: 'misi cli — servidor y diagnóstico',
            copy: '$ misi serve\n$ misi doctor\n$ misi route:list\n$ misi config:list',
            html: '<span class="t-prompt">$</span> misi serve\n<span class="t-comment">  # levanta el servidor local en http://127.0.0.1:8000</span>\n<span class="t-prompt">$</span> misi serve --port=8080\n<span class="t-comment">  # levanta en puerto personalizado</span>\n\n<span class="t-prompt">$</span> misi doctor\n<span class="t-comment">  # valida versión de PHP (8.1+), extensiones (pdo_mysql, mbstring, etc.), .env y permisos</span>\n\n<span class="t-prompt">$</span> misi route:list\n<span class="t-comment">  # tabla con métodos HTTP, URIs, controladores y middlewares activos</span>'
          },
          db: {
            title: 'misi cli — migraciones y base de datos',
            copy: '$ misi db migrate\n$ misi db status\n$ misi db rollback\n$ misi db seed\n$ misi db fresh',
            html: '<span class="t-prompt">$</span> misi db migrate\n<span class="t-comment">  # ejecuta migraciones pendientes con lock seguro</span>\n\n<span class="t-prompt">$</span> misi db status\n<span class="t-comment">  # muestra el estado de cada archivo de migración</span>\n\n<span class="t-prompt">$</span> misi db rollback\n<span class="t-comment">  # revierte el último lote de migraciones</span>\n\n<span class="t-prompt">$</span> misi db seed\n<span class="t-comment">  # inserta datos iniciales / usuarios demo</span>\n\n<span class="t-prompt">$</span> misi db fresh\n<span class="t-comment">  # recrea todas las tablas desde cero y corre seeds</span>'
          },
          make: {
            title: 'misi cli — generadores de código',
            copy: '$ misi make controller Customer\n$ misi make model Customer\n$ misi make repository Customer\n$ misi make service Customer\n$ misi make migration create_orders_table\n$ misi make module Inventory',
            html: '<span class="t-prompt">$</span> misi make controller Customer\n<span class="t-comment">  # crea app/Http/Controllers/CustomerController.php</span>\n\n<span class="t-prompt">$</span> misi make repository Product\n<span class="t-comment">  # crea app/Repositories/ProductRepository.php</span>\n\n<span class="t-prompt">$</span> misi make service Order\n<span class="t-comment">  # crea app/Services/OrderService.php</span>\n\n<span class="t-prompt">$</span> misi make migration create_orders_table\n<span class="t-comment">  # crea database/migrations/NNN_create_orders_table.php</span>\n\n<span class="t-prompt">$</span> misi make module Inventory\n<span class="t-comment">  # estructura completa en modules/Inventory/ (rutas, controllers, migraciones)</span>'
          },
          business: {
            title: 'misi cli — Business Core y proyectos',
            copy: '$ misi create business catalog\n$ misi create business customers\n$ misi new mi-sistema-admin',
            html: '<span class="t-prompt">$</span> misi create business catalog\n<span class="t-comment">  # instala el módulo Business Core de catálogo + panel de administración con RBAC</span>\n\n<span class="t-prompt">$</span> misi new mi-sistema-admin\n<span class="t-comment">  # inicializa un nuevo proyecto listo para desarrollo con toda la estructura de Misi</span>'
          }
        };

        function bootCliTabs() {
          var cliTabButtons = document.querySelectorAll('.cli-tab-btn');
          var cliCodeBlock = document.getElementById('cliCodeBlock');
          var cliTerminalTitle = document.getElementById('cliTerminalTitle');
          var btnCliCopy = document.getElementById('btnCliCopy');

          if (!(cliTabButtons.length && cliCodeBlock && cliTerminalTitle && btnCliCopy)) return;

          cliTabButtons.forEach(function (tab) {
            tab.addEventListener('click', function () {
              cliTabButtons.forEach(function (t) {
                t.classList.remove('active');
                t.setAttribute('aria-selected', 'false');
              });
              tab.classList.add('active');
              tab.setAttribute('aria-selected', 'true');

              var tabKey = tab.getAttribute('data-tab');
              var data = cliSnippets[tabKey] || cliSnippets.all;

              cliTerminalTitle.textContent = data.title;
              cliCodeBlock.innerHTML = data.html;
              btnCliCopy.setAttribute('data-copy', data.copy);
            });
          });

          btnCliCopy.setAttribute('data-copy', cliSnippets.all.copy);
        }

        if (document.readyState === 'loading') {
          document.addEventListener('DOMContentLoaded', bootCliTabs);
        } else {
          bootCliTabs();
        }
      } catch (e) {
        console.error('Misi landing: error en tabs CLI.', e);
      }
    })();
