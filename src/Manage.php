<?php
/**
 * @brief httpPassword, a plugin for Dotclear 2
 *
 * @package Dotclear
 * @subpackage Plugin
 *
 * @author Frederic PLE and contributors
 *
 * @copyright Jean-Christian Denis
 * @copyright GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
declare(strict_types=1);

namespace Dotclear\Plugin\httpPassword;

use dcCore;
use dcNsProcess;
use dcPage;
use Dotclear\Helper\Html\Html;
use Dotclear\Helper\Html\Form\{
    Checkbox,
    Div,
    Form,
    Hidden,
    Input,
    Label,
    Note,
    Para,
    Select,
    Submit,
    Text
};
use dt;

/**
 * Manage contributions list
 */
class Manage extends dcNsProcess
{
    public static function init(): bool
    {
        static::$init = defined('DC_CONTEXT_ADMIN') && dcCore::app()->auth->check(
            dcCore::app()->auth->makePermissions([
                My::PERMISSION,
            ]), dcCore::app()->blog->id
        );

        return static::$init;
    }

    public static function process(): bool
    {
        if (!static::$init) {
            return false;
        }

        if (!Utils::isWritable()) {
            dcPage::addWarningNotice(
                __('No write permissions on blogs directories.')
            );
        }

        $part   = self::getSection();
        $action = $_POST['action'] ?? '';
        if (empty($action)) {
            return true;
        }

        if ('savesettings' == $action) {
            $s = dcCore::app()->blog->settings->get(My::id());
            $s->put('active', !empty($_POST['active']));
            $s->put('crypt', in_array((string) $_POST['crypt'], My::cryptCombo()) ? $_POST['crypt'] : 'paintext');
            $s->put('message', (string) $_POST['message']);

            dcCore::app()->blog->triggerBlog();

            dcPage::addSuccessNotice(
                __('Settings successfully updated.')
            );

            dcCore::app()->adminurl->redirect(
                'admin.plugin.' . My::id(),
                ['part' => $part]
            );
        }

        if ('savelogins' == $action) {
            $logs = dcCore::app()->log->getLogs(['log_table' => My::id()]);
            if (!$logs->isEmpty()) {
                $ids = [];
                while ($logs->fetch()) {
                    $ids[] = $logs->__get('log_id');
                }
                $logs = dcCore::app()->log->delLogs($ids);

                dcPage::addSuccessNotice(
                    __('Logs successfully cleared.')
                );

                dcCore::app()->adminurl->redirect(
                    'admin.plugin.' . My::id(),
                    ['part' => $part]
                );
            }
        }

        if ('savepasswords' == $action) {
            $passwords = self::getPasswords();
            $lines     = [];
            if (!empty($_POST['login']) && !empty($_POST['password'])) {
                $lines[$_POST['login']] = Utils::crypt($_POST['password']);
            }
            foreach ($passwords as $l => $p) {
                // add login
                if (array_key_exists($l, $lines)) {
                    continue;
                }
                // delete login
                if (!empty($_POST['delete']) && array_key_exists($l, $_POST['delete'])) {
                    continue;
                }
                // change password
                if (!empty($_POST['edit']) && array_key_exists($l, $_POST['edit'])
                                           && !empty($_POST['newpassword']) && array_key_exists($l, $_POST['newpassword'])
                ) {
                    $lines[$l] = Utils::crypt($_POST['newpassword'][$l]);
                } else {
                    $lines[$l] = $p;
                }
            }

            $contents = '';
            foreach ($lines as $l => $p) {
                $contents .= sprintf("%s:%s\r\n", $l, $p);
            }
            file_put_contents(Utils::passwordFile(), $contents);

            dcCore::app()->blog->triggerBlog();

            dcPage::addSuccessNotice(
                __('Logins successfully updated.')
            );

            dcCore::app()->adminurl->redirect(
                'admin.plugin.' . My::id(),
                ['part' => $part]
            );
        }

        return true;
    }

