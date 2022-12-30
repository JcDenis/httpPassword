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
 
$m_version = $core->plugins->moduleInfo('httpPassword','version');
 
$i_version = $core->getVersion('httpPassword');

if (version_compare($i_version,$m_version,'>=')) {
	return;
}
 
# Création du setting (s'il existe, il ne sera pas écrasé)
$settings = new dcSettings($core,null);
$settings->setNamespace('httppassword');
$mydomain = preg_replace('/^.*\.([^.]+[^.])$/','$1',gethostbyaddr($_SERVER['SERVER_ADDR']));
$defaultcrypt = '';

$settings->put('httppassword_active',false,'boolean','Activer',false,false);
$settings->put('httppassword_crypt',$defaultcrypt,'string','Fonction de cryptage',false,false);
$settings->put('httppassword_message','Zone Privee','String','Message personnalisable dans le popup d\'authentification',false,false);
$settings->put('httppassword_trace',false,'boolean','Activation des traces (debug)',false,false);
$settings->put('httppassword_debugmode',false,'boolean','Activation du mode Debug',false,false);
 
$core->setVersion('httpPassword',$m_version);
?>
