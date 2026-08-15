<?php

/**
 *
 * @copyright  2014-2026 izend.org
 * @version    2
 * @link       http://www.izend.org
 */

function is_https() {
	return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && ($_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https'));
}
