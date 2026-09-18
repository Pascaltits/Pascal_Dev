# divBlock — bouton « Insérer un bloc div » pour dcCKEditor

Ajoute un bouton **« Insérer un bloc div »** dans la barre d'outils de
l'éditeur de billets/pages (dcCKEditor), en s'appuyant sur le plugin natif
`div` déjà embarqué dans CKEditor 4 mais non exposé par Dotclear, et enrichit
sa boîte de dialogue de **mises en page prédéfinies**.

La boîte de dialogue ouverte par ce bouton permet de :

- choisir une **mise en page prédéfinie** dans le menu déroulant « Style » :
  - Encadré, Mise en avant, Alerte ;
  - Texte sur deux / trois colonnes (le texte coule d'une colonne à l'autre) ;
  - Deux / trois blocs côte à côte (chaque paragraphe, image, liste… occupe
    une case de la grille) ;
  - Deux blocs qui se chevauchent (le second recouvre partiellement le
    premier, effet « carte ») ;
- saisir librement une **classe CSS** ;
- dans l'onglet « Avancé » : `id`, langue, **style CSS en ligne**, titre, sens
  de lecture (`dir`).

Les mises en page s'affichent **dans l'éditeur** pendant la rédaction et sur
le **site public** avec le même rendu (la feuille de style est chargée des
deux côtés). Elles passent en une seule colonne sous 640 px de large.

## Comment ça marche

Ce plugin **ne modifie aucun fichier du cœur** de Dotclear ni de
`dcCKEditor` ; il s'appuie uniquement sur des points d'extension officiels et
reste donc compatible avec les mises à jour.

- `src/Backend.php` enregistre deux comportements :
  - `ckeditorExtraPlugins` (le même que celui utilisé par le bouton « Média »
    ou par le plugin `tags`) pour afficher le bouton `CreateDiv`, déjà fourni
    par CKEditor, dans la barre d'outils ;
  - `adminPostEditor` pour transmettre à l'éditeur la liste des mises en page
    et l'URL de la feuille de style.
- `src/DivStyles.php` déclare la liste des mises en page (libellé traduisible
  → classe CSS). **C'est ici qu'on ajoute ou modifie un préréglage.**
- `css/divblock.css` définit le rendu de chaque classe. À garder en phase avec
  `DivStyles.php`.
- `js/ckeditor-divblock-plugin.js` ajoute ces mises en page au jeu de styles
  déjà chargé par CKEditor (celui que lit la boîte de dialogue du plugin
  `div`) et charge la feuille de style dans la zone d'édition.
- `src/Frontend.php` charge la même feuille de style sur le site public
  (comportement `publicHeadContent`).
- `locales/fr/main.po` : traductions françaises des libellés.

## Installation

1. Copier le dossier `divBlock` dans `plugins/` d'une installation Dotclear
   2.34+ utilisant `dcCKEditor` comme éditeur actif.
2. Activer le plugin depuis la liste des plugins (back-office).
3. Ouvrir l'éditeur d'un billet ou d'une page : le bouton apparaît dans la
   barre d'outils, à côté de Média/Image/Notes.

## Utilisation

1. Sélectionner un ou plusieurs paragraphes (ou n'importe quels blocs).
2. Cliquer sur le bouton « Insérer un bloc div ».
3. Choisir une mise en page dans « Style » (et/ou saisir une classe), valider.

Pour les mises en page « blocs côte à côte » et « qui se chevauchent »,
chaque bloc sélectionné (paragraphe, image, liste…) devient une case : deux
paragraphes sélectionnés → deux colonnes.

Un clic droit dans un `div` existant donne accès aux entrées de menu
contextuel « Modifier le div » (pour changer de mise en page) et « Supprimer
le div » (qui retire le conteneur en conservant son contenu).

## Ajouter une mise en page

1. Ajouter une ligne dans `src/DivStyles.php` :
   `['name' => __('My layout'), 'class' => 'dcdiv-my-layout']`
2. Définir `.dcdiv-my-layout { … }` dans `css/divblock.css`.
3. (Optionnel) Ajouter la traduction du libellé dans `locales/fr/main.po`.
