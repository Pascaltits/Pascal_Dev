#!/usr/bin/env bash
# Crée un nouveau plugin à partir du modèle plugins/devSandbox.
#   scripts/new-plugin.sh monPlugin "Mon plugin"
set -euo pipefail
cd "$(dirname "$0")/.."

ID="${1:-}"
NAME="${2:-$ID}"
TEMPLATE="plugins/devSandbox"

if [ -z "$ID" ]; then
    echo "usage : $0 <idDuPlugin> [\"Nom affiché\"]" >&2
    exit 1
fi
if ! [[ "$ID" =~ ^[A-Za-z][A-Za-z0-9_]*$ ]]; then
    echo "!! L'identifiant doit être un nom de namespace PHP valide (lettres, chiffres, _), ex. monPlugin" >&2
    exit 1
fi
if [ -e "plugins/$ID" ]; then
    echo "!! plugins/$ID existe déjà" >&2
    exit 1
fi

cp -r "$TEMPLATE" "plugins/$ID"
rm -f "plugins/$ID/README.md"

# Renommage de l'identifiant, du namespace et des libellés
find "plugins/$ID" -type f \( -name '*.php' -o -name '*.po' -o -name '*.xml' -o -name '*.js' -o -name '*.css' -o -name '*.html' \) -print0 \
    | xargs -0 sed -i \
        -e "s/devSandbox/${ID}/g" \
        -e "s/dev-sandbox/$(tr '[:upper:]' '[:lower:]' <<<"$ID")/g" \
        -e "s/Dev Sandbox/${NAME}/g" \
        -e "s/Bac à sable de développement/${NAME}/g" \
        -e "s/'date'        => '[^']*'/'date'        => '$(date +%Y-%m-%dT%H:%M:%S%z)'/"

cat > "plugins/$ID/README.md" <<MD
# ${NAME}

Plugin Dotclear \`${ID}\`, créé à partir du modèle \`devSandbox\`.

- \`_define.php\` : déclaration du module (nom, version, permissions, dépendances)
- \`src/My.php\` : identité du plugin (\`My::id()\`, \`My::settings()\`, \`My::checkContext()\`)
- \`src/Install.php\` : valeurs par défaut des réglages, tables SQL
- \`src/Backend.php\` : entrée de menu et comportements côté administration
- \`src/Manage.php\` : page d'administration du plugin
- \`src/Frontend.php\` : comportements et balises de template côté blog
- \`locales/fr/main.po\` : traductions
MD

echo ">> Plugin créé : plugins/$ID"
echo "   Il apparaît dans Admin > Plugins (rechargez la page), à activer avant utilisation."
