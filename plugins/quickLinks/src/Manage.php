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
use Dotclear\Helper\Html\Form\Link;
use Dotclear\Helper\Html\Form\Note;
use Dotclear\Helper\Html\Form\Para;
use Dotclear\Helper\Html\Form\Select;
use Dotclear\Helper\Html\Form\Span;
use Dotclear\Helper\Html\Form\Submit;
use Dotclear\Helper\Html\Form\Table;
use Dotclear\Helper\Html\Form\Tbody;
use Dotclear\Helper\Html\Form\Td;
use Dotclear\Helper\Html\Form\Text;
use Dotclear\Helper\Html\Form\Th;
use Dotclear\Helper\Html\Form\Thead;
use Dotclear\Helper\Html\Form\Tr;
use Dotclear\Helper\Html\Form\Url;
use Dotclear\Helper\Html\Html;
use Dotclear\Helper\Process\TraitProcess;
use Exception;

/**
 * @brief   The module manage quickLinks process.
 * @ingroup quickLinks
 */
class Manage
{
    use TraitProcess;

    protected static string $default_tab = '';

    protected static string $qlink_label = '';

    protected static string $qlink_url = '';

    protected static int $qlink_status = QuickLinks::STATUS_ONLINE;

    private static bool $edit = false;

    public static function init(): bool
    {
        if (self::status(My::checkContext(My::MANAGE))) {
            self::$edit = !empty($_REQUEST['edit']) && !empty($_REQUEST['id']) && ManageEdit::init();
        }

        return self::status();
    }

    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        if (self::$edit) {
            return ManageEdit::process();
        }

        $quicklinks = new QuickLinks(App::blog());

        // Add a quick link
        if (!empty($_POST['add_link'])) {
            $qlink_label  = is_string($qlink_label = $_POST['qlink_label'] ?? '') ? $qlink_label : '';
            $qlink_url    = is_string($qlink_url = $_POST['qlink_url'] ?? '') ? $qlink_url : '';
            $qlink_status = is_numeric($qlink_status = $_POST['qlink_status'] ?? '') ? (int) $qlink_status : QuickLinks::STATUS_ONLINE;

            try {
                $quicklinks->addLink($qlink_label, $qlink_url, $qlink_status);

                App::backend()->notices()->addSuccessNotice(__('Link has been successfully created.'));
                My::redirect();
            } catch (Exception $e) {
                App::error()->add($e->getMessage());
                self::$default_tab  = 'add-link';
                self::$qlink_label  = $qlink_label;
                self::$qlink_url    = $qlink_url;
                self::$qlink_status = $qlink_status;
            }
        }

        // Delete a quick link
        if (!empty($_POST['delete']) && !empty($_POST['qlink_id'])) {
            $id = is_numeric($_POST['qlink_id']) ? (string) $_POST['qlink_id'] : '';

            try {
                $quicklinks->delLink($id);
                App::backend()->notices()->addSuccessNotice(__('Link has been successfully deleted.'));
            } catch (Exception $e) {
                App::error()->add($e->getMessage());
            }

            My::redirect();
        }

        return true;
    }

    public static function render(): void
    {
        if (!self::status()) {
            return;
        }

        if (self::$edit) {
            ManageEdit::render();

            return;
        }

        $quicklinks = new QuickLinks(App::blog());

        App::backend()->page()->openModule(
            My::name(),
            App::backend()->page()->jsPageTabs(self::$default_tab)
        );

        echo
        App::backend()->page()->breadcrumb(
            [
                Html::escapeHTML(App::blog()->name()) => '',
                My::name()                            => '',
            ]
        ) .
        App::backend()->notices()->getNotices();

        $rs = null;

        try {
            $rs = $quicklinks->getLinks();
        } catch (Exception $exception) {
            App::error()->add($exception->getMessage());
        }

        if ($rs instanceof MetaRecord && !$rs->isEmpty()) {
            $rows = [];
            while ($rs->fetch()) {
                $qlink_id     = $rs->intField('qlink_id');
                $qlink_label  = $rs->strField('qlink_label');
                $qlink_url    = $rs->strField('qlink_url');
                $qlink_status = $rs->intField('qlink_status');

                $rows[] = (new Tr('l_' . $qlink_id))
                    ->class('line')
                    ->cols([
                        (new Td())->items([
                            (new Link())
                                ->href(App::backend()->getPageURL() . '&amp;edit=1&amp;id=' . $qlink_id)
                                ->text(Html::escapeHTML($qlink_label)),
                        ]),
                        (new Td())->items([
                            (new Text(null, Html::escapeHTML($qlink_url))),
                        ]),
                        (new Td())
                            ->class('nowrap')
                            ->text($qlink_status === QuickLinks::STATUS_ONLINE ? __('Online') : __('Offline')),
                        (new Td())
                            ->class(['nowrap', 'action'])
                            ->items([
                                (new Form(['delete-link-' . $qlink_id]))
                                    ->method('post')
                                    ->action(App::backend()->getPageURL())
                                    ->fields([
                                        (new Hidden(['qlink_id'], (string) $qlink_id)),
                                        ...My::hiddenFields(),
                                        (new Submit(['delete'], __('Delete')))
                                            ->class(['delete', 'reset'])
                                            ->extra('onclick="return window.confirm(\'' . Html::escapeJS(__('Are you sure you want to delete this link?')) . '\');"'),
                                    ]),
                            ]),
                    ]);
            }

            $table = (new Div())
                ->class('table-outer')
                ->items([
                    (new Table())
                        ->items([
                            (new Thead())->rows([
                                (new Tr())->cols([
                                    (new Th())->text(__('Label')),
                                    (new Th())->text(__('URL')),
                                    (new Th())->text(__('Status')),
                                    (new Th())->text(''),
                                ]),
                            ]),
                            (new Tbody('quicklinks-list'))->rows($rows),
                        ]),
                ]);
        } else {
            $table = (new Para())->items([
                (new Text(null, __('No quick link so far.'))),
            ]);
        }

        echo (new Div('main-list'))
            ->class('multi-part')
            ->title(My::name())
            ->items([$table])
        ->render();

        $user_lang = is_string($user_lang = App::auth()->getInfo('user_lang')) ? $user_lang : '';

        echo (new Div('add-link'))
            ->class('multi-part')
            ->title(__('Add a quick link'))
            ->items([
                (new Form('add-link-form'))
                    ->method('post')
                    ->action(App::backend()->getPageURL())
                    ->fields([
                        (new Text('h3', __('Add a new quick link'))),
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
                                ...My::hiddenFields(),
                                (new Submit(['add_link'], __('Save'))),
                            ]),
                    ]),
            ])
        ->render();

        App::backend()->page()->helpBlock(My::id());

        App::backend()->page()->closeModule();
    }
}
