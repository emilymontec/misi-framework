#!/bin/sh
#
# Instalador global de Misi -- UNIVERSAL.
#
# El MISMO archivo funciona en:
#   - Linux / macOS / WSL / Git Bash:    curl -fsSL <URL> | sh
#   - Windows PowerShell (nativo):       irm <URL> | iex
#
# El encabezado de abajo es sintaxis polyglot: en sh/bash/zsh no hace
# nada (es un here-doc / redirect sin efecto); en PowerShell abre un
# comentario multilínea (<#) que ignora TODO el cuerpo sh hasta el #>
# que aparece al final del mismo. Así cada intérprete ejecuta solo su
# parte y jamás ve el código del otro.
#
# Qué hace, en orden (igual en ambos shells):
#   1. Verifica requisitos (git, php) sin abortar si falta php.
#   2. Clona o actualiza el framework en $MISI_HOME (~/.misi/framework
#      o $env:USERPROFILE\.misi\framework en Windows). Ese checkout es
#      la plantilla que usa "misi new".
#   3. Instala el wrapper global de "misi" en un directorio escribible
#      del usuario (preferentemente uno que ya esté en tu PATH), con
#      fallback seguro para no requerir sudo / admin.
#
# Variables de entorno que puedes fijar antes:
#   MISI_REPO   URL del repo git   (default: https://github.com/emilymontec/misi-framework.git)
#   MISI_REF    rama o tag         (default: main)
#   MISI_HOME   ruta de instalación (default: ~/.misi/framework)

<# 2>/dev/null; :
set -eu

MISI_REPO="${MISI_REPO:-https://github.com/emilymontec/misi-framework.git}"
MISI_REF="${MISI_REF:-main}"
MISI_HOME="${MISI_HOME:-$HOME/.misi/framework}"

info()  { printf '  %s\n' "$1"; }
ok()    { printf '  \033[32m✓\033[0m %s\n' "$1"; }
warn()  { printf '  \033[33m!\033[0m %s\n' "$1"; }
error() { printf '  \033[31m✗\033[0m %s\n' "$1" >&2; }

command_exists() {
    command -v "$1" >/dev/null 2>&1
}

echo "Instalando Misi..."
echo ""

if ! command_exists git; then
    error "git no está instalado -- es necesario para descargar/actualizar Misi."
    exit 1
fi
ok "git encontrado"

if command_exists php; then
    php_version="$(php -r 'echo PHP_VERSION;' 2>/dev/null || echo '0.0.0')"
    ok "PHP $php_version encontrado"
else
    warn "php no está en el PATH todavía -- instálalo antes de usar 'misi'."
    warn "El framework se instala igual; 'misi doctor' te lo va a recordar."
fi

if [ -d "$MISI_HOME/.git" ]; then
    info "Ya existe una instalación en $MISI_HOME -- actualizando..."
    git -C "$MISI_HOME" fetch --depth 1 origin "$MISI_REF"
    git -C "$MISI_HOME" checkout "$MISI_REF"
    git -C "$MISI_HOME" reset --hard "origin/$MISI_REF"
    ok "Actualizado a la última versión de '$MISI_REF'"
elif [ -e "$MISI_HOME" ]; then
    error "$MISI_HOME existe pero no es una instalación de Misi (no tiene .git)."
    error "Muévelo o elimínalo, o define MISI_HOME apuntando a otra ruta."
    exit 1
else
    mkdir -p "$(dirname "$MISI_HOME")"
    info "Clonando $MISI_REPO ($MISI_REF) en $MISI_HOME..."
    git clone --depth 1 --branch "$MISI_REF" "$MISI_REPO" "$MISI_HOME"
    ok "Framework instalado en $MISI_HOME"
fi

chmod +x "$MISI_HOME/bin/biz" "$MISI_HOME/bin/misi"

can_write() {
    [ -d "$1" ] || return 1
    tmpf="$(mktemp "$1/.misi_test_write.XXXXXX" 2>/dev/null)" || return 1
    rm -f "$tmpf" 2>/dev/null
    return 0
}

link_target=""
system_wide_target=""

for candidate in "$HOME/.local/bin" "$HOME/bin"; do
    case ":$PATH:" in
        *":$candidate:"*)
            link_target="$candidate"
            break
            ;;
    esac
done

if [ -z "$link_target" ]; then
    for candidate in "$HOME/.local/bin" "$HOME/bin"; do
        mkdir -p "$candidate" 2>/dev/null || true
        if can_write "$candidate"; then
            link_target="$candidate"
            break
        fi
    done
