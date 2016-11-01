<?php

/**
 *
 * @copyright  2010-2016 izend.org
 * @version    6
 * @link       http://www.izend.org
 */

require_once 'userisidentified.php';
require_once 'userhasrole.php';

function banner($lang, $components=false) {
	global $home_action, $cookieconsent;

	$consent=false;
	if ($cookieconsent and !isset($_COOKIE['cookieconsent'])) {
		setcookie('cookieconsent', true, time()+60*60*24*365, '/');
		$consent=view('consent', $lang);
	}

	$home_page=url($home_action, $lang);
	$logo = view('logo', false, compact('home_page'));

	$menu=$languages=$headline=$search=$donate=false;

	$contact_page=$user_page=$nobody_page=$account_page=$edit_page=$view_page=$validate_page=$admin_page=false;

	$is_identified = user_is_identified();
	$is_admin = user_has_role('administrator');
	$is_writer = user_has_role('writer');

	if ($components) {
		foreach ($components as $v => $param) {
			switch ($v) {
				case 'logout':
					if ($is_identified) {
						$nobody_page=url('nobody', $lang);
					}
					break;
				case 'login':
					if (!$is_identified) {
						$user_page=url('user', $lang);
					}
					break;
				case 'account':
					if ($param) {
						if ($is_identified) {
							$account_page=url('account', $lang);
						}
						else {
							$user_page=url('user', $lang);
						}
					}
					break;
				case 'contact':
					if ($param) {
						$contact_page=url('contact', $lang);
					}
					break;
				case 'languages':
					if ($param) {
						$languages = build('languages', $lang, $param);
					}
					break;
				case 'donate':
					if ($param) {
						$donate = build('donate', $lang);
					}
					break;
				case 'headline':
					if ($param) {
						$headline = view('headline', false, $param);
					}
					break;
				case 'search':
					if ($param) {
						$search = view('searchinput', $lang, $param);
					}
					break;
				case 'edit':
					if ($param) {
						if ($is_writer) {
							$edit_page=$param;
						}
					}
					break;
				case 'view':
					if ($param) {
						if ($is_writer) {
							$view_page=$param;
						}
					}
					break;
				case 'validate':
					if ($param) {
						if ($is_writer) {
							$validate_page=$param;
						}
					}
					break;
				case 'admin':
					if ($param) {
						if ($is_admin) {
							$admin_page=url('admin', $lang);
						}
					}
					break;
				default:
					break;
			}
		}
	}

	$menu = view('bannermenu', $lang, compact('user_page', 'nobody_page', 'account_page', 'contact_page', 'edit_page', 'view_page', 'validate_page', 'admin_page'));

	$output = view('banner', false, compact('consent', 'logo', 'menu', 'languages', 'headline', 'search', 'donate'));

	return $output;
}

