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
    return;
}

try {
    // Check versions
    if (!dcCore::app()->newVersion(
        basename(__DIR__),
        dcCore::app()->plugins->moduleInfo(basename(__DIR__), 'version')
    )) {
        return null;
    }

    // Set settings
    $s = dcCore::app()->blog->settings->get(basename(__DIR__));
    $s->put('active', false, 'boolean', 'Enable plugin', false, false);
    $s->put('crypt', 'crypt_md5', 'string', 'Crypt algorithm', false, false);
    $s->put('message', 'Private space', 'String', 'Personalized message on Authentication popup', false, false);

    return true;
} catch (Exception $e) {
    dcCore::app()->error->add($e->getMessage());
}

return false;
