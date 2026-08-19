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
use Dotclear\Module\MyPlugin;

/**
 * @brief   The module helper.
 * @ingroup divBlock
 */
class My extends MyPlugin
{
    protected static function checkCustomContext(int $context): ?bool
    {
        // No admin page: just needs to run for any backend (post/page editor) user.
        return match ($context) {
            self::INSTALL => null,
            default       => App::task()->checkContext('BACKEND'),
        };
    }
}
