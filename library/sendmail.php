<?php

/**
 *
 * @copyright  2026 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

function sendmail($to, $subject, $body, $headers, $sender) {
	// add the -f option so the SMTP MAIL FROM address can be used by SPF authentication
	return @mail($to, $subject, $body, $headers, '-f' . $sender);
}
