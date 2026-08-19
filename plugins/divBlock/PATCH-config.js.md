# Patch optionnel : styles/classes prédéfinis dans la boîte de dialogue « Insérer un div »

Le plugin `divBlock` (voir `_define.php`, `src/`, `js/`) suffit à lui seul pour
faire apparaître le bouton **« Insérer un div »** dans la barre d'outils de
l'éditeur de billets/pages. Une fois ce plugin activé, la boîte de dialogue
native de CKEditor permet déjà à l'auteur de :

- choisir un style dans le menu déroulant **Style** (liste définie ci-dessous) ;
- saisir librement une **classe CSS** ;
- dans l'onglet **Avancé** : un `id`, une langue, un **style CSS en ligne**
  libre, un titre et un sens de lecture (`dir`).

Par défaut, le menu déroulant **Style** ne contient qu'une seule entrée
d'exemple (« Special Container », héritée du fichier de démo CKEditor). Pour
y ajouter vos propres styles/classes prédéfinis (ex. « Alerte », « Encadré »,
« Deux colonnes »...), il faut modifier un fichier du **cœur** de
`dcCKEditor` : `plugins/dcCKEditor/js/ckeditor/config.js`.

⚠️ **Ce fichier fait partie de la distribution officielle de Dotclear** : une
mise à jour de Dotclear ou du plugin `dcCKEditor` écrasera cette
personnalisation. Conservez cet extrait dans votre dépôt (ou un gestionnaire
de configuration) pour pouvoir le réappliquer après chaque mise à jour.

## Contenu actuel (`plugins/dcCKEditor/js/ckeditor/config.js`)

```js
CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	// config.uiColor = '#AADC6E';
};
```

## Contenu à mettre à la place

```js
CKEDITOR.editorConfig = function( config ) {
	// Styles proposés dans le menu déroulant "Style" de la boîte de dialogue
	// "Insérer un div" (plugin natif CKEditor 'div'). Adaptez cette liste aux
	// classes CSS réellement définies dans le thème du blog : chaque entrée
	// applique soit une classe ('attributes'), soit un style CSS inline
	// ('styles'), soit les deux.
	config.stylesSet = [
		{
			name: 'Encadré',
			element: 'div',
			styles: { padding: '5px 10px', background: '#eee', border: '1px solid #ccc' }
		},
		{ name: 'Alerte',        element: 'div', attributes: { class: 'alert' } },
		{ name: 'Mise en avant', element: 'div', attributes: { class: 'box-highlight' } },
		{ name: 'Deux colonnes', element: 'div', attributes: { class: 'row row-2cols' } },
		{ name: 'Trois colonnes', element: 'div', attributes: { class: 'row row-3cols' } }
	];
};
```

Remplacez/complétez la liste `config.stylesSet` par les classes CSS réellement
présentes dans la feuille de style de votre thème public (celles-ci ne sont
que des exemples).

## Pourquoi ce fichier précisément ?

CKEditor lit la liste de styles très tôt dans le cycle de vie de l'éditeur,
avant même le chargement des plugins additionnels (mécanisme
`ckeditorExtraPlugins` utilisé par le plugin `divBlock`). Le seul point
d'extension officiel de CKEditor 4 exécuté suffisamment tôt pour définir
`config.stylesSet` de façon fiable est ce fichier `config.js`, chargé
automatiquement par CKEditor pour chaque instance d'éditeur — il n'existe pas
de hook Dotclear équivalent permettant d'injecter cette configuration depuis
un plugin tiers sans modifier ce fichier.

Si vous préférez ne pas toucher au cœur, le bouton fonctionne quand même :
l'auteur peut simplement taper une classe CSS à la main dans le champ
« Classe » de la boîte de dialogue — seul le menu déroulant de raccourcis
prédéfinis nécessite ce patch.
