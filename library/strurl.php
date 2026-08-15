<?php

/**
 *
 * @copyright  2018-2026 izend.org
 * @version    3
 * @link       http://www.izend.org
 */

function strurl($url) {
	if (is_string($url)) {
		$url = parse_url($url);
	}

	$scheme = isset($url['scheme']) ? $url['scheme'] . '://' : '';
	$host = isset($url['host']) ? $url['host'] : '';
	$port = isset($url['port']) ? ':' . $url['port'] : '';
	$user = isset($url['user']) ? rawurlencode($url['user']) : '';
	$pass = isset($url['pass']) ? ':' . rawurlencode($url['pass']) : '';
	$pass = ($user || $pass) ? "$pass@" : '';
	$path = isset($url['path']) ? implode('/', array_map('rawurlencode', explode('/', $url['path']))) : '';

	if (isset($url['query'])) {
		$query = '?' . implode('&', array_map(function($arg) {
			$parts = explode('=', $arg, 2);
			$key = urlencode($parts[0]);

			if (count($parts) == 1) {
				return $key;
			}

			return $key . '=' . urlencode($parts[1]);
		}, explode('&', $url['query'])));
	}
	else {
		$query = '';
	}

	$fragment = isset($url['fragment']) ? '#' . rawurlencode($url['fragment']) : '';

	return "$scheme$user$pass$host$port$path$query$fragment";
}
