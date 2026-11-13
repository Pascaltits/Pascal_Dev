#!/usr/bin/env bash
# Point d'entrée du conteneur Dotclear :
#   1. télécharge Dotclear dans /var/www/html s'il est absent (le dossier est monté depuis ./dotclear)
#   2. installe inc/config.php (piloté par variables d'environnement)
#   3. attend la base de données puis lance Apache
set -euo pipefail

DC_ROOT=/var/www/html
DC_VERSION="${DC_VERSION:-2.39.3}"

download_dotclear() {
    local tmp
    tmp="$(mktemp -d)"
    echo ">> Téléchargement de Dotclear ${DC_VERSION}…"
    if curl -fsSL -o "$tmp/dotclear.tar.gz" "https://download.dotclear.org/attic/dotclear-${DC_VERSION}.tar.gz"; then
        echo "   source : download.dotclear.org"
    elif curl -fsSL -o "$tmp/dotclear.tar.gz" "https://github.com/dotclear/dotclear/archive/refs/tags/${DC_VERSION}.tar.gz"; then
        echo "   source : github.com/dotclear/dotclear (archive du tag)"
    else
        echo "!! Impossible de télécharger Dotclear ${DC_VERSION}" >&2
        exit 1
    fi
    tar -xzf "$tmp/dotclear.tar.gz" --strip-components=1 -C "$DC_ROOT"
    rm -rf "$tmp"
}

if [ ! -f "$DC_ROOT/index.php" ] || [ ! -d "$DC_ROOT/inc" ]; then
    download_dotclear
fi

if [ ! -f "$DC_ROOT/inc/config.php" ]; then
    echo ">> Création de inc/config.php"
    cp /opt/dotclear/config.php "$DC_ROOT/inc/config.php"
fi

mkdir -p "$DC_ROOT/cache" "$DC_ROOT/var" "$DC_ROOT/public" /tmp/xdebug
chown -R www-data:www-data "$DC_ROOT/cache" "$DC_ROOT/var" "$DC_ROOT/public" "$DC_ROOT/inc" /tmp/xdebug
# Les plugins du dépôt doivent rester modifiables par l'utilisateur hôte ; Dotclear n'y écrit pas.

if [ "${DC_DBDRIVER:-mysqlimb4}" != "sqlite" ] && [ -n "${DC_DBHOST:-}" ]; then
    echo ">> Attente de la base de données ${DC_DBHOST}…"
    for i in $(seq 1 60); do
        if php -r 'exit(@mysqli_connect(getenv("DC_DBHOST"), getenv("DC_DBUSER"), getenv("DC_DBPASSWORD"), getenv("DC_DBNAME")) ? 0 : 1);' 2>/dev/null; then
            echo "   base disponible"
            break
        fi
        sleep 1
        [ "$i" = 60 ] && echo "!! Base de données injoignable (on continue quand même)" >&2
    done
fi

cat <<MSG
----------------------------------------------------------------
 Dotclear ${DC_VERSION} — environnement de développement
   Blog   : ${DC_PUBLIC_URL:-http://localhost:8080}/
   Admin  : ${DC_PUBLIC_URL:-http://localhost:8080}/admin/
   Install: ${DC_PUBLIC_URL:-http://localhost:8080}/admin/install/  (ou \`make install\`)
   Xdebug : mode=${XDEBUG_MODE:-debug,develop} start_with_request=${XDEBUG_START_WITH_REQUEST:-yes} port=9003
----------------------------------------------------------------
MSG

exec "$@"
