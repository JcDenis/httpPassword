<?php

declare(strict_types=1);

namespace Dotclear\Plugin\httpPassword;

use Dotclear\App;
use Dotclear\Core\Backend\{
    Notices,
    Page
};
use Dotclear\Core\Process;
use Dotclear\Helper\Date;
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

/**
 * @brief       httpPassword manage class.
 * @ingroup     httpPassword
 *
 * @author      Frederic PLE (author)
 * @author      Jean-Christian Denis (latest)
 * @copyright   GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
class Manage extends Process
{
    public static function init(): bool
    {
        return self::status(My::checkContext(My::MANAGE));
    }

    public static function process(): bool
    {
        if (!self::status() || !App::blog()->isDefined()) {
            return false;
        }

        if (!Utils::isWritable()) {
            Notices::addWarningNotice(
                __('No write permissions on blogs directories.')
            );
        }

        $part   = self::getSection();
        $action = $_POST['action'] ?? '';
        if (empty($action)) {
            return true;
        }

        // save settings
        if ('savesettings' == $action) {
            $s = My::settings();
            $s->put('active', !empty($_POST['active']));
            $s->put('crypt', in_array((string) $_POST['crypt'], My::cryptCombo()) ? $_POST['crypt'] : 'paintext');
            $s->put('message', (string) $_POST['message']);

            App::blog()->triggerBlog();

            Notices::addSuccessNotice(
                __('Settings successfully updated.')
            );

            My::redirect(['part' => $part]);
        }

        // delete users logins
        if ('savelogins' == $action) {
            $logs = App::log()->getLogs(['log_table' => My::id()]);
            if (!$logs->isEmpty()) {
                $ids = [];
                while ($logs->fetch()) {
                    $ids[] = $logs->__get('log_id');
                }
                $logs = App::log()->delLogs($ids);

                Notices::addSuccessNotice(
                    __('Logs successfully cleared.')
                );

                My::redirect(['part' => $part]);
            }
        }

        // save users logins / passwords in frontend passwords file
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

            App::blog()->triggerBlog();

            Notices::addSuccessNotice(
                __('Logins successfully updated.')
            );

            My::redirect(['part' => $part]);
        }

        return true;
    }

    public static function render(): void
    {
        if (!self::status() || !App::blog()->isDefined()) {
            return;
        }

        $part = self::getSection();

        Page::openModule(
            My::name(),
            Page::jsPageTabs() .
            My::jsLoad('backend')
        );

        echo
        Page::breadcrumb([
            __('Plugins')                           => '',
            My::name()                              => My::manageUrl(),
            array_search($part, My::sectionCombo()) => '',
        ]) .
        Notices::getNotices() .

        // Filters select menu list
        (new Form('section_menu'))->action(My::manageUrl())->method('get')->fields([
            (new Para())->class('anchor-nav')->items([
                (new Label(__('Select section:')))->for('part')->class('classic'),
                (new Select('part'))->default($part)->items(My::sectionCombo()),
                (new Submit(['go']))->value(__('Ok')),
                ... My::hiddenFields(),
            ]),
        ])->render() .

        '<h3>' . array_search($part, My::sectionCombo()) . '</h3>';

        // settigns form
        if ('settings' == $part) {
            echo
            (new Form('section_settings'))->action(My::manageUrl())->method('post')->fields([
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
                    ... My::hiddenFields(['part' => $part]),
                ]),
            ])->render();
        }

        // delete logins form
        if ('logins' == $part) {
            $logs = App::log()->getLogs(['log_table' => My::id()]);
            if ($logs->isEmpty()) {
                echo
                '<p>' . __('Logins history is empty.') . '</p>';
            } else {
                echo
                (new Form('section_logins'))->action(My::manageUrl())->method('post')->fields([
                    (new Para())->items([
                        (new Submit(['save']))->value(__('Clear logs')),
                        ... My::hiddenFields([
                            'action' => 'savelogins',
                            'part'   => $part,
                        ]),
                    ]),
                ])->render() .

                '<div class="table-outer"><table>' .
                '<caption>' . sprintf(__('List of %s last logins.'), $logs->count()) . '</caption>' .
                '<thead><tr>' .
                '<th scope="col" class="first">' . __('Login') . '</th>' .
                '<th scope="col">' . __('Date') . '</th>' .
                '</tr></thead<tbody>';

                while ($logs->fetch()) {
                    $msg = is_string($logs->f('log_msg')) ? $logs->f('log_msg') : '';
                    $dt  = is_string($logs->f('log_dt')) ? $logs->f('log_dt') : '';
                    echo
                    '<tr class="line">' .
                    '<td class="nowrap maximal">' . Html::escapeHTML($msg) . '</td>' .
                    '<td class="nowrap count">' . Html::escapeHTML(Date::dt2str(__('%Y-%m-%d %H:%M'), $dt)) . '</td>' .
                    '</tr>';
                }

                echo
                '</table></div>';
            }
        }

        // existing logins/passwords form
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
                (new Form('section_passwords'))->action(My::manageUrl())->method('post')->fields([
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
                        ... My::hiddenFields(['part' => $part]),
                    ]),
                ])->render();
            }

            // new login form
            echo
            (new Form('section_new'))->action(My::manageUrl())->method('post')->fields([
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
                    ... My::hiddenFields([
                        'action' => 'savepasswords',
                        'part'   => $part,
                    ]),
                ]),
            ])->render();
        }

        Page::closeModule();
    }

    /**
     * Get page section.
     *
     * @return  string  The section
     */
    private static function getSection(): string
    {
        $part = $_REQUEST['part'] ?? 'settings';
        if (!in_array($part, My::sectionCombo()) || !Utils::isWritable()) {
            $part = 'settings';
        }

        return $part;
    }

    /**
     * Get existing passwords from file.
     *
     * @return  array<string,string>    The passwords list
     */
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
