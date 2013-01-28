<?php

/**
 *
 * @copyright  2010-2013 izend.org
 * @version    7
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

	$blogsummary=array();
	foreach ($nodelist as $node) {
		extract($node);
		$author=$user_name;
		$title=$node_title ? $node_title : $node_id;
		$created=$node_created;
		$modified=$node_modified;
		$abstract=$node_abstract;
		$author=$user_name;
		$summary=build('nodecontent', $lang, $node_id);
		$cloud=build('cloud', $lang, $cloud_url, $blog_id, $node_id, 10, compact('inclusive', 'byname', 'bycount', 'index', 'flat'));
		$id=blog_node($node_name, $lang);
		$uri=$id ? $lang . '/' . $node_name : false;
		$vote=$id ? build('vote', $lang, $id, 'node', false) : false;
		$blogsummary[]=compact('author', 'title', 'uri', 'created', 'modified', 'abstract', 'cloud', 'summary', 'vote');
	}

	$output = view('blogsummary', $lang, compact('blogsummary', 'taglist', 'count', 'page', 'pagesize'));

	return $output;
}

