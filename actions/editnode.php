<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    5
 * @link       http://www.izend.org
 */

require_once 'userhasrole.php';
require_once 'models/node.inc';

function editnode($lang, $arglist=false) {
	global $supported_languages, $with_toolbar;

	if (!user_has_role('writer')) {
		return run('error/unauthorized', $lang);
	}

	$node=false;

	if (is_array($arglist)) {
		if (isset($arglist[0])) {
			$node=$arglist[0];
		}
	}

	if (!$node) {
		return run('error/notfound', $lang);
	}

	$node_id = node_id($node);
	if (!$node_id) {
		return run('error/notfound', $lang);
	}

	$clang=false;
	foreach ($supported_languages as $slang) {
		if (isset($_POST[$slang])) {
			$clang=$slang;
			break;
		}
	}
	if (!$clang) {
		if (isset($_POST['clang'])) {
			$clang = $_POST['clang'];
		}
		else if (isset($_GET['clang'])) {
			$clang = $_GET['clang'];
		}
		else {
			$clang=$lang;
		}
		if (!in_array($clang, $supported_languages)) {
			return run('error/notfound', $lang);
		}
	}

	$node_editor = build('nodeeditor', $lang, $clang, $node_id);

	head('title', $node_id);
	head('description', false);
	head('keywords', false);
	head('robots', 'noindex, nofollow');

	$view=url('node', $clang) . '/'. $node_id . '?' . 'slang=' . $lang;

	$banner = build('banner', $lang, $with_toolbar ? false : compact('view'));

	$scroll=true;
	$toolbar = $with_toolbar ? build('toolbar', $lang, compact('view', 'scroll')) : false;

	$content = view('editing/editnode', $lang, compact('node_editor'));

	$output = layout('editing', compact('toolbar', 'banner', 'content'));

	return $output;
}