fi

if [ -z "$link_target" ]; then
    if can_write "/usr/local/bin"; then
        link_target="/usr/local/bin"
    else
        system_wide_target="/usr/local/bin"
    fi
fi

use_sudo=0
if [ -z "$link_target" ] && [ -n "$system_wide_target" ]; then
    info "No se puede escribir en ~/.local/bin ni ~/.bin sin privilegios."
    info "Se intentará con 'sudo' en $system_wide_target..."
    if command_exists sudo && sudo -n true 2>/dev/null; then
        use_sudo=1
        link_target="$system_wide_target"
    else
        link_target="$HOME/.local/bin"
        warn "sudo no disponible o requiere contraseña."
        warn "Se usará $link_target (agrega la ruta al PATH a mano)."
    fi
fi

if [ -z "$link_target" ]; then
    link_target="$HOME/.local/bin"
fi

if [ "$use_sudo" -eq 1 ]; then
    sudo mkdir -p "$link_target"
else
    mkdir -p "$link_target"
fi

install_symlink() {
    src="$1"; dst="$2"; use_sudo="$3"
    if [ "$use_sudo" -eq 1 ]; then
        sudo ln -sf "$src" "$dst"
    else
        ln -sf "$src" "$dst"
    fi
}

install_copy() {
    src="$1"; dst="$2"; use_sudo="$3"
    if [ "$use_sudo" -eq 1 ]; then
        sudo cp -f "$src" "$dst"
        sudo chmod +x "$dst"
    else
        cp -f "$src" "$dst"
        chmod +x "$dst"
    fi
}

case "$(uname -s)" in
    MINGW*|MSYS*|CYGWIN*)
        if install_copy "$MISI_HOME/bin/misi" "$link_target/misi" "$use_sudo"; then
            ok "misi copiado en $link_target/misi"
            warn "En Windows se copia en vez de enlazar -- si actualizas Misi,"
            warn "vuelve a correr este instalador para refrescar la copia."
        else
            error "No se pudo copiar 'misi' en $link_target/misi."
            error "Revisa permisos de escritura en $link_target."
            exit 1
        fi
        ;;
    *)
        if install_symlink "$MISI_HOME/bin/misi" "$link_target/misi" "$use_sudo"; then
            ok "misi enlazado en $link_target/misi"
        else
            warn "No se pudo crear enlace simbólico -- intentando copia directa..."
            if install_copy "$MISI_HOME/bin/misi" "$link_target/misi" "$use_sudo"; then
                ok "misi copiado en $link_target/misi"
                warn "Instalación por copia: si actualizas Misi,"
                warn "vuelve a correr este instalador para refrescar la copia."
            else
                error "No se pudo instalar 'misi' en $link_target/misi."
                error "Revisa permisos de escritura en $link_target,"
                error "o define MISI_HOME a una ruta alternativa."
                exit 1
            fi
        fi
        ;;
esac

echo ""

case ":$PATH:" in
    *":$link_target:"*)
        echo "Instalación completa. Prueba:"
        echo ""
        echo "  misi version"
        echo "  misi doctor"
        echo "  misi new mi-primer-sitio"
        ;;
    *)
        warn "$link_target no está en tu PATH todavía."
        echo ""
        echo "Agrega esto a tu ~/.bashrc, ~/.zshrc o equivalente, y abre una terminal nueva:"
        echo ""
        echo "  export PATH=\"$link_target:\$PATH\""
        echo ""
        echo "Después de eso:"
        echo ""
        echo "  misi version"
        echo "  misi new mi-primer-sitio"
        ;;
esac

exit 0
#>

# ============================================================
# A PARTIR DE AQUÍ: código PowerShell (intérprete de Windows).
# El cuerpo sh ya hizo exit 0; PowerShell ve el <# ... #> como
# comentario multilínea y arranca la ejecución justo aquí.
# ============================================================

$ErrorActionPreference = 'Stop'

function Write-Info($m)  { Write-Host "  $m" }
function Write-Ok($m)    { Write-Host "  " -NoNewline; Write-Host ([char]0x2713) -ForegroundColor Green -NoNewline; Write-Host " $m" }
function Write-Warn($m)  { Write-Host "  " -NoNewline; Write-Host '!' -ForegroundColor Yellow -NoNewline; Write-Host " $m" }
function Write-Err($m)   { Write-Host "  " -NoNewline; Write-Host ([char]0x2717) -ForegroundColor Red -NoNewline; Write-Host " $m" }

