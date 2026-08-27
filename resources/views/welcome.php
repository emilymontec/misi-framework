<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Misi — base de desarrollo en PHP, ligera y sin dependencias, para construir sistemas administrativos de pequeños negocios sin reinventar routing, auth, CSRF o validación.">
  <title>Misi — Base PHP para sistemas administrativos</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/css/main.css">
</head>
<body>

  <a href="#contenido" class="skip-link">Saltar al contenido</a>

  <!-- ENCABEZADO / HERO SUPERIOR -->
  <header class="hero-banner">
    <div class="cube-field" aria-hidden="true" id="cubeField">
      <div class="cube cube--1"><div class="cube__face cube__face--front"></div><div class="cube__face cube__face--back"></div><div class="cube__face cube__face--right"></div><div class="cube__face cube__face--left"></div><div class="cube__face cube__face--top"></div><div class="cube__face cube__face--bottom"></div></div>
      <div class="cube cube--2"><div class="cube__face cube__face--front"></div><div class="cube__face cube__face--back"></div><div class="cube__face cube__face--right"></div><div class="cube__face cube__face--left"></div><div class="cube__face cube__face--top"></div><div class="cube__face cube__face--bottom"></div></div>
      <div class="cube cube--3"><div class="cube__face cube__face--front"></div><div class="cube__face cube__face--back"></div><div class="cube__face cube__face--right"></div><div class="cube__face cube__face--left"></div><div class="cube__face cube__face--top"></div><div class="cube__face cube__face--bottom"></div></div>
      <div class="cube cube--4"><div class="cube__face cube__face--front"></div><div class="cube__face cube__face--back"></div><div class="cube__face cube__face--right"></div><div class="cube__face cube__face--left"></div><div class="cube__face cube__face--top"></div><div class="cube__face cube__face--bottom"></div></div>
      <div class="cube cube--5"><div class="cube__face cube__face--front"></div><div class="cube__face cube__face--back"></div><div class="cube__face cube__face--right"></div><div class="cube__face cube__face--left"></div><div class="cube__face cube__face--top"></div><div class="cube__face cube__face--bottom"></div></div>
      <div class="cube cube--6"><div class="cube__face cube__face--front"></div><div class="cube__face cube__face--back"></div><div class="cube__face cube__face--right"></div><div class="cube__face cube__face--left"></div><div class="cube__face cube__face--top"></div><div class="cube__face cube__face--bottom"></div></div>
      <div class="cube cube--7"><div class="cube__face cube__face--front"></div><div class="cube__face cube__face--back"></div><div class="cube__face cube__face--right"></div><div class="cube__face cube__face--left"></div><div class="cube__face cube__face--top"></div><div class="cube__face cube__face--bottom"></div></div>
      <div class="cube cube--8"><div class="cube__face cube__face--front"></div><div class="cube__face cube__face--back"></div><div class="cube__face cube__face--right"></div><div class="cube__face cube__face--left"></div><div class="cube__face cube__face--top"></div><div class="cube__face cube__face--bottom"></div></div>
    </div>
    <nav class="nav-top" aria-label="Navegación principal" style="position:relative; z-index:1;">
      <ul class="nav-links" id="navLinks">
        <li><a href="#inicio" class="active">&lt;INICIO&gt;</a></li>
        <li><a href="#caracteristicas">CARACTERÍSTICAS</a></li>
        <li><a href="#instalacion">INSTALACIÓN</a></li>
        <li><a href="#cli">CLI</a></li>
        <li><a href="/ui-kit">UI KIT</a></li>
      </ul>
      <div class="nav-actions">
        <button class="nav-toggle" id="navToggle" aria-expanded="false" aria-controls="navLinks" aria-label="Abrir menú de navegación">
          <span></span><span></span><span></span>
        </button>
        <a href="/ui-kit" class="btn-ui-kit">
          <svg class="icon" viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg>
          UI Kit Explorer
        </a>
        <a href="#instalacion" class="btn-contact">
          <span>EMPEZAR</span>
          <svg class="icon" viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="7" y1="17" x2="17" y2="7"/><polyline points="7 7 17 7 17 17"/></svg>
        </a>
      </div>
    </nav>

    <div class="brand-hero" style="position:relative; z-index:1;">
      <div class="logo">
        <span class="logo-spark" aria-hidden="true">✦</span> Misi <span class="version-tag">v1.3 · producción</span>
      </div>
      <div class="scroll-notice">Scroll para más ↓</div>
    </div>
  </header>

  <!-- MARQUESINA INFINITA (TICKER TAPE) -->
  <div class="ticker">
    <div class="ticker-track">
      <div class="ticker-item"><span class="ticker-highlight" aria-hidden="true">✦</span> SIN ORM · SQL EXPLÍCITO</div>
      <div class="ticker-item"><span class="ticker-highlight" aria-hidden="true">✦</span> CERO DEPENDENCIAS EN PRODUCCIÓN</div>
      <div class="ticker-item"><span class="ticker-highlight" aria-hidden="true">✦</span> AUTH Y CSRF INCLUIDOS</div>
      <div class="ticker-item"><span class="ticker-highlight" aria-hidden="true">✦</span> HOSTING COMPARTIDO ECONÓMICO</div>
      <div class="ticker-item" aria-hidden="true"><span class="ticker-highlight">✦</span> SIN ORM · SQL EXPLÍCITO</div>
      <div class="ticker-item" aria-hidden="true"><span class="ticker-highlight">✦</span> CERO DEPENDENCIAS EN PRODUCCIÓN</div>
      <div class="ticker-item" aria-hidden="true"><span class="ticker-highlight">✦</span> AUTH Y CSRF INCLUIDOS</div>
      <div class="ticker-item" aria-hidden="true"><span class="ticker-highlight">✦</span> HOSTING COMPARTIDO ECONÓMICO</div>
      <div class="ticker-item" aria-hidden="true"><span class="ticker-highlight">✦</span> SIN ORM · SQL EXPLÍCITO</div>
      <div class="ticker-item" aria-hidden="true"><span class="ticker-highlight">✦</span> CERO DEPENDENCIAS EN PRODUCCIÓN</div>
      <div class="ticker-item" aria-hidden="true"><span class="ticker-highlight">✦</span> AUTH Y CSRF INCLUIDOS</div>
      <div class="ticker-item" aria-hidden="true"><span class="ticker-highlight">✦</span> HOSTING COMPARTIDO ECONÓMICO</div>
    </div>
  </div>

  <!-- CONTENIDO PRINCIPAL -->
  <main class="container" id="contenido">
    <section class="hero-main" id="inicio">
      <h1 class="hero-title">
        Una base PHP <br>
        <span class="highlight-box">[ sin dependencias ]</span> <br>
        para sistemas de gestión.
      </h1>

      <div class="hero-right">
        <div class="trust-badge">
          <span>PHP 8.1+</span><span class="dot">·</span>
          <span>MySQL / MariaDB</span><span class="dot">·</span>
          <span>Sin Composer obligatorio</span>
        </div>

        <p class="hero-description">
          Misi es una base de desarrollo pensada para construir rápido sistemas
          administrativos de pequeños negocios — sin reinventar routing,
          autenticación, CSRF o validación en cada proyecto nuevo. Sin ORM.
          Sin Node.js en producción. Nada que no puedas leer y entender en
          una tarde.
        </p>

        <!-- Bloque de instalación rápida universal con tabs + copy -->
        <div class="quick-install-box">
          <div class="install-tabs" role="tablist" aria-label="Sistema">
            <button class="install-tab active" data-install-tab="bash" role="tab" aria-selected="true">
              <svg class="icon" viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="4 17 10 11 4 5"/>
                <line x1="12" x2="20" y1="19" y2="19"/>
              </svg>
              Linux / macOS / WSL
            </button>
            <button class="install-tab" data-install-tab="ps" role="tab" aria-selected="false">
              <svg class="icon" viewBox="0 0 24 24" width="12" height="12" fill="currentColor">
                <path d="M3 3h18v18H3z" rx="3"/>
              </svg>
              Windows PowerShell
            </button>
          </div>
          <div class="quick-install-code" id="quickInstallCode">
            <span class="q-prompt q-prompt-bash" data-install-prompt="bash">$</span>
            <span class="q-prompt q-prompt-ps" data-install-prompt="ps" style="display:none">PS></span>
            <span class="q-cmd" data-install-cmd="bash">bash -c "curl -fsSL https://raw.githubusercontent.com/emilymontec/misi-framework/main/install.sh | sh"</span>
            <span class="q-cmd" data-install-cmd="ps" style="display:none">Invoke-Expression (New-Object Net.WebClient).DownloadString("https://raw.githubusercontent.com/emilymontec/misi-framework/main/install.sh")</span>
          </div>
          <button class="btn-copy-quick" id="btnCopyQuick"
            data-copy='bash -c "curl -fsSL https://raw.githubusercontent.com/emilymontec/misi-framework/main/install.sh | sh"'>
            <svg class="icon" viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect width="14" height="14" x="8" y="8" rx="2" ry="2"/>
              <path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/>
            </svg>
            <span>Copiar</span>
          </button>
        </div>

        <div class="hero-actions">
          <a href="#instalacion" class="btn-primary">
            <svg class="icon" viewBox="0 0 24 24" width="14" height="14" fill="currentColor"><rect width="18" height="18" x="3" y="3" rx="2"/></svg>
            EMPEZAR AHORA
          </a>
          <a href="#cli" class="btn-secondary">
            <svg class="icon" viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="4 17 10 11 4 5"/><line x1="12" x2="20" y1="19" y2="19"/></svg>
            VER EL CLI
          </a>
        </div>

        <!-- Live Demos Bar -->
        <div class="live-demos-bar">
          <div class="live-demos-title">
            <svg class="icon" viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
            <span>Rutas y demos activas:</span>
          </div>
          <div class="live-demos-links">
            <a href="/ui-kit" class="live-demo-chip">
              <svg class="icon" viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg>
              UI Kit Explorer (/ui-kit)
            </a>
            <a href="/api/ping" class="live-demo-chip">
              <svg class="icon" viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v6"/><path d="m4.93 10.93 4.24 4.24"/><path d="M2 12h6"/><path d="m4.93 13.07 4.24-4.24"/><path d="M14 12h8"/><path d="m14.83 9.17 4.24-4.24"/><path d="m14.83 14.83 4.24 4.24"/></svg>
              API Ping (/api/ping)
            </a>
            <a href="/saludo/Misi" class="live-demo-chip">
              <svg class="icon" viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="6" x2="6" y1="3" y2="15"/><circle cx="18" cy="6" r="3"/><circle cx="6" cy="18" r="3"/><path d="M18 9a9 9 0 0 1-9 9"/></svg>
              Ruta Dinámica (/saludo/Misi)
            </a>
          </div>
        </div>
      </div>
    </section>

    <!-- SUBSISTEMAS INCLUIDOS -->
    <section aria-label="Subsistemas incluidos">
      <div class="logos-grid">
        <div class="logo-item">Router<small>parámetros + middleware</small></div>
        <div class="logo-item">Database<small>PDO, sin ORM</small></div>
        <div class="logo-item">Validation<small>18 reglas</small></div>
        <div class="logo-item">Auth · CSRF<small>sesión + RBAC</small></div>
        <div class="logo-item">Storage<small>uploads seguros</small></div>
        <div class="logo-item">CLI<small>misi + make:*</small></div>
      </div>
      <div class="integration-banner">
        ... &nbsp;&nbsp; &gt; &nbsp;&nbsp; SIN COMPOSER OBLIGATORIO &nbsp;→&nbsp; LISTO PARA HOSTING COMPARTIDO &nbsp;&nbsp; &lt; &nbsp;&nbsp; ...
      </div>
    </section>

    <!-- CARACTERÍSTICAS -->
    <section class="page-section" id="caracteristicas" aria-labelledby="features-title">
      <div class="section-header">
        <div>
          <div class="section-tag">&gt; QUÉ TRAE MISI</div>
          <h2 class="section-title" id="features-title">/ Menos boilerplate. <br>Más código que sí importa. /</h2>
          <p class="section-lead">Construido y probado de punta a punta contra MySQL real, no solo diseñado en el papel.</p>
        </div>
        <a href="#instalacion" class="btn-primary">
          <svg class="icon" viewBox="0 0 24 24" width="14" height="14" fill="currentColor"><rect width="18" height="18" x="3" y="3" rx="2"/></svg>
          EMPEZAR AHORA
        </a>
      </div>

      <div class="cards-grid">
        <article class="card">
          <div class="card-num">// 001</div>
          <div>
            <h3 class="card-title">Sin ORM, SQL explícito</h3>
            <p class="card-text">Un wrapper delgado sobre PDO: <code>select()</code>, <code>insert()</code>, <code>transaction()</code>. Prepared statements siempre. Nunca adivinas qué SQL se ejecutó de verdad.</p>
          </div>
        </article>

        <article class="card">
          <div class="card-num">// 002</div>
          <div>
            <h3 class="card-title highlight">Auth y CSRF de fábrica</h3>
            <p class="card-text">Sesiones seguras, roles y permisos simples, protección CSRF automática en cada ruta que muta estado. No es opcional agregarlo después.</p>
          </div>
        </article>

        <article class="card">
          <div class="card-num">// 003</div>
          <div>
            <h3 class="card-title">Validación completa</h3>
            <p class="card-text">18 reglas listas — <code>required</code>, <code>email</code>, <code>unique</code>, <code>image</code>, <code>max_size</code>... con errores estructurados por campo.</p>
          </div>
        </article>

        <article class="card">
          <div class="card-num">// 004</div>
          <div>
            <h3 class="card-title">Storage sin sustos</h3>
            <p class="card-text">Subida de archivos con MIME real, nombre generado y bloqueo de path traversal — verificado con un intento de ataque real.</p>
          </div>
        </article>

        <article class="card">
          <div class="card-num">// 005</div>
          <div>
            <h3 class="card-title">Módulos reutilizables</h3>
            <p class="card-text">Empaqueta rutas y migraciones propias en <code>modules/</code>. Descubrimiento automático, sin tocar el core.</p>
          </div>
        </article>

        <article class="card">
          <div class="card-num">// 006</div>
          <div>
            <h3 class="card-title">CLI con generadores</h3>
            <p class="card-text"><code>misi</code>: <code>serve</code>, <code>db migrate</code>, <code>doctor</code>, <code>route:list</code>, <code>make:*</code>, <code>new</code> y <code>create business</code>. Cero dependencias externas.</p>
          </div>
        </article>
      </div>
    </section>

    <!-- INSTALACIÓN -->
    <section class="page-section" id="instalacion" aria-labelledby="install-title">
      <div class="section-header">
        <div>
          <div class="section-tag">&gt; PRIMEROS PASOS</div>
          <h2 class="section-title" id="install-title">/ De cero a servidor <br>corriendo en 5 pasos. /</h2>
        </div>
      </div>

      <div class="install-grid">
        <ol class="steps-list">
          <li>
            <span class="step-num">0</span>
            <span class="step-text">Instala el CLI global <code>misi</code> usando el comando universal (funciona en
              Linux, macOS, WSL y Windows PowerShell). El MISMO archivo <code>install.sh</code> sirve para todos.</span>
          </li>
          <li>
            <span class="step-num">1</span>
            <span class="step-text">Crea tu proyecto con <code>misi new</code> y entra a la carpeta.</span>
          </li>
          <li>
            <span class="step-num">2</span>
            <span class="step-text">Copia <code>.env.example</code> a <code>.env</code> y completa tus credenciales de
              MySQL.</span>
          </li>
          <li>
            <span class="step-num">3</span>
            <span class="step-text">Corre las migraciones — crean las tablas de usuarios, roles y permisos.</span>
          </li>
          <li>
            <span class="step-num">4</span>
            <span class="step-text">(Opcional) Siembra datos demo: un usuario admin con <code>changeme</code> como
              contraseña.</span>
          </li>
          <li>
            <span class="step-num">5</span>
            <span class="step-text">Levanta el servidor de desarrollo y abre <code>localhost:8000</code>.</span>
          </li>
        </ol>

        <div class="terminal terminal--install">
          <div class="terminal-bar">
            <div class="terminal-dots"><span></span><span></span><span></span></div>
            <div class="install-tabs install-tabs--inline" role="tablist" aria-label="Shell">
              <button class="install-tab active" data-install-tab="bash" role="tab" aria-selected="true">
                <svg class="icon" viewBox="0 0 24 24" width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <polyline points="4 17 10 11 4 5"/>
                  <line x1="12" x2="20" y1="19" y2="19"/>
                </svg>
                bash / sh
              </button>
              <button class="install-tab" data-install-tab="ps" role="tab" aria-selected="false">
                <svg class="icon" viewBox="0 0 24 24" width="11" height="11" fill="currentColor">
                  <path d="M3 3h18v18H3z" rx="3"/>
                </svg>
                PowerShell
              </button>
            </div>
            <div class="terminal-actions">
              <button class="btn-terminal-copy" id="btnInstallCopy" data-copy="# 0. instalar CLI global
