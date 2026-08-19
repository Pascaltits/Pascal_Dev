# Pascal_Dev — Développement de plugins Dotclear

Ce dépôt contient des plugins pour [Dotclear](https://dotclear.org), le CMS de blog
open-source en PHP. Il a été mis en place après analyse du cœur officiel
([dotclear/dotclear](https://github.com/dotclear/dotclear), branche `develop`,
version `2.40-dev`, PHP >= 8.2).

## Architecture des plugins Dotclear (2.3x / 2.4x)

Depuis la version 2.27, les plugins Dotclear utilisent une architecture moderne
orientée classes avec autoload PSR-4 (namespace `Dotclear\Plugin\<NomDuPlugin>`).
Un plugin est un dossier sous `plugins/<NomDuPlugin>/` avec cette structure :

```
plugins/<NomDuPlugin>/
├── _define.php              # Métadonnées du module (nom, description, version, permissions...)
├── icon.svg                 # Icône affichée dans le menu et la liste des plugins
├── src/
│   ├── My.php                # Classe utilitaire : contexte, permissions, URL du plugin
│   ├── Install.php            # (optionnel) Création/mise à jour du schéma BDD
│   ├── Prepend.php            # (optionnel) Init précoce (routes front, hooks globaux)
│   ├── Backend.php             # (optionnel) Enregistrement de l'entrée de menu back-office
│   ├── Frontend.php / FrontendUrl.php / FrontendTemplate.php  # (optionnel) Partie publique
│   ├── Manage.php              # Page d'administration principale (contrôleur + vue)
│   ├── ManageEdit.php          # (optionnel) Sous-page d'édition
│   └── Widgets.php             # (optionnel) Widgets de blocs latéraux
├── js/                        # Scripts JS chargés côté back-office
└── locales/
    ├── fr/resources.php + help/help.html
    └── en/resources.php + help/help.html
```

Points clés observés dans le cœur (`plugins/aboutConfig`, `plugins/blogroll`,
`plugins/userPref`) :

- **`_define.php`** appelle `$this->registerModule(name, description, author, version, [...])`.
  Les clés utiles : `type` (`plugin`/`theme`), `permissions` (permission requise,
  ou `'My'` pour déléguer à `My::checkContext()`), `requires` (dépendances de
  version du cœur), `settings`.
- **Classes `Install`, `Prepend`, `Backend`, `Frontend`, `Manage`, `ManageEdit`**
  utilisent le trait `Dotclear\Helper\Process\TraitProcess` avec le cycle
  `init()` → `process()` → `render()` (pour les pages) — ces classes sont
  auto-découvertes par convention de nom de fichier, pas besoin de les
  déclarer manuellement.
- **`My` (extends `Dotclear\Module\MyPlugin`)** centralise les contrôles de
  contexte (`checkCustomContext()`), la construction d'URL (`My::manageUrl()`),
  la redirection (`My::redirect()`) et les champs cachés de formulaire
  (`My::hiddenFields()` — inclut le jeton anti-CSRF).
- **Accès à la façade globale `Dotclear\App`** : `App::blog()`, `App::auth()`,
  `App::db()`, `App::backend()`, `App::error()`, etc.
- **Accès BDD** : `App::db()->structure()` pour créer/synchroniser les tables
  (dans `Install.php`), et `SelectStatement` / `UpdateStatement` /
  `DeleteStatement` / `Cursor` (`App::db()->con()->openCursor()`) pour les
  requêtes CRUD.
- **Formulaires HTML** : classes sous `Dotclear\Helper\Html\Form\*`
  (`Form`, `Para`, `Input`, `Hidden`, `Select`, `Submit`, `Table`, etc.),
  échappement via `Dotclear\Helper\Html\Html::escapeHTML()`.
- **i18n** : chaînes traduisibles via `__('...')`, aide contextuelle déclarée
  dans `locales/<lang>/resources.php`.

## Plugin d'exemple : `quickLinks`

Un plugin back-office complet et fonctionnel a été créé dans
[`plugins/quickLinks`](plugins/quickLinks) pour servir de base de départ. Il
gère une liste de liens rapides (« quick links ») par blog, directement depuis
l'administration :

- table BDD dédiée (`quicklink`), créée/synchronisée dans `Install.php` ;
- entrée de menu dans le back-office (section « Blog ») ;
- page de gestion : liste, ajout, suppression (`Manage.php`) ;
- page d'édition dédiée (`ManageEdit.php`) ;
- contrôle d'accès (admin du blog / content admin) via `My.php` ;
- aide contextuelle en français et en anglais.

Ce plugin est un **squelette prêt à adapter** : renommez le namespace,
la table, les champs et la vue selon la fonctionnalité back-office réelle que
vous souhaitez développer.

### Installer le plugin pour le tester

1. Copiez (ou liez en symlink) `plugins/quickLinks` dans le dossier
   `plugins/` d'une installation Dotclear 2.34+ fonctionnelle.
2. Connectez-vous à l'administration, section **Plugins** : activez
   « Quick Links ».
3. Le plugin apparaît dans le menu **Blog** du back-office.

### Pour aller plus loin

- Générer les fichiers `.po`/`.mo` de traduction complémentaires si le plugin
  doit être distribué (voir `crowdin.yml` / `transifex.yaml` du cœur pour le
  processus utilisé par l'équipe officielle).
- Ajouter un `src/Widgets.php` si le plugin doit exposer un widget de bloc
  latéral (voir `plugins/blogroll/src/Widgets.php` comme référence).
- Ajouter des tests via `phpunit.dist.xml` du cœur si le plugin devient plus
  complexe.

## Prochaines étapes possibles

Dites-moi quelle fonctionnalité back-office précise vous souhaitez (ex. :
gestion d'un contenu personnalisé, page de réglages avancés, tableau de bord,
intégration API externe...) et j'adapterai `quickLinks` — ou je créerai un
nouveau plugin dédié — en conséquence.
