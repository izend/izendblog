<?php

/**
 *
 * @copyright  2010-2018 izend.org
 * @version    6
 * @link       http://www.izend.org
 */

require_once 'userisidentified.php';
require_once 'userprofile.php';

function account($lang) {
	if (!user_is_identified()) {
		return run('user', $lang);
	}

	head('title', translate('account:title', $lang));
	head('description', false);
	head('keywords', false);
	head('robots', 'noindex');

	$banner = build('banner', $lang);

	$user_id = user_profile('id');
	$useredit = build('useredit', $lang, $user_id);

	$content = view('account', $lang, compact('useredit'));

	$contact=true;
	$footer = build('footer', $lang, compact('contact'));

	$output = layout('standard', compact('lang', 'footer', 'banner', 'content'));

	return $output;
}