function Test-CommandExists([string]$name) {
    return [bool](Get-Command -Name $name -ErrorAction SilentlyContinue)
}

function p([string]$a, [string]$b) {
    return [io.path]::Combine($a, $b)
}
function Parent([string]$p) {
    if ([string]::IsNullOrEmpty($p)) { return '' }
    $q = $p.TrimEnd('\','/')
    if ($q.Length -eq 2 -and $q[1] -eq ':') { return '' }
    $r = [io.path]::GetDirectoryName($q)
    if ([string]::IsNullOrEmpty($r)) { return '' }
    return $r
}
function IsDir([string]$p) {
    if ([string]::IsNullOrEmpty($p)) { return $false }
    return [io.directory]::Exists($p)
}
function Exists([string]$p) {
    if ([string]::IsNullOrEmpty($p)) { return $false }
    return ([io.file]::Exists($p) -bor [io.directory]::Exists($p))
}
function EnsureDir([string]$p) {
    if ([string]::IsNullOrEmpty($p)) { return }
    if (-not [io.directory]::Exists($p)) {
        [void][io.directory]::CreateDirectory($p)
    }
}
function Rm([string]$p) {
    if ([string]::IsNullOrEmpty($p)) { return }
    if ([io.file]::Exists($p)) { [io.file]::Delete($p) }
    elseif ([io.directory]::Exists($p)) { [io.directory]::Delete($p, $true) }
}

function Test-CanWrite([string]$dir) {
    if (-not (IsDir $dir)) { return $false }
    try {
        $tmp = p $dir ('.misi_test_write_' + [guid]::NewGuid().ToString('N') + '.tmp')
        [io.file]::WriteAllText($tmp, 'ok')
        Rm $tmp
        return $true
    } catch {
        return $false
    }
}

function Add-PathPermanently([string]$newPath) {
    try {
        $curUser = [Environment]::GetEnvironmentVariable('PATH', 'User')
        if ($curUser -split ';' -notcontains $newPath) {
            if ([string]::IsNullOrWhiteSpace($curUser)) { $joined = $newPath }
            else { $joined = $curUser.TrimEnd(';') + ';' + $newPath }
            [Environment]::SetEnvironmentVariable('PATH', $joined, 'User')
        }
        if ($env:PATH -split ';' -notcontains $newPath) {
            $env:PATH = $newPath + ';' + $env:PATH
        }
        return $true
    } catch {
        return $false
    }
}

Write-Host 'Instalando Misi...'
Write-Host ''

if (-not (Test-CommandExists 'git')) {
    Write-Err 'git no está instalado -- es necesario para descargar/actualizar Misi.'
    exit 1
}
Write-Ok 'git encontrado'

if (Test-CommandExists 'php') {
    $phpVersion = (php -r 'echo PHP_VERSION;' 2>$null)
    if (-not $phpVersion) { $phpVersion = '0.0.0' }
    Write-Ok "PHP $phpVersion encontrado"
} else {
    Write-Warn 'php no está en el PATH todavía -- instálalo antes de usar misi.'
    Write-Warn 'El framework se instala igual; misi doctor te lo va a recordar.'
}

$defRepo = 'https://github.com/emilymontec/misi-framework.git'
$defRef  = 'main'
$defHome = p $env:USERPROFILE '.misi\framework'

if ($env:MISI_REPO) { $MISI_REPO = $env:MISI_REPO } else { $MISI_REPO = $defRepo }
if ($env:MISI_REF)  { $MISI_REF  = $env:MISI_REF  } else { $MISI_REF  = $defRef  }
if ($env:MISI_HOME) { $MISI_HOME = $env:MISI_HOME } else { $MISI_HOME = $defHome }

$gitDir = p $MISI_HOME '.git'
if (IsDir $gitDir) {
    Write-Info "Ya existe una instalación en $MISI_HOME -- actualizando..."
    git -C $MISI_HOME fetch --depth 1 origin $MISI_REF
    git -C $MISI_HOME checkout $MISI_REF
    git -C $MISI_HOME reset --hard "origin/$MISI_REF"
    Write-Ok "Actualizado a la última versión de '$MISI_REF'"
} elseif (Exists $MISI_HOME) {
    Write-Err "$MISI_HOME existe pero no es una instalación de Misi (no tiene .git)."
    Write-Err 'Muévelo o elimínalo, o define la variable de entorno MISI_HOME apuntando a otra ruta.'
    exit 1
} else {
    $parent = Parent $MISI_HOME
    if (-not [string]::IsNullOrEmpty($parent)) { EnsureDir $parent }
    Write-Info "Clonando $MISI_REPO ($MISI_REF) en $MISI_HOME..."
    git clone --depth 1 --branch $MISI_REF $MISI_REPO $MISI_HOME
    Write-Ok "Framework instalado en $MISI_HOME"
}

$misiBin     = p $MISI_HOME 'bin\misi'
$bizBin      = p $MISI_HOME 'bin\biz'
$misiBinUnix = p $MISI_HOME 'bin/misi'
$bizBinUnix  = p $MISI_HOME 'bin/biz'
if (Exists $misiBinUnix) { $misiBin = $misiBinUnix }
if (Exists $bizBinUnix)  { $bizBin  = $bizBinUnix  }

$pathEntries = $env:PATH -split ';'

$userCandidates = @(
    (p $env:USERPROFILE '.misi\bin'),
    (p $env:LOCALAPPDATA 'Microsoft\WindowsApps'),
    (p $env:USERPROFILE 'bin'),
    (p $env:USERPROFILE '.local\bin')
)
$systemCandidates = @(
    (p $env:ProgramFiles 'Misi')
)

$linkTarget = $null
foreach ($c in $userCandidates) {
    if ($pathEntries -contains $c) { $linkTarget = $c; break }
}

if (-not $linkTarget) {
    foreach ($c in $userCandidates) {
        try { EnsureDir $c } catch {}
        if (Test-CanWrite $c) { $linkTarget = $c; break }
    }
}

$needsAdmin = $false
$systemTarget = $null
if (-not $linkTarget) {
    foreach ($c in $systemCandidates) {
        if (Test-CanWrite $c) { $linkTarget = $c; break }
        $systemTarget = $c
    }
}

if (-not $linkTarget -and $systemTarget) {
    try {
        $principal = New-Object Security.Principal.WindowsPrincipal([Security.Principal.WindowsIdentity]::GetCurrent())
        $isAdmin   = $principal.IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)
    } catch { $isAdmin = $false }

    if ($isAdmin) {
        $linkTarget = $systemTarget
    } else {
        $fallback = p $env:USERPROFILE '.misi\bin'
        try { EnsureDir $fallback } catch {}
        $linkTarget = $fallback
        Write-Warn 'No se puede escribir en rutas de sistema sin privilegios de administrador.'
        Write-Warn "Se usará $linkTarget."
    }
}

