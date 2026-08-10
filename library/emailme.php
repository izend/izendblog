<?php

/**
 *
 * @copyright  2010-2026 izend.org
 * @version    3
 * @link       http://www.izend.org
 */

require_once 'sendmail.php';

function emailme($subject, $msg, $from=false, $to=false) {
	global $webmaster, $mailer;

	if (!$from) {
		$from = $webmaster;
	}
	if (!$to) {
		$to = $webmaster;
	}

	$headers = <<<_SEP_
From: $from
MIME-Version: 1.0
Content-Type: text/plain; charset=UTF-8
Content-Transfer-Encoding: 8bit
X-Mailer: $mailer
_SEP_;

	return sendmail($to, $subject, $msg, $headers, $from);
}

