<?php

/**
 *
 * @copyright  2022-2026 izend.org
 * @version    2
 * @link       http://www.izend.org
 */

function array_put(&$v, $keys, $value) {
	if (!is_array($keys) || empty($keys))
		return false;

	$array=null;

	foreach ($keys as $k) {
		if (!is_array($v))
			return false;

		if (!array_key_exists($k, $v))
			$v[$k] = array();

		$array=&$v;

        $v = &$v[$k];
    } 
	   
	return $array ? $array[$k]=$value : false;
}
