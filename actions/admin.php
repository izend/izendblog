<?php

/**
 *
 * @copyright  2012-2017 izend.org
 * @version    8
 * @link       http://www.izend.org
 */

require_once 'paypal.inc';
require_once 'userhasrole.php';

function admin($lang) {
	global $paypal_username, $paypal_password, $paypal_signature;
	global $googleanalyticsaccount, $googleanalyticskeyfile;
	global $newsletter_thread;
	global $with_toolbar;

	if (!user_has_role('administrator')) {
		return run('error/unauthorized', $lang);
	}

	$banner = build('banner', $lang);

	$newuser_page=url('newuser', $lang);

	$newsletter_page=$newsletter_thread ? url('newsletter', $lang) : false;

	$traffic_page=$googleanalyticsaccount && $googleanalyticskeyfile ? url('traffic', $lang) :false;

	$balance=false;
	if (!(empty($paypal_username) or empty($paypal_password) or empty($paypal_signature))) {
		$balance = build('balance', $lang);
	}
	$upload = build('upload', $lang);
	$usersearch = build('usersearch', $lang);
	$content = view('admin', $lang, compact('newuser_page', 'newsletter_page', 'traffic_page', 'balance', 'usersearch', 'upload'));

	head('title', translate('admin:title', $lang));
	head('description', false);
	head('keywords', false);
	head('robots', 'noindex, nofollow');

	$banner = build('banner', $lang);
	$toolbar = $with_toolbar ? build('toolbar', $lang) : false;

	$footer = build('footer', $lang);

	$output = layout('standard', compact('toolbar', 'footer', 'banner', 'content'));

	return $output;
}

