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

use dcAdmin;
use dcCore;
use dcPage;
use dcMenu;
use dcNsProcess;

class Backend extends dcNsProcess
{
    public static function init(): bool
    {
        static::$init = defined('DC_CONTEXT_ADMIN');

        return static::$init;
    }

    public static function process(): bool
    {
        if (!static::$init || is_null(dcCore::app()->auth) || is_null(dcCore::app()->blog) || is_null(dcCore::app()->adminurl)) {
            return false;
        }

        // add backend sidebar menu icon
        if ((dcCore::app()->menu[dcAdmin::MENU_PLUGINS] instanceof dcMenu)) {
            dcCore::app()->menu[dcAdmin::MENU_PLUGINS]->addItem(
                My::name(),
                dcCore::app()->adminurl->get('admin.plugin.' . My::id()),
                dcPage::getPF(My::id() . '/icon.svg'),
                preg_match('/' . preg_quote(dcCore::app()->adminurl->get('admin.plugin.' . My::id())) . '(&.*)?$/', $_SERVER['REQUEST_URI']),
                dcCore::app()->auth->check(dcCore::app()->auth->makePermissions([
                    My::PERMISSION,
                ]), dcCore::app()->blog->id)
            );
        }

        return true;
    }
}
