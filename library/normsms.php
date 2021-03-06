<?php

/**
 *
 * @copyright  2010-2012 izend.org
 * @version    2
 * @link       http://www.izend.org
 */

function normsms($s) {
	/* GSM 03.38
	 @ 	£ 	$ 	¥ 	è 	é 	ù 	ì 	ò 	Ç 	? 	Ø 	ø 	CR 	Å 	å
	 ? 	_ 	? 	? 	? 	? 	? 	? 	? 	? 	? 	?   Æ 	æ 	ß 	É
	 SP ! 	" 	# 	€ 	% 	& 	' 	( 	) 	* 	+ 	, 	- 	.
	 0 	1 	2 	3 	4 	5 	6 	7 	8 	9 	: 	; 	< 	= 	> 	?
	 ¡ 	A 	B 	C 	D 	E 	F 	G 	H 	I 	J 	K 	L 	M 	N
	 P 	Q 	R 	S 	T 	U 	V 	W 	X 	Y 	Z 	Ä 	Ö 	Ñ 	Ü 	§
	 ¿ 	a 	b 	c 	d 	e 	f 	g 	h 	i 	j 	k 	l 	m 	n
	 p 	q 	r 	s 	t 	u 	v 	w 	x 	y 	z 	ä 	ö 	ñ 	ü 	à
	 */
	$from = array(
				"\r\n", "\n\r", "\n",
				'â', 'á', 'ã',
				'î', 'ï', 'í',
				'ô', 'ó', 'õ',
				'û', 'ú',
				'ê', 'ë',
				'ç',
				'À', 'Â', 'Á', 'Ã',
				'Î', 'Ï', 'Ì', 'Í',
				'Ô', 'Ò', 'Ó', 'Õ',
				'Ù', 'Û', 'Ú',
				'È', 'Ê', 'Ë',
				'`',
				'[', ']',	//	'[', ']', '{', '}', '~', '\\',
	);

	$to = array(
				"\r", "\r", "\r",
				'a', 'a', 'a',
				'i', 'i', 'i',
				'o', 'o', 'o',
				'u', 'u',
				'e', 'e',
				'c',
				'A', 'A', 'A', 'A',
				'I', 'I', 'I', 'I',
				'O', 'O', 'O', 'O',
				'U', 'U', 'U',
				'E', 'E', 'E',
				"'",
				'(', ')',	//	'(', ')', '(', ')',	'-', '/',
	);

	$gsmset = array(
				'@', '£', '$', '¥', 'è', 'é', 'ù', 'ì', 'ò', 'Ç', 'Ø', 'ø', "\r", 'Å', 'å',
				'_', 'Æ', 'æ', 'ß', 'É',
				' ', '!', '"', '#', '€', '%', '&', "'", '(', ')', '*', '+', ',', '-', '.', '/',
				'0', '1', '2', '3', '4', '5', '6', '7', '8', '9', ':', ';', '<', '=', '>', '?',
				'¡', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O',
				'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'Ä', 'Ö', 'Ñ', 'Ü', '§',
				'¿', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o',
				'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', 'ä', 'ö', 'ñ', 'ü', 'à',
				'[', ']', '{', '}', '~', '^', '|', '\\',
	);

	return preg_replace('/[^' . preg_quote(implode($gsmset), '/') . ']/', '.', str_replace($from, $to, $s));
}

