<?php

/**
 *
 * @copyright  2010-2012 izend.org
 * @version    1
 * @link       http://www.izend.org
 */


function blogsummary($lang, $blog_id, $page, $pagesize=4) {
	$sqllang=db_sql_arg($lang, false);

	$tabthreadnode=db_prefix_table('thread_node');
	$tabnode=db_prefix_table('node');
	$tabnodelocale=db_prefix_table('node_locale');
	$tabnodecontent=db_prefix_table('node_content');
	$tabcontenttext=db_prefix_table('content_text');
	$tabuser=db_prefix_table('user');

	$count=0;

	$where="WHERE tn.thread_id=$blog_id AND tn.ignored=0 AND nl.locale=$sqllang";

	if ($pagesize) {
		$sql="SELECT COUNT(*) AS count FROM $tabthreadnode tn JOIN $tabnode n ON n.node_id=tn.node_id JOIN $tabnodelocale nl ON nl.node_id=tn.node_id $where";

		$r = db_query($sql);
		if (!$r) {
			return false;
		}
		$count=$r[0]['count'];

		$limit=($page - 1) * $pagesize . ', ' . $pagesize;
	}

	$sql="SELECT tn.node_id, u.name AS user_name, UNIX_TIMESTAMP(n.created) AS node_created, UNIX_TIMESTAMP(n.modified) AS node_modified, nl.name AS node_name, nl.title AS node_title, nl.abstract AS node_abstract, nl.cloud AS node_cloud, nc.content_id AS content_id, ct.text AS content_text FROM $tabthreadnode tn JOIN $tabnode n ON n.node_id=tn.node_id JOIN $tabnodelocale nl ON nl.node_id=tn.node_id LEFT JOIN $tabuser u ON u.user_id=n.user_id JOIN $tabnodecontent nc ON nc.node_id=n.node_id AND nc.number=1 JOIN $tabcontenttext ct ON ct.content_id=nc.content_id AND ct.locale=nl.locale $where ORDER BY tn.number DESC";

	if ($limit) {
		$sql .= " LIMIT $limit";
	}

	$r = db_query($sql);
	if (!$r) {
		return false;
	}

	$blogsummary = array();
	foreach ($r as $node) {
		extract($node);
		$author = $user_name;
		$title = $node_title;
		$uri = $lang . '/' . $node_name;
		$created = $node_created;
		$modified = $node_modified;
		$abstract = $node_abstract;
		$cloud = $node_cloud;
		$summary = $content_text;
		$author = $user_name;
		$blogsummary[] = compact('author', 'title', 'uri', 'created', 'modified', 'abstract', 'cloud', 'summary');
	}

	$output = view('blogsummary', $lang, compact('blogsummary', 'count', 'page', 'pagesize'));

	return $output;
}

