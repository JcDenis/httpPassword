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
if (!dcCore::app()->blog->settings->get(basename(__DIR__))->get('active')) {
    return null;
}

dcCore::app()->addBehavior('publicPrependV2', function (): void {
    $PHP_AUTH_USER = $PHP_AUTH_PW = '';

    if (isset($_SERVER['PHP_AUTH_USER']) and isset($_SERVER['PHP_AUTH_PW'])) {
        $PHP_AUTH_USER = $_SERVER['PHP_AUTH_USER'];
        $PHP_AUTH_PW   = $_SERVER['PHP_AUTH_PW'];
    } elseif (isset($_ENV['REMOTE_USER'])) {
        [$PHP_AUTH_PW, $PHP_AUTH_USER] = explode(' ', $_ENV['REMOTE_USER'], 2);
        [$PHP_AUTH_USER, $PHP_AUTH_PW] = explode(':', base64_decode($PHP_AUTH_USER));
    }
    if ($PHP_AUTH_PW === '' or $PHP_AUTH_USER === '') {
        httpPassword::sendHttp401();
    }

    if (!is_file(dcCore::app()->blog->public_path . DIRECTORY_SEPARATOR . initHttpPassword::FILE_PASSWORD)) {
        header('HTTP/1.0 500 Internal Server Error');
        echo 'httpPassword plugin is not well configured.';
        exit(1);
    }

    $htpasswd      = file(dcCore::app()->blog->public_path . DIRECTORY_SEPARATOR . initHttpPassword::FILE_PASSWORD, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $authenticated = false;
    foreach ($htpasswd as $ligne) {
        [$cur_user, $cur_pass] = explode(':', trim($ligne), 2);
        if ($cur_user == $PHP_AUTH_USER and crypt($PHP_AUTH_PW, $cur_pass) == $cur_pass) {
            $authenticated = true;
        }
        if ($authenticated) {
            break;
        }
    }
    unset($htpasswd);
    if (!$authenticated) {
        httpPassword::sendHttp401();
    } else {
        $logs = dcCore::app()->log->getLogs(['log_table' => basename(__DIR__), 'log_msg' => $PHP_AUTH_USER]);
        if (!$logs->isEmpty()) {
            $ids = [];
            while ($logs->fetch()) {
                $ids[] = $logs->__get('log_id');
            }
            $logs = dcCore::app()->log->delLogs($ids);
        }
        $cursor = dcCore::app()->con->openCursor(dcCore::app()->prefix . dcLog::LOG_TABLE_NAME);
        $cursor->__set('log_table', basename(__DIR__));
        $cursor->__set('log_msg', $PHP_AUTH_USER);
        dcCore::app()->log->addLog($cursor);
    }
});
