#!/bin/sh
#
# Instalador global de Misi -- estilo rustup/pnpm: se corre una vez por
# máquina y deja el comando "misi" disponible en cualquier carpeta.
#
#   curl -fsSL https://raw.githubusercontent.com/emilymontec/misi-framework/main/install.sh | sh
#
# Qué hace, en orden:
#   1. Clona (o actualiza, si ya existe) el framework en $MISI_HOME
#      (por defecto ~/.misi/framework). ESE checkout es la plantilla
#      que "misi new" usará de ahí en adelante -- no hay un paquete ni
#      un registro separado del framework en sí (ver INSTALL.md).
#   2. Enlaza bin/misi a una carpeta que ya esté en tu PATH (o lo copia,
#      en sistemas donde crear symlinks requiere privilegios especiales,
#      como Windows/Git Bash).
#
# Variables de entorno que puedes fijar antes de correrlo:
#   MISI_REPO      URL del repositorio git a clonar
#                  (default: https://github.com/emilymontec/misi-framework.git)
#   MISI_REF       rama o tag a usar (default: main)
#   MISI_HOME      dónde instalar el framework (default: ~/.misi/framework)
#
# Requiere: git, php (>= 8.1). Sin eso, el instalador avisa y no falla
# a medias -- revisa los mensajes antes de continuar.
#
# sh puro (no bash): para que "curl | sh" funcione igual en cualquier
# sistema, sin asumir qué shell tiene el usuario por defecto.

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

# --- Requisitos ------------------------------------------------------

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

# --- Descarga / actualización del framework --------------------------

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

# --- Enlazar "misi" a una carpeta del PATH ----------------------------

link_target=""
for candidate in "$HOME/.local/bin" "$HOME/bin" "/usr/local/bin"; do
    case ":$PATH:" in
        *":$candidate:"*)
            link_target="$candidate"
            break
            ;;
    esac
done

# Si nada de lo ya presente en PATH sirve, usa/crea ~/.local/bin de
# todas formas (es la convención más común) y avisa al final que hay
# que agregarlo al PATH a mano.
if [ -z "$link_target" ]; then
    link_target="$HOME/.local/bin"
fi

mkdir -p "$link_target"

# En Windows (Git Bash / MSYS / Cygwin) crear symlinks normalmente
# requiere privilegios de administrador o el "Modo desarrollador"
# activado. Para no depender de eso -- sobre todo pensando en
# clientes sin conocimientos técnicos -- copiamos el binario en vez
# de enlazarlo cuando detectamos ese entorno. En Linux/macOS seguimos
# usando symlink, que se actualiza solo en cada "misi update".
case "$(uname -s)" in
    MINGW*|MSYS*|CYGWIN*)
        cp -f "$MISI_HOME/bin/misi" "$link_target/misi"
        chmod +x "$link_target/misi"
        ok "misi copiado en $link_target/misi"
        warn "En Windows se copia en vez de enlazar -- si actualizas Misi,"
        warn "vuelve a correr este instalador para refrescar la copia."
        ;;
    *)
        ln -sf "$MISI_HOME/bin/misi" "$link_target/misi"
        ok "misi enlazado en $link_target/misi"
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