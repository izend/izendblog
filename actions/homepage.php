<?php

/**
 *
 * @copyright  2010-2019 izend.org
 * @version    10
 * @link       http://www.izend.org
 */

require_once 'socialize.php';

function homepage($lang) {
	global $sitename, $siteshot;

	$page_contents = build('content', $lang, 'homepage');

	$besocial=$sharebar=false;
	$ilike=true;
	$tweetit=true;
	$linkedin=true;
	$pinit=true;
	$whatsapp=true;
	if ($tweetit or $pinit) {
		$description=translate('description', $lang);
		if ($tweetit) {
			$tweet_text=$description ? $description : $sitename;
			$tweetit=$tweet_text ? compact('tweet_text') : true;
		}
		if ($pinit) {
			$pinit_text=$description ? $description : $sitename;
			$pinit_image=$siteshot;
			$pinit=$pinit_text && $pinit_image ? compact('pinit_text', 'pinit_image') : true;
		}
	}
	list($besocial, $sharebar) = socialize($lang, compact('ilike', 'tweetit', 'linkedin', 'pinit', 'whatsapp'));

	$content = view('anypage', false, compact('page_contents', 'besocial'));

	head('title', $sitename);

	$banner = build('banner', $lang);

	$languages='homepage';
	$contact=true;
	$footer = build('footer', $lang, compact('languages', 'contact'));

	$social = view('social', $lang);

	$output = layout('standard', compact('lang', 'sharebar', 'banner', 'footer', 'content', 'social'));

	return $output;
}

