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

use ArrayObject;
use Dotclear\App;

/**
 * Balises de template fournies par le plugin.
 *
 * Une « valeur » de template retourne du code PHP qui sera inséré dans le template
 * compilé (dotclear/cache/cbtpl/…), puis exécuté à chaque affichage.
 *
 * Usage dans un thème : {{tpl:DevSandboxMessage}}  ou  {{tpl:DevSandboxMessage encode_html="1"}}
 */
class FrontendTemplate
{
    /**
     * @param   ArrayObject<string, mixed>  $attr   Attributs de la balise
     */
    public static function message(ArrayObject $attr): string
    {
        $filters = App::frontend()->template()->getFilters($attr);

        return '<?php echo ' . sprintf(
            $filters,
            '\\' . My::class . '::settings()->getStr(\'' . My::SETTING_MESSAGE . '\') ?? \'' . addslashes(My::DEFAULT_MESSAGE) . '\'',
        ) . '; ?>';
    }
}
