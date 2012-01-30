<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    4
 * @link       http://www.izend.org
 */

require_once 'socialize.php';

function homepage($lang) {
	global $sitename;

	$page_contents = build('content', $lang, 'homepage');

	$besocial=$sharebar=false;
	$ilike=true;
	$tweetit=true;
	$plusone=true;
	$linkedin=true;
	if ($tweetit) {
		$tweet_text=$sitename;
		$tweetit=$tweet_text ? compact('tweet_text') : true;
	}
	list($besocial, $sharebar) = socialize($lang, compact('ilike', 'tweetit', 'plusone', 'linkedin'));

	$content = view('anypage', false, compact('page_contents', 'besocial'));

	head('title', $sitename);

	$banner = build('banner', $lang);

	$languages='homepage';
	$contact=true;
	$footer = build('footer', $lang, compact('languages', 'contact'));

	$social = view('social', $lang);

	$output = layout('standard', compact('sharebar', 'banner', 'footer', 'content', 'social'));

	return $output;
}

