<?php

/**
 *
 * @copyright  2010-2026 izend.org
 * @version    2
 * @link       http://www.izend.org
 */

function array_power_set($arr) {
	$r = array(array( ));

	foreach ($arr as $e) {
		foreach ($r as $c) {
			$r[] = array_merge(array($e), $c);
		}
	}

	return $r;
}

