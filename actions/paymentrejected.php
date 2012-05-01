<?php

/**
 *
 * @copyright  2010-2012 izend.org
 * @version    3
 * @link       http://www.izend.org
 */

function paymentrejected($lang, $amount, $currency, $context) {
	head('title', translate('payment_rejected:title', $lang));
	head('robots', 'noindex, nofollow');

	$banner = build('banner', $lang);

	$contact_page=url('contact', $lang);
	$content = view('paymentrejected', $lang, compact('amount', 'currency', 'contact_page'));

	$contact=true;
	$footer = build('footer', $lang, compact('contact'));

	$output = layout('standard', compact('footer', 'banner', 'content'));

	return $output;
}

