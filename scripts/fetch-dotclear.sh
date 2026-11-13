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

# Une installation est complète si la racine contient index.php et inc/.
is_dotclear_root() {
    [ -f "$1/index.php" ] && [ -d "$1/inc" ]
}

# Retrouve la racine de Dotclear dans une archive extraite, quelle que soit son
# arborescence (dotclear/…, ./dotclear/…, dotclear-x.y.z/…, niveau supplémentaire…).
find_dotclear_root() {
    local depth dir
    for depth in 0 1 2 3; do
        while IFS= read -r dir; do
            if is_dotclear_root "$dir"; then
                printf '%s\n' "$dir"
                return 0
            fi
        done < <(find "$1" -mindepth "$depth" -maxdepth "$depth" -type d | sort)
    done
    return 1
}

if is_dotclear_root "$DEST"; then
    echo ">> Dotclear déjà présent dans $DEST"
    exit 0
fi

mkdir -p "$DEST"
tmp="$(mktemp -d)"
trap 'rm -rf "$tmp"' EXIT
mkdir "$tmp/src"
echo ">> Téléchargement de Dotclear ${DC_VERSION}…"
if curl -fsSL -o "$tmp/dc.tar.gz" "https://download.dotclear.org/attic/dotclear-${DC_VERSION}.tar.gz" 2>/dev/null \
   || curl -fsSL -o "$tmp/dc.tar.gz" "https://github.com/dotclear/dotclear/archive/refs/tags/${DC_VERSION}.tar.gz" 2>/dev/null; then
    tar -xzf "$tmp/dc.tar.gz" -C "$tmp/src"
else
    echo "   archives indisponibles, clonage git du tag ${DC_VERSION}…"
    git clone --depth 1 --branch "${DC_VERSION}" https://github.com/dotclear/dotclear.git "$tmp/src/dotclear"
    rm -rf "$tmp/src/dotclear/.git"
fi

if ! root="$(find_dotclear_root "$tmp/src")"; then
    echo "!! Archive Dotclear ${DC_VERSION} inattendue : index.php et inc/ introuvables" >&2
    exit 1
fi
cp -a "$root/." "$DEST/"
echo ">> Dotclear ${DC_VERSION} installé dans $DEST"
