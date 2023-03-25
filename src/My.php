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

class My
{
    /** @var string This plugin permissions */
    public const PERMISSION = 'httpPassword';

    /** @var string Passwords file name */
    public const FILE_PASSWORD = '.htpasswd';

    /**
     * This module id
     */
    public static function id(): string
    {
        return basename(dirname(__DIR__));
    }

    /**
     * This module name
     */
    public static function name(): string
    {
        return __((string) dcCore::app()->plugins->moduleInfo(self::id(), 'name'));
    }

    /**
     * Encryption methods combo
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
     * Admin section menu
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
