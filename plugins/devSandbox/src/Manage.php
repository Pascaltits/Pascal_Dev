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
use Dotclear\Helper\Html\Form\Checkbox;
use Dotclear\Helper\Html\Form\Div;
use Dotclear\Helper\Html\Form\Fieldset;
use Dotclear\Helper\Html\Form\Form;
use Dotclear\Helper\Html\Form\Input;
use Dotclear\Helper\Html\Form\Label;
use Dotclear\Helper\Html\Form\Legend;
use Dotclear\Helper\Html\Form\Note;
use Dotclear\Helper\Html\Form\Para;
use Dotclear\Helper\Html\Form\Submit;
use Dotclear\Helper\Html\Form\Text;
use Dotclear\Helper\Html\Html;
use Dotclear\Helper\Process\TraitProcess;
use Exception;

/**
 * Page d'administration du plugin (Admin > Plugins > Dev Sandbox).
 *
 * Cycle de vie imposé par Dotclear :
 *   init()    → vérifie que l'utilisateur a le droit d'accéder à la page
 *   process() → traite le formulaire (POST) avant tout affichage
 *   render()  → affiche la page
 *
 * Pour déboguer : poser un point d'arrêt dans process() puis enregistrer le formulaire.
 */
class Manage
{
    use TraitProcess;

    public static function init(): bool
    {
        return self::status(My::checkContext(My::MANAGE));
    }

    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        if (isset($_POST['save'])) {
            try {
                $settings = My::settings();

                // <-- Point d'arrêt conseillé : inspecter $_POST et $settings dans l'IDE
                $active  = !empty($_POST[My::SETTING_ACTIVE]);
                $message = isset($_POST[My::SETTING_MESSAGE]) && is_string($_POST[My::SETTING_MESSAGE])
                    ? trim($_POST[My::SETTING_MESSAGE])
                    : '';

                if ($message === '') {
                    throw new Exception(__('Le message ne peut pas être vide.'));
                }

                $settings->put(My::SETTING_ACTIVE, $active, App::blogWorkspace()::NS_BOOL);
                $settings->put(My::SETTING_MESSAGE, $message, App::blogWorkspace()::NS_STRING);

                // Invalide le cache des pages publiques pour voir le changement immédiatement
                App::blog()->triggerBlog();

                App::backend()->notices()->addSuccessNotice(__('Les réglages ont été enregistrés.'));
                My::redirect();
            } catch (Exception $e) {
                App::error()->add($e->getMessage());
            }
        }

        return true;
    }

    public static function render(): void
    {
        if (!self::status()) {
            return;
        }

        $settings = My::settings();
        $active   = ($settings->getBool(My::SETTING_ACTIVE) ?? true);
        $message  = ($settings->getStr(My::SETTING_MESSAGE) ?? My::DEFAULT_MESSAGE);

        App::backend()->page()->openModule(My::name());

        echo
        App::backend()->page()->breadcrumb([
            Html::escapeHTML(App::blog()->name()) => '',
            My::name()                            => '',
        ]) .
        App::backend()->notices()->getNotices();

        echo (new Form('devsandbox-form'))
            ->method('post')
            ->action(My::manageUrl())
            ->fields([
                (new Fieldset())
                    ->legend(new Legend(__('Message en pied de page du blog')))
                    ->fields([
                        (new Para())->items([
                            (new Checkbox(My::SETTING_ACTIVE, $active))
                                ->value(1)
                                ->label(new Label(__('Afficher le message sur le blog'), Label::IL_FT)),
                        ]),
                        (new Para())->items([
                            (new Input(My::SETTING_MESSAGE))
                                ->size(60)
                                ->maxlength(255)
                                ->value(Html::escapeHTML($message))
                                ->label(new Label(__('Message :'), Label::OL_TF)),
                        ]),
                        (new Note())
                            ->class('form-note')
                            ->text(__('Le message est aussi disponible dans les templates via {{tpl:DevSandboxMessage}}.')),
                    ]),
                (new Div())
                    ->class('fieldset')
                    ->items([
                        (new Text('h4', __('Informations de débogage'))),
                        (new Para())->items([
                            (new Text(null, sprintf(
                                __('Dotclear %s — PHP %s — Xdebug %s — blog « %s » (%s)'),
                                App::config()->dotclearVersion(),
                                PHP_VERSION,
                                extension_loaded('xdebug') ? (string) phpversion('xdebug') : __('absent'),
                                Html::escapeHTML(App::blog()->name()),
                                Html::escapeHTML(App::blog()->id()),
                            ))),
                        ]),
                    ]),
                (new Para())
                    ->class('form-buttons')
                    ->items([
                        ...My::hiddenFields(),
                        (new Submit(['save'], __('Enregistrer'))),
                    ]),
            ])
            ->render();

        App::backend()->page()->helpBlock(My::id());
        App::backend()->page()->closeModule();
    }
}
