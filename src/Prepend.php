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

Clearbricks::lib()->autoload([
    'httpPassword' => implode(DIRECTORY_SEPARATOR, [__DIR__, 'inc', 'class.httppassword.php']),
]);

dcCore::app()->auth->setPermissionType(
    initHttpPassword::PERMISSION,
    __('Manage http password blog protection')
);