bash -c &quot;curl -fsSL https://raw.githubusercontent.com/emilymontec/misi-framework/main/install.sh | sh&quot;

# 1. crear y entrar al proyecto
misi new mi-proyecto
cd mi-proyecto

# 2. preparar el entorno
cp .env.example .env
nano .env

# 3-4. base de datos
misi migrate
misi db:seed

# 5. arrancar
misi serve">
                <svg class="icon" viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/></svg>
                <span>Copiar</span>
              </button>
            </div>
          </div>
          <pre class="terminal-body"><code id="installCodeBlock"><span class="t-comment"># 0. instalar CLI global (mismo install.sh en todos los sistemas)</span>
<span class="t-prompt" data-install-prompt="bash">$</span><span class="t-prompt" data-install-prompt="ps" style="display:none">PS></span> <span data-install-cmd="bash">bash -c "curl -fsSL https://raw.githubusercontent.com/emilymontec/misi-framework/main/install.sh | sh"</span><span data-install-cmd="ps" style="display:none">Invoke-Expression (New-Object Net.WebClient).DownloadString("https://raw.githubusercontent.com/emilymontec/misi-framework/main/install.sh")</span>

<span class="t-comment"># 1. crear y entrar al proyecto</span>
<span class="t-prompt" data-install-prompt="bash">$</span><span class="t-prompt" data-install-prompt="ps" style="display:none">PS></span> misi new mi-proyecto
<span class="t-prompt" data-install-prompt="bash">$</span><span class="t-prompt" data-install-prompt="ps" style="display:none">PS></span> cd mi-proyecto

