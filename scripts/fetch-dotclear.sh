#!/usr/bin/env bash
# Télécharge les sources Dotclear dans ./dotclear (utilisé par la CI et par `make stan` hors Docker).
# Dans Docker, c'est le conteneur qui s'en charge au premier démarrage.
set -euo pipefail
cd "$(dirname "$0")/.."

if [ -f .env ]; then
    # shellcheck disable=SC1091
    set -a; . ./.env; set +a
fi
DC_VERSION="${DC_VERSION:-2.39.3}"
DEST="${1:-dotclear}"

if [ -f "$DEST/index.php" ]; then
    echo ">> Dotclear déjà présent dans $DEST"
    exit 0
fi

mkdir -p "$DEST"
tmp="$(mktemp -d)"
echo ">> Téléchargement de Dotclear ${DC_VERSION}…"
if curl -fsSL -o "$tmp/dc.tar.gz" "https://download.dotclear.org/attic/dotclear-${DC_VERSION}.tar.gz" \
   || curl -fsSL -o "$tmp/dc.tar.gz" "https://github.com/dotclear/dotclear/archive/refs/tags/${DC_VERSION}.tar.gz"; then
    tar -xzf "$tmp/dc.tar.gz" --strip-components=1 -C "$DEST"
else
    echo "   archives indisponibles, clonage git du tag ${DC_VERSION}…"
    git clone --depth 1 --branch "${DC_VERSION}" https://github.com/dotclear/dotclear.git "$tmp/dc"
    cp -a "$tmp/dc/." "$DEST/"
    rm -rf "$DEST/.git"
fi
rm -rf "$tmp"
echo ">> Dotclear ${DC_VERSION} installé dans $DEST"
