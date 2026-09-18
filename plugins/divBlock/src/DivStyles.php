<?php
/**
 * @package     Dotclear
 *
 * @copyright   Pascal Tiberghien
 * @copyright   AGPL-3.0
 */
declare(strict_types=1);

namespace Dotclear\Plugin\divBlock;

/**
 * @brief   The div style presets offered in the "Create Div Container" dialog.
 * @ingroup divBlock
 *
 * Each preset maps a translatable label to a CSS class defined in
 * css/divblock.css (loaded both in the editor and on the public site).
 * Edit this list and the stylesheet together to add or change presets.
 */
class DivStyles
{
    /**
     * @return  list<array{name: string, class: string}>
     */
    public static function list(): array
    {
        return [
            ['name' => __('Framed box'),               'class' => 'dcdiv-framed'],
            ['name' => __('Highlighted box'),          'class' => 'dcdiv-highlight'],
            ['name' => __('Alert box'),                'class' => 'dcdiv-alert'],
            ['name' => __('Text in two columns'),      'class' => 'dcdiv-cols-2'],
            ['name' => __('Text in three columns'),    'class' => 'dcdiv-cols-3'],
            ['name' => __('Two side-by-side blocks'),  'class' => 'dcdiv-grid-2'],
            ['name' => __('Three side-by-side blocks'), 'class' => 'dcdiv-grid-3'],
            ['name' => __('Two overlapping blocks'),   'class' => 'dcdiv-overlap-2'],
        ];
    }
}
