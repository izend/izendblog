<?php

/**
 *
 * @copyright  2011 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

require_once 'userhasrole.php';

function admin($lang) {
	if (!user_has_role('administrator')) {
		return run('error/unauthorized', $lang);
	}

	head('title', translate('admin:title', $lang));
	head('description', false);
	head('keywords', false);
	head('robots', 'noindex, nofollow');

	$banner = build('banner', $lang);

	$usersearch = build('usersearch', $lang);
	$content = view('admin', $lang, compact('usersearch'));

	$footer = build('footer', $lang);

	$output = layout('standard', compact('footer', 'banner', 'content'));

	return $output;
}

