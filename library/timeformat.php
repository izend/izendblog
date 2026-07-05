<?php

/**
 *
 * @copyright  2025-2026 izend.org
 * @version    2
 * @link       http://www.izend.org
 */

function time_format($n, $d=false) {
	if ($n < 0) {
		return 0;
	}

	if ($n < 60) {
		return sprintf('%ds', $n);
	}

	if ($n < 3600) {
		return sprintf('%dm%02ds', intdiv($n, 60), $n % 60);
	}

	if ($n < 24*3600 or $d === false) {
		return sprintf('%dh%02dm%02ds', intdiv($n, 3600), intdiv($n, 60) % 60, $n % 60);
	}

	return sprintf('%d%s%02dh%02dm%02ds', intdiv($n, 24*3600), $d, intdiv($n, 3600) % 24, intdiv($n, 60) % 60, $n % 60);
}
