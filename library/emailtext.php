<?php

/**
 *
 * @copyright  2013-2026 izend.org
 * @version    2
 * @link       http://www.izend.org
 */

require_once 'sendmail.php';

function emailtext($text, $to, $subject, $from=false) {
	global $signature, $mailer, $webmaster;

	if (!$from) {
		$from = $webmaster;
	}

	$headers = <<<_SEP_
From: $from
MIME-Version: 1.0
Content-Type: text/plain; charset=UTF-8
Content-Transfer-Encoding: 8bit
X-Mailer: $mailer
_SEP_;

	$body = <<<_SEP_
$text

$signature

_SEP_;

	return sendmail($to, $subject, $body, $headers, $from);
}

