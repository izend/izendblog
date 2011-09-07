<?php

/**
 *
 * @copyright  2011 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

require_once 'readarg.php';
require_once 'models/user.inc';

function usersearch($lang) {
	$action='init';
	if (isset($_POST['usersearch_search']) or isset($_GET['q'])) {
		$action='search';
	}

	$pagesize=20;
	$page=1;

	$what=false;

	switch($action) {
		case 'search':
			if (isset($_POST['usersearch_what'])) {
				$what=readarg($_POST['usersearch_what']);
			}
			else if (isset($_GET['q'])) {
				$what=readarg($_GET['q']);
				if (isset($_GET['p'])) {
					$page=readarg($_GET['p']);
					if (!is_numeric($page)) {
						$page=1;
					}
				}
			}

			break;
		default:
			break;
	}

	$count=0;
	$result=false;

	switch($action) {
		case 'search':
			$r = user_search($what, $pagesize, $page);

			if (!$r) {
				break;
			}

			list($count, $result) = $r;

			$edit_url = url('adminuser', $lang);
			foreach ($result as &$r) {
				$r['edit'] = $edit_url . '/' . $r['user_id'];
			}

			break;
		default:
			break;
	}

	$output = view('usersearch', $lang, compact('what', 'page', 'pagesize', 'count', 'result'));

	return $output;
}

