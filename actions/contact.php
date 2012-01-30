<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

function contact($lang) {
	head('title', translate('contact:title', $lang));
	head('description', false);
	head('keywords', false);
	head('robots', 'noindex, nofollow');

	$banner = build('banner', $lang);

	$mailme = build('mailme', $lang);

	$content = view('contact', $lang, compact('mailme'));

	$languages='contact';
	$footer = build('footer', $lang, compact('languages'));

	$output = layout('standard', compact('footer', 'banner', 'content'));

	return $output;
}