<span class="t-comment"># 2. preparar el entorno (.env)</span>
<span class="t-prompt" data-install-prompt="bash">$</span><span class="t-prompt" data-install-prompt="ps" style="display:none">PS></span> <span data-install-cmd="bash">cp .env.example .env</span><span data-install-cmd="ps" style="display:none">copy .env.example .env</span>
<span class="t-prompt" data-install-prompt="bash">$</span><span class="t-prompt" data-install-prompt="ps" style="display:none">PS></span> <span data-install-cmd="bash">nano .env</span><span data-install-cmd="ps" style="display:none">notepad .env</span>   <span class="t-comment"># DB_DATABASE, DB_USERNAME, DB_PASSWORD</span>

<span class="t-comment"># 3-4. base de datos (igual en ambos shells)</span>
<span class="t-prompt" data-install-prompt="bash">$</span><span class="t-prompt" data-install-prompt="ps" style="display:none">PS></span> misi migrate
<span class="t-out">Migrado: 001_create_users_table.php
Migrado: 002_create_roles_and_permissions.php
Migrado: 003_create_uploads_table.php</span>

<span class="t-prompt" data-install-prompt="bash">$</span><span class="t-prompt" data-install-prompt="ps" style="display:none">PS></span> misi db:seed
<span class="t-out">Usuario admin demo creado (admin@misi.test / changeme)</span>