    public static function render(): void
    {
        if (!static::$init) {
            return;
        }

        $part = self::getSection();

        dcPage::openModule(
            My::name(),
            dcPage::jsPageTabs() .
            dcPage::jsModuleLoad(My::id() . '/js/backend.js')
        );

        echo
        dcPage::breadcrumb([
            __('Plugins')                           => '',
            My::name()                              => dcCore::app()->adminurl->get('admin.plugin.' . My::id()),
            array_search($part, My::sectionCombo()) => '',
        ]) .
        dcPage::notices() .

        # Filters select menu list
        (new Form('section_menu'))->action(dcCore::app()->adminurl->get('admin.plugin.' . My::id()))->method('get')->fields([
            (new Para())->class('anchor-nav')->items([
                (new Label(__('Select section:')))->for('part')->class('classic'),
                (new Select('part'))->default($part)->items(My::sectionCombo()),
                (new Submit(['go']))->value(__('Ok')),
                (new Hidden(['p'], My::id())),
            ]),
        ])->render() .

        '<h3>' . array_search($part, My::sectionCombo()) . '</h3>';

        if ('settings' == $part) {
            echo
            (new Form('section_settings'))->action(dcCore::app()->adminurl->get('admin.plugin.' . My::id(), ['part' => 'settings']))->method('post')->fields([
                // active
                (new Para())->items([
                    (new Checkbox('active', Utils::isActive()))->value(1),
                    (new Label(__('Enable http password protection on this blog'), Label::OUTSIDE_LABEL_AFTER))->for('active')->class('classic'),
                ]),
                // crypt
                (new Para())->items([
                    (new Label(__('Crypt algorithm:'), Label::OUTSIDE_LABEL_BEFORE))->for('crypt')->class('classic'),
                    (new Select('crypt'))->default(Utils::cryptMethod())->items(My::cryptCombo()),
                ]),
                (new Note())->text(__('Some web servers does not surpport plaintext (no) encryption.'))->class('form-note'),
                (new Note())->text(__('If you change crypt algo, you must edit and resave each users passwords.'))->class('form-note'),
                // message
                (new Para())->items([
                    (new Label(__('Authentication message:')))->for('message'),
                    (new Input('message'))->size(60)->maxlenght(255)->value(Utils::httpMessage()),
                ]),
                (new Div())->class('clear')->items([
                    (new Submit(['save']))->value(__('Save')),
                    (new Hidden(['action'], 'savesettings')),
                    (new Hidden(['part'], $part)),
                    (new Text('', dcCore::app()->formNonce())),
                ]),
            ])->render();
        }

        if ('logins' == $part) {
            $logs = dcCore::app()->log->getLogs(['log_table' => My::id()]);
            if ($logs->isEmpty()) {
                echo
                '<p>' . __('Logins history is empty.') . '</p>';
            } else {
                echo
                (new Form('section_logins'))->action(dcCore::app()->adminurl->get('admin.plugin.' . My::id(), ['part' => 'logins']))->method('post')->fields([
                    (new Para())->items([
                        (new Submit(['save']))->value(__('Clear logs')),
                        (new Hidden(['action'], 'savelogins')),
                        (new Hidden(['part'], $part)),
                        (new Text('', dcCore::app()->formNonce())),
                    ]),
                ])->render() .

                '<div class="table-outer"><table>' .
                '<caption>' . sprintf(__('List of %s last logins.'), $logs->count()) . '</caption>' .
                '<thead><tr>' .
                '<th scope="col" class="first">' . __('Login') . '</th>' .
                '<th scope="col">' . __('Date') . '</th>' .
                '</tr></thead<tbody>';

                while ($logs->fetch()) {
                    echo
                    '<tr class="line">' .
                    '<td class="nowrap maximal">' . Html::escapeHTML($logs->f('log_msg')) . '</td>' .
                    '<td class="nowrap count">' . Html::escapeHTML(dt::dt2str(__('%Y-%m-%d %H:%M'), $logs->f('log_dt'))) . '</td>' .
                    '</tr>';
                }

                echo
                '</table></div>';
            }
        }

        if ('passwords' == $part) {
            $passwords = self::getPasswords();

            if (empty($passwords)) {
                echo
                '<p>' . __('Authorized users list is empty.') . '</p>';
            } else {
                $lines = '';
                foreach ($passwords as $login => $pwd) {
                    $lines .= '<tr class="line">' .
                    '<td class="nowrap maximal">' .
                        Html::escapeHTML($login) .
                    '</td>' .
                    '<td class="nowrap">' .
                        (new Input(['newpassword[' . Html::escapeHTML($login) . ']']))->size(60)->maxlenght(255)->render() .
                    '</td>' .
                    '<td class="nowrap">' .
                        (new Submit(['edit[' . Html::escapeHTML($login) . ']']))->value(__('Change password'))->render() .
                        (new Submit(['delete[' . Html::escapeHTML($login) . ']']))->value(__('Delete'))->class('delete')->render() .
                    '</td>' .
                    '</tr>';
                }

                echo
                (new Form('section_passwords'))->action(dcCore::app()->adminurl->get('admin.plugin.' . My::id(), ['part' => $part]))->method('post')->fields([
                    (new Text(
                        '',
                        '<div class="table-outer"><table>' .
                        '<caption>' . sprintf(__('List of %s authorized users.'), count($passwords)) . '</caption>' .
                        '<thead><tr>' .
                        '<th scope="col" class="first nowrap">' . __('Login') . '</th>' .
                        '<th scope="col" class="first nowrap">' . __('New password') . '</th>' .
                        '<th scope="col" class="nowrap">' . __('Action') . '</th>' .
                        '</tr></thead<tbody>' .
                        $lines .
                        '</table></div>'
                    )),
                    (new Para())->items([
                        (new Hidden(['action'], 'savepasswords')),
                        (new Hidden(['part'], $part)),
                        (new Text('', dcCore::app()->formNonce())),
                    ]),
                ])->render();
            }

            echo
            (new Form('section_new'))->action(dcCore::app()->adminurl->get('admin.plugin.' . My::id(), ['part' => $part]))->method('post')->fields([
                (new Text('h3', Html::escapeHTML(__('Add a user')))),
                // login
                (new Para())->items([
                    (new Label(__('Login:')))->for('login'),
                    (new Input('login'))->size(60)->maxlenght(255),
                ]),
                // password
                (new Para())->items([
                    (new Label(__('Password:')))->for('password'),
                    (new Input('password'))->size(60)->maxlenght(255),
                ]),
                (new Para())->items([
                    (new Submit(['add']))->value(__('Save')),
                    (new Hidden(['action'], 'savepasswords')),
                    (new Hidden(['part'], $part)),
                    (new Text('', dcCore::app()->formNonce())),
                ]),
            ])->render();
        }

        dcPage::closeModule();
    }

    private static function getSection(): string
    {
        $part = $_REQUEST['part'] ?? 'settings';
        if (!in_array($part, My::sectionCombo()) || !Utils::isWritable()) {
            $part = 'settings';
        }

        return $part;
    }

    private static function getPasswords(): array
    {
        $passwords = [];
        $lines     = file(Utils::passwordFile());
        if (!is_array($lines)) {
            $lines = [];
        }
        sort($lines);
        foreach ($lines as $line) {
            [$login, $pwd]           = explode(':', $line, 2);
            $passwords[trim($login)] = trim($pwd);
        }
        unset($lines);

        return $passwords;
    }
}
