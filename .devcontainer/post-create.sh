#!/usr/bin/env bash
# Exécuté une fois à la création du devcontainer / Codespace.
set -euo pipefail
cd /workspace

echo ">> Installation des outils qualité (composer install)…"
composer install --no-interaction --prefer-dist || echo "!! composer install a échoué (réseau ?) — relancer plus tard : composer install"

# Dans Codespaces, l'URL publique est fournie par l'environnement
if [ -n "${CODESPACE_NAME:-}" ]; then
    url="https://${CODESPACE_NAME}-80.${GITHUB_CODESPACES_PORT_FORWARDING_DOMAIN:-app.github.dev}"
    if [ ! -f .env ]; then
        cp .env.example .env
    fi
    sed -i "s#^DC_PUBLIC_URL=.*#DC_PUBLIC_URL=${url}#; s#^DC_REVERSE_PROXY=.*#DC_REVERSE_PROXY=1#" .env
    echo ">> Codespace détecté : DC_PUBLIC_URL=${url} (dans .env)"
    echo "   Relancer \`make up\` pour appliquer, puis \`make install\`."
fi

echo ">> Prêt. Commandes utiles : make help"
