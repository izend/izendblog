<?php

/**
 *
 * @copyright  2010-2014 izend.org
 * @version    10
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
		$author=$user_name;	// $user_firstname ? $user_firstname . ' ' . $user_lastname : $user_lastname;
		$website=$user_website;
		$title=$node_title ? $node_title : $node_id;
		$created=$node_created;
		$modified=$node_modified;
		$abstract=$node_abstract;
		$summary=build('nodecontent', $lang, $node_id);
		$cloud=build('cloud', $lang, $cloud_url, $blog_id, $node_id, false, 10, compact('inclusive', 'byname', 'bycount', 'index', 'flat'));
		$id=blog_node($node_name, $lang);
		$uri=$id ? $lang . '/' . $node_name : false;
		$vote=$id ? build('vote', $lang, $id, 'node', false) : false;
		$visits=$id ? build('visits', $lang, $id, true) : false;
		$blogsummary[]=compact('author', 'website', 'title', 'uri', 'created', 'modified', 'abstract', 'cloud', 'summary', 'vote', 'visits');
	}

	$output = view('blogsummary', $lang, compact('blogsummary', 'taglist', 'count', 'page', 'pagesize'));

	return $output;
}

