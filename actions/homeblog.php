<?php

/**
 *
 * @copyright  2010-2013 izend.org
 * @version    14
 * @link       http://www.izend.org
 */

require_once 'readarg.php';
require_once 'socialize.php';
require_once 'userhasrole.php';
require_once 'models/thread.inc';

function homeblog($lang, $arglist=false) {
	global $request_path, $with_toolbar, $sitename, $siteshot;
	global $blog_thread, $blog_pagesize;

	$request_path=$lang;

	$r = thread_get($lang, $blog_thread);
	if (!$r) {
		return run('error/internalerror', $lang);
	}
	extract($r); /* thread_abstract thread_cloud thread_image thread_ilike thread_tweet thread_plusone thread_linkedin thread_pinit */

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

	$action='none';
	if (isset($_POST['search'])) {
		$action='search';
	}

	$searchtext=$taglist=false;
	$rsearch=false;
	switch($action) {
		case 'none':
			if (!empty($arglist['q'])) {
				$searchtext=$arglist['q'];
				$taglist=explode(' ', $searchtext);
			}
			break;
		case 'search':
			if (isset($_POST['searchtext'])) {
				$searchtext=readarg($_POST['searchtext'], true, false);	// trim but DON'T strip!

				if ($searchtext) {
					global $search_distance, $search_closest;

					$taglist=cloud_match($lang, $blog_thread, $searchtext, $search_distance, $search_closest);
				}
			}
			break;
		default:
			break;
	}

	$page_contents=build('blogsummary', $lang, $blog_thread, $taglist, $blog_pagesize, $page);

	$besocial=$sharebar=false;
	$ilike=$thread_ilike;
	$tweetit=$thread_tweet;
	$plusone=$thread_plusone;
	$linkedin=$thread_linkedin;
	$pinit=$thread_pinit;
	if ($tweetit or $pinit) {
		$description=translate('description', $lang);
		if ($tweetit) {
			$tweet_text=$thread_abstract ? $thread_abstract : $description;
			$tweetit=$tweet_text ? compact('tweet_text') : true;
		}
		if ($pinit) {
			$pinit_text=$thread_abstract ? $thread_abstract : $description;
			$pinit_image=$thread_image ? $thread_image : $siteshot;
			$pinit=$pinit_text && $pinit_image ? compact('pinit_text', 'pinit_image') : false;
		}
	}
	list($besocial, $sharebar) = socialize($lang, compact('ilike', 'tweetit', 'plusone', 'linkedin', 'pinit'));

	$content = view('homeblog', false, compact('page_header', 'page_footer', 'page_contents', 'besocial'));

	$cloud_url= url('homeblog', $lang);
	$byname=$bycount=true;
	$index=false;
	$cloud = build('cloud', $lang, $cloud_url, $blog_thread, false, 10, compact('byname', 'bycount', 'index'));

	$social = view('social', $lang);
	$donate = build('donate', $lang);
	$sticker = view('slideshow', false);

	$sidebar = view('sidebar', false, compact('social', 'cloud', 'donate', 'sticker'));

	$search_text=$searchtext;
	$search_url=url('homeblog', $lang);
	$suggest_url=url('suggestblog', $lang);
	$search=compact('search_url', 'search_text', 'suggest_url');
	$edit=user_has_role('writer') ? url('threadedit', $_SESSION['user']['locale']) . '/'. $blog_thread . '?' . 'clang=' . $lang : false;
	$validate=url('homeblog', $lang);

	$banner = build('banner', $lang, $with_toolbar ? compact('search') : compact('edit', 'validate', 'search'));
	$toolbar = $with_toolbar ? build('toolbar', $lang, compact('edit', 'validate')) : false;

	$languages='homeblog';
	$legal=$contact=$account=$admin=true;
	$footer = build('footer', $lang, compact('legal', 'contact', 'account', 'admin', 'languages'));

	$output = layout('standard', compact('sharebar', 'toolbar', 'footer', 'banner', 'content', 'sidebar'));

	return $output;
}

