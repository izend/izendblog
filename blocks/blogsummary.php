<?php

/**
 *
 * @copyright  2010-2012 izend.org
 * @version    3
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

	$blogsummary = array();
	foreach ($nodelist as $node) {
		extract($node);
		$author = $user_name;
		$title = $node_title;
		$uri = $lang . '/' . $node_name;
		$created = $node_created;
		$modified = $node_modified;
		$abstract = $node_abstract;
		$cloud = $node_cloud;
		$author = $user_name;
		$summary = build('nodecontent', $lang, $node_id);
		$blogsummary[] = compact('author', 'title', 'uri', 'created', 'modified', 'abstract', 'cloud', 'summary');
	}

	$output = view('blogsummary', $lang, compact('blogsummary', 'taglist', 'count', 'page', 'pagesize'));

	return $output;
}