<span class="t-comment"># 5. arrancar (igual en ambos shells)</span>
<span class="t-prompt" data-install-prompt="bash">$</span><span class="t-prompt" data-install-prompt="ps" style="display:none">PS></span> misi serve
<span class="t-out">Misi escuchando en http://127.0.0.1:8000</span></code></pre>
        </div>
      </div>
    </section>

    <!-- EJEMPLO DE USO -->
    <section class="page-section" id="ejemplo" aria-labelledby="example-title">
      <div class="section-header">
        <div>
          <div class="section-tag">&gt; DE RUTA A RESPUESTA</div>
          <h2 class="section-title" id="example-title">/ Un endpoint completo, <br>sin ceremonia. /</h2>
          <p class="section-lead">Ruta, validación y guardado en base de datos — así se ve un endpoint típico en un proyecto real sobre Misi.</p>
        </div>
      </div>

      <div class="example-grid">
        <div class="example-col">
          <div class="example-label">routes/web.php</div>
          <div class="terminal">
            <div class="terminal-bar">
              <div class="terminal-dots"><span></span><span></span><span></span></div>
              <div class="terminal-title">routes/web.php</div>
              <div class="terminal-actions">
                <button class="btn-terminal-copy" data-copy="use App\Http\Controllers\CustomerController;

