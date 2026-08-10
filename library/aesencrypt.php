<?php

/**
 *
 * @copyright  2013-2026 izend.org
 * @version    5
 * @link       http://www.izend.org
 */

function aesencrypt($s, $key) {
	$cipher = 'aes-256-cbc';
	$iv_size = openssl_cipher_iv_length($cipher);
	$iv = random_bytes($iv_size);

	$crypto = @openssl_encrypt($s, $cipher, $key, 0, $iv);

	return $crypto ? $iv . $crypto : false;
}

function aesdecrypt($s, $key) {
	$cipher = 'aes-256-cbc';
	$iv_size = openssl_cipher_iv_length($cipher);

	if (strlen($s) < $iv_size)
		return false;

	$iv = substr($s, 0, $iv_size);

	$crypto = substr($s, $iv_size);

	return @openssl_decrypt($crypto, $cipher, $key, 0, $iv);
}
