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

if ($core->blog->settings->httppassword_active) {
	$core->addBehavior('publicPrepend',array('httpPassword','Check'));
	//$core->addBehavior('publicPrepend',array('httpPassword','LastLogin'));
}

class httpPassword {

	private static function __debuglog ($core,$trace) {
		static $fic = false;
		if (!$core->blog->settings->httppassword_trace)
			return;
		if ($fic === false)
			$fic = fopen($core->blog->public_path . '/.htpasswd.trc.txt','a');
		if ($fic !== false) {
			fprintf($fic,"%s - %s\n",date('Ymd-His'),$trace);
		}
	}

	private static function __debugmode ($core) {
		$fic = fopen($core->blog->public_path . '/.debugmode','a');
		fprintf($fic,"\n%s\n%s\n", str_repeat('-', 30), date('Ymd-His'));
		fprintf($fic,".... \$_SERVER =\n%s\n",var_export($_SERVER,true));
		fprintf($fic,".... \$_ENV =\n%s\n",var_export($_ENV,true));
		fprintf($fic,".... Apache headers =\n%s\n",var_export(apache_request_headers(),true));
	}
	
	private static function __HTTP401($core) {
		httpPassword::__debuglog($core,__FUNCTION__);
		header('HTTP/1.1 401 Unauthorized');
		header('WWW-Authenticate: Basic realm="'. utf8_decode(htmlspecialchars_decode($core->blog->settings->httppassword_message)) .'"'); 
		exit(0);
	}
	public static function Check($core) {
		httpPassword::__debuglog($core,'ENV = ' . var_export($_ENV,true));
		if ($core->blog->settings->httppassword_debugmode)
			httpPassword::__debugmode($core);
		if (isset($_SERVER['PHP_AUTH_USER']) and isset($_SERVER['PHP_AUTH_PW'])) {
			$PHP_AUTH_USER = $_SERVER['PHP_AUTH_USER'];
			$PHP_AUTH_PW = $_SERVER['PHP_AUTH_PW'];
			httpPassword::__debuglog($core,__FUNCTION__	. ' user identication found in $_SERVER');
		} else if (isset($_ENV['REMOTE_USER'])) {
			list($PHP_AUTH_PW,$PHP_AUTH_USER) = explode(' ',$_ENV['REMOTE_USER'],2);
			list($PHP_AUTH_USER,$PHP_AUTH_PW) = explode(':',base64_decode($PHP_AUTH_USER));
			httpPassword::__debuglog($core,__FUNCTION__	. ' user identication found in $_ENV');
		}
		if (!isset($PHP_AUTH_USER) or !isset($PHP_AUTH_PW) or $PHP_AUTH_USER === '')
			httpPassword::__HTTP401($core);

		httpPassword::__debuglog($core,'Testing user: '.$PHP_AUTH_USER.'	 pass: '.$PHP_AUTH_PW);

		if (!is_file($core->blog->public_path . '/.htpasswd')) {
			header('HTTP/1.0 500 Internal Server Error');
			echo "Le plugin httppassword pr&eacute;sente une anomalie de configuration";
			exit(1);
		}

		$htpasswd = file($core->blog->public_path . '/.htpasswd',FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);
		$authenticated = false;
		foreach($htpasswd as $ligne) {
			list($cur_user,$cur_pass) = explode(':',trim($ligne),2);
			httpPassword::__debuglog($core,'cur_user: '.$cur_user.'	 cur_pass: '.$cur_pass);
			if ($cur_user == $PHP_AUTH_USER and crypt($PHP_AUTH_PW,$cur_pass) == $cur_pass) {
				$authenticated = true;
				httpPassword::__debuglog($core,'		OK');
			}
			if ($authenticated) break;
		}
		unset($htpasswd);
		if (!$authenticated) httpPassword::__HTTP401($core);
		else httpPassword::LastLogin($core,$PHP_AUTH_USER);

		return(true);
	}

	public static function LastLogin($core,$user) {
		$fic = $core->blog->public_path . '/.lastlogin';

		$httpPasswordLastLogin = array();
		if (is_file($fic))
			$httpPasswordLastLogin = unserialize(file_get_contents($fic));

		$httpPasswordLastLogin[$user] = date('Y-m-d H:i');

		file_put_contents($fic,serialize($httpPasswordLastLogin));

		return(true);
	}
}
?>
