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
if (!defined('DC_CONTEXT_ADMIN')) {
    return null;
}

$s            = dcCore::app()->blog->settings->get(basename(__DIR__));
$pwd_file     = dcCore::app()->blog->public_path . DIRECTORY_SEPARATOR . initHttpPassword::FILE_PASSWORD;
$action       = $_POST['action']   ?? '';
$redir        = $_REQUEST['redir'] ?? '';
$part         = $_REQUEST['part']  ?? 'settings';
$passwords    = [];
$writable     = httpPassword::isWritable();
$section_menu = [
    __('Settings')         => 'settings',
    __('Logins history')   => 'logins',
    __('Authorized users') => 'passwords',
];

if (!in_array($part, $section_menu) || !$writable) {
    $part = 'settings';
}
if (empty($redir)) {
    $redir = dcCore::app()->adminurl->get('admin.plugin.' . basename(__DIR__), ['part' => $part]);
}
if (!$writable) {
    dcAdminNotices::addWarningNotice(
        __('No write permissions on blogs directories.')
    );
}

if ('passwords' == $part) {
    $lines = file($pwd_file);
    if (!is_array($lines)) {
        $lines = [];
    }
    sort($lines);
    foreach ($lines as $line) {
        [$login, $pwd]           = explode(':', $line, 2);
        $passwords[trim($login)] = trim($pwd);
    }
    unset($lines);
}

if ('savesettings' == $action) {
    $s->put('active', !empty($_POST['active']));
    $s->put('crypt', in_array((string) $_POST['crypt'], httpPassword::getCryptCombo()) ? $_POST['crypt'] : 'paintext');
    $s->put('message', (string) $_POST['message']);

    dcCore::app()->blog->triggerBlog();

    dcAdminNotices::addSuccessNotice(
        __('Settings successfully updated.')
    );

    dcCore::app()->adminurl->redirect(
        'admin.plugin.' . basename(__DIR__),
        ['part' => $part]
    );
}

if ('savelogins' == $action) {
    $logs = dcCore::app()->log->getLogs(['log_table' => basename(__DIR__)]);
    if (!$logs->isEmpty()) {
        $ids = [];
        while ($logs->fetch()) {
            $ids[] = $logs->__get('log_id');
        }
        $logs = dcCore::app()->log->delLogs($ids);

        dcAdminNotices::addSuccessNotice(
            __('Logs successfully cleared.')
        );

        dcCore::app()->adminurl->redirect(
            'admin.plugin.' . basename(__DIR__),
            ['part' => $part]
        );
    }
}

if ('savepasswords' == $action) {
    $lines = [];
    if (!empty($_POST['login']) && !empty($_POST['password'])) {
        $lines[$_POST['login']] = httpPassword::crypt($_POST['password']);
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
            $lines[$l] = httpPassword::crypt($_POST['newpassword'][$l]);
        } else {
            $lines[$l] = $p;
        }
    }

    $contents = '';
    foreach ($lines as $l => $p) {
        $contents .= sprintf("%s:%s\r\n", $l, $p);
    }
    file_put_contents($pwd_file, $contents);

    dcCore::app()->blog->triggerBlog();

    dcAdminNotices::addSuccessNotice(
        __('Logins successfully updated.')
    );

    dcCore::app()->adminurl->redirect(
        'admin.plugin.' . basename(__DIR__),
        ['part' => $part]
    );
}

echo
'<html><head><title>' . __('Http password') . '</title>' .
dcPage::jsPageTabs() .
dcPage::jsModuleLoad(basename(__DIR__) . '/js/index.js') .
'</head><body>' .
dcPage::breadcrumb([
    __('Plugins')                      => '',
    __('Http password')                => dcCore::app()->adminurl->get('admin.plugin.' . basename(__DIR__)),
    array_search($part, $section_menu) => '',
]) .
dcPage::notices() .

# Filters select menu list
'<form method="get" action="' . dcCore::app()->adminurl->get('admin.plugin.' . basename(__DIR__)) . '" id="section_menu">' .
'<p class="anchor-nav"><label for="part" class="classic">' . __('Select section:') . ' </label>' .
form::combo('part', $section_menu, $part) . ' ' .
'<input type="submit" value="' . __('Ok') . '" />' .
form::hidden('p', basename(__DIR__)) . '</p>' .
'</form>' .
'<h3>' . array_search($part, $section_menu) . '</h3>';

