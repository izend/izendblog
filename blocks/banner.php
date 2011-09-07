<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    4
 * @link       http://www.izend.org
 */

require_once 'userisidentified.php';
require_once 'userhasrole.php';

function banner($lang, $components=false) {
	global $home_action;

	$home_page=url($home_action, $lang);
	$logo = view('logo', $lang, compact('home_page'));

	$menu=$languages=$headline=$search=false;

	$contact=$login=$logout=$account=$edit=$view=$validate=$admin=false;
	$contact_page=$account_page=$nobody_page=$edit_page=$view_page=$validate_page=$admin_page=false;

	$is_identified = user_is_identified();
	$is_admin = user_has_role('administrator');
	$is_writer = user_has_role('writer');

	if ($is_identified) {
		$nobody_page=url('nobody', $lang);
		$logout = true;
	}

	if ($components) {
		foreach ($components as $v => $param) {
			switch ($v) {
				case 'account':
					if ($param) {
						if ($is_identified) {
							$account_page=url('account', $lang);
							$account = $account_page !== false;
						}
						else {
							$user_page=url('user', $lang);
							$login = $user_page !== false;
						}
					}
					break;
				case 'contact':
					if ($param) {
						$contact_page=url('contact', $lang);
						$contact = $contact_page !== false;
					}
					break;
				case 'languages':
					if ($param) {
						$languages = build('languages', $lang, $param);
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
							$edit=true;
						}
					}
					break;
				case 'view':
					if ($param) {
						if ($is_writer) {
							$view_page=$param;
							$view=true;
						}
					}
					break;
				case 'validate':
					if ($param) {
						if ($is_writer) {
							$validate_page=$param;
							$validate=true;
						}
					}
					break;
				case 'admin':
					if ($param) {
						if ($is_admin) {
							$admin_page=url('admin', $lang);
							$admin = $admin_page !== false;
						}
					}
					break;
				default:
					break;
			}
		}
	}

	if ($contact or $login or $logout or $account or $edit or $view or $validate or $admin) {
		$menu = view('bannermenu', $lang, compact('account', 'account_page', 'contact', 'contact_page', 'edit', 'edit_page', 'view', 'view_page', 'validate', 'validate_page', 'logout', 'nobody_page', 'login', 'user_page', 'admin', 'admin_page'));
	}

	$output = view('banner', false, compact('logo', 'menu', 'languages', 'headline', 'search'));

	return $output;
}