$router->get('/customers', [CustomerController::class, 'index']);

$router->post(
    '/customers',
    [CustomerController::class, 'store'],
    ['auth', 'csrf']
);">
                  <svg class="icon" viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/></svg>
                  <span>Copiar</span>
                </button>
              </div>
            </div>
            <pre class="terminal-body"><code><span class="code-kw">use</span> App\Http\Controllers\<span class="code-class">CustomerController</span>;

<span class="code-fn">$router</span>->get(<span class="code-str">'/customers'</span>, [<span class="code-class">CustomerController</span>::class, <span class="code-str">'index'</span>]);

<span class="code-fn">$router</span>->post(
    <span class="code-str">'/customers'</span>,
    [<span class="code-class">CustomerController</span>::class, <span class="code-str">'store'</span>],
    [<span class="code-str">'auth'</span>, <span class="code-str">'csrf'</span>]
);</code></pre>
          </div>
        </div>

        <div class="example-col">
          <div class="example-label">app/Http/Controllers/CustomerController.php</div>
          <div class="terminal">
            <div class="terminal-bar">
              <div class="terminal-dots"><span></span><span></span><span></span></div>
              <div class="terminal-title">CustomerController.php</div>
              <div class="terminal-actions">
                <button class="btn-terminal-copy" data-copy="use Misi\Http\JsonResponse;
