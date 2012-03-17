<?php

/**
 *
 * @copyright  2010-2012 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

function paymentaccepted($lang) {
	head('title', translate('payment_accepted:title', $lang));
	head('robots', 'noindex, nofollow');

	$banner = build('banner', $lang);

	$content = view('paymentaccepted', $lang);

	$contact=true;
	$footer = build('footer', $lang, compact('contact'));

	$output = layout('standard', compact('footer', 'banner', 'content'));

	return $output;
}

