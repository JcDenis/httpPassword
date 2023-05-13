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

class Utils
{
    /**
     * Crypt password
     *
     * @param   string  $secret     The secret
     *
     * @return  string  The crypt password (empty on error)
     */
    public static function crypt(?string $secret): string
    {
        $secret = (string) $secret;

        switch (self::cryptMethod()) {
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

        return $secret;
    }

    /**
     * Setting: active
     *
     * @return  bool    True if module is active
     */
    public static function isActive(): bool
    {
        return !is_null(dcCore::app()->blog) && (bool) dcCore::app()->blog->settings->get(My::id())->get('active');
    }

    /**
     * Setting: crypt
     *
     * @return  string  The crypt method
     */
    public static function cryptMethod(): string
    {
        return !is_null(dcCore::app()->blog) && is_string(dcCore::app()->blog->settings->get(My::id())->get('crypt')) ? dcCore::app()->blog->settings->get(My::id())->get('crypt') : '';
    }

    /**
     * Setting: message
     *
     * @return  string  The frontend message
     */
    public static function httpMessage(): string
    {
        return !is_null(dcCore::app()->blog) && is_string(dcCore::app()->blog->settings->get(My::id())->get('message')) ? dcCore::app()->blog->settings->get(My::id())->get('message') : '';
    }

    /**
     * Get passwords file path
     *
     * @return  string  The passwords file path (empty on error)
     */
    public static function passwordFile(): string
    {
        return is_null(dcCore::app()->blog) ? '' : dcCore::app()->blog->public_path . DIRECTORY_SEPARATOR . My::FILE_PASSWORD;
    }

    /**
     * Check passwords file
     *
     * @return  bool    True if passwords file is writable
     */
    public static function isWritable(): bool
    {
        if (false === ($fp = fopen(self::passwordFile(), 'a+'))) {
            return false;
        }
        fclose($fp);

        return true;
    }

    /**
     * Send HTTP message
     */
    public static function sendHttp401(): void
    {
        header('HTTP/1.1 401 Unauthorized');
        header('WWW-Authenticate: Basic realm="' . utf8_decode(htmlspecialchars_decode(self::httpMessage())) . '"');
        exit(0);
    }
}
