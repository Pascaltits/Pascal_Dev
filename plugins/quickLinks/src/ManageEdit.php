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
use Dotclear\Database\MetaRecord;
use Dotclear\Helper\Html\Form\Div;
use Dotclear\Helper\Html\Form\Form;
use Dotclear\Helper\Html\Form\Hidden;
use Dotclear\Helper\Html\Form\Input;
use Dotclear\Helper\Html\Form\Label;
use Dotclear\Helper\Html\Form\Note;
use Dotclear\Helper\Html\Form\Para;
use Dotclear\Helper\Html\Form\Select;
use Dotclear\Helper\Html\Form\Span;
use Dotclear\Helper\Html\Form\Submit;
use Dotclear\Helper\Html\Form\Text;
use Dotclear\Helper\Html\Form\Url;
use Dotclear\Helper\Html\Html;
use Dotclear\Helper\Process\TraitProcess;
use Exception;

/**
 * @brief   The module manage quickLinks edit process.
 * @ingroup quickLinks
 */
class ManageEdit
{
    use TraitProcess;

    protected static string $id;

    protected static MetaRecord $rs;

    protected static string $qlink_label;

    protected static string $qlink_url;

    protected static int $qlink_status;

    public static function init(): bool
    {
        self::status(My::checkContext(My::MANAGE) && !empty($_REQUEST['edit']) && !empty($_REQUEST['id']));

        if (self::status()) {
            self::$id = is_numeric($_REQUEST['id']) ? (string) $_REQUEST['id'] : '0';

            $quicklinks = new QuickLinks(App::blog());

            try {
                self::$rs = $quicklinks->getLink(self::$id);
            } catch (Exception $e) {
                App::error()->add($e->getMessage());
            }

            if (!App::error()->flag() && isset(self::$rs) && !self::$rs->isEmpty()) {
                self::$qlink_label  = self::$rs->strField('qlink_label');
                self::$qlink_url    = self::$rs->strField('qlink_url');
                self::$qlink_status = self::$rs->intField('qlink_status');
            } else {
                self::status(false);
            }
        }

        return self::status();
    }

    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        if (!empty($_POST['save'])) {
            $qlink_label  = is_string($qlink_label = $_POST['qlink_label'] ?? '') ? $qlink_label : '';
            $qlink_url    = is_string($qlink_url = $_POST['qlink_url'] ?? '') ? $qlink_url : '';
            $qlink_status = is_numeric($qlink_status = $_POST['qlink_status'] ?? '') ? (int) $qlink_status : QuickLinks::STATUS_ONLINE;

            try {
                $quicklinks = new QuickLinks(App::blog());
                $quicklinks->updateLink(self::$id, $qlink_label, $qlink_url, $qlink_status);

                App::backend()->notices()->addSuccessNotice(__('Link has been successfully updated.'));
                My::redirect();
            } catch (Exception $e) {
                App::error()->add($e->getMessage());
                self::$qlink_label  = $qlink_label;
                self::$qlink_url    = $qlink_url;
                self::$qlink_status = $qlink_status;
            }
        }

        return true;
    }

    public static function render(): void
    {
        if (!self::status()) {
            return;
        }

        App::backend()->page()->openModule(My::name());

        echo
        App::backend()->page()->breadcrumb(
            [
                Html::escapeHTML(App::blog()->name()) => '',
                My::name()                            => My::manageUrl(),
                __('Edit link')                       => '',
            ]
        ) .
        App::backend()->notices()->getNotices();

        $user_lang = is_string($user_lang = App::auth()->getInfo('user_lang')) ? $user_lang : '';

        echo (new Div())
            ->items([
                (new Form('edit-link-form'))
                    ->method('post')
                    ->action(App::backend()->getPageURL())
                    ->fields([
                        (new Text('h3', __('Edit link'))),
                        (new Note())
                            ->class('form-note')
                            ->text(sprintf(__('Fields preceded by %s are mandatory.'), (new Span('*'))->class('required')->render())),
                        (new Para())->items([
                            (new Label((new Span('*'))->render() . __('Label:')))
                                ->class('required')
                                ->for('qlink_label'),
                            (new Input('qlink_label'))
                                ->size(30)
                                ->maxlength(255)
                                ->value(Html::escapeHTML(self::$qlink_label))
                                ->required(true)
                                ->lang($user_lang)
                                ->spellcheck(true)
                                ->title(__('Required field')),
                        ]),
                        (new Para())->items([
                            (new Label((new Span('*'))->render() . __('URL:')))
                                ->class('required')
                                ->for('qlink_url'),
                            (new Url('qlink_url'))
                                ->size(30)
                                ->maxlength(255)
                                ->value(Html::escapeHTML(self::$qlink_url))
                                ->required(true)
                                ->title(__('Required field')),
                        ]),
                        (new Para())->items([
                            (new Select('qlink_status'))
                                ->items([
                                    __('Online')  => (string) QuickLinks::STATUS_ONLINE,
                                    __('Offline') => (string) QuickLinks::STATUS_OFFLINE,
                                ])
                                ->default((string) self::$qlink_status)
                                ->label(new Label(__('Status:'), Label::OUTSIDE_LABEL_BEFORE)),
                        ]),
                        (new Para())
                            ->class('form-buttons')
                            ->items([
                                (new Hidden(['id'], self::$id)),
                                ...My::hiddenFields(),
                                (new Submit(['save'], __('Save'))),
                            ]),
                    ]),
            ])
        ->render();

        App::backend()->page()->helpBlock(My::id());

        App::backend()->page()->closeModule();
    }
}
