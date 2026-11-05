# devSandbox — plugin d'exemple

Plugin minimal mais complet, à copier pour démarrer (`make new-plugin ID=monPlugin NAME="Mon plugin"`).

| Fichier | Rôle |
|---|---|
| `_define.php` | Déclaration du module : nom, version, `requires`, permissions |
| `src/My.php` | Identité du plugin, constantes des réglages |
| `src/Install.php` | Réglages par défaut (rejoué quand la version change) |
| `src/Backend.php` | Menu d'administration, behaviors admin |
| `src/BackendBehaviors.php` | Favori de tableau de bord |
| `src/Manage.php` | Page d'administration (formulaire + sauvegarde) |
| `src/Frontend.php` | Behaviors et balise de template côté blog |
| `src/FrontendBehaviors.php` | CSS dans `<head>`, message en pied de page |
| `src/FrontendTemplate.php` | Balise `{{tpl:DevSandboxMessage}}` |
| `locales/fr/` | Traductions (`main.po`) et aide en ligne |
| `css/`, `icon.svg` | Ressources statiques |
| `dcstore.xml` | Flux de mise à jour pour la distribution |

Les commentaires `<-- Point d'arrêt conseillé` indiquent où poser un breakpoint pour voir le plugin s'exécuter sous Xdebug.
