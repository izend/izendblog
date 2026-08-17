<?php

/**
 *
 * @copyright  2010-2026 izend.org
 * @version    10
 * @link       http://www.izend.org
 */

require_once 'models/node.inc';

function nodecontent($lang, $node_id) {
	$contents = array();
	$r = node_get_contents($lang, $node_id);

	if ($r) {
		foreach ($r as $c) {
			if ($c['content_ignored'])
				continue;
			$type=$c['content_type'];
			switch($type) {
				case 'text':
					$s = $c['content_text_text'];
					if (!empty($s)) {
						$eval = $c['content_text_eval'] == 1 ? true : false;
						if ($eval) {
							require_once 'seval.php';
							$s = seval($s);
						}
						$text = $s;
						$contents[] = compact('type', 'text');
					}
					break;
				case 'infile':
					$infile=$c['content_infile_path'];
					if ($infile) {
						$contents[] = compact('type', 'infile');
					}
					break;
				case 'download':
					$file=$c['content_download_name'];
					if ($file) {
						$download_url = url('download', $lang) . '/' . $node_id . '/' . urlencode($file);
						$contents[] = compact('type', 'file', 'download_url');
					}
					break;
				case 'file':
					$path=$c['content_file_path'];
					$start = $c['content_file_start'];
					$end = $c['content_file_end'];
					$format=$c['content_file_format'];
					$lineno=$c['content_file_lineno'] == 1 ? true : false;
					if ($path) {
						require_once 'prettyfile.php';
						$file = pretty_file($path, $format, $start, $end, $lineno);
						if ($file) {
							head('stylesheet', 'geshi'.'/'.$format, 'screen');
							$contents[] = compact('type', 'file', 'start', 'end', 'format', 'lineno');
						}
					}
					break;
				case 'youtube':
					$id=$c['content_youtube_id'];
					$width=$c['content_youtube_width'];
					$height=$c['content_youtube_height'];
					$center=$c['content_youtube_center'] == 1 ? true : false;
					$miniature=$c['content_youtube_miniature'];
					$title=$c['content_youtube_title'];
					$autoplay = $c['content_youtube_autoplay'] == 1 ? true : false;
					if ($id and $width > 0 and $height > 0) {
						$contents[] = compact('type', 'id', 'width', 'height', 'center', 'miniature', 'title', 'autoplay');
					}
					break;
				default:
					break;
			}
		}
	}

	$output = view('nodecontent', false, compact('contents'));

	return $output;
}

