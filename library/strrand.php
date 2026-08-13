<?php

/**
 *
 * @copyright  2010-2026 izend.org
 * @version    2
 * @link       http://www.izend.org
 */

function strrand($charset, $len) {
	$max=strlen($charset)-1;
	$s = '';

	for ($i=0; $i < $len; $i++) {
		$s .= $charset[rand(0, $max)];
	}

	return $s;
}

