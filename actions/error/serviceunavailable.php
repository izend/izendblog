<?php

/**
 *
 * @copyright  2010-2012 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

function serviceunavailable($lang, $arglist) {
	head('title', translate('http_service_unavailable:title', $lang));
	head('robots', 'noindex, nofollow');

	$contact=false;
	$banner = build('banner', $lang, compact('contact'));

	list($closing_time, $opening_time) = $arglist;
	$content = view('error/serviceunavailable', $lang, compact('closing_time', 'opening_time'));

	$footer = build('footer', $lang);

	$output = layout('standard', compact('footer', 'banner', 'content'));

	header('HTTP/1.1 503 Service Unavailable');

	return $output;
}

