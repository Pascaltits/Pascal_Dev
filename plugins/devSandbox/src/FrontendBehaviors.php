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

use Dotclear\Helper\Html\Html;

/**
 * Comportements (behaviors) côté blog.
 */
class FrontendBehaviors
{
    /**
     * Injecte une feuille de style dans le <head> des pages publiques.
     */
    public static function publicHeadContent(): string
    {
        if (!(My::settings()->getBool(My::SETTING_ACTIVE) ?? true)) {
            return '';
        }

        echo My::cssLoad('devsandbox.css');

        return '';
    }

    /**
     * Affiche le message configuré dans le pied de page du blog.
     */
    public static function publicFooterContent(): string
    {
        $settings = My::settings();

        // <-- Point d'arrêt conseillé : recharger la page d'accueil du blog pour s'arrêter ici
        if (!($settings->getBool(My::SETTING_ACTIVE) ?? true)) {
            return '';
        }

        $message = ($settings->getStr(My::SETTING_MESSAGE) ?? My::DEFAULT_MESSAGE);

        echo '<p class="devsandbox-message">' . Html::escapeHTML($message) . '</p>';

        return '';
    }
}
