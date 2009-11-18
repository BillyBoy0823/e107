<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/alt_auth/e_help.php,v $
 * $Revision: 1.4 $
 * $Date: 2009-11-18 01:05:22 $
 * $Author: e107coders $
 */

if (!defined('e107_INIT')) { exit; }

define('ALT_AUTH_PATH', e_PLUGIN.'alt_auth/');

if (e_PAGE == 'alt_auth_conf.php')
{
	include_lan(ALT_AUTH_PATH.'languages/'.e_LANGUAGE.'/admin_alt_auth.php');
	$ns -> tablerender('help',LAN_ALT_AUTH_HELP);
}
else
{
	include_lan(ALT_AUTH_PATH.'languages/'.e_LANGUAGE.'/admin_'.e_PAGE);
	if (!defined('LAN_ALT_VALIDATE_HELP')) include_lan(ALT_AUTH_PATH.'languages/'.e_LANGUAGE.'/admin_alt_auth.php');
	$ns -> tablerender('help',LAN_AUTHENTICATE_HELP.'<br /><br />'.(defined('SHOW_COPY_HELP') ? LAN_ALT_COPY_HELP : '').(defined('SHOW_CONVERSION_HELP') ? LAN_ALT_CONVERSION_HELP : '').LAN_ALT_VALIDATE_HELP);
}

?>