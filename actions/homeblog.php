<?php

/**
 *
 * @copyright  2010-2012 izend.org
 * @version    11
 * @link       http://www.izend.org
 */

require_once 'readarg.php';
require_once 'socialize.php';
require_once 'userhasrole.php';
require_once 'models/thread.inc';

function homeblog($lang, $arglist=false) {
	global $request_path, $with_toolbar, $sitename;
	global $blog_thread, $blog_pagesize;

	$request_path=$lang;

	$r = thread_get($lang, $blog_thread);
	if (!$r) {
		return run('error/internalerror', $lang);
	}
	extract($r); /* thread_abstract thread_cloud thread_ilike thread_tweet thread_plusone thread_linkedin */

	head('title', translate('home:title', $lang));
	if ($thread_abstract) {
		head('description', $thread_abstract);
	}
	else {
		head('description', translate('description', $lang));
	}
	if ($thread_cloud) {
		head('keywords', $thread_cloud);
	}
	else {
		head('keywords', translate('keywords', $lang));
	}

	$page_header = build('nodecontent', $lang, 1);
	$page_footer = build('nodecontent', $lang, 2);

	$page=1;

	if (!empty($arglist['p'])) {
		$page=$arglist['p'];
		if (!is_numeric($page) or $page < 1) {
			$page=1;
		}
	}

	$page_contents=build('blogsummary', $lang, $blog_thread, $page, $blog_pagesize);

	$besocial=$sharebar=false;
	if ($page_contents) {
		$ilike=$thread_ilike;
		$tweetit=$thread_tweet;
		$plusone=$thread_plusone;
		$linkedin=$thread_linkedin;
		if ($tweetit) {
			$tweet_text=$thread_abstract ? $thread_abstract : translate('description', $lang);
			$tweetit=$tweet_text ? compact('tweet_text') : true;
		}
		list($besocial, $sharebar) = socialize($lang, compact('ilike', 'tweetit', 'plusone', 'linkedin'));
	}

	$donate = build('donate', $lang);

	$content = view('homeblog', false, compact('page_header', 'page_footer', 'page_contents', 'besocial', 'donate'));

	$search_text='';
	$search_url=url('search', $lang);
	$suggest_url=url('suggest', $lang);
	$search=compact('search_url', 'search_text', 'suggest_url');
	$edit=user_has_role('writer') ? url('threadedit', $_SESSION['user']['locale']) . '/'. $blog_thread . '?' . 'clang=' . $lang : false;
	$validate=url('homeblog', $lang);

	$banner = build('banner', $lang, $with_toolbar ? compact('search') : compact('edit', 'validate', 'search'));
	$toolbar = $with_toolbar ? build('toolbar', $lang, compact('edit', 'validate')) : false;

	$languages='homeblog';
	$contact=$account=$admin=true;
	$footer = build('footer', $lang, compact('contact', 'account', 'admin', 'languages'));

	$social = view('social', $lang);

	$output = layout('standard', compact('sharebar', 'toolbar', 'footer', 'banner', 'content', 'social'));

	return $output;
}

