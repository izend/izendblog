<?php

/**
 *
 * @copyright  2012 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

require_once 'models/cloud.inc';

function blog_summary($lang, $blog_id, $pagesize=false, $page=1) {
	$sqllang=db_sql_arg($lang, false);

	$tabthreadnode=db_prefix_table('thread_node');
	$tabnode=db_prefix_table('node');
	$tabnodelocale=db_prefix_table('node_locale');
	$tabuser=db_prefix_table('user');

	$where="WHERE tn.thread_id=$blog_id AND tn.ignored=0 AND nl.locale=$sqllang";

	$sql="SELECT COUNT(*) AS count FROM $tabthreadnode tn JOIN $tabnode n ON n.node_id=tn.node_id JOIN $tabnodelocale nl ON nl.node_id=tn.node_id $where";

	$r = db_query($sql);
	if (!$r) {
		return false;
	}

	$count = $r[0]['count'];

	$sql="SELECT tn.node_id, u.user_id, u.name AS user_name, UNIX_TIMESTAMP(n.created) AS node_created, UNIX_TIMESTAMP(n.modified) AS node_modified, nl.name AS node_name, nl.title AS node_title, nl.abstract AS node_abstract, nl.cloud AS node_cloud FROM $tabthreadnode tn JOIN $tabnode n ON n.node_id=tn.node_id JOIN $tabnodelocale nl ON nl.node_id=tn.node_id JOIN $tabuser u ON u.user_id=n.user_id $where ORDER BY tn.number";

	if ($pagesize) {
		$limit=($page > 1 ? ($page - 1) * $pagesize : 0) . ', ' . $pagesize;

		$sql .= " LIMIT $limit";
	}

	$r = db_query($sql);
	if (!$r) {
		return false;
	}

	return array($count, $r);
}

function blog_search($lang, $blog_id, $taglist, $pagesize=false, $page=1) {
	$r=cloud_search($lang, $blog_id, $taglist);

	if (!$r) {
		return false;
	}

	$count = count($r);

	if ($pagesize) {
		$r = array_slice($r, $page > 1 ? ($page - 1) * $pagesize : 0, $pagesize);
	}

	return array($count, $r);
}
