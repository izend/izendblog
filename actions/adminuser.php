<?php

/**
 *
 * @copyright  2011-2017 izend.org
 * @version    2
 * @link       http://www.izend.org
 */

require_once 'userhasrole.php';
require_once 'models/user.inc';

function adminuser($lang, $arglist=false) {
	global $with_toolbar;

	if (!user_has_role('administrator')) {
		return run('error/unauthorized', $lang);
	}

	$user_id=false;

	if (is_array($arglist)) {
		if (isset($arglist[0])) {
			$user_id=$arglist[0];
		}
	}

	if (!$user_id) {
		return run('error/notfound', $lang);
	}

	$user_id = user_id($user_id);
	if (!$user_id) {
		return run('error/notfound', $lang);
	}

	$useredit = build('useredit', $lang, $user_id);

	if ($useredit === false) {
		return redirect('admin', $lang);
	}

	head('title', translate('admin:title', $lang));
	head('description', false);
	head('keywords', false);
	head('robots', 'noindex, nofollow');

	$admin=true;
	$banner = build('banner', $lang);
	$toolbar = $with_toolbar ? build('toolbar', $lang, compact('admin')) : false;

	$content = view('adminuser', $lang, compact('useredit'));

	$admin=true;
	$footer = build('footer', $lang, compact('admin'));

	$output = layout('standard', compact('toolbar', 'footer', 'banner', 'content'));

	return $output;
}