use Misi\Http\Request;

final class CustomerController
{
    public function store(Request $request): JsonResponse
    {
        $data = app()->validator()->validate($request->all(), [
            'name'  => ['required', 'max:150'],
            'email' => ['required', 'email', 'unique:customers,email'],
        ]);

        $id = app()->database()->insert('customers', $data);

        return JsonResponse::success(
            ['id' => $id],
            'Cliente creado',
            201
        );
    }
}">
                  <svg class="icon" viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/></svg>
                  <span>Copiar</span>
                </button>
              </div>
            </div>
            <pre class="terminal-body"><code><span class="code-kw">use</span> Misi\Http\JsonResponse;
<span class="code-kw">use</span> Misi\Http\Request;

<span class="code-kw">final class</span> <span class="code-class">CustomerController</span>
{
    <span class="code-kw">public function</span> <span class="code-fn">store</span>(Request $request): JsonResponse
    {
        $data = app()->validator()->validate($request->all(), [
            <span class="code-str">'name'</span>  => [<span class="code-str">'required'</span>, <span class="code-str">'max:150'</span>],
            <span class="code-str">'email'</span> => [<span class="code-str">'required'</span>, <span class="code-str">'email'</span>,
                        <span class="code-str">'unique:customers,email'</span>],
        ]);

        $id = app()->database()->insert(<span class="code-str">'customers'</span>, $data);

        <span class="code-kw">return</span> JsonResponse::success(
            [<span class="code-str">'id'</span> => $id],
            <span class="code-str">'Cliente creado'</span>,
            201
        );
    }
}</code></pre>
          </div>
        </div>
      </div>
    </section>

    <!-- CLI CON TABS INTERACTIVOS -->
    <section class="page-section cli-section-wrap" id="cli" aria-labelledby="cli-title">
      <div class="section-header">
        <div>
          <div class="section-tag">&gt; MISI CLI</div>
          <h2 class="section-title" id="cli-title">/ Un CLI, sin dependencias, <br>que hace lo necesario. /</h2>
          <p class="section-lead">Nada de Symfony Console — un solo script PHP que despacha comandos. Generadores incluidos, sin sobrescribir nada por accidente.</p>
        </div>
      </div>

      <!-- Tabs de comandos CLI -->
      <div class="cli-tabs" role="tablist" aria-label="Comandos de Misi CLI">
        <button class="cli-tab-btn active" data-tab="all" role="tab" aria-selected="true">
          <svg class="icon" viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="4 17 10 11 4 5"/><line x1="12" x2="20" y1="19" y2="19"/></svg>
          Todos los comandos
        </button>
        <button class="cli-tab-btn" data-tab="server" role="tab" aria-selected="false">
          <svg class="icon" viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="8" x="2" y="2" rx="2" ry="2"/><rect width="20" height="8" x="2" y="14" rx="2" ry="2"/><line x1="6" x2="6.01" y1="6" y2="6"/><line x1="6" x2="6.01" y1="18" y2="18"/></svg>
          Servidor y Salud
        </button>
        <button class="cli-tab-btn" data-tab="db" role="tab" aria-selected="false">
          <svg class="icon" viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M3 5V19A9 3 0 0 0 21 19V5"/><path d="M3 12A9 3 0 0 0 21 12"/></svg>
          Base de datos
        </button>
        <button class="cli-tab-btn" data-tab="make" role="tab" aria-selected="false">
          <svg class="icon" viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
          Generadores
        </button>
        <button class="cli-tab-btn" data-tab="business" role="tab" aria-selected="false">
          <svg class="icon" viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
          Negocio
        </button>
      </div>

      <div class="terminal">
        <div class="terminal-bar">
          <div class="terminal-dots"><span></span><span></span><span></span></div>
          <div class="terminal-title" id="cliTerminalTitle">misi cli — referencia</div>
          <div class="terminal-actions">
            <button class="btn-terminal-copy" id="btnCliCopy">
              <svg class="icon" viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/></svg>
              <span>Copiar</span>
            </button>
          </div>
        </div>
        <pre class="terminal-body"><code id="cliCodeBlock"><span class="t-prompt">$</span> misi serve
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
<span class="t-comment">  # nuevo proyecto a partir de Misi</span></code></pre>
      </div>
    </section>
  </main>

  <!-- FOOTER MEJORADO -->
  <footer class="site-footer">
    <div class="container">
      <div class="footer-top">
        <!-- Brand Col -->
        <div class="footer-brand-col">
          <div class="footer-logo">
            <span class="logo-spark" aria-hidden="true">✦</span> Misi
            <span class="version-tag">v1.3 · producción</span>
          </div>
          <p class="footer-tagline">
            Base de desarrollo en PHP para construir rápidamente sistemas administrativos y paneles de gestión para pequeños negocios sin dependencias pesadas ni curva de aprendizaje compleja.
          </p>
          <div class="footer-tech-badges">
            <span class="footer-badge">PHP 8.1+</span>
            <span class="footer-badge">MySQL / MariaDB</span>
            <span class="footer-badge">Sin Composer en prod</span>
          </div>
        </div>

        <!-- Col 1: Navegación -->
        <div class="footer-col">
          <div class="footer-col-title">Framework</div>
          <ul class="footer-nav-list">
            <li><a href="#inicio">Inicio</a></li>
            <li><a href="#caracteristicas">Características</a></li>
            <li><a href="#instalacion">Primeros pasos</a></li>
            <li><a href="#ejemplo">Ejemplo de código</a></li>
            <li><a href="#cli">Misi CLI</a></li>
          </ul>
        </div>

        <!-- Col 2: Demos y Endpoints -->
        <div class="footer-col">
          <div class="footer-col-title">Demos activas</div>
          <ul class="footer-nav-list">
            <li>
              <a href="/ui-kit">
                <svg class="icon" viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg>
                UI Kit Explorer
              </a>
            </li>
            <li>
              <a href="/api/ping">
                <svg class="icon" viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v6"/><path d="m4.93 10.93 4.24 4.24"/><path d="M2 12h6"/><path d="m4.93 13.07 4.24-4.24"/><path d="M14 12h8"/><path d="m14.83 9.17 4.24-4.24"/><path d="m14.83 14.83 4.24 4.24"/></svg>
                API Ping Endpoint
              </a>
            </li>
            <li>
              <a href="/saludo/Misi">
                <svg class="icon" viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="6" x2="6" y1="3" y2="15"/><circle cx="18" cy="6" r="3"/><circle cx="6" cy="18" r="3"/><path d="M18 9a9 9 0 0 1-9 9"/></svg>
                Ruta con parámetro
              </a>
            </li>
            <li>
              <a href="/api/csrf-token">
                <svg class="icon" viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                CSRF Token Endpoint
              </a>
            </li>
          </ul>
        </div>

        <!-- Col 3: Arquitectura y Filosofía -->
        <div class="footer-col">
          <div class="footer-col-title">Arquitectura</div>
          <ul class="footer-nav-list">
            <li><span class="footer-arch-item">PDO explícito sin ORM</span></li>
            <li><span class="footer-arch-item">RBAC y Auth por sesión</span></li>
            <li><span class="footer-arch-item">Módulos en <code>modules/</code></span></li>
            <li><span class="footer-arch-item">Business Core desacoplado</span></li>
            <li><span class="footer-arch-item">Hosting compartido / Apache</span></li>
          </ul>
        </div>
      </div>

      <div class="footer-bottom">
        <div class="footer-copyright">
          <p>© Misi Framework · Construido para sistemas administrativos reales.</p>
          <p class="footer-micro">Código libre y modular sin dependencias pesadas en producción.</p>
        </div>
        <a href="#inicio" class="btn-scroll-top" aria-label="Volver arriba">
          <span>Volver arriba</span>
          <svg class="icon" viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" x2="12" y1="19" y2="5"/><polyline points="5 12 12 5 19 12"/></svg>
        </a>
      </div>
    </div>
  </footer>

  <script src="/js/welcome.js"></script>

</body>
</html>
