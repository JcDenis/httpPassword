<?php

declare(strict_types=1);

namespace Dotclear\Plugin\httpPassword;

use Dotclear\App;

/**
 * @brief       httpPassword utils.
 * @ingroup     httpPassword
 *
 * @author      Frederic PLE (author)
 * @author      Jean-Christian Denis (latest)
 * @copyright   GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
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
                sha1(App::nonce()->getNonce() . date('U')),
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
        return (bool) My::settings()->get('active');
    }

    /**
     * Setting: crypt
     *
     * @return  string  The crypt method
     */
    public static function cryptMethod(): string
    {
        return is_string(My::settings()->get('crypt')) ? My::settings()->get('crypt') : '';
    }

    /**
     * Setting: message
     *
     * @return  string  The frontend message
     */
    public static function httpMessage(): string
    {
        return is_string(My::settings()->get('message')) ? My::settings()->get('message') : '';
    }

    /**
     * Get passwords file path
     *
     * @return  string  The passwords file path (empty on error)
     */
    public static function passwordFile(): string
    {
        return App::blog()->isDefined() ? App::blog()->publicPath() . DIRECTORY_SEPARATOR . My::FILE_PASSWORD : '';
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