if (-not $linkTarget) {
    $linkTarget = p $env:USERPROFILE '.misi\bin'
}

try {
    EnsureDir $linkTarget
} catch {
    Write-Err "No se puede crear el directorio $linkTarget"
    exit 1
}

$misiCmdDst = p $linkTarget 'misi.cmd'
$misiPs1Dst = p $linkTarget 'misi.ps1'

$wrapperContent = @"
@echo off
setlocal EnableDelayedExpansion

set "MISI_HOME=${MISI_HOME}"
if defined MISI_HOME_OVERRIDE set "MISI_HOME=%MISI_HOME_OVERRIDE%"

if /I "%~1"=="self-update" (
    if not exist "%MISI_HOME%\.git" (
        echo misi: no hay una instalacion global en %MISI_HOME%. >&2
        exit /b 1
    )
    echo Actualizando Misi en %MISI_HOME%...
    git -C "%MISI_HOME%" pull --ff-only
    exit /b !ERRORLEVEL!
)

set "DIR=%CD%"
:loop
if exist "%DIR%\bin\biz" (
    php "%DIR%\bin\biz" %*
    exit /b !ERRORLEVEL!
)
if "%DIR%\"=="%~d0\" goto :global
for %%D in ("%DIR%") do set "DIR=%%~dpD"
if "!DIR:~-1!"=="\" set "DIR=!DIR:~0,-1!"
goto loop

:global
if exist "%MISI_HOME%\bin\biz" (
    php "%MISI_HOME%\bin\biz" %*
    exit /b !ERRORLEVEL!
)

echo misi: no se encontro ningun proyecto Misi desde %CD% hacia arriba, >&2
echo       y tampoco hay una instalacion global en %MISI_HOME%. >&2
exit /b 1
"@

$wrapperPs1 = @"
`$ErrorActionPreference = 'Stop'
`$misiHome = @(`$env:MISI_HOME, '$MISI_HOME') | Where-Object { -not [string]::IsNullOrEmpty(`$_) } | Select-Object -First 1

