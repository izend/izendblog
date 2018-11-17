<?php

/**
 *
 * @copyright  2010-2018 izend.org
 * @version    3
 * @link       http://www.izend.org
 */

function internalerror($lang) {
	head('title', translate('http_internal_error:title', $lang));
	head('robots', 'noindex');

	$contact=false;
	$banner = build('banner', $lang, compact('contact'));

	$contact_page=url('contact', $lang);
	$content = view('error/internalerror', $lang, compact('contact_page'));

	$footer = build('footer', $lang);

	$output = layout('standard', compact('lang', footer', 'banner', 'content'));

	header('HTTP/1.1 500 Internal Error');

	return $output;
}

