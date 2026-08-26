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
  <style>
    /* --- RESET & VARIABLES --- */
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    :root {
      /* Paleta de color Misi */
      --c-periwinkle: #B8A2FF;
      --c-lime: #E3FC87;
      --c-navy: #253A82;
      --c-pink: #FFB2F7;
      --c-ice-blue: #C0E0FF;
      --c-violet: #AB9DFF;

      --bg-dark: var(--c-navy);
      --bg-light: #ffffff;
      --bg-subtle: #f8fafc;
      --primary: var(--c-violet);

      --text-main: #0f172a;
      --text-muted: #475569;
      --border-color: #cbd5e1;

      --font-display: 'Outfit', sans-serif;
      --font-tech: 'Space Grotesk', sans-serif;
      --font-body: 'Plus Jakarta Sans', sans-serif;
      --font-code: ui-monospace, SFMono-Regular, "SF Mono", Consolas, "Liberation Mono", monospace;
    }

    html { scroll-behavior: smooth; }

    body {
      font-family: var(--font-body);
      background-color: var(--bg-light);
      color: var(--text-main);
      line-height: 1.6;
      -webkit-font-smoothing: antialiased;
    }

    a { text-decoration: none; color: inherit; }

    a:focus-visible, button:focus-visible {
      outline: 2px solid var(--c-lime);
      outline-offset: 3px;
      border-radius: 4px;
    }

    .icon {
      display: inline-block;
      vertical-align: -2px;
      flex-shrink: 0;
    }

    .skip-link {
      position: absolute;
      top: -48px;
      left: 16px;
      background: var(--c-lime);
      color: var(--c-navy);
      font-family: var(--font-tech);
      font-weight: 700;
      padding: 10px 16px;
      border-radius: 8px;
      z-index: 100;
      transition: top 0.2s ease;
    }
    .skip-link:focus { top: 16px; }

    /* --- HERO BANNER SUPERIOR --- */
    .hero-banner {
      background-color: var(--bg-dark);
      color: #ffffff;
      padding: 24px 40px 32px;
      position: relative;
      z-index: 2;
      overflow: hidden;
      border-bottom: 2px solid rgba(184, 162, 255, 0.3);
      perspective: 900px;
    }

    .cube-field { position: absolute; inset: 0; z-index: 0; pointer-events: none; }

    .cube {
      position: absolute;
      transform-style: preserve-3d;
      animation: cube-spin linear infinite;
      pointer-events: auto;
      cursor: pointer;
      transition: filter 0.2s ease;
    }

    .cube__face { position: absolute; inset: 0; border-radius: 22%; opacity: 1; }
    .cube:hover { filter: brightness(1.15); animation-play-state: paused; }

    .cube--1 { width: 46px; height: 46px; top: 10%; left: 34%; animation-duration: 14s; }
    .cube--2 { width: 30px; height: 30px; top: 68%; left: 44%; animation-duration: 9s; animation-direction: reverse; }
    .cube--3 { width: 60px; height: 60px; top: 20%; left: 56%; animation-duration: 20s; }
    .cube--4 { width: 24px; height: 24px; top: 78%; left: 66%; animation-duration: 7s; }
    .cube--5 { width: 40px; height: 40px; top: 8%; left: 75%; animation-duration: 16s; animation-direction: reverse; }
    .cube--6 { width: 34px; height: 34px; top: 55%; left: 80%; animation-duration: 11s; }
    .cube--7 { width: 20px; height: 20px; top: 38%; left: 40%; animation-duration: 8s; animation-direction: reverse; }
    .cube--8 { width: 50px; height: 50px; top: 58%; left: 60%; animation-duration: 18s; }

    .cube--1 .cube__face--front  { background: rgb(184, 162, 255); transform: translateZ(23px); }
    .cube--1 .cube__face--back   { background: rgb(184, 162, 255); transform: rotateY(180deg) translateZ(23px); }
    .cube--1 .cube__face--right  { background: rgb(227, 252, 135); transform: rotateY(90deg) translateZ(23px); }
    .cube--1 .cube__face--left   { background: rgb(227, 252, 135); transform: rotateY(-90deg) translateZ(23px); }
    .cube--1 .cube__face--top    { background: rgb(255, 178, 247); transform: rotateX(90deg) translateZ(23px); }
    .cube--1 .cube__face--bottom { background: rgb(255, 178, 247); transform: rotateX(-90deg) translateZ(23px); }

    .cube--2 .cube__face--front  { background: rgb(227, 252, 135); transform: translateZ(15px); }
    .cube--2 .cube__face--back   { background: rgb(227, 252, 135); transform: rotateY(180deg) translateZ(15px); }
    .cube--2 .cube__face--right  { background: rgb(192, 224, 255); transform: rotateY(90deg) translateZ(15px); }
    .cube--2 .cube__face--left   { background: rgb(192, 224, 255); transform: rotateY(-90deg) translateZ(15px); }
    .cube--2 .cube__face--top    { background: rgb(184, 162, 255); transform: rotateX(90deg) translateZ(15px); }
    .cube--2 .cube__face--bottom { background: rgb(184, 162, 255); transform: rotateX(-90deg) translateZ(15px); }

    .cube--3 .cube__face--front  { background: rgb(255, 178, 247); transform: translateZ(30px); }
    .cube--3 .cube__face--back   { background: rgb(255, 178, 247); transform: rotateY(180deg) translateZ(30px); }
    .cube--3 .cube__face--right  { background: rgb(184, 162, 255); transform: rotateY(90deg) translateZ(30px); }
    .cube--3 .cube__face--left   { background: rgb(184, 162, 255); transform: rotateY(-90deg) translateZ(30px); }
    .cube--3 .cube__face--top    { background: rgb(227, 252, 135); transform: rotateX(90deg) translateZ(30px); }
    .cube--3 .cube__face--bottom { background: rgb(227, 252, 135); transform: rotateX(-90deg) translateZ(30px); }

    .cube--4 .cube__face--front  { background: rgb(192, 224, 255); transform: translateZ(12px); }
    .cube--4 .cube__face--back   { background: rgb(192, 224, 255); transform: rotateY(180deg) translateZ(12px); }
    .cube--4 .cube__face--right  { background: rgb(255, 178, 247); transform: rotateY(90deg) translateZ(12px); }
    .cube--4 .cube__face--left   { background: rgb(255, 178, 247); transform: rotateY(-90deg) translateZ(12px); }
    .cube--4 .cube__face--top    { background: rgb(184, 162, 255); transform: rotateX(90deg) translateZ(12px); }
    .cube--4 .cube__face--bottom { background: rgb(184, 162, 255); transform: rotateX(-90deg) translateZ(12px); }

    .cube--5 .cube__face--front  { background: rgb(184, 162, 255); transform: translateZ(20px); }
    .cube--5 .cube__face--back   { background: rgb(184, 162, 255); transform: rotateY(180deg) translateZ(20px); }
    .cube--5 .cube__face--right  { background: rgb(227, 252, 135); transform: rotateY(90deg) translateZ(20px); }
    .cube--5 .cube__face--left   { background: rgb(227, 252, 135); transform: rotateY(-90deg) translateZ(20px); }
    .cube--5 .cube__face--top    { background: rgb(192, 224, 255); transform: rotateX(90deg) translateZ(20px); }
    .cube--5 .cube__face--bottom { background: rgb(192, 224, 255); transform: rotateX(-90deg) translateZ(20px); }

    .cube--6 .cube__face--front  { background: rgb(255, 178, 247); transform: translateZ(17px); }
    .cube--6 .cube__face--back   { background: rgb(255, 178, 247); transform: rotateY(180deg) translateZ(17px); }
    .cube--6 .cube__face--right  { background: rgb(227, 252, 135); transform: rotateY(90deg) translateZ(17px); }
    .cube--6 .cube__face--left   { background: rgb(227, 252, 135); transform: rotateY(-90deg) translateZ(17px); }
    .cube--6 .cube__face--top    { background: rgb(184, 162, 255); transform: rotateX(90deg) translateZ(17px); }
    .cube--6 .cube__face--bottom { background: rgb(184, 162, 255); transform: rotateX(-90deg) translateZ(17px); }

    .cube--7 .cube__face--front  { background: rgb(227, 252, 135); transform: translateZ(10px); }
    .cube--7 .cube__face--back   { background: rgb(227, 252, 135); transform: rotateY(180deg) translateZ(10px); }
    .cube--7 .cube__face--right  { background: rgb(192, 224, 255); transform: rotateY(90deg) translateZ(10px); }
    .cube--7 .cube__face--left   { background: rgb(192, 224, 255); transform: rotateY(-90deg) translateZ(10px); }
    .cube--7 .cube__face--top    { background: rgb(255, 178, 247); transform: rotateX(90deg) translateZ(10px); }
    .cube--7 .cube__face--bottom { background: rgb(255, 178, 247); transform: rotateX(-90deg) translateZ(10px); }

    .cube--8 .cube__face--front  { background: rgb(184, 162, 255); transform: translateZ(25px); }
    .cube--8 .cube__face--back   { background: rgb(184, 162, 255); transform: rotateY(180deg) translateZ(25px); }
    .cube--8 .cube__face--right  { background: rgb(255, 178, 247); transform: rotateY(90deg) translateZ(25px); }
    .cube--8 .cube__face--left   { background: rgb(255, 178, 247); transform: rotateY(-90deg) translateZ(25px); }
    .cube--8 .cube__face--top    { background: rgb(227, 252, 135); transform: rotateX(90deg) translateZ(25px); }
    .cube--8 .cube__face--bottom { background: rgb(227, 252, 135); transform: rotateX(-90deg) translateZ(25px); }

    @keyframes cube-spin {
      0%   { transform: rotateX(0deg) rotateY(0deg); }
      100% { transform: rotateX(360deg) rotateY(360deg); }
    }

    .nav-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 48px; position: relative; }

    .nav-links {
      display: flex;
      gap: 18px;
      list-style: none;
      background: rgba(20, 30, 74, 0.55);
      padding: 8px 18px;
      border-radius: 30px;
      backdrop-filter: blur(12px);
      border: 1px solid rgba(255, 255, 255, 0.15);
    }

    .nav-links a {
      font-family: var(--font-tech);
      font-size: 0.82rem;
      letter-spacing: 0.3px;
      color: var(--c-ice-blue);
      font-weight: 600;
      transition: color 0.2s ease, transform 0.2s ease;
      display: inline-block;
    }

    .nav-links a.active, .nav-links a:hover { color: var(--c-lime); transform: translateY(-1px); }

    .nav-actions {
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .btn-ui-kit {
      font-family: var(--font-tech);
      font-size: 0.82rem;
      font-weight: 700;
      color: var(--c-ice-blue);
      background: rgba(255, 255, 255, 0.1);
      border: 1px solid rgba(255, 255, 255, 0.25);
      padding: 9px 16px;
      border-radius: 8px;
      transition: all 0.2s ease;
      display: inline-flex;
      align-items: center;
      gap: 7px;
    }

    .btn-ui-kit:hover {
      background: rgba(255, 255, 255, 0.2);
      color: #ffffff;
      border-color: var(--c-lime);
      transform: translateY(-1px);
    }

    .btn-contact {
      font-family: var(--font-tech);
      font-size: 0.85rem;
      font-weight: 700;
      letter-spacing: 0.3px;
      background: var(--c-lime);
      color: var(--c-navy);
      padding: 10px 22px;
      border-radius: 8px;
      transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
      box-shadow: 0 4px 14px rgba(227, 252, 135, 0.3);
      border: 1px solid var(--c-lime);
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }

    .btn-contact:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(227, 252, 135, 0.5);
      background: #edff9e;
    }

    .nav-toggle {
      display: none;
      background: rgba(255, 255, 255, 0.08);
      border: 1px solid rgba(255, 255, 255, 0.15);
      border-radius: 8px;
      width: 42px;
      height: 38px;
      cursor: pointer;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      gap: 4px;
    }
    .nav-toggle span { width: 20px; height: 2px; background: var(--c-ice-blue); border-radius: 2px; transition: transform 0.2s ease, opacity 0.2s ease; }
    .nav-toggle[aria-expanded="true"] span:nth-child(1) { transform: translateY(6px) rotate(45deg); }
    .nav-toggle[aria-expanded="true"] span:nth-child(2) { opacity: 0; }
    .nav-toggle[aria-expanded="true"] span:nth-child(3) { transform: translateY(-6px) rotate(-45deg); }

    .brand-hero { display: flex; justify-content: space-between; align-items: flex-end; }

    .logo {
      font-family: var(--font-display);
      font-size: 2.4rem;
      font-weight: 800;
      letter-spacing: -0.5px;
      display: flex;
      align-items: center;
      gap: 12px;
      color: #ffffff;
    }

    .logo-spark { color: var(--c-pink); font-size: 1.6rem; animation: pulse 2s infinite alternate; }

    @keyframes pulse {
      0% { transform: scale(1); opacity: 0.8; }
      100% { transform: scale(1.15); opacity: 1; }
    }

    .version-tag {
      font-family: var(--font-tech);
      font-size: 0.75rem;
      color: var(--c-navy);
      background: var(--c-ice-blue);
      padding: 3px 10px;
      border-radius: 6px;
      font-weight: 700;
      letter-spacing: 0.2px;
    }

    .scroll-notice {
      font-family: var(--font-tech);
      font-size: 0.78rem;
      color: var(--c-periwinkle);
      text-transform: uppercase;
      letter-spacing: 1px;
      font-weight: 600;
    }

    /* MARQUESINA (TICKER) */
    .ticker {
      background: var(--c-navy);
      border-top: 1px solid rgba(255, 255, 255, 0.1);
      border-bottom: 2px solid var(--c-periwinkle);
      padding: 12px 0;
      font-family: var(--font-tech);
      font-size: 0.85rem;
      font-weight: 600;
      letter-spacing: 0.5px;
      color: var(--c-ice-blue);
      overflow: hidden;
      display: flex;
    }

    .ticker-track {
      display: flex;
      white-space: nowrap;
      width: max-content;
      flex-shrink: 0;
      animation: marquee 26s linear infinite;
    }
    .ticker:hover .ticker-track { animation-play-state: paused; }
    .ticker-item { padding: 0 20px; }
    .ticker-highlight { color: var(--c-lime); font-weight: 700; margin-right: 6px; }

    @keyframes marquee {
      0% { transform: translateX(0); }
      100% { transform: translateX(-33.3333%); }
    }

    /* --- HERO PRINCIPAL --- */
    .container { max-width: 1200px; margin: 0 auto; padding: 0 24px; }

    .hero-main {
      padding: 70px 0 50px 0;
      display: grid;
      grid-template-columns: 1.25fr 0.75fr;
      gap: 56px;
      align-items: center;
    }

    .hero-title {
      font-family: var(--font-display);
      font-size: 3.6rem;
      font-weight: 800;
      line-height: 1.1;
      letter-spacing: -1.5px;
      color: var(--text-main);
    }

    .highlight-box {
      font-family: var(--font-display);
      color: var(--c-navy);
      background: var(--c-periwinkle);
      padding: 2px 14px;
      border-radius: 10px;
      display: inline-block;
      box-shadow: 4px 4px 0px var(--c-pink);
      border: 2px solid var(--c-navy);
      font-weight: 800;
    }

    .hero-right { display: flex; flex-direction: column; gap: 20px; }

    .trust-badge {
      font-family: var(--font-tech);
      display: flex;
      align-items: center;
      flex-wrap: wrap;
      gap: 10px;
      font-size: 0.8rem;
      font-weight: 600;
      color: var(--text-muted);
      background: var(--bg-subtle);
      padding: 10px 16px;
      border-radius: 14px;
      width: fit-content;
      border: 1px solid var(--border-color);
    }

    .trust-badge .dot { color: var(--c-navy); opacity: 0.35; }

    .hero-description {
      font-family: var(--font-body);
      font-size: 1.08rem;
      color: var(--text-muted);
      line-height: 1.65;
      font-weight: 500;
    }

    /* Instalador rápido interactivo */
    .quick-install-box {
      background: #0b1530;
      border: 2px solid var(--c-navy);
      border-radius: 10px;
      padding: 10px 14px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 10px;
      box-shadow: 3px 3px 0px var(--c-lime);
    }

    .quick-install-code {
      font-family: var(--font-code);
      font-size: 0.82rem;
      color: var(--c-ice-blue);
      overflow-x: auto;
      white-space: nowrap;
    }
    .quick-install-code span { color: var(--c-lime); font-weight: 700; }

    .btn-copy-quick {
      background: var(--c-lime);
      color: var(--c-navy);
      border: none;
      border-radius: 6px;
      padding: 6px 12px;
      font-family: var(--font-tech);
      font-size: 0.75rem;
      font-weight: 700;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      transition: all 0.2s ease;
      flex-shrink: 0;
    }
    .btn-copy-quick:hover { background: #edff9e; transform: scale(1.02); }

    .hero-actions { display: flex; align-items: center; gap: 14px; padding-top: 4px; flex-wrap: wrap; }

    .btn-primary {
      font-family: var(--font-tech);
      background: var(--c-navy);
      color: #ffffff;
      padding: 14px 28px;
      font-weight: 700;
      border-radius: 8px;
      font-size: 0.88rem;
      letter-spacing: 0.3px;
      display: inline-flex;
      align-items: center;
      gap: 10px;
      transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
      border: 2px solid var(--c-navy);
      box-shadow: 3px 3px 0px var(--c-violet);
      cursor: pointer;
    }

    .btn-primary:hover {
      background: var(--c-violet);
      border-color: var(--c-navy);
      color: var(--c-navy);
      transform: translate(-2px, -2px);
      box-shadow: 5px 5px 0px var(--c-navy);
    }

    .btn-secondary {
      font-family: var(--font-tech);
      font-size: 0.88rem;
      font-weight: 700;
      letter-spacing: 0.3px;
      color: var(--c-navy);
      padding: 14px 24px;
      border-radius: 8px;
      background: var(--c-ice-blue);
      display: inline-flex;
      align-items: center;
      gap: 8px;
      transition: all 0.2s ease;
      border: 2px solid var(--c-navy);
    }

    .btn-secondary:hover { background: var(--c-periwinkle); transform: translateY(-2px); }

    /* Live Demos Bar */
    .live-demos-bar {
      margin-top: 10px;
      background: var(--bg-subtle);
      border: 2px dashed var(--c-periwinkle);
      border-radius: 12px;
      padding: 14px 20px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 12px;
    }

    .live-demos-title {
      font-family: var(--font-tech);
      font-size: 0.82rem;
      font-weight: 700;
      color: var(--c-navy);
      display: flex;
      align-items: center;
      gap: 6px;
    }

    .live-demos-links {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
    }

    .live-demo-chip {
      font-family: var(--font-tech);
      font-size: 0.78rem;
      font-weight: 700;
      color: var(--c-navy);
      background: #ffffff;
      border: 1px solid var(--c-navy);
      padding: 5px 12px;
      border-radius: 20px;
      transition: all 0.2s ease;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      box-shadow: 2px 2px 0px rgba(37, 58, 130, 0.2);
    }

    .live-demo-chip:hover {
      background: var(--c-lime);
      transform: translate(-1px, -1px);
      box-shadow: 3px 3px 0px var(--c-navy);
    }

    /* --- SUBSISTEMAS INCLUIDOS (grid tipo "logos") --- */
    .logos-grid {
      display: grid;
      grid-template-columns: repeat(6, 1fr);
      border: 2px solid var(--c-navy);
      border-radius: 12px 12px 0 0;
      overflow: hidden;
      background: #ffffff;
    }

    .logo-item {
      font-family: var(--font-tech);
      padding: 22px 12px;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      gap: 2px;
      font-weight: 700;
      color: var(--text-muted);
      border-right: 1px solid var(--border-color);
      font-size: 0.85rem;
      text-align: center;
      transition: background 0.2s, color 0.2s;
    }

    .logo-item:hover { background: var(--bg-subtle); color: var(--c-navy); }
    .logo-item:last-child { border-right: none; }
    .logo-item small { font-family: var(--font-body); font-weight: 500; font-size: 0.68rem; color: var(--text-muted); }

    .integration-banner {
      background: var(--c-navy);
      border: 2px solid var(--c-navy);
      border-top: none;
      border-radius: 0 0 12px 12px;
      padding: 18px 20px;
      text-align: center;
      font-family: var(--font-tech);
      color: var(--c-lime);
      font-weight: 700;
      font-size: 0.8rem;
      letter-spacing: 0.6px;
    }

    /* --- SECCIONES GENERALES --- */
    section.page-section { padding: 90px 0 0; }

    .section-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-end;
      border-top: 2px solid var(--border-color);
      padding-top: 32px;
      margin-bottom: 48px;
      gap: 24px;
      flex-wrap: wrap;
    }

    .section-tag {
      font-family: var(--font-tech);
      font-size: 0.8rem;
      color: var(--c-navy);
      font-weight: 700;
      background: var(--c-ice-blue);
      padding: 4px 12px;
      border-radius: 6px;
      display: inline-block;
      margin-bottom: 12px;
      border: 1px solid rgba(37, 58, 130, 0.2);
      letter-spacing: 0.3px;
    }

    .section-title {
      font-family: var(--font-display);
      font-size: 2.3rem;
      font-weight: 800;
      letter-spacing: -0.8px;
      color: var(--text-main);
      line-height: 1.15;
    }

    .section-lead {
      font-family: var(--font-body);
      color: var(--text-muted);
      font-size: 1rem;
      max-width: 60ch;
      margin-top: 10px;
    }

    /* --- CARACTERÍSTICAS (cards) --- */
    .cards-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 28px; padding-bottom: 100px; }

    .card {
      border: 2px solid var(--c-navy);
      border-radius: 14px;
      padding: 30px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      min-height: 220px;
      background: #ffffff;
      transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
      position: relative;
      box-shadow: 4px 4px 0px var(--border-color);
    }

    .card-num { font-family: var(--font-tech); font-size: 0.85rem; color: var(--text-muted); text-align: right; font-weight: 700; margin-bottom: 10px; }

    .card-title { font-family: var(--font-display); font-size: 1.25rem; font-weight: 700; margin-bottom: 10px; color: var(--text-main); letter-spacing: -0.3px; }

    .card-title.highlight {
      color: var(--c-navy);
      background: var(--c-pink);
      display: inline-block;
      padding: 2px 8px;
      border-radius: 6px;
      border: 1px solid var(--c-navy);
    }

    .card-text { font-family: var(--font-body); font-size: 0.93rem; color: var(--text-muted); line-height: 1.6; font-weight: 500; }

    .card:hover { transform: translate(-4px, -4px); box-shadow: 8px 8px 0px var(--c-navy); }

    /* --- INSTALACIÓN (pasos numerados) --- */
    .install-grid { display: grid; grid-template-columns: 0.9fr 1.1fr; gap: 48px; align-items: start; padding-bottom: 100px; }

    .steps-list { list-style: none; display: flex; flex-direction: column; gap: 20px; }

    .steps-list li { display: flex; gap: 16px; align-items: flex-start; }

    .step-num {
      font-family: var(--font-tech);
      font-weight: 800;
      font-size: 0.85rem;
      color: var(--c-navy);
      background: var(--c-lime);
      border: 2px solid var(--c-navy);
      width: 30px;
      height: 30px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
    }

    .step-text { font-family: var(--font-body); font-size: 0.95rem; color: var(--text-main); padding-top: 3px; }
    .step-text code {
      font-family: var(--font-code);
      background: var(--bg-subtle);
      border: 1px solid var(--border-color);
      padding: 1px 6px;
      border-radius: 5px;
      font-size: 0.85em;
    }

    /* --- TERMINAL (bloques de código) --- */
    .terminal {
      border: 2px solid var(--c-navy);
      border-radius: 14px;
      overflow: hidden;
      box-shadow: 4px 4px 0px var(--c-lime);
      background: #0b1530;
      min-width: 0;
      position: relative;
    }

    .terminal-bar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 10px 16px;
      background: var(--c-navy);
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .terminal-dots { display: flex; gap: 7px; align-items: center; }
    .terminal-dots span { width: 11px; height: 11px; border-radius: 50%; display: inline-block; }
    .terminal-dots span:nth-child(1) { background: #ff6159; }
    .terminal-dots span:nth-child(2) { background: #ffbd2e; }
    .terminal-dots span:nth-child(3) { background: #28ca41; }

    .terminal-title {
      font-family: var(--font-tech);
      font-size: 0.72rem;
      color: var(--c-ice-blue);
      font-weight: 600;
      opacity: 0.8;
      letter-spacing: 0.5px;
    }

    .terminal-actions {
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .btn-terminal-copy {
      background: rgba(255, 255, 255, 0.1);
      border: 1px solid rgba(255, 255, 255, 0.2);
      color: var(--c-ice-blue);
      border-radius: 5px;
      padding: 4px 8px;
      font-family: var(--font-tech);
      font-size: 0.72rem;
      cursor: pointer;
      transition: all 0.2s ease;
      display: inline-flex;
      align-items: center;
      gap: 5px;
    }

    .btn-terminal-copy:hover {
      background: var(--c-lime);
      color: var(--c-navy);
      border-color: var(--c-lime);
    }

    .terminal-body {
      margin: 0;
      padding: 22px 24px;
      font-family: var(--font-code);
      font-size: 0.82rem;
      line-height: 1.8;
      color: var(--c-ice-blue);
      white-space: pre-wrap;
      overflow-wrap: anywhere;
    }

    .t-prompt { color: var(--c-lime); font-weight: 700; }
    .t-comment { color: rgba(192, 224, 255, 0.45); }
    .t-out { color: rgba(192, 224, 255, 0.75); }

    /* Resaltado manual y ligero para los snippets PHP */
    .code-kw     { color: var(--c-periwinkle); font-weight: 600; }
    .code-str    { color: var(--c-lime); }
    .code-class  { color: var(--c-pink); font-weight: 600; }
    .code-fn     { color: #ffffff; font-weight: 600; }
    .code-cmt    { color: rgba(192, 224, 255, 0.45); font-style: italic; }

    /* --- EJEMPLO DE USO --- */
    .example-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; padding-bottom: 100px; }
    .example-col { display: flex; flex-direction: column; gap: 10px; min-width: 0; }
    .example-label { font-family: var(--font-tech); font-size: 0.78rem; font-weight: 700; color: var(--c-navy); letter-spacing: 0.4px; }

    /* --- CLI CON TABS INTERACTIVOS --- */
    .cli-section-wrap { padding-bottom: 100px; }

    .cli-tabs {
      display: flex;
      gap: 8px;
      margin-bottom: 14px;
      flex-wrap: wrap;
    }

    .cli-tab-btn {
      font-family: var(--font-tech);
      font-size: 0.8rem;
      font-weight: 700;
      background: var(--bg-subtle);
      color: var(--text-muted);
      border: 2px solid var(--border-color);
      border-radius: 8px;
      padding: 8px 16px;
      cursor: pointer;
      transition: all 0.2s ease;
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }

    .cli-tab-btn:hover {
      border-color: var(--c-navy);
      color: var(--c-navy);
    }

    .cli-tab-btn.active {
      background: var(--c-navy);
      color: #ffffff;
      border-color: var(--c-navy);
      box-shadow: 3px 3px 0px var(--c-lime);
    }

    /* --- FOOTER MEJORADO --- */
    .site-footer {
      background: var(--c-navy);
      color: var(--c-ice-blue);
      padding: 70px 0 36px;
      font-family: var(--font-body);
      margin-top: 100px;
      border-top: 3px solid var(--c-periwinkle);
      position: relative;
    }

    .footer-top {
      display: grid;
      grid-template-columns: 1.4fr 0.8fr 1fr 1.1fr;
      gap: 40px;
      padding-bottom: 48px;
      border-bottom: 1px solid rgba(192, 224, 255, 0.15);
    }

    .footer-brand-col {
      display: flex;
      flex-direction: column;
      gap: 16px;
    }

    .footer-logo {
      font-family: var(--font-display);
      font-size: 1.8rem;
      font-weight: 800;
      letter-spacing: -0.5px;
      display: flex;
      align-items: center;
      gap: 10px;
      color: #ffffff;
    }

    .footer-tagline {
      font-size: 0.88rem;
      color: rgba(192, 224, 255, 0.8);
      line-height: 1.6;
      max-width: 38ch;
    }

    .footer-tech-badges {
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
      margin-top: 4px;
    }

    .footer-badge {
      font-family: var(--font-tech);
      font-size: 0.72rem;
      font-weight: 700;
      color: var(--c-lime);
      background: rgba(227, 252, 135, 0.1);
      border: 1px solid rgba(227, 252, 135, 0.3);
      padding: 3px 9px;
      border-radius: 6px;
      letter-spacing: 0.3px;
    }

    .footer-col {
      display: flex;
      flex-direction: column;
      gap: 16px;
    }

    .footer-col-title {
      font-family: var(--font-tech);
      font-size: 0.85rem;
      font-weight: 700;
      letter-spacing: 0.5px;
      color: var(--c-lime);
      text-transform: uppercase;
    }

    .footer-nav-list {
      list-style: none;
      display: flex;
      flex-direction: column;
      gap: 10px;
      padding: 0;
      margin: 0;
    }

    .footer-nav-list a {
      font-family: var(--font-tech);
      font-size: 0.84rem;
      font-weight: 600;
      color: var(--c-ice-blue);
      transition: all 0.2s ease;
      display: inline-flex;
      align-items: center;
      gap: 7px;
    }

    .footer-nav-list a:hover {
      color: var(--c-lime);
      transform: translateX(3px);
    }

    .footer-arch-item {
      font-family: var(--font-tech);
      font-size: 0.82rem;
      color: rgba(192, 224, 255, 0.75);
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }

    .footer-arch-item code {
      font-family: var(--font-code);
      background: rgba(255, 255, 255, 0.1);
      padding: 1px 5px;
      border-radius: 4px;
      font-size: 0.78rem;
      color: var(--c-periwinkle);
    }

    .footer-bottom {
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 20px;
      padding-top: 32px;
    }

    .footer-copyright p {
      font-family: var(--font-tech);
      font-size: 0.82rem;
      color: var(--c-ice-blue);
    }

    .footer-copyright .footer-micro {
      font-size: 0.75rem;
      color: rgba(192, 224, 255, 0.55);
      margin-top: 4px;
    }

    .btn-scroll-top {
      font-family: var(--font-tech);
      font-size: 0.8rem;
      font-weight: 700;
      background: rgba(255, 255, 255, 0.08);
      color: var(--c-ice-blue);
      border: 1px solid rgba(255, 255, 255, 0.2);
      padding: 8px 14px;
      border-radius: 8px;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      transition: all 0.2s ease;
    }

    .btn-scroll-top:hover {
      background: var(--c-lime);
      color: var(--c-navy);
      border-color: var(--c-lime);
      transform: translateY(-2px);
    }

    @media (prefers-reduced-motion: reduce) {
      html { scroll-behavior: auto; }
      .logo-spark { animation: none; }
      .ticker-track { animation: none; }
      .card, .btn-primary, .btn-secondary, .btn-contact, .nav-links a { transition: none; }
    }

    /* ===== 1200px: pantalla grande ajustada ===== */
    @media (max-width: 1200px) {
      .container { padding: 0 32px; }
      .hero-banner { padding: 22px 32px 28px; }
      .hero-title { font-size: 3.2rem; }
    }

    /* ===== 992px: tablet landscape ===== */
    @media (max-width: 992px) {
      /* Contenedor */
      .container { padding: 0 24px; }
      .hero-banner { padding: 20px 24px 28px; overflow: visible; }
      .cube-field { overflow: hidden; }

      /* Navegación: hamburger menu */
      .nav-top { position: relative; }
      .nav-toggle { display: flex; }
      .nav-links {
        display: none;
        position: absolute;
        top: calc(100% + 12px);
        left: 0;
        right: auto;
        flex-direction: column;
        align-items: flex-start;
        gap: 2px;
        background: #162050;
        border: 1px solid rgba(255, 255, 255, 0.15);
        border-radius: 14px;
        padding: 12px 20px;
        min-width: 220px;
        box-shadow: 0 12px 32px rgba(0, 0, 0, 0.4);
        z-index: 30;
        background-clip: padding-box;
        backdrop-filter: blur(16px);
      }
      .nav-links.open { display: flex; }
      .nav-links a { padding: 8px 0; font-size: 0.88rem; }

      /* Ocultar btn-ui-kit en tablet/mobile (ya está en la nav) */
      .btn-ui-kit { display: none; }

      /* Hero grids */
      .hero-main {
        grid-template-columns: 1fr;
        gap: 40px;
        padding: 52px 0 40px;
      }
      .hero-title { font-size: 2.9rem; }
      .hero-right { max-width: 100%; }

      /* Feature cards */
      .cards-grid { grid-template-columns: repeat(2, 1fr); }

      /* Install + Example grids → stack */
      .install-grid { grid-template-columns: 1fr; }
      .example-grid { grid-template-columns: 1fr; }

      /* Section header: stack titulo + CTA */
      .section-header { flex-direction: column; align-items: flex-start; gap: 20px; }

      /* Footer */
      .footer-top { grid-template-columns: 1fr 1fr; gap: 36px; }
    }

    /* ===== 768px: tablet portrait ===== */
    @media (max-width: 768px) {
      .hero-banner { padding: 18px 20px 24px; }
      .hero-title { font-size: 2.5rem; letter-spacing: -1px; }
      .logo { font-size: 2rem; }

      /* Nav actions: solo toggle + btn-contact */
      .nav-actions { gap: 8px; }

      /* Quick install: allow text to wrap */
      .quick-install-box { flex-direction: column; align-items: stretch; gap: 10px; }
      .quick-install-code { font-size: 0.75rem; white-space: normal; word-break: break-all; }
      .btn-copy-quick { align-self: flex-start; }

      /* Hero actions: stack buttons */
      .hero-actions { flex-direction: column; align-items: flex-start; }
      .btn-primary, .btn-secondary { width: 100%; justify-content: center; }

      /* Live demos chips: smaller */
      .live-demos-bar { flex-direction: column; align-items: flex-start; gap: 10px; }
      .live-demo-chip { font-size: 0.75rem; padding: 5px 10px; }

      /* Logos grid: 3 cols */
      .logos-grid { grid-template-columns: repeat(3, 1fr); }
      .logo-item { border-bottom: 1px solid var(--border-color); font-size: 0.78rem; padding: 16px 8px; }
      .logo-item:nth-child(3n) { border-right: none; }
      .logo-item:nth-last-child(-n+3) { border-bottom: none; }

      /* Cards */
      .cards-grid { grid-template-columns: 1fr; }

      /* Section */
      .section-title { font-size: 1.85rem; }
      .page-section { padding: 60px 0 0; }

      /* Terminal bar: hide title on small */
      .terminal-title { display: none; }
      .terminal-body { font-size: 0.78rem; padding: 16px 16px; }

      /* CLI tabs: scroll horizontal */
      .cli-tabs { flex-wrap: nowrap; overflow-x: auto; -webkit-overflow-scrolling: touch; padding-bottom: 4px; }
      .cli-tab-btn { white-space: nowrap; flex-shrink: 0; font-size: 0.75rem; padding: 7px 12px; }

      /* Footer: 2 col → 1 col */
      .footer-top { grid-template-columns: 1fr; gap: 28px; }
      .site-footer { padding: 48px 0 28px; margin-top: 60px; }
    }

    /* ===== 600px: large phone ===== */
    @media (max-width: 600px) {
      .hero-title { font-size: 2.1rem; }
      .trust-badge { flex-direction: column; align-items: flex-start; gap: 6px; }
      .trust-badge .dot { display: none; }
      .section-title { font-size: 1.7rem; }

      /* Footer bottom */
      .footer-bottom { flex-direction: column; align-items: flex-start; gap: 16px; }
      .footer-copyright { width: 100%; }
    }

    /* ===== 480px: standard phone ===== */
    @media (max-width: 480px) {
      .container { padding: 0 16px; }
      .hero-banner { padding: 16px 16px 22px; }
      .hero-title { font-size: 1.85rem; letter-spacing: -0.8px; }
      .hero-description { font-size: 0.95rem; }
      .logo { font-size: 1.75rem; }
      .version-tag { font-size: 0.7rem; padding: 2px 7px; }
      .brand-hero { flex-direction: column; align-items: flex-start; gap: 10px; }
      .scroll-notice { display: none; }

      /* Logos: 2 cols on very small */
      .logos-grid { grid-template-columns: repeat(2, 1fr); }
      .logo-item:nth-child(3n) { border-right: 1px solid var(--border-color); }
      .logo-item:nth-child(2n) { border-right: none; }
      .logo-item:nth-last-child(-n+3) { border-bottom: 1px solid var(--border-color); }
      .logo-item:nth-last-child(-n+2) { border-bottom: none; }

      /* Hero actions */
      .hero-actions { gap: 10px; }
      .btn-primary, .btn-secondary { font-size: 0.82rem; padding: 12px 20px; }

      /* Section */
      .section-title { font-size: 1.55rem; letter-spacing: -0.5px; }
      .section-header { padding-top: 24px; margin-bottom: 32px; }

      /* Cards */
      .card { padding: 22px; min-height: auto; }

      /* Terminal */
      .terminal-body { font-size: 0.72rem; padding: 14px 14px; }
      .terminal-bar { padding: 8px 12px; }

      /* Ticker */
      .ticker { padding: 10px 0; font-size: 0.78rem; }

      /* Steps */
      .steps-list li { gap: 12px; }
      .step-text { font-size: 0.88rem; }

      /* Footer */
      .footer-logo { font-size: 1.5rem; }
      .footer-tagline { font-size: 0.82rem; }
    }

    /* ===== 360px: very small phone ===== */
    @media (max-width: 360px) {
      .hero-title { font-size: 1.65rem; }
      .logo { font-size: 1.55rem; }
      .btn-contact { font-size: 0.78rem; padding: 8px 14px; }
      .quick-install-code { font-size: 0.68rem; }
      .hero-description { font-size: 0.88rem; }
    }
  </style>
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
            <span class="q-cmd" data-install-cmd="ps" style="display:none">& { $script = (irm -Uri https://raw.githubusercontent.com/emilymontec/misi-framework/main/install.sh -UseBasicParsing | Out-String); & ([scriptblock]::Create($script)) }</span>
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
<span class="t-prompt" data-install-prompt="bash">$</span><span class="t-prompt" data-install-prompt="ps" style="display:none">PS></span> <span data-install-cmd="bash">bash -c "curl -fsSL https://raw.githubusercontent.com/emilymontec/misi-framework/main/install.sh | sh"</span><span data-install-cmd="ps" style="display:none">& { $script = (irm -Uri https://raw.githubusercontent.com/emilymontec/misi-framework/main/install.sh -UseBasicParsing | Out-String); & ([scriptblock]::Create($script)) }</span>

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

  <script>
    (function () {
      'use strict';

      var installCommands = {
        bash: {
          quick: 'bash -c "curl -fsSL https://raw.githubusercontent.com/emilymontec/misi-framework/main/install.sh | sh"',
          full: '# 0. instalar CLI global\nbash -c "curl -fsSL https://raw.githubusercontent.com/emilymontec/misi-framework/main/install.sh | sh"\n\n# 1. crear y entrar al proyecto\nmisi new mi-proyecto\ncd mi-proyecto\n\n# 2. preparar el entorno\ncp .env.example .env\nnano .env\n\n# 3-4. base de datos\nmisi migrate\nmisi db:seed\n\n# 5. arrancar\nmisi serve'
        },
        ps: {
          quick: '& { $script = (irm -Uri https://raw.githubusercontent.com/emilymontec/misi-framework/main/install.sh -UseBasicParsing | Out-String); & ([scriptblock]::Create($script)) }',
          full: '# 0. instalar CLI global\n& { $script = (irm -Uri https://raw.githubusercontent.com/emilymontec/misi-framework/main/install.sh -UseBasicParsing | Out-String); & ([scriptblock]::Create($script)) }\n\n# 1. crear y entrar al proyecto\nmisi new mi-proyecto\ncd mi-proyecto\n\n# 2. preparar el entorno\ncopy .env.example .env\nnotepad .env\n\n# 3-4. base de datos\nmisi migrate\nmisi db:seed\n\n# 5. arrancar\nmisi serve'
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
  </script>

</body>
</html>
