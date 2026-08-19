<?php
/**
 * @package     Dotclear
 *
 * @copyright   Pascal Tiberghien
 * @copyright   AGPL-3.0
 */
declare(strict_types=1);

namespace Dotclear\Plugin\quickLinks;

use Dotclear\App;
use Dotclear\Helper\Process\TraitProcess;

/**
 * @brief   The module install process.
 * @ingroup quickLinks
 */
class Install
{
    use TraitProcess;

    public static function init(): bool
    {
        return self::status(My::checkContext(My::INSTALL));
    }

    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        $struct = App::db()->structure();

        $struct->table(QuickLinks::TABLE_NAME)
            ->field('qlink_id', 'bigint', 0, false)
            ->field('blog_id', 'varchar', 32, false)
            ->field('qlink_label', 'varchar', 255, false)
            ->field('qlink_url', 'varchar', 255, false)
            ->field('qlink_position', 'integer', 0, false, 0)
            ->field('qlink_status', 'smallint', 0, false, QuickLinks::STATUS_ONLINE)

            ->primary('pk_qlink', 'qlink_id')
            ->index('idx_qlink_blog_id', 'btree', 'blog_id')
            ->reference('fk_qlink_blog', 'blog_id', 'blog', 'blog_id', 'cascade', 'cascade')
        ;

        App::db()->structure()->synchronize($struct);

        return true;
    }
}
