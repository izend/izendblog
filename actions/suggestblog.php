<?php

/**
 *
 * @copyright  2012 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

require_once 'models/cloud.inc';

function suggestblog($lang, $arglist=false) {
	global $blog_thread;

	$term=isset($arglist['term']) ? $arglist['term'] : false;
	if (!$term) {
		header('HTTP/1.1 400 Bad Request');
		return false;
	}

	$r = cloud_suggest($lang, $blog_thread, $term);

	if (!$r) {
		header('HTTP/1.1 404 Not Found');
		return false;
	}

	$taglist=array();
	foreach ($r as $tag) {
		$taglist[]=$tag['tag_name'];
	}

	return json_encode($taglist);
}

