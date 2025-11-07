<?php

/**
 *
 * @copyright  2010-2025 izend.org
 * @version    3
 * @link       http://www.izend.org
 */

function strtag($text) {
	$len=strlen($text);

	$fontfile=ROOT_DIR  . DIRECTORY_SEPARATOR . 'font.ttf';
	$fontsize=24.0;

	$bbox = imageftbbox($fontsize, 0, $fontfile, $text);

	$w=$bbox[2]+$len*15;
	$h=40;

	$img = @imagecreatetruecolor($w, $h) or die();

	$bg=imagecolorallocate($img, 255, 255, 224);
	$fg=imagecolorallocate($img, 64, 64, 64);

	imagefill($img, 0, 0, $bg);

	// print text unevenly in a random color
	for ($x=15, $i=0; $i<$len; $i++) {
		$y = rand($h/2,$h/2+15);
		$r = rand(-45, 45);
		$fg=imagecolorallocate($img, rand(32, 128), rand(32, 128), rand(32, 128));
		imagettftext($img, $fontsize, $r, $x, $y, $fg, $fontfile, $text[$i]);
		$x += rand(25, 35);
	}

	// blur
	for ($i=0; $i<4; $i++) {
		imagefilter($img, IMG_FILTER_GAUSSIAN_BLUR);
	}

	// blur with colored dots
	for ($i=0; $i<$w*$h/2.0; $i++) {
		$color=imagecolorallocate($img, rand(128,255), rand(128,255), rand(128,255));
		imagesetpixel($img, rand(0,$w-1), rand(0,$h-1), $color);
	}

	return $img;
}
