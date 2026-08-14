<?php

/**
 *
 * @copyright  2010-2026 izend.org
 * @version    3
 * @link       http://www.izend.org
 */

function token_id() {
	return md5(uniqid(rand(), true));	// bin2hex(random_bytes(16))
}

