<?php

/**
 *
 * @copyright  2010-2012 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

function paymentrejected($lang) {
	head('title', translate('payment_rejected:title', $lang));
	head('robots', 'noindex, nofollow');

	$banner = build('banner', $lang);

	$contact_page=url('contact', $lang);
	$content = view('paymentrejected', $lang, compact('contact_page'));

	$contact=true;
	$footer = build('footer', $lang, compact('contact'));

	$output = layout('standard', compact('footer', 'banner', 'content'));

	return $output;
}

