<?php

declare(strict_types=1);

namespace Dotclear\Plugin\httpPassword;

use Dotclear\App;
use Dotclear\Helper\Process\TraitProcess;

/**
 * @brief       httpPassword frontend class.
 * @ingroup     httpPassword
 *
 * @author      Frederic PLE (author)
 * @author      Jean-Christian Denis (latest)
 * @copyright   GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
class Frontend
{
    use TraitProcess;

    public static function init(): bool
    {
        return self::status(My::checkContext(My::FRONTEND));
    }

    public static function process(): bool
    {
        if (!self::status() || !Utils::isActive()) {
            return false;
        }

        // check password on frontend
        App::behavior()->addBehavior('publicPrependV2', function (): void {
            if (!App::blog()->isDefined()) {
                return;
            }
            $PHP_AUTH_USER = $PHP_AUTH_PW = '';

            if (isset($_SERVER['PHP_AUTH_USER']) and isset($_SERVER['PHP_AUTH_PW'])) {
                $PHP_AUTH_USER = $_SERVER['PHP_AUTH_USER'];
                $PHP_AUTH_PW   = $_SERVER['PHP_AUTH_PW'];
            } elseif (isset($_ENV['REMOTE_USER'])) {
                [$PHP_AUTH_PW, $PHP_AUTH_USER] = explode(' ', $_ENV['REMOTE_USER'], 2);
                [$PHP_AUTH_USER, $PHP_AUTH_PW] = explode(':', base64_decode((string) $PHP_AUTH_USER));
            }
            if ($PHP_AUTH_PW === '' or $PHP_AUTH_USER === '') {
                Utils::sendHttp401();
            }

            if (!is_file(App::blog()->publicPath() . DIRECTORY_SEPARATOR . My::FILE_PASSWORD)) {
                header('HTTP/1.0 500 Internal Server Error');
                echo 'httpPassword plugin is not well configured.';
                exit(1);
            }

            $htpasswd      = file(App::blog()->publicPath() . DIRECTORY_SEPARATOR . My::FILE_PASSWORD, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
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
                $logs = App::log()->getLogs(['log_table' => My::id(), 'log_msg' => $PHP_AUTH_USER]);
                if (!$logs->isEmpty()) {
                    $ids = [];
                    while ($logs->fetch()) {
                        $ids[] = is_numeric($logs->f('log_id')) ? (int) $logs->f('log_id') : 0;
                    }
                    App::log()->delLogs($ids);
                }
                $cursor = App::log()->openLogCursor();
                $cursor->setField('log_table', My::id());
                $cursor->setField('log_msg', $PHP_AUTH_USER);
                App::log()->addLog($cursor);
            }
        });

        return true;
    }
}
