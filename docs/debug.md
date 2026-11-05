# Déboguer Dotclear et ses plugins avec Xdebug

L'image Docker embarque **Xdebug 3**, configuré dans `docker/xdebug.ini` :

- port `9003` (défaut Xdebug 3), clé IDE `VSCODE` ;
- `discover_client_host = 1` : Xdebug se connecte à l'adresse d'où vient la requête HTTP,
  ce qui fonctionne aussi bien en local (via `host.docker.internal`) qu'en Codespaces ;
- `start_with_request = yes` par défaut : chaque requête tente de joindre l'IDE
  (sans IDE à l'écoute, aucun ralentissement notable ni message d'erreur).

## 1. Déboguer avec VS Code

1. Installer l'extension **PHP Debug** (`xdebug.php-debug`).
2. Ouvrir le dossier du dépôt (pas un sous-dossier : les `pathMappings` de `.vscode/launch.json`
   supposent `${workspaceFolder}` = racine du dépôt).
3. Poser un point d'arrêt (clic dans la marge) :
   - `plugins/devSandbox/src/FrontendBehaviors.php`, fonction `publicFooterContent` → page du blog ;
   - `plugins/devSandbox/src/Manage.php`, fonction `process` → clic sur « Enregistrer » dans l'admin.
4. `F5` (ou Exécuter → Démarrer le débogage) avec **« Dotclear : écouter Xdebug (Docker) »**.
5. Recharger la page dans le navigateur. VS Code s'arrête sur le point d'arrêt : variables locales,
   pile d'appels (on voit tout le chemin depuis `index.php` jusqu'au plugin), évaluation d'expressions
   dans la console de débogage (`App::blog()->id()`, `$_POST`, …).

Deux configurations sont fournies :

| Configuration | Usage |
|---|---|
| Dotclear : écouter Xdebug (Docker) | Tout : plugins **et** cœur Dotclear (`./dotclear/`) |
| Dotclear : écouter Xdebug (plugins seulement) | Ignore les fichiers du cœur (`skipFiles`) : le « pas à pas » reste dans vos plugins |

### Correspondance des chemins

| Dans le conteneur | Sur l'hôte |
|---|---|
| `/workspace` | racine du dépôt |
| `/var/www/html` | `./dotclear` |

Si un point d'arrêt reste « gris » (non résolu), c'est presque toujours un problème de correspondance :
vérifier que le fichier est bien sous `plugins/` ou `dotclear/` et que VS Code a ouvert la racine du dépôt.

## 2. Ne déboguer qu'à la demande (mode `trigger`)

Dans `.env` : `XDEBUG_START_WITH_REQUEST=trigger` puis `make up`. Xdebug ne démarre alors que si :

- le paramètre `XDEBUG_TRIGGER` est présent : `http://localhost:8080/?XDEBUG_TRIGGER=1`, ou
- le cookie `XDEBUG_SESSION` est posé — c'est ce que font les extensions navigateur
  « Xdebug helper » (Chrome) / « Xdebug Helper for Firefox » : un clic pour activer/désactiver.

## 3. Déboguer un script en ligne de commande

```bash
make shell
php -dxdebug.start_with_request=yes -dxdebug.client_host=host.docker.internal mon-script.php
```

Dans Codespaces, remplacer `host.docker.internal` par `127.0.0.1`.

## 4. Comprendre le cœur : où poser les points d'arrêt

| Fichier (dans `./dotclear/`) | Ce qu'on y voit |
|---|---|
| `index.php` → `src/App.php` | Démarrage, choix du contexte (Frontend / Backend / Install) |
| `src/Core/Frontend/Utility.php` | Résolution de l'URL publique, chargement du thème |
| `src/Core/Backend/Utility.php` | Chargement de l'administration, menus, authentification |
| `src/Module/Modules.php` → `loadModules()` | Découverte et chargement des plugins (`_define.php`, `src/Prepend.php`, `src/Backend.php`, `src/Frontend.php`) |
| `src/Core/Behavior.php` → `callBehavior()` | Exécution des behaviors : point d'arrêt conditionnel sur `$behavior === 'publicFooterContent'` |
| `src/Helper/Html/Template/Template.php` | Compilation des templates `{{tpl:…}}` vers PHP (cache dans `dotclear/cache/cbtpl/`) |
| `src/Core/Backend/Page.php` | Rendu des pages d'administration |

Astuce : un **point d'arrêt conditionnel** (clic droit → *Add Conditional Breakpoint*) dans
`callBehavior` avec la condition `str_starts_with($behavior, 'public')` permet de suivre
tous les behaviors appelés lors de l'affichage d'une page de blog.

## 5. Profilage

```bash
XDEBUG_MODE=profile make up
```

Les fichiers `cachegrind.out.*` sont écrits dans `/tmp/xdebug` du conteneur :

```bash
docker compose cp dotclear:/tmp/xdebug ./build/xdebug
```

À ouvrir avec KCachegrind/QCachegrind, ou l'extension VS Code « PHP Profiler ».
Revenir à `XDEBUG_MODE=debug,develop` ensuite.

## 6. Autres outils de diagnostic

- `make logs` : erreurs PHP (`display_errors` est aussi activé : elles apparaissent dans la page).
- `DC_DEBUG=1` (défaut) : Dotclear affiche la pile complète des exceptions et désactive certains caches.
- `DC_DEV=1` (défaut) : JS/CSS de l'administration non minifiés.
- Dans le mode `develop` de Xdebug, `var_dump()` est enrichi (couleurs, types) et les erreurs
  affichent une pile d'appels avec arguments.
- Adminer (<http://localhost:8081/>) pour inspecter les tables `dc_setting`, `dc_version`, `dc_post`…
- Vider le cache des templates : `rm -rf dotclear/cache/cbtpl/*` (ou Admin → Maintenance).

## 7. PhpStorm

*Settings → PHP → Servers* : nom `dotclear`, hôte `localhost`, port `8080`, cocher *Use path mappings* :
`<dépôt>` → `/workspace` et `<dépôt>/dotclear` → `/var/www/html`.
Puis *Run → Start Listening for PHP Debug Connections*. Le port 9003 est celui par défaut.

## 8. Dépannage

| Symptôme | Cause probable / remède |
|---|---|
| L'IDE ne s'arrête jamais | VS Code n'écoute pas (`F5`), ou port 9003 pris. Vérifier `make logs` après `XDEBUG_MODE=debug` et temporairement `xdebug.log_level=7` dans `docker/xdebug.ini` + `make up` |
| Linux : pas de connexion | `host.docker.internal` repose sur `extra_hosts: host-gateway` (Docker ≥ 20.10). Pare-feu sur 9003 ? |
| Points d'arrêt gris | Mauvais `pathMappings` (voir §1) |
| Requêtes très lentes | `XDEBUG_MODE=off` quand on ne débogue pas, ou mode `trigger` |
| Codespaces : pas d'arrêt | Le port 9003 ne doit pas être redirigé ; VS Code écoute dans le conteneur même, ça fonctionne sans réglage |
