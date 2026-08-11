<?php

/**
 *
 * @copyright  2022-2026 izend.org
 * @version    3
 * @link       http://www.izend.org
 */

function array_get($v, $keys) {
	if (!is_array($keys) || empty($keys))
		return false;

	foreach ($keys as $k) {
		if (!is_array($v) || !array_key_exists($k, $v))
			return false;

        $v = $v[$k];
    } 
	   
	return $v;
}
