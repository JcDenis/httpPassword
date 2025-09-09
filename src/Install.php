<?php

declare(strict_types=1);

namespace Dotclear\Plugin\httpPassword;

use Dotclear\App;
use Dotclear\Helper\Process\TraitProcess;
use Exception;

/**
 * @brief       httpPassword install class.
 * @ingroup     httpPassword
 *
 * @author      Frederic PLE (author)
 * @author      Jean-Christian Denis (latest)
 * @copyright   GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
class Install
{
    use TraitProcess;

    public static function init(): bool
    {
        return self::status(My::checkContext(My::INSTALL));
    }

    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        try {
            // Set settings
            $s = My::settings();
            $s->put('active', false, 'boolean', 'Enable plugin', false, false);
            $s->put('crypt', 'crypt_md5', 'string', 'Crypt algorithm', false, false);
            $s->put('message', 'Private space', 'String', 'Personalized message on Authentication popup', false, false);

            return true;
        } catch (Exception $e) {
            App::error()->add($e->getMessage());
        }

        return true;
    }
}
