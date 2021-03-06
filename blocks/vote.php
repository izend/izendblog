<?php

/**
 *
 * @copyright  2010-2012 izend.org
 * @version    5
 * @link       http://www.izend.org
 */

require_once 'models/vote.inc';

function vote($lang, $content_id, $content_type, $nomore) {
	$action='init';

	if (!$nomore) {
		if (isset($_POST['vote_plusone']) and isset($_POST['vote_id']) and $_POST['vote_id'] == $content_id and isset($_POST['vote_type']) and $_POST['vote_type'] == $content_type) {
			$action='vote';
		}
	}

	switch($action) {
		case 'vote':
			require_once 'clientipaddress.php';
			require_once 'userprofile.php';

			$ip_address=client_ip_address();
			$user_id=user_profile('id');

			$r = vote_plusone($content_type, $content_id, $lang, $ip_address, $user_id);

			break;

		default:
			break;
	}

	$vote_count=$vote_total=0;

	$r = vote_get_total_count($content_type, $content_id, $lang);
	if ($r) {
		extract($r);	// vote_count, vote_total
	}

	$output = view('vote', $lang, compact('content_type', 'content_id', 'vote_total', 'nomore'));

	return $output;
}

