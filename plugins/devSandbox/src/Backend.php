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
use Dotclear\Core\Backend\Menus;
use Dotclear\Helper\Process\TraitProcess;

/**
 * Chargé sur chaque page de l'administration.
 *
 * Rôle : enregistrer l'entrée de menu et les comportements (behaviors) côté admin.
 * Pas de HTML ici : la page du plugin est rendue par Manage.
 */
class Backend
{
    use TraitProcess;

    public static function init(): bool
    {
        // Code « mort » mais utile : permet aux outils de traduction de repérer ces chaînes
        __('Dev Sandbox');
        __('Bac à sable de développement : exemple de plugin');

        return self::status(My::checkContext(My::BACKEND));
    }

    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        // Entrée dans le menu « Plugins » de la barre latérale (icône icon.svg du plugin)
        My::addBackendMenuItem(Menus::MENU_PLUGINS);

        // Exemple de comportement : ajoute une entrée au tableau de bord
        App::behavior()->addBehaviors([
            'adminDashboardFavoritesV2' => BackendBehaviors::adminDashboardFavorites(...),
        ]);

        return true;
    }
}
