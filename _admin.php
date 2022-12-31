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
if (!defined('DC_CONTEXT_ADMIN')) {
    return null;
}

dcCore::app()->menu[dcAdmin::MENU_PLUGINS]->addItem(
    __('Http password'),
    dcCore::app()->adminurl->get('admin.plugin.' . basename(__DIR__)),
    urldecode(dcPage::getPF(basename(__DIR__) . '/icon.png')),
    preg_match('/' . preg_quote(dcCore::app()->adminurl->get('admin.plugin.' . basename(__DIR__))) . '(&.*)?$/', $_SERVER['REQUEST_URI']),
    dcCore::app()->auth->check(dcCore::app()->auth->makePermissions([
        dcAuth::PERMISSION_USAGE,
        initHttpPassword::PERMISSION,
    ]), dcCore::app()->blog->id)
);
