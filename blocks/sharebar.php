<?php

/**
 *
 * @copyright  2010-2013 izend.org
 * @version    3
 * @link       http://www.izend.org
 */

function sharebar($lang, $components=false) {
	$ilike=$tweetit=$plusone=$linkedin=$pinit=false;

	extract($components);	/* ilike, tweetit, plusone, linkedin, pinit */

	$mode='bar';

	if ($ilike) {
		$ilike=view('ilike', $lang, compact('mode'));
	}
	if ($tweetit) {
		$tweet_text=false;
		if (is_array($tweetit)) {
			extract($tweetit);	/* tweet_text */
		}
		$tweetit=view('tweetit', $lang, compact('mode', 'tweet_text'));
	}
	if ($plusone) {
		$plusone=view('plusone', $lang, compact('mode'));
	}
	if ($linkedin) {
		$linkedin=view('linkedin', $lang, compact('mode'));
	}
	if ($pinit) {
		$pinit_text=$pinit_image=false;
		if (is_array($pinit)) {
			extract($pinit);	/* pinit_text pinit_image */
		}
		$pinit=view('pinit', $lang, compact('mode', 'pinit_text', 'pinit_image'));
	}

	$output = view('sharebar', false, compact('ilike', 'tweetit', 'plusone', 'linkedin', 'pinit'));

	return $output;
}

