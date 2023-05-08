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

/**
 * This module definitions.
 */
class My
{
    /** @var    string  This plugin permissions */
    public const PERMISSION = 'httpPassword';

    /** @var    string  Passwords file name */
    public const FILE_PASSWORD = '.htpasswd';

    /** @var    string  This module required php version */
    public const PHP_MIN = '7.4';

    /**
     * This module id.
     */
    public static function id(): string
    {
        return basename(dirname(__DIR__));
    }

    /**
     * This module name.
     */
    public static function name(): string
    {
        $name = dcCore::app()->plugins->moduleInfo(self::id(), 'name');

        return __(is_string($name) ? $name : self::id());
    }

    /**
     * This module path.
     */
    public static function path(): string
    {
        return dirname(__DIR__);
    }

    /**
     * Check this module PHP version compliant.
     */
    public static function phpCompliant(): bool
    {
        return version_compare(phpversion(), self::PHP_MIN, '>=');
    }

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
