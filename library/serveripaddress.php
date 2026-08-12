<?php

/**
 *
 * @copyright  2014-2026 izend.org
 * @version    2
 * @link       http://www.izend.org
 */

function server_ip_address() {
	return isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : false;
}

