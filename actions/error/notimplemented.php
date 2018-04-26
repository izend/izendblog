<?php

/**
 *
 * @copyright  2010-2018 izend.org
 * @version    2
 * @link       http://www.izend.org
 */

function notimplemented($lang) {
	head('title', translate('http_not_implemented:title', $lang));
	head('robots', 'noindex');

	$contact=false;
	$banner = build('banner', $lang, compact('contact'));

	$contact_page=url('contact', $lang);
	$content = view('error/notimplemented', $lang, compact('contact_page'));

	$footer = build('footer', $lang);

	$output = layout('standard', compact('footer', 'banner', 'content'));

	header('HTTP/1.1 501 Not Implemented');

	return $output;
}

