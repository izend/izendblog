<?php

/**
 *
 * @copyright  2010-2018 izend.org
 * @version    5
 * @link       http://www.izend.org
 */

function paymentcancelled($lang, $amount, $currency, $context) {
	head('title', translate('payment_cancelled:title', $lang));
	head('robots', 'noindex');

	$banner = build('banner', $lang);

	$contact_page=url('contact', $lang);
	$content = view('paymentcancelled', $lang, compact('amount', 'currency', 'contact_page'));

	$contact=true;
	$footer = build('footer', $lang, compact('contact'));

	$output = layout('standard', compact('lang', 'footer', 'banner', 'content'));

	return $output;
}

