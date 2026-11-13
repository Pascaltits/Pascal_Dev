<?php
/**
 * @brief devSandbox, un plugin d'exemple pour Dotclear 2
 *
 * @package Dotclear
 * @subpackage Plugins
 *
 * @author Pascal Tits
 * @copyright GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
declare(strict_types=1);

namespace Dotclear\Plugin\devSandbox;

use Dotclear\App;
use Dotclear\Helper\Process\TraitProcess;

/**
 * Chargé sur chaque page publique du blog.
 *
 * Rôle : enregistrer les behaviors et les balises de template côté blog.
 */
class Frontend
{
    use TraitProcess;

    public static function init(): bool
    {
        return self::status(My::checkContext(My::FRONTEND));
    }

    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        // Behavior appelé par {{tpl:SysBehavior behavior="publicFooterContent"}} dans les thèmes
        App::behavior()->addBehaviors([
            'publicHeadContent'   => FrontendBehaviors::publicHeadContent(...),
            'publicFooterContent' => FrontendBehaviors::publicFooterContent(...),
        ]);

        // Nouvelle balise de template : {{tpl:DevSandboxMessage}}
        App::frontend()->template()->addValue('DevSandboxMessage', FrontendTemplate::message(...));

        return true;
    }
}
