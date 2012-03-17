<?php

/**
 *
 * @copyright  2010-2012 izend.org
 * @version    10
 * @link       http://www.izend.org
 */

require_once 'readarg.php';
require_once 'socialize.php';
require_once 'userhasrole.php';
require_once 'models/thread.inc';

function homeblog($lang, $arglist=false) {
	global $request_path, $with_toolbar, $sitename;
	global $default_folder;

	$pagesize=3;

	$page=1;

	if (isset($_SESSION['homepage'])) {
		$page=$_SESSION['homepage'];
		unset($_SESSION['homepage']);
	}

	if (is_array($arglist)) {
		if (isset($arglist[0])) {
			$page=$arglist[0];
		}
		else if (isset($arglist['p'])) {
			$page=$arglist['p'];
		}
		if (!is_numeric($page) or $page < 1) {
			$page=1;
		}

		$_SESSION['homepage']=$page;
	}

	$r = thread_get_node($lang, 1, 1);
	if (!$r) {
		return run('error/notfound', $lang);
	}
	extract($r); /* node_name node_title node_abstract node_cloud node_created node_modified node_nocomment node_nomorecomment node_ilike node_tweet node_plusone */

	head('title', translate('home:title', $lang));
	if ($node_abstract) {
		head('description', $node_abstract);
	}
	else {
		head('description', translate('description', $lang));
	}
	if ($node_cloud) {
		head('keywords', $node_cloud);
	}
	else {
		head('keywords', translate('keywords', $lang));
	}

	$request_path=$lang;

	$page_header = build('nodecontent', $lang, 1);
	$page_footer = build('nodecontent', $lang, 2);

	$page_contents=build('blogsummary', $lang, $default_folder, $page, $pagesize);

	$besocial=$sharebar=false;
	if ($page_contents) {
		$ilike=$node_ilike;
		$tweetit=$node_tweet;
		$plusone=$node_plusone;
		$linkedin=$node_linkedin;
		if ($tweetit) {
			$tweet_text=$node_abstract ? $node_abstract : translate('description', $lang);
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
	$edit=user_has_role('writer') ? url('folderedit', $_SESSION['user']['locale']) . '/'. 1 . '/'. 1 . '?' . 'clang=' . $lang : false;
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

