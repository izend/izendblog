<?php

/**
 *
 * @copyright  2010-2026 izend.org
 * @version    3
 * @link       http://www.izend.org
 */

require_once 'sendmail.php';
require_once 'strtag.php';

function emailcrypto($text, $tag, $to, $subject, $from=false) {
	global $signature, $mailer, $webmaster;

	if (!$from) {
		$from = $webmaster;
	}

	$img=strtag($tag);

	ob_start();
	imagepng($img);
	imagedestroy($img);
	$imgdata=ob_get_contents();
	ob_end_clean();

	$sep=md5(uniqid('sep'));
	$data=chunk_split(base64_encode($imgdata));

	$headers = <<<_SEP_
From: $from
MIME-Version: 1.0
Content-Type: multipart/mixed; boundary="$sep"
X-Mailer: $mailer
_SEP_;

	$body = '';

	if ($text) {
		$body .= <<<_SEP_
--$sep
Content-Type: text/plain; charset=UTF-8
Content-Transfer-Encoding: 8bit

$text

$signature

_SEP_;
	}

	$body .= <<<_SEP_
--$sep
Content-Type: image/png; name="crypto.png"
Content-Transfer-Encoding: base64
Content-Disposition: inline; filename="crypto.png"

$data
--$sep--
_SEP_;

	return sendmail($to, $subject, $body, $headers, $from);
}

