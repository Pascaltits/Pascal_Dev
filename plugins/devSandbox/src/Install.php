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
 * Installation / mise à jour du plugin.
 *
 * Exécuté par Dotclear quand la version déclarée dans _define.php est plus récente
 * que celle enregistrée en base (My::checkContext(My::INSTALL) fait la comparaison).
 * C'est ici que l'on crée les tables SQL et les réglages par défaut.
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

        // Réglages par défaut, enregistrés au niveau global (valables pour tous les blogs).
        // Chaque blog peut ensuite les surcharger depuis la page d'administration du plugin.
        // Signature : put(nom, valeur, type, libellé, ignore_value, global)
        $settings = My::settings();
        $settings->put(My::SETTING_ACTIVE, true, App::blogWorkspace()::NS_BOOL, 'Activer le message en pied de page', false, true);
        $settings->put(My::SETTING_MESSAGE, My::DEFAULT_MESSAGE, App::blogWorkspace()::NS_STRING, 'Message affiché', false, true);

        return true;
    }
}