if ('settings' == $part) {
    echo '
	<form method="post" action="' . dcCore::app()->adminurl->get('admin.plugin.' . basename(__DIR__), ['part' => 'settings']) . '">

	<p><label for="active">' .
    form::checkbox('active', '1', (bool) $s->get('active')) .
    __('Enable http password protection on this blog') . '</label></p>

    <p><label for="crypt">' . __('Crypt algorithm:') . '</label> ' .
    form::combo('crypt', httpPassword::getCryptCombo(), (string) $s->get('crypt')) . '</p>
	<p class="form-note">' .
        __('Some web servers does not surpport plaintext (no) encryption.') . ' ' .
        __('If you change crypt algo, you must edit and resave each users passwords.') .
    '</p>

	<p><label for="message">' . __('Authentication message:') . '</label>' .
    form::field('message', 60, 255, html::escapeHTML((string) $s->get('message'))) . '
	</p>

	<div class="clear">
	<p>' .
    dcCore::app()->formNonce() .
    form::hidden(['action'], 'savesettings') .
    form::hidden(['part'], $part) . '
	<input type="submit" name="save" value="' . __('Save') . '" />
	</p></form>';
}

if ('logins' == $part) {
    $logs = dcCore::app()->log->getLogs(['log_table' => basename(__DIR__)]);
    if ($logs->isEmpty()) {
        echo
        '<p>' . __('Logins history is empty.') . '</p>';
    } else {
        echo '
		<form method="post" action="' . dcCore::app()->adminurl->get('admin.plugin.' . basename(__DIR__), ['part' => 'logins']) . '">
		<p>' .
        dcCore::app()->formNonce() .
        form::hidden(['action'], 'savelogins') .
        form::hidden(['part'], $part) . '
		<input type="submit" name="save" value="' . __('Clear logs') . '" />
		</p></form>' .

        '<div class="table-outer"><table>' .
        '<caption>' . sprintf(__('List of %s last logins.'), $logs->count()) . '</caption>' .
        '<thead><tr>' .
        '<th scope="col" class="first">' . __('Login') . '</th>' .
        '<th scope="col">' . __('Date') . '</th>' .
        '</tr></thead<tbody>';

        while ($logs->fetch()) {
            echo
            '<tr class="line">' .
            '<td class="nowrap maximal">' . html::escapeHTML($logs->__get('log_msg')) . '</td>' .
            '<td class="nowrap count">' . html::escapeHTML(dt::dt2str(__('%Y-%m-%d %H:%M'), $logs->__get('log_dt'))) . '</td>' .
            '</tr>';
        }

        echo
        '</table></div>';
    }
}

if ('passwords' == $part) {
    if (empty($passwords)) {
        echo
        '<p>' . __('Authorized users list is empty.') . '</p>';
    } else {
        echo
        '<form method="post" action="' . dcCore::app()->adminurl->get('admin.plugin.' . basename(__DIR__), ['part' => $part]) . '">' .
        '<div class="table-outer"><table>' .
        '<caption>' . sprintf(__('List of %s authorized users.'), count($passwords)) . '</caption>' .
        '<thead><tr>' .
        '<th scope="col" class="first nowrap">' . __('Login') . '</th>' .
        '<th scope="col" class="first nowrap">' . __('New password') . '</th>' .
        '<th scope="col" class="nowrap">' . __('Action') . '</th>' .
        '</tr></thead<tbody>';

        foreach ($passwords as $login => $pwd) {
            echo
            '<tr class="line">' .
            '<td class="nowrap maximal">' .
                html::escapeHTML($login) .
            '</td>' .
            '<td class="nowrap">' .
                form::field(['newpassword[' . html::escapeHTML($login) . ']'], 60, 255, '') .
            '</td>' .
            '<td class="nowrap">' .
                '<input type="submit" name="edit[' . html::escapeHTML($login) . ']" value="' . __('Change password') . '" /> ' .
                '<input type="submit" class="delete" name="delete[' . html::escapeHTML($login) . ']" value="' . __('Delete') . '" />' .
            '</td>' .
            '</tr>';
        }

        echo
        '</table></div>
		<p>' .
        dcCore::app()->formNonce() .
        form::hidden(['action'], 'savepasswords') .
        form::hidden(['part'], $part) . '
		</p></form>';
    }

    echo '
	<form method="post" action="' . dcCore::app()->adminurl->get('admin.plugin.' . basename(__DIR__), ['part' => $part]) . '">
	<h3>' . __('Add a user') . '</h3>

	<p><label for="login">' . __('Login:') . '</label>' .
    form::field('login', 60, 255, '') . '
	</p>

	<p><label for="password">' . __('Password:') . '</label>' .
    form::field('password', 60, 255, '') . '
	</p>

	<p>' .
    dcCore::app()->formNonce() .
    form::hidden(['action'], 'savepasswords') .
    form::hidden(['part'], $part) . '
	<input type="submit" name="add" value="' . __('Save') . '" />
	</p></form>';
}

echo
'</body></html>';
