<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of httpPassword, a plugin for Dotclear.
# 
# Copyright (c) 2007-2009 Frederic PLE
# dotclear@frederic.ple.name
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { return; }

$_menu['Plugins']->addItem('httpPassword',
	'plugin.php?p=httpPassword',
	'index.php?pf=httpPassword/icon.png',
	preg_match('/plugin.php\?p=httpPassword(&.*)?$/',
		$_SERVER['REQUEST_URI']),
	$core->auth->check('usage,contentadmin',$core->blog->id)
);

$core->auth->setPermissionType(
	'httpPassword',
	'Gestion de la protection du site httpPassword'
);
?>
