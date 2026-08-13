<?php

/**
 *
 * @copyright  2010-2026 izend.org
 * @version    2
 * @link       http://www.izend.org
 */

function protect_mail($s) {
	return preg_replace('/[\r\n\t]|%0a|%0d|%08|%09/i', '', $s);
}

