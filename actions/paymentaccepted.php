<?php

/**
 *
 * @copyright  2010-2018 izend.org
 * @version    5
 * @link       http://www.izend.org
 */

function paymentaccepted($lang, $amount, $currency, $context) {
	head('title', translate('payment_accepted:title', $lang));
	head('robots', 'noindex');

	$banner = build('banner', $lang);

	$content = view('paymentaccepted', $lang, compact('amount', 'currency'));

	$contact=true;
	$footer = build('footer', $lang, compact('contact'));

	$output = layout('standard', compact('lang', 'footer', 'banner', 'content'));

	return $output;
}

