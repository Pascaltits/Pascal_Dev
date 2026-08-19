/*global CKEDITOR */
'use strict';

/*
 * CKEditor already bundles a full "div" plugin (command 'creatediv'/'editdiv',
 * toolbar button 'CreateDiv', and the "Create Div Container" dialog offering a
 * predefined Style dropdown, a free-text Class field, and — in its Advanced
 * tab — id, lang, inline style and text direction).
 *
 * That plugin is loaded on every editor instance already, it is simply never
 * added to Dotclear's toolbar layout. This tiny wrapper plugin exists only so
 * Dotclear's 'ckeditorExtraPlugins' behavior (see BackendBehaviors.php) can
 * reference the 'CreateDiv' button and have it rendered in the toolbar, the
 * same way the 'media', 'img' and 'entrylink' buttons are added.
 */
CKEDITOR.plugins.add('dcdivblock', {
  requires: 'div',
  init() {
    // Nothing to do: the 'div' dependency guarantees the 'CreateDiv' command
    // and button are registered; only the toolbar entry (added via the
    // 'ckeditorExtraPlugins' behavior) was missing.
  },
});
