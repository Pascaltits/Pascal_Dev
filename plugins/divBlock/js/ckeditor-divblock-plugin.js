/*global CKEDITOR, dotclear */
'use strict';

/*
 * CKEditor already bundles a full "div" plugin (command 'creatediv'/'editdiv',
 * toolbar button 'CreateDiv', and the "Create Div Container" dialog offering a
 * predefined Style dropdown, a free-text Class field, and — in its Advanced
 * tab — id, lang, inline style and text direction).
 *
 * That plugin is loaded on every editor instance already, it is simply never
 * added to Dotclear's toolbar layout. Dotclear's 'ckeditorExtraPlugins'
 * behavior (see BackendBehaviors.php) references the 'CreateDiv' button so it
 * gets rendered, the same way the 'media', 'img' and 'entrylink' buttons are.
 *
 * On top of that, this plugin feeds the dialog's Style dropdown with the
 * layout presets declared in src/DivStyles.php (columns, side-by-side or
 * overlapping blocks, boxes...) and loads css/divblock.css inside the editing
 * area so authors see the chosen layout while writing.
 */
(() => {
  // Read once: dotclear.getData() removes the JSON element after reading, and
  // this file runs once per page even with several editor instances.
  const divblock = dotclear.getData('divblock');

  CKEDITOR.plugins.add('dcdivblock', {
    requires: 'div',
    init(editor) {
      if (divblock.css) {
        editor.addContentsCss(divblock.css);
      }

      if (!Array.isArray(divblock.styles)) {
        return;
      }

      // The dialog reads styles with element === 'div' from the editor's
      // styles set. That set is already resolved (and cached) when plugins
      // initialize, so append to it in place. The array is shared by every
      // editor instance on the page, hence the duplicate check.
      editor.getStylesSet((styles) => {
        if (!styles) {
          return;
        }
        for (const preset of divblock.styles) {
          if (styles.some((style) => style.name === preset.name)) {
            continue;
          }
          styles.push({
            name: preset.name,
            element: 'div',
            attributes: { class: preset.class },
          });
        }
      });
    },
  });
})();