function pj([string]`$a, [string]`$b) { return [io.path]::Combine(`$a, `$b) }
function pp([string]`$p) {
    if ([string]::IsNullOrEmpty(`$p)) { return `$null }
    `$q = `$p.TrimEnd('\','/')
    if (`$q.Length -eq 2 -and `$q[1] -eq ':') { return `$null }
    `$r = [io.path]::GetDirectoryName(`$q)
    if ([string]::IsNullOrEmpty(`$r)) { return `$null }
    return `$r
}
function fe([string]`$p) { return [io.file]::Exists(`$p) }
function de([string]`$p) { return [io.directory]::Exists(`$p) }

function Find-ProjectRoot {
    param([string]`$start = `$PWD.Path)
    `$dir = `$start
    while (`$true) {
        if (fe (pj `$dir 'bin\biz')) { return `$dir }
        `$parent = pp `$dir
        if (-not `$parent -or `$parent -eq `$dir) { return `$null }
        `$dir = `$parent
    }
}

if (`$args.Count -ge 1 -and `$args[0] -eq 'self-update') {
    if (-not (de (pj `$misiHome '.git'))) {
        Write-Error "misi: no hay una instalacion global en `$misiHome"
        exit 1
    }
    Write-Host "Actualizando Misi en `$misiHome..."
    git -C `$misiHome pull --ff-only
    exit `$LASTEXITCODE
}

`$root = Find-ProjectRoot
if (`$root) {
    php (pj `$root 'bin\biz') @args
    exit `$LASTEXITCODE
}
`$globalBiz = pj `$misiHome 'bin\biz'
if (fe `$globalBiz) {
    php `$globalBiz @args
    exit `$LASTEXITCODE
}
Write-Error "misi: no se encontro ningun proyecto Misi desde `$PWD hacia arriba, y tampoco hay una instalacion global en `$misiHome"
exit 1
"@

$installedOk = $false

try {
    [io.file]::WriteAllText($misiCmdDst, $wrapperContent, [Text.Encoding]::ASCII)
    [io.file]::WriteAllText($misiPs1Dst, $wrapperPs1, [Text.UTF8Encoding]::new($false))
    $installedOk = $true
    Write-Ok "misi instalado en $misiCmdDst"
} catch {
    try {
        EnsureDir $linkTarget
        [io.file]::WriteAllText($misiCmdDst, $wrapperContent, [Text.Encoding]::ASCII)
        [io.file]::WriteAllText($misiPs1Dst, $wrapperPs1, [Text.UTF8Encoding]::new($false))
        $installedOk = $true
        Write-Ok "misi instalado en $misiCmdDst"
    } catch {
        Write-Err "No se pudo escribir 'misi' en $linkTarget."
        Write-Err 'Revisa permisos de escritura, o define MISI_HOME a una ruta alternativa.'
        exit 1
    }
}

$alreadyInPath = $pathEntries -contains $linkTarget
$permanentOk = $false
if (-not $alreadyInPath) {
    $permanentOk = Add-PathPermanently $linkTarget
    if ($permanentOk) {
        Write-Ok "$linkTarget agregado permanentemente al PATH de usuario."
        Write-Warn 'Abre una NUEVA terminal para que el cambio al PATH surta efecto.'
    } else {
        Write-Warn 'No se pudo actualizar el PATH del sistema de forma permanente.'
    }
}

Write-Host ''
if ($alreadyInPath -or $permanentOk) {
    Write-Host 'Instalacion completa. Prueba (en una terminal nueva si cambio el PATH):'
    Write-Host ''
    Write-Host '  misi version'
    Write-Host '  misi doctor'
    Write-Host '  misi new mi-primer-sitio'
} else {
    Write-Warn "$linkTarget no esta en tu PATH todavia."
    Write-Host ''
    Write-Host 'Agregalo a tu PATH de usuario con este comando (o manualmente en Panel de Control):'
    Write-Host ''
    Write-Host "  [Environment]::SetEnvironmentVariable('PATH', [Environment]::GetEnvironmentVariable('PATH','User') + ';$linkTarget', 'User')"
    Write-Host ('  $env:PATH = "' + $linkTarget + ';" + $env:PATH')
    Write-Host ''
    Write-Host 'Despues (y en una terminal nueva):'
    Write-Host ''
    Write-Host '  misi version'
    Write-Host '  misi new mi-primer-sitio'
}
