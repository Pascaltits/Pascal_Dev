# divBlock — bouton « Insérer un div » pour dcCKEditor

Ajoute un bouton **« Insérer un div »** dans la barre d'outils de l'éditeur de
billets/pages (dcCKEditor), en s'appuyant sur le plugin natif `div` déjà
embarqué dans CKEditor 4 mais non exposé par Dotclear.

La boîte de dialogue ouverte par ce bouton permet de :

- choisir un **style prédéfini** (menu déroulant « Style ») ;
- saisir librement une **classe CSS** ;
- dans l'onglet « Avancé » : `id`, langue, **style CSS en ligne**, titre, sens
  de lecture (`dir`).

## Comment ça marche

Ce plugin **ne modifie aucun fichier du cœur** de Dotclear ni de
`dcCKEditor` : il s'accroche au point d'extension officiel
`ckeditorExtraPlugins` (le même que celui utilisé par le bouton "Média", ou
par le plugin `tags` pour son propre bouton). Il est donc totalement
compatible avec les mises à jour de Dotclear.

- `src/Backend.php` enregistre le comportement `ckeditorExtraPlugins`.
- `src/BackendBehaviors.php` déclare un plugin CKEditor externe minimal
  (`js/ckeditor-divblock-plugin.js`) et demande l'affichage du bouton
  `CreateDiv` (déjà fourni par CKEditor) dans la barre d'outils.
- `js/ckeditor-divblock-plugin.js` ne fait qu'indiquer une dépendance sur le
  plugin natif `div` (`requires: 'div'`) — tout le reste (commande, boîte de
  dialogue, icône) existe déjà dans CKEditor.

## Installation

1. Copier le dossier `divBlock` dans `plugins/` d'une installation Dotclear
   utilisant `dcCKEditor` comme éditeur actif.
2. Activer le plugin depuis la liste des plugins (back-office).
3. Ouvrir l'éditeur d'un billet ou d'une page : le bouton apparaît dans le
   groupe "custom" de la barre d'outils, à côté de Média/Image/Notes.

## Aller plus loin : personnaliser la liste de styles/classes proposée

Voir [`PATCH-config.js.md`](PATCH-config.js.md) — un petit patch optionnel
(qui, lui, touche un fichier du cœur `dcCKEditor` et doit donc être
réappliqué après mise à jour) pour proposer une liste de styles/classes
prédéfinis dans le menu déroulant de la boîte de dialogue, plutôt que de
laisser l'auteur taper la classe à la main.
