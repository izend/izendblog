<?php

/**
 *
 * @copyright  2012-2013 izend.org
 * @version    3
 * @link       http://www.izend.org
 */

require_once 'isfilenameallowed.php';
require_once 'readarg.php';
require_once 'tokenid.php';
require_once 'validatefilename.php';

define('FILES_DIR', ROOT_DIR . DIRECTORY_SEPARATOR . 'files');

function upload($lang) {
	$maxfilesize=1000000;

	$action='init';
	if (isset($_POST['upload_put'])) {
		$action='upload';
	}

	$file=$name=$type=$error=false;
	$size=0;
	$token=false;

	switch($action) {
		case 'upload':
			if (isset($_POST['upload_token'])) {
				$token=readarg($_POST['upload_token']);
			}

			if (isset($_FILES['upload_file'])) {
				if (isset($_FILES['upload_file']['tmp_name'])) {
					$file=$_FILES['upload_file']['tmp_name'];
				}
				if (isset($_FILES['upload_file']['error'])) {
					$error=$_FILES['upload_file']['error'];
				}
				if (isset($_FILES['upload_file']['name'])) {
					$name=$_FILES['upload_file']['name'];
				}
				if (isset($_FILES['upload_file']['type'])) {
					$type=$_FILES['upload_file']['type'];
				}
				if (isset($_FILES['upload_file']['size'])) {
					$size=$_FILES['upload_file']['size'];
				}
			}
			break;
		default:
			break;
	}

	$bad_token=false;

	$missing_file=false;
	$bad_file=false;
	$bad_name=false;
	$bad_size=false;
	$bad_copy=false;

	$copy_error=false;

	$file_copied=false;

	switch($action) {
		case 'upload':
			if (!isset($_SESSION['upload_token']) or $token != $_SESSION['upload_token']) {
				$bad_token=true;
				break;
			}

			if (!$file) {
				$missing_file=true;
			}
			else if (!is_uploaded_file($file)) {
				$bad_file=true;
			}
			else if ($error != UPLOAD_ERR_OK) {
				$bad_copy=true;
			}
			else if ($size > $maxfilesize) {
				$bad_size=true;
			}
			else if (!validate_filename($name) or !is_filename_allowed($name)) {
				$bad_name=true;
			}

			break;
		default:
			break;
	}

	switch($action) {
		case 'upload':
			if ($bad_token or $missing_file or $bad_file or $bad_size or $bad_name or $bad_copy) {
				break;
			}

			$filecopy = FILES_DIR . DIRECTORY_SEPARATOR . $name;

			if (!@move_uploaded_file($file, $filecopy))  {
				$copy_error=true;
				break;
			}

			$file_copied=true;
			break;
		default:
			break;
	}

	$_SESSION['upload_token'] = $token = token_id();

	$errors = compact('missing_file', 'bad_file', 'bad_size', 'bad_name', 'bad_copy', 'copy_error');
	$infos = compact('file_copied');

	$output = view('upload', $lang, compact('token', 'maxfilesize', 'name', 'errors', 'infos'));

	return $output;
}

