# Pascal_Dev — atelier de développement de plugins Dotclear

Environnement prêt à l'emploi pour **étudier le cœur de Dotclear**, **développer des plugins** et
**les déboguer pas à pas** (Xdebug), en local avec Docker ou dans le navigateur avec GitHub Codespaces.

| Composant | Rôle |
|---|---|
| `docker/` + `docker-compose.yml` | Apache + PHP 8.3 + Xdebug, MariaDB, Adminer. Dotclear est téléchargé au premier démarrage dans `./dotclear/` |
| `.devcontainer/` | Ouverture en un clic dans GitHub Codespaces ou VS Code « Dev Containers » |
| `.vscode/` | Configuration de débogage Xdebug (`F5`), tâches, extensions recommandées |
| `plugins/devSandbox/` | Plugin d'exemple commenté, modèle pour `make new-plugin` |
| `.github/workflows/` | CI : syntaxe, style, analyse statique (phpstan) contre l'API Dotclear ; packaging des plugins |
| `Makefile`, `scripts/` | Commandes courantes (`make help`) |
| `docs/` | Guides : démarrage, débogage, anatomie d'un plugin, ressources |

## Démarrage rapide (local)

Prérequis : Docker (Desktop ou Engine + Compose v2), `make`, et VS Code avec l'extension **PHP Debug** (xdebug.php-debug).

```bash
git clone https://github.com/pascaltits/pascal_dev.git
cd pascal_dev
make up        # construit l'image, télécharge Dotclear 2.39.3, démarre la base
make install   # crée le compte admin/adminadmin et le blog « My first blog »
```

- Blog : <http://localhost:8080/>
- Administration : <http://localhost:8080/admin/> (identifiant `admin`, mot de passe `adminadmin`)
- Base de données (Adminer) : <http://localhost:8081/> (serveur `db`, utilisateur `dotclear`, mot de passe `dotclear`)

Dans l'administration, **Plugins** → activer **Dev Sandbox** → l'entrée apparaît dans le menu de gauche.

## Démarrage rapide (GitHub Codespaces)

1. Sur GitHub : **Code → Codespaces → Create codespace on main**.
2. Une fois le terminal ouvert : `make up` puis `make install`.
3. Ouvrir l'onglet **Ports** → port 80 « Dotclear ».

Le script de création du Codespace renseigne automatiquement l'URL publique dans `.env`.

## Déboguer un plugin

1. Dans VS Code, ouvrir `plugins/devSandbox/src/FrontendBehaviors.php` et poser un point d'arrêt
   sur la ligne marquée `<-- Point d'arrêt conseillé`.
2. `F5` → configuration **« Dotclear : écouter Xdebug (Docker) »**.
3. Recharger <http://localhost:8080/> : l'exécution s'arrête dans l'IDE, variables et pile d'appels visibles.

Le guide complet (déclenchement, profilage, débogage du cœur, PhpStorm, dépannage) est dans [docs/debug.md](docs/debug.md).

## Créer un nouveau plugin

```bash
make new-plugin ID=monPlugin NAME="Mon plugin"
```

Le dossier `plugins/monPlugin/` est créé à partir du modèle et est immédiatement visible dans
**Admin → Plugins** (les plugins du dépôt sont chargés directement depuis `/workspace/plugins`).
Anatomie détaillée : [docs/plugin-anatomy.md](docs/plugin-anatomy.md).

## Qualité et CI

```bash
make check     # lint + style (php-cs-fixer) + analyse statique (phpstan niveau 6)
make cs-fix    # corrige le style automatiquement
make package ID=monPlugin   # build/monPlugin-x.y.z.zip, installable dans Dotclear
```

Le workflow GitHub **CI** rejoue ces vérifications sur PHP 8.2, 8.3 et 8.4 à chaque push.
Le workflow **Package plugin** produit l'archive d'un plugin (manuellement, ou à la création d'un tag `monPlugin-v1.0.0`, qui crée aussi une release GitHub).

## Toutes les commandes

```
make help
```

## Étudier le cœur de Dotclear

Après `make up`, les sources complètes de Dotclear sont dans `./dotclear/` (ignoré par git) :
`src/` (cœur moderne, namespace `Dotclear\`), `plugins/` (plugins officiels : excellents exemples),
`inc/`, `admin/`, `themes/`. Elles sont indexées par Intelephense pour l'autocomplétion
et peuvent être parcourues pas à pas sous Xdebug (voir docs/debug.md).

Changer de version : modifier `DC_VERSION` dans `.env` puis `make core-update`.

## Licence

Outils et plugin d'exemple : GPL-2.0-or-later. Dotclear est distribué sous licence AGPL-3.0.
