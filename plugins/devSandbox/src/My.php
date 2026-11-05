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

use Dotclear\Module\MyPlugin;

/**
 * Identité du plugin.
 *
 * MyPlugin fournit, sans rien écrire :
 *  - My::id(), My::name(), My::path()           → identifiant, nom, dossier du plugin
 *  - My::settings()                              → réglages du blog, espace de noms « devSandbox »
 *  - My::prefs()                                 → préférences de l'utilisateur courant
 *  - My::checkContext(My::BACKEND|FRONTEND|…)    → contrôle des permissions par contexte
 *  - My::addBackendMenuItem(), My::manageUrl(), My::redirect(), My::hiddenFields()
 *  - My::fileURL(), My::cssLoad(), My::jsLoad()   → ressources du plugin
 */
class My extends MyPlugin
{
    /** Nom des réglages (My::settings()->get(self::SETTING_MESSAGE)) */
    public const SETTING_ACTIVE  = 'active';
    public const SETTING_MESSAGE = 'message';

    /** Valeur par défaut du message affiché dans le pied de page du blog */
    public const DEFAULT_MESSAGE = 'Bonjour depuis le plugin devSandbox !';
}
