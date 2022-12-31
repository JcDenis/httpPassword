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
if (!defined('DC_RC_PATH')) {
    return null;
}

class httpPassword
{
    public static function id(): string
    {
        return basename(dirname(__DIR__));
    }

    public static function crypt(?string $secret): string
    {
        switch (dcCore::app()->blog->settings->get(self::id())->get('crypt')) {
            case 'plaintext':
                $saltlen = -1;
                $salt    = '';

                break;
            case 'crypt_std_des':
                $saltlen = 2;
                $salt    = '';

                break;
            case 'crypt_ext_des':
                $saltlen = 9;
                $salt    = '';

                break;
            case 'crypt_md5':
                $saltlen = 12;
                $salt    = '$1$';

                break;
            case 'crypt_blowfish':
                $saltlen = 16;
                $salt    = '$2$';

                break;
            case 'crypt_sha256':
                $saltlen = 16;
                $salt    = '$5$';

                break;
            case 'crypt_sha512':
                $saltlen = 16;
                $salt    = '$6$';

                break;
            default:
                return '';
        }

        if ($saltlen > 0) {
            $salt .= substr(
                sha1(dcCore::app()->getNonce() . date('U')),
                2,
                $saltlen - strlen($salt)
            );
            $secret = crypt($secret, $salt);
        }

        return($secret);
    }

    public static function isWritable(): bool
    {
        if (false === ($fp = fopen(dcCore::app()->blog->public_path . DIRECTORY_SEPARATOR . initHttpPassword::FILE_PASSWORD, 'a+'))) {
            return false;
        }
        fclose($fp);

        return true;
    }

    public static function getCryptCombo(): array
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

    public static function sendHttp401(): void
    {
        header('HTTP/1.1 401 Unauthorized');
        header('WWW-Authenticate: Basic realm="' . utf8_decode(htmlspecialchars_decode(dcCore::app()->blog->settings->get(self::id())->get('message'))) . '"');
        exit(0);
    }
}
