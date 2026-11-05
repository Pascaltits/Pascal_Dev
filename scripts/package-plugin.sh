#!/usr/bin/env bash
# Construit l'archive de distribution d'un plugin : build/<id>-<version>.zip
#   scripts/package-plugin.sh monPlugin
set -euo pipefail
cd "$(dirname "$0")/.."

ID="${1:-}"
[ -n "$ID" ] && [ -d "plugins/$ID" ] || { echo "usage : $0 <idDuPlugin> (dossier plugins/<id> inexistant)" >&2; exit 1; }

VERSION="$(php -r '
// Version = 4e chaîne littérale passée à registerModule() dans _define.php
$strings = [];
$seen    = false;
foreach (token_get_all(file_get_contents($argv[1])) as $t) {
    if (is_array($t) && $t[0] === T_STRING && $t[1] === "registerModule") { $seen = true; continue; }
    if ($seen && is_array($t) && $t[0] === T_CONSTANT_ENCAPSED_STRING) { $strings[] = trim($t[1], "\x27\""); }
    if (count($strings) === 4) break;
}
echo $strings[3] ?? "0.0";' "plugins/$ID/_define.php")"

mkdir -p build
OUT="build/${ID}-${VERSION}.zip"
rm -f "$OUT"
( cd plugins && zip -qr "../$OUT" "$ID" -x "*/.git*" "*/node_modules/*" "*/.php-cs-fixer.cache" "*/README.md" )
echo ">> $OUT ($(du -h "$OUT" | cut -f1))"
echo "   Installable via Admin > Plugins > « Installer ou mettre à jour un plugin »."
