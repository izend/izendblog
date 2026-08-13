<?php

/**
 *
 * @copyright  2010-2026 izend.org
 * @version    3
 * @link       http://www.izend.org
 */

function is_mobile($agent=false) {
	if (!$agent) {
		require_once 'useragent.php';

		$agent=user_agent();
	}

	return $agent and preg_match('/android.*mobile|iphone|ipod|windows phone|iemobile|opera mini|opera mobi|blackberry|bb10/i', $agent) == 1;
}
