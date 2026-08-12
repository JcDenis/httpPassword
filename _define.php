<?php
/**
 * @file
 * @brief       The plugin httpPassword definition
 * @ingroup     httpPassword
 *
 * @defgroup    httpPassword Plugin httpPassword.
 *
 * Manage .htpasswd file to make the blog private.
 *
 * @author      Frederic PLE (author)
 * @author      Jean-Christian Denis (latest)
 * @copyright   GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
declare(strict_types=1);

if (!isset($this) || !is_object($this) || !method_exists($this, 'registerModule') || !isset($this->id) || !is_string($this->id)) {
    return;
}

$this->registerModule(
    'Http password',
    'Manage .htpasswd file to make the blog private',
    'Frederic PLE and contributors',
    '1.7',
    [
        'requires'    => [['core', '2.39']],
        'permissions' => 'My',
        'type'        => 'plugin',
        'support'     => 'https://github.com/JcDenis/' . $this->id . '/issues',
        'details'     => 'https://github.com/JcDenis/' . $this->id . '/',
        'repository'  => 'https://raw.githubusercontent.com/JcDenis/' . $this->id . '/master/dcstore.xml',
        'date'        => '2026-08-12T21:03:50+00:00',
    ]
);
