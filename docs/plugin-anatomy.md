# Anatomie d'un plugin Dotclear 2.39+

Depuis Dotclear 2.27/2.28, un plugin est un **namespace PHP** `Dotclear\Plugin\<id>` dont les classes
vivent dans `src/`. Dotclear charge automatiquement certaines classes selon le contexte ;
les anciens fichiers `_prepend.php`, `_admin.php`, `_public.php`, `index.php` ne sont plus nécessaires.

```
plugins/monPlugin/
├── _define.php            ← OBLIGATOIRE : déclaration du module
├── icon.svg               ← icône du menu (optionnel : icon.png, icon-dark.svg…)
├── src/
│   ├── My.php             ← OBLIGATOIRE : classe My extends MyPlugin
│   ├── Install.php        ← tables, réglages par défaut (à chaque nouvelle version)
│   ├── Prepend.php        ← chargé partout, très tôt (URL handlers, REST, autoload…)
│   ├── Backend.php        ← chargé sur toutes les pages d'administration
│   ├── Manage.php         ← page d'administration du plugin (plugin.php?p=monPlugin)
│   ├── Config.php         ← page de configuration (Admin > Plugins > Configurer)
│   ├── Frontend.php       ← chargé sur toutes les pages publiques
│   ├── Widgets.php        ← déclaration de widgets
│   ├── Uninstall.php      ← actions de désinstallation (plugin Uninstaller)
│   └── *.php              ← vos autres classes (autoload PSR-4 dans le namespace du plugin)
├── locales/<lang>/
│   ├── main.po            ← traductions (chargées automatiquement, .po ou .lang.php)
│   ├── resources.php      ← enregistrement des pages d'aide
│   └── help/*.html
├── css/, js/, img/        ← ressources servies via My::fileURL(), My::cssLoad(), My::jsLoad()
├── default-templates/     ← templates fournis aux thèmes ({{tpl:…}})
├── dcstore.xml            ← flux de mise à jour (DotAddict ou dépôt GitHub)
└── README.md
```

## `_define.php`

```php
$this->registerModule(
    'Nom affiché',
    'Description',
    'Auteur',
    '1.0.0',                              // incrémenter pour rejouer Install
    [
        'requires'    => [['core', '2.39']],
        'permissions' => 'My',            // ou App::auth()->makePermissions([...])
        'type'        => 'plugin',
        'settings'    => ['self' => ''],  // 'self' = lien vers Manage, 'blog' => '#params.x' = onglet des paramètres du blog
        'priority'    => 1000,            // ordre de chargement (optionnel)
        'details'     => 'https://…',
        'support'     => 'https://…',
        'repository'  => 'https://…/dcstore.xml',
    ]
);
```

## Le cycle de vie `TraitProcess`

Chaque classe de contexte (`Backend`, `Frontend`, `Manage`, …) utilise `Dotclear\Helper\Process\TraitProcess` :

```php
public static function init(): bool      // suis-je autorisé ici ? → self::status(My::checkContext(My::BACKEND))
public static function process(): bool   // traitements (POST, enregistrement des behaviors…)
public static function render(): void    // affichage (Manage / Config uniquement)
```

`My::checkContext()` vérifie les permissions selon le contexte : `INSTALL`, `PREPEND`, `FRONTEND`,
`BACKEND`, `MANAGE`, `CONFIG`, `MENU`, `WIDGETS`, `UNINSTALL`.
Pour des règles sur mesure, surcharger `My::checkCustomContext(int $context): ?bool`.

## Les points d'entrée vers le cœur (`Dotclear\App`)

| Appel | Rôle |
|---|---|
| `App::blog()` | Blog courant : `id()`, `name()`, `settings()`, `triggerBlog()` |
| `App::auth()` | Utilisateur connecté, permissions |
| `App::behavior()->addBehavior(s)()` | Brancher du code sur un événement (liste : documentation « behaviors ») |
| `App::frontend()->template()->addValue() / addBlock()` | Nouvelles balises `{{tpl:…}}` |
| `App::backend()->page()`, `->notices()`, `->menus()` | Pages, notifications, menus de l'administration |
| `App::url()->register()` | Nouvelles URL publiques (dans `Prepend`) |
| `App::rest()->addFunction()` | Services REST (dans `Prepend`) |
| `App::db()->con()` / `App::db()->structure()` | Base de données, création de tables |
| `App::cache()`, `App::log()`, `App::version()` | Cache, journal, versions |

Réglages : `My::settings()` renvoie l'espace de noms du plugin dans `dc_setting`
(`put()`, `get()`, `getBool()`, `getStr()`, `getArray()`, `drop()`), par blog ou global.

## Formulaires d'administration

Dotclear fournit des composants (`Dotclear\Helper\Html\Form\*` : `Form`, `Fieldset`, `Para`,
`Input`, `Checkbox`, `Select`, `Textarea`, `Submit`, …) qui génèrent du HTML conforme à la charte
de l'administration — voir `plugins/devSandbox/src/Manage.php`. Toujours inclure
`...My::hiddenFields()` (nonce CSRF + identifiant du plugin).

## Étudier les plugins officiels

Les meilleurs exemples sont dans `./dotclear/plugins/` :

| Plugin | À regarder pour |
|---|---|
| `aboutConfig` | Page Manage simple, menu système |
| `pings` | Formulaire et réglages globaux/par blog |
| `blogroll` | Table SQL (`Install.php`), widget, template, favoris |
| `pages` | Type de billet personnalisé, URL handler, permissions dédiées |
| `tags` | REST, intégration à l'éditeur, widgets |
| `widgets` | Système de widgets |
| `antispam` | Filtres extensibles, behaviors sur les commentaires |
| `maintenance` | Tâches planifiées, pages à onglets |

Et sur GitHub, les plugins de Franck Paul (`franck-paul/*`) et de Jean-Christian Denis (`JcDenis/*`)
suivent exactement cette structure moderne.
