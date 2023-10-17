<?php

declare(strict_types=1);

namespace Dotclear\Plugin\httpPassword;

use Dotclear\Module\MyPlugin;

/**
 * @brief       httpPassword My helper.
 * @ingroup     httpPassword
 *
 * @author      Frederic PLE (author)
 * @author      Jean-Christian Denis (latest)
 * @copyright   GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
class My extends MyPlugin
{
    /**
     * Passwords file name.
     *
     * @var     string  FILE_PASSWORD
     */
    public const FILE_PASSWORD = '.htpasswd';

    /**
     * Encryption methods combo.
     *
     * @return  array<string,string>
     */
    public static function cryptCombo(): array
    {
        return [
            __('No encryption')      => 'plaintext',
            __('Crypt DES standard') => 'crypt_std_des',
            __('Crypt DES étendu')   => 'crypt_ext_des',
            __('Crypt MD5')          => 'crypt_md5',
            __('Crypt Blowfish')     => 'crypt_blowfish',
            __('Crypt SHA256')       => 'crypt_sha256',
            __('Crypt SHA512')       => 'crypt_sha512',
        ];
    }

    /**
     * Admin section menu.
     *
     * @return  array<string,string>
     */
    public static function sectionCombo(): array
    {
        return [
            __('Settings')         => 'settings',
            __('Logins history')   => 'logins',
            __('Authorized users') => 'passwords',
        ];
    }
}
