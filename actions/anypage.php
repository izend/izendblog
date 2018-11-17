<?php

/**
 *
 * @copyright  2010-2018 izend.org
 * @version    7
 * @link       http://www.izend.org
 */

require_once 'socialize.php';

function anypage($lang, $arglist=false) {
	global $sitename, $siteshot;

	$page=false;

	if (is_array($arglist)) {
		$page=implode('/', $arglist);
	}

	if (!$page) {
		return run('error/notfound', $lang);
	}

	$page_contents = build('content', $lang, $page);
	if ($page_contents === false) {
		return run('error/notfound', $lang);
	}

	$besocial=$sharebar=false;
	$ilike=true;
	$tweetit=true;
	$plusone=true;
	$linkedin=true;
	$pinit=true;
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
	list($besocial, $sharebar) = socialize($lang, compact('ilike', 'tweetit', 'plusone', 'linkedin', 'pinit'));

	$content = view('anypage', false, compact('page_contents', 'besocial'));

	head('title', $sitename);

	$banner = build('banner', $lang);

	$contact=true;
	$footer = build('footer', $lang, compact('contact'));

	$output = layout('standard', compact('lang', 'sharebar', 'banner', 'footer', 'content'));

	return $output;
}

