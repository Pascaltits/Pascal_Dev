<?php
/**
 * @package     Dotclear
 *
 * @copyright   Pascal Tiberghien
 * @copyright   AGPL-3.0
 */
declare(strict_types=1);

namespace Dotclear\Plugin\divBlock;

use Dotclear\App;
use Dotclear\Helper\Process\TraitProcess;

/**
 * @brief   The module backend process.
 * @ingroup divBlock
 */
class Backend
{
    use TraitProcess;

    public static function init(): bool
    {
        // Dead but useful code (for l10n)
        __('Div Block');
        __('Adds an "Insert div block" button (layout presets, class, style) to the CKEditor toolbar');

        return self::status(My::checkContext(My::BACKEND));
    }

    public static function process(): bool
    {
        if (self::status()) {
            App::behavior()->addBehaviors([
                'adminPostEditor'      => BackendBehaviors::adminPostEditor(...),
                'ckeditorExtraPlugins' => BackendBehaviors::ckeditorExtraPlugins(...),
            ]);
        }

        return self::status();
    }
}
