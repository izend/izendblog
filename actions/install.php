<?php

/**
 *
 * @copyright  2010-2012 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

function install($lang) {
	head('title', translate('install:title', $lang));
	head('description', false);
	head('keywords', false);
	head('robots', 'noindex, nofollow');

	$banner = build('banner', $lang);

	$configure = build('configure', $lang);

	$content = view('install', $lang, compact('configure'));

	$languages='install';
	$contact=true;
	$footer = build('footer', $lang, compact('languages', 'contact'));

	$output = layout('standard', compact('footer', 'banner', 'content'));

	return $output;
}

