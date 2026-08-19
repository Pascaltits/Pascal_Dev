<?php
/**
 * @package     Dotclear
 *
 * @copyright   Pascal Tiberghien
 * @copyright   AGPL-3.0
 */
declare(strict_types=1);

namespace Dotclear\Plugin\divBlock;

use ArrayObject;
use Dotclear\App;

/**
 * @brief   The module backend behaviors.
 * @ingroup divBlock
 */
class BackendBehaviors
{
    /**
     * Register the "CreateDiv" button in the CKEditor toolbar.
     *
     * CKEditor ships a built-in 'div' plugin (dialog "Create Div Container":
     * predefined Style, free Class field, and an Advanced tab with id, lang,
     * inline style and dir) but Dotclear does not expose its toolbar button.
     * This behavior hooks into dcCKEditor's officially supported extension
     * point (the same one used by the 'media', 'img', 'entrylink' buttons)
     * to surface it, without touching dcCKEditor's own files.
     *
     * @param   ArrayObject<int, array{name:string, url:string, button:string}>     $extraPlugins   The extra plugins
     * @param   string                                                              $context        The page context (post, page, comment, ...)
     */
    public static function ckeditorExtraPlugins(ArrayObject $extraPlugins, string $context): string
    {
        if ($context !== 'post') {
            // 'post' context covers both posts and pages (pages reuse the post editor).
            return '';
        }

        $extraPlugins->append([
            'name'   => 'dcdivblock',
            'button' => 'CreateDiv',
            'url'    => App::config()->adminUrl() . My::fileURL('js/ckeditor-divblock-plugin.js'),
        ]);

        return '';
    }
}
