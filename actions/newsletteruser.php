<?php

/**
 *
 * @copyright  2012 izend.org
 * @version    2
 * @link       http://www.izend.org
 */

function newsletteruser($lang) {
	head('title', translate('newsletter:title', $lang));
	head('description', false);
	head('keywords', false);
	head('robots', 'noindex, nofollow');

	$banner = build('banner', $lang);

	$subscribe = build('subscribe', $lang);

	$content = view('newsletteruser', $lang, compact('subscribe'));

	$contact=true;
	$footer = build('footer', $lang, compact('contact'));

	$output = layout('standard', compact('footer', 'banner', 'content'));

	return $output;
}

