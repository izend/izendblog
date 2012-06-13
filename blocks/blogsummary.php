<?php

/**
 *
 * @copyright  2010-2012 izend.org
 * @version    4
 * @link       http://www.izend.org
 */

require_once 'models/blog.inc';

function blogsummary($lang, $blog_id, $taglist=false, $pagesize=false, $page=1) {
	$r=false;

	if ($taglist) {
		$r=blog_search($lang, $blog_id, $taglist, $pagesize, $page);
	}

	if (!$r) {
		$r=blog_summary($lang, $blog_id, $pagesize, $page);
	}

	if (!$r) {
		return false;
	}

	list($count, $nodelist)=$r;

	$cloud_url=url('homeblog', $lang);
	$inclusive=true;
	$byname=$bycount=true;
	$index=false;
	$flat=true;

	$blogsummary = array();
	foreach ($nodelist as $node) {
		extract($node);
		$author = $user_name;
		$title = $node_title;
		$uri = $lang . '/' . $node_name;
		$created = $node_created;
		$modified = $node_modified;
		$abstract = $node_abstract;
		$author = $user_name;
		$summary = build('nodecontent', $lang, $node_id);
		$cloud=build('cloud', $lang, $cloud_url, $blog_id, $node_id, 10, compact('inclusive', 'byname', 'bycount', 'index', 'flat'));
		$blogsummary[] = compact('author', 'title', 'uri', 'created', 'modified', 'abstract', 'cloud', 'summary', 'cloudlist');
	}

	$output = view('blogsummary', $lang, compact('blogsummary', 'taglist', 'count', 'page', 'pagesize'));

	return $output;
}

