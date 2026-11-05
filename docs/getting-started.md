# Démarrage détaillé

## 1. Prérequis

### En local
- **Docker** : Docker Desktop (Windows/macOS) ou Docker Engine + plugin Compose v2 (Linux).
- **make** (Linux/macOS : présent ou via `build-essential` / Xcode CLT ; Windows : via WSL2, recommandé, ou `choco install make`).
- **VS Code** + extensions recommandées (VS Code les propose à l'ouverture du dossier) :
  - `xdebug.php-debug` (débogage),
  - `bmewburn.vscode-intelephense-client` (autocomplétion PHP),
  - `junstyle.php-cs-fixer` (style).
- Optionnel : PHP 8.2+ et Composer sur la machine hôte pour lancer `make check` sans Docker.
  Sinon, lancer les outils depuis le conteneur : `make shell` puis `composer install && make check`.

### Dans GitHub Codespaces
Rien à installer : le fichier `.devcontainer/devcontainer.json` décrit tout.

## 2. Premier démarrage

```bash
make up
```

Ce que fait cette commande :
1. copie `.env.example` en `.env` s'il n'existe pas ;
2. construit l'image `docker/Dockerfile` (PHP 8.3 + extensions + Xdebug + Composer) ;
3. démarre MariaDB, attend qu'elle soit prête, démarre Apache ;
4. au premier lancement, le conteneur **télécharge Dotclear** (`DC_VERSION`, 2.39.3 par défaut)
   dans `./dotclear/`, et y installe `inc/config.php` à partir de `docker/dotclear-config.php`.

Puis :

```bash
make install
```

qui soumet le formulaire d'installation de Dotclear (`admin/install/index.php`) avec les valeurs
`DC_ADMIN_*` du `.env`. Alternative : ouvrir <http://localhost:8080/admin/install/> dans le navigateur.

## 3. Variables de configuration (`.env`)

| Variable | Défaut | Rôle |
|---|---|---|
| `DC_VERSION` | `2.39.3` | Version du cœur téléchargée |
| `DC_HTTP_PORT` | `8080` | Port HTTP sur l'hôte |
| `DC_PUBLIC_URL` | `http://localhost:8080` | URL utilisée pour `DC_ADMIN_URL` et l'installation |
| `DC_DB*` | `db` / `dotclear` | Connexion MariaDB (service `db`) |
| `DC_MASTER_KEY` | `dev-master-key-change-me` | Clé de chiffrement des mots de passe |
| `DC_DEBUG`, `DC_DEV` | `1` | Mode développement de Dotclear |
| `DC_REVERSE_PROXY` | `0` | À passer à `1` derrière un proxy HTTPS (Codespaces) |
| `DC_ADMIN_*` | `admin` / `adminadmin` | Compte créé par `make install` |
| `XDEBUG_MODE` | `debug,develop` | `off` pour désactiver, `profile` pour profiler |
| `XDEBUG_START_WITH_REQUEST` | `yes` | `trigger` pour ne déboguer qu'à la demande |

Après modification du `.env` : `make up` (recrée le conteneur avec les nouvelles variables).
`inc/config.php` lit les variables à chaque requête, il n'y a donc jamais besoin de l'éditer.

## 4. Cycle de travail quotidien

```bash
make up          # démarrer
make logs        # voir les erreurs PHP en direct
make shell       # shell dans le conteneur (php, composer, mysql client)
make db-shell    # client MariaDB
make down        # arrêter (les données restent)
make reset       # tout effacer (base + config) pour réinstaller
```

## 5. Où sont les fichiers ?

| Sur l'hôte | Dans le conteneur | Contenu |
|---|---|---|
| `./dotclear/` | `/var/www/html` | Cœur Dotclear (non versionné) |
| `./plugins/` | `/workspace/plugins` | Vos plugins, chargés via `DC_PLUGINS_ROOT` |
| `./` | `/workspace` | Le dépôt complet |

Dotclear accepte plusieurs dossiers de plugins séparés par `:` dans `DC_PLUGINS_ROOT` ;
c'est ce qui permet de développer directement dans le dépôt sans copier les fichiers.

## 6. Problèmes fréquents

- **Port 8080 déjà utilisé** : changer `DC_HTTP_PORT` et `DC_PUBLIC_URL` dans `.env`, puis `make up`.
- **« Dotclear is already installed »** lors de `make install` : normal après une première installation.
- **Page blanche / erreur 500** : `make logs`. `DC_DEBUG=1` affiche la trace complète dans la page.
- **Fichiers `./dotclear` appartenant à root (Linux)** : `sudo chown -R $USER ./dotclear`.
- **Téléchargement de Dotclear impossible** : vérifier l'accès réseau du conteneur, ou déposer
  manuellement une archive extraite dans `./dotclear/` puis `make restart`.
