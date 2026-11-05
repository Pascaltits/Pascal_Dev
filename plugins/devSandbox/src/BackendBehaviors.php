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
use Dotclear\Core\Backend\Favorites;

/**
 * Comportements (behaviors) côté administration.
 *
 * La liste complète des behaviors disponibles :
 * https://dotclear.org/documentation/2.0/ref/behaviors
 */
class BackendBehaviors
{
    /**
     * Déclare le plugin comme favori possible du tableau de bord
     * (Mon tableau de bord > Favoris).
     */
    public static function adminDashboardFavorites(Favorites $favs): string
    {
        $favs->register(My::id(), [
            'title'          => My::name(),
            'url'            => My::manageUrl(),
            'menu-icon'      => My::icon(),
            'dashboard-icon' => My::icon(),
            'permissions'    => App::auth()->makePermissions([
                App::auth()::PERMISSION_ADMIN,
            ]),
        ]);

        return '';
    }
}
