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
        // No admin page. Backend: any post/page editor user. Frontend: default
        // rules (the public stylesheet must be served to every visitor).
        return match ($context) {
            self::INSTALL, self::FRONTEND => null,
            default                       => App::task()->checkContext('BACKEND'),
        };
    }
}
