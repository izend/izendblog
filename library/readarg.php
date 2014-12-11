<?php

/**
 *
 * @copyright  2010-2014 izend.org
 * @version    5
 * @link       http://www.izend.org
 */

function readarg($s, $trim=true, $strip=true) {
	if (is_array($s)) {
		return array_map('readarg', $s, array_fill(0, count($s), $trim), array_fill(0, count($s), $strip));
	}

	if (get_magic_quotes_gpc()) {
		$s = stripslashes($s);
	}

	if ($trim) {
		$s = trim($s);
	}

	if ($strip) {
		$s = strip_tags($s);
	}

	return $s;
}

