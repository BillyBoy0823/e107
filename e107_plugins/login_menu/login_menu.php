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
 * $Source: /cvs_backup/e107_0.8/e107_plugins/login_menu/login_menu.php,v $
 * $Revision: 1.14 $
 * $Date: 2009-11-18 01:05:53 $
 * $Author: e107coders $
 */

if (!defined('e107_INIT')) { exit; }

if(defined("FPW_ACTIVE"))
{
	return;      // prevent failed login attempts when fpw.php is loaded before this menu.
}

global $eMenuActive, $pref, $e107, $sql, $tp, $ns, $use_imagecode, $ADMIN_DIRECTORY, $LOGIN_MENU_MESSAGE, $LOGIN_MENU_STATITEM, $LM_STATITEM_SEPARATOR,
       $login_menu_shortcodes, $LOGIN_MENU_FORM, $LOGIN_MENU_LOGGED, $LOGIN_MENU_STATS, $LOGIN_MENU_EXTERNAL_LINK;
$ip = $e107->getip();

//shortcodes
    require_once(e_PLUGIN."login_menu/login_menu_shortcodes.php");

//Bullet
	if(defined("BULLET"))
	{
   		$bullet = "<img src='".THEME_ABS."images/".BULLET."' alt='' class='icon' />";
   		$bullet_src = THEME_ABS."images/".BULLET;
	}
	elseif(file_exists(THEME."images/bullet2.gif"))
	{
		$bullet = "<img src='".THEME_ABS."images/bullet2.gif' alt='bullet' class='icon' />";
		$bullet_src = THEME_ABS."images/bullet2.gif";
	}
	else
	{
		$bullet = "";
		$bullet_src = "";
	}

//Corrup cookie - template? - TODO
    if (defined('CORRUPT_COOKIE') && CORRUPT_COOKIE == TRUE)
    {
    	$text = "<div class='core-sysmsg loginbox'>".LOGIN_MENU_L7."<br /><br />
    	{$bullet} <a href='".SITEURL."index.php?logout'>".LOGIN_MENU_L8."</a></div>";
    	$ns->tablerender(LOGIN_MENU_L9, $text, 'loginbox_error');
    }
    
//Image code
    $use_imagecode = ($pref['logcode'] && extension_loaded('gd'));
    
    if ($use_imagecode)
    {
    	global $sec_img;
    	include_once(e_HANDLER.'secure_img_handler.php');
    	$sec_img = new secure_image;
    }

    $text = '';
    
// START LOGGED CODE
if (USER == TRUE || ADMIN == TRUE)
{
    require_once(e_PLUGIN."login_menu/login_menu_class.php");

    //login class ??? - TODO
	if ($sql->db_Select('online', 'online_ip', "`online_ip` = '{$ip}' AND `online_user_id` = '0' "))
	{	// User now logged in - delete 'guest' record (tough if several users on same IP)
		$sql->db_Delete('online', "`online_ip` = '{$ip}' AND `online_user_id` = '0' ");
	}

	//get templates
    if (!isset($LOGIN_MENU_LOGGED)) {
		if (file_exists(THEME."login_menu_template.php")){
	   		require(THEME."login_menu_template.php");
		}else{
			require(e_PLUGIN."login_menu/login_menu_template.php");
		}
	}
	if(!$LOGIN_MENU_LOGGED){
    	require(e_PLUGIN."login_menu/login_menu_template.php");
	}

    //prepare
	$new_total = 0;
	$time = USERLV;
	$menu_data = array();

		// ------------ News Stats -----------

		if (varsettrue($menu_pref['login_menu']['new_news']))
		{
			$nobody_regexp = "'(^|,)(".str_replace(",", "|", e_UC_NOBODY).")(,|$)'";
            $menu_data['new_news'] = $sql->db_Count("news", "(*)", "WHERE `news_datestamp` > {$time} AND news_class REGEXP '".e_CLASS_REGEXP."' AND NOT (news_class REGEXP ".$nobody_regexp.")");
			$new_total += $menu_data['new_news'];
		}

		// ------------ Comments Stats -----------

		if (varsettrue($menu_pref['login_menu']['new_comments']))
		{
			$menu_data['new_comments'] = $sql->db_Count('comments', '(*)', 'WHERE `comment_datestamp` > '.$time);
			$new_total += $menu_data['new_comments'];
		}

		// ------------ Member Stats -----------

		if (varsettrue($menu_pref['login_menu']['new_members'])) 
        {
			$menu_data['new_users'] = $sql->db_Count('user', '(user_join)', 'WHERE user_join > '.$time);
			$new_total += $menu_data['new_users'];
		}
		
		// ------------ Enable stats / other ---------------
		
		$menu_data['enable_stats'] = $menu_data || varsettrue($menu_pref['login_menu']['external_stats']) ? true : false;
		$menu_data['new_total'] = $new_total + login_menu_class::get_stats_total();
		$menu_data['link_bullet'] = $bullet;
		$menu_data['link_bullet_src'] = $bullet_src;
		
		// ------------ List New Link ---------------
		
		$menu_data['listnew_link'] = '';
		if ($menu_data['new_total'] && array_key_exists('list_new', $pref['plug_installed'])) 
        {
            $menu_data['listnew_link'] = e_PLUGIN.'list_new/list.php?new';
		}

		// ------------ Pass the data & parse ------------
		cachevars('login_menu_data', $menu_data);
		$text = $tp->parseTemplate($LOGIN_MENU_LOGGED, true, $login_menu_shortcodes);
    
    //menu caption
	if (file_exists(THEME.'images/login_menu.png')) {
		$caption = '<img src="'.THEME_ABS.'images/login_menu.png" alt="" />'.LOGIN_MENU_L5.' '.USERNAME;
	} else {
		$caption = LOGIN_MENU_L5.' '.USERNAME;
	}
	
	//render
	$ns->tablerender($caption, $text, 'loginbox');

// END LOGGED CODE	
} 
// START NOT LOGGED CODE	
else 
{
    //get templates
	if (!$LOGIN_MENU_FORM || !$LOGIN_MENU_MESSAGE) {
		if (file_exists(THEME."login_menu_template.php")){
	   		require_once(THEME."login_menu_template.php");
		}else{
			require_once(e_PLUGIN."login_menu/login_menu_template.php");
		}
	}
	if(!$LOGIN_MENU_FORM || !$LOGIN_MENU_MESSAGE){
    	require(e_PLUGIN."login_menu/login_menu_template.php");
	}

	$text = '<form method="post" action="'.e_SELF.(e_QUERY ? '?'.e_QUERY : '');
	if (varsettrue($pref['password_CHAP'],0))
	{
	  $text .= '" onsubmit="hashLoginPassword(this)';
	}
	$text .= '">'.$tp->parseTemplate($LOGIN_MENU_FORM, true, $login_menu_shortcodes);
	$text .= '</form>';

	if (file_exists(THEME.'images/login_menu.png')) {
		$caption = '<img src="'.THEME_ABS.'images/login_menu.png" alt="" />'.LOGIN_MENU_L5;
	} else {
		$caption = LOGIN_MENU_L5;
	}
	$ns->tablerender($caption, $text, 'loginbox');
}
// END NOT LOGGED CODE
?>