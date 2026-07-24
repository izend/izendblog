<?php

/**
 *
 * @copyright  2010-2026 izend.org
 * @version    11
 * @link       http://www.izend.org
 */

function besocial($lang, $components, $sharemode, $shareline=false) {
	$ilike=$tweetit=$linkedin=$pinit=$whatsapp=false;

	extract($components);	/* ilike tweetit linkedin pinit whatsapp */

	if ($ilike) {
		$ilike=view('ilike', $lang, compact('sharemode'));
	}
	if ($tweetit) {
		$tweet_text=false;
		if (is_array($tweetit)) {
			extract($tweetit);	/* tweet_text */
		}
		$tweet_text=preg_replace('/\s+/', ' ', trim($tweet_text));
		$tweetit=view('tweetit', $lang, compact('sharemode', 'tweet_text'));
	}
	if ($linkedin) {
		$linkedin=view('linkedin', $lang, compact('sharemode'));
	}
	if ($pinit) {
		$pinit_text=$pinit_image=false;
		if (is_array($pinit)) {
			extract($pinit);	/* pinit_text pinit_image */
		}
		$pinit=view('pinit', $lang, compact('sharemode', 'pinit_text', 'pinit_image'));
	}
	if ($whatsapp) {
		$whatsapp=view('whatsapp', $lang, compact('sharemode'));
	}

	$output = view('besocial', false, compact('shareline', 'sharemode', 'ilike', 'tweetit', 'linkedin', 'pinit', 'whatsapp'));

	return $output;
}

