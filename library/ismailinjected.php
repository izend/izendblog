<?php

/**
 *
 * @copyright  2010-2026 izend.org
 * @version    2
 * @link       http://www.izend.org
 */

function is_mail_injected($s) {
    return preg_match('/[\r\n\t]|%0a|%0d|%08|%09/i', $s) == 1;
}
