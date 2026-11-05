#!/usr/bin/env bash
# Installation non interactive de Dotclear (création du super-administrateur et du blog par défaut).
# S'exécute DANS le conteneur (make install) : il poste le formulaire de admin/install/index.php
# avec l'en-tête Host correspondant à DC_PUBLIC_URL pour que l'URL du blog soit correcte.
set -euo pipefail

PUBLIC_URL="${DC_PUBLIC_URL:-http://localhost:8080}"
HOST_HEADER="${PUBLIC_URL#*://}"; HOST_HEADER="${HOST_HEADER%%/*}"
SCHEME="${PUBLIC_URL%%://*}"
LOGIN="${DC_ADMIN_LOGIN:-admin}"
PASSWORD="${DC_ADMIN_PASSWORD:-adminadmin}"
EMAIL="${DC_ADMIN_EMAIL:-admin@example.org}"
FIRSTNAME="${DC_ADMIN_FIRSTNAME:-Admin}"
NAME="${DC_ADMIN_NAME:-Dotclear}"

if [ "${#PASSWORD}" -lt 6 ]; then
    echo "!! DC_ADMIN_PASSWORD doit contenir au moins 6 caractères" >&2
    exit 1
fi

DC_ROOT="${DC_ROOT:-/var/www/html}"
if [ ! -f "$DC_ROOT/inc/config.php" ]; then
    echo "!! $DC_ROOT/inc/config.php absent : le conteneur n'a pas fini de démarrer ?" >&2
    exit 1
fi

extra=()
if [ "$SCHEME" = "https" ]; then
    extra+=(-H "X-Forwarded-Proto: https" -H "X-Forwarded-For: 127.0.0.1")
fi

echo ">> Installation de Dotclear (compte « ${LOGIN} », URL ${PUBLIC_URL})…"
response="$(curl -sS -H "Host: ${HOST_HEADER}" "${extra[@]}" \
    --data-urlencode "u_login=${LOGIN}" \
    --data-urlencode "u_firstname=${FIRSTNAME}" \
    --data-urlencode "u_name=${NAME}" \
    --data-urlencode "u_email=${EMAIL}" \
    --data-urlencode "u_pwd=${PASSWORD}" \
    --data-urlencode "u_pwd2=${PASSWORD}" \
    --data-urlencode "u_date=$(date +'%a %b %d %Y %H:%M:%S GMT%z (Europe/Paris)')" \
    "${DC_INSTALL_ENDPOINT:-http://127.0.0.1}/admin/install/index.php")"

if grep -q "already installed" <<<"$response"; then
    echo ">> Dotclear est déjà installé. Rien à faire."
    echo "   Pour repartir de zéro : make reset && make up && make install"
    exit 0
fi

if grep -qi "All done\|Installation is now complete\|Dotclear has been successfully installed" <<<"$response"; then
    echo ">> Installation terminée."
else
    # Extraction des messages d'erreur éventuels (<p>…</p> dans la zone d'erreur)
    echo "!! Réponse inattendue de l'installeur. Extrait :" >&2
    sed -n 's/.*<p>\(.*\)<\/p>.*/   \1/p' <<<"$response" | head -n 10 >&2
    echo "   Ouvrir ${PUBLIC_URL}/admin/install/ dans un navigateur pour finir l'installation." >&2
    exit 1
fi

cat <<MSG
----------------------------------------------------------------
 Administration : ${PUBLIC_URL}/admin/
   identifiant  : ${LOGIN}
   mot de passe : ${PASSWORD}
 Blog           : ${PUBLIC_URL}/
 Le plugin d'exemple « devSandbox » est visible dans Admin > Plugins
 (activer-le puis ouvrir son menu dans la barre latérale).
----------------------------------------------------------------
MSG
