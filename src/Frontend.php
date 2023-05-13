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
use dcLog;
use dcNsProcess;

class Frontend extends dcNsProcess
{
    public static function init(): bool
    {
        static::$init = defined('DC_RC_PATH');

        return static::$init;
    }

    public static function process(): bool
    {
        if (!static::$init || !Utils::isActive()) {
            return false;
        }

        // check password on frontend
        dcCore::app()->addBehavior('publicPrependV2', function (): void {
            // nullsafe
            if (is_null(dcCore::app()->blog)) {
                return;
            }
            $PHP_AUTH_USER = $PHP_AUTH_PW = '';

            if (isset($_SERVER['PHP_AUTH_USER']) and isset($_SERVER['PHP_AUTH_PW'])) {
                $PHP_AUTH_USER = $_SERVER['PHP_AUTH_USER'];
                $PHP_AUTH_PW   = $_SERVER['PHP_AUTH_PW'];
            } elseif (isset($_ENV['REMOTE_USER'])) {
                [$PHP_AUTH_PW, $PHP_AUTH_USER] = explode(' ', $_ENV['REMOTE_USER'], 2);
                [$PHP_AUTH_USER, $PHP_AUTH_PW] = explode(':', base64_decode($PHP_AUTH_USER));
            }
            if ($PHP_AUTH_PW === '' or $PHP_AUTH_USER === '') {
                Utils::sendHttp401();
            }

            if (!is_file(dcCore::app()->blog->public_path . DIRECTORY_SEPARATOR . My::FILE_PASSWORD)) {
                header('HTTP/1.0 500 Internal Server Error');
                echo 'httpPassword plugin is not well configured.';
                exit(1);
            }

            $htpasswd      = file(dcCore::app()->blog->public_path . DIRECTORY_SEPARATOR . My::FILE_PASSWORD, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $authenticated = false;
            if ($htpasswd !== false) {
                foreach ($htpasswd as $ligne) {
                    [$cur_user, $cur_pass] = explode(':', trim($ligne), 2);
                    if ($cur_user == $PHP_AUTH_USER and crypt($PHP_AUTH_PW, $cur_pass) == $cur_pass) {
                        $authenticated = true;
                    }
                    if ($authenticated) {
                        break;
                    }
                }
            }
            unset($htpasswd);
            if (!$authenticated) {
                Utils::sendHttp401();
            } else {
                $logs = dcCore::app()->log->getLogs(['log_table' => My::id(), 'log_msg' => $PHP_AUTH_USER]);
                if (!$logs->isEmpty()) {
                    $ids = [];
                    while ($logs->fetch()) {
                        $ids[] = is_numeric($logs->f('log_id')) ? (int) $logs->f('log_id') : 0;
                    }
                    $logs = dcCore::app()->log->delLogs($ids);
                }
                $cursor = dcCore::app()->con->openCursor(dcCore::app()->prefix . dcLog::LOG_TABLE_NAME);
                $cursor->setField('log_table', My::id());
                $cursor->setField('log_msg', $PHP_AUTH_USER);
                dcCore::app()->log->addLog($cursor);
            }
        });

        return true;
    }
}
