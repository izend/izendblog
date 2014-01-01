<?php

/**
 *
 * @copyright  2010-2013 izend.org
 * @version    41
 * @link       http://www.izend.org
 */

require_once 'readarg.php';
require_once 'identicon.php';
require_once 'newpassword.php';
require_once 'validatepassword.php';
require_once 'validatedbname.php';
require_once 'validatehostname.php';
require_once 'validateipaddress.php';
require_once 'tokenid.php';
require_once 'strlogo.php';

define('DB_INC', 'db.inc');
define('CONFIG_INC', 'config.inc');
define('ALIASES_INC', 'aliases.inc');
define('INIT_DIRNAME', 'init');
define('CONFIG_DIRNAME', 'includes');
define('INIT_DIR', ROOT_DIR . DIRECTORY_SEPARATOR . INIT_DIRNAME);
define('CONFIG_DIR', ROOT_DIR . DIRECTORY_SEPARATOR . CONFIG_DIRNAME);

define('SITELOGO_PNG', 'sitelogo.png');
define('LOGOS_DIRNAME', 'logos');
define('LOGOS_DIR', ROOT_DIR . DIRECTORY_SEPARATOR . LOGOS_DIRNAME);

define('AVATARS_DIRNAME', 'avatars');
define('AVATAR_SIZE', 24);
define('AVATARS_DIR', ROOT_DIR . DIRECTORY_SEPARATOR . AVATARS_DIRNAME);

define('LOG_DIRNAME', 'log');
define('LOG_DIR', ROOT_DIR . DIRECTORY_SEPARATOR . LOG_DIRNAME);

define('SITEMAP_XML', 'sitemap.xml');

function configure($lang) {
	global $system_languages;
	global $base_url;

	$writable_files=array(
						CONFIG_DIRNAME . DIRECTORY_SEPARATOR . DB_INC,
						CONFIG_DIRNAME . DIRECTORY_SEPARATOR . CONFIG_INC,
						CONFIG_DIRNAME . DIRECTORY_SEPARATOR . ALIASES_INC,
						LOGOS_DIRNAME . DIRECTORY_SEPARATOR . SITELOGO_PNG,
						SITEMAP_XML,
						AVATARS_DIRNAME,
						LOG_DIRNAME,
						);
	$bad_write_permission=false;

	foreach ($writable_files as $fname) {
		$fpath = ROOT_DIR . DIRECTORY_SEPARATOR . $fname;
		clearstatcache(true, $fpath);
		if (!is_writable($fpath)) {
			if (!is_array($bad_write_permission)) {
				$bad_write_permission=array();
			}
			$bad_write_permission[]=$fname;
		}
	}

	$token=false;
	if (isset($_POST['configure_token'])) {
		$token=readarg($_POST['configure_token']);
	}

	$action='init';
	if (isset($_POST['configure_configure'])) {
		$action='configure';
	}

	$sitename=$webmaster='';
	$content_languages=false;
	$default_language=false;
	$db_reuse=false;
	$db_host='localhost';
	$db_admin_user=$db_admin_password='';
	$db_name=$db_user=$db_password=$db_prefix='';
	$site_admin_user=$site_admin_password='';

	switch($action) {
		case 'init':
			$sitename='izendblog.org';
			$webmaster='webmaster@izendblog.org';
			$content_languages=array($lang);
			$default_language=$lang;
			$db_reuse=false;
			$db_name='izendblog';
			$db_user='izendblog';
			$db_prefix='izendblog_';

			do {
				$db_password=newpassword(8);
			}
			while (!validate_password($db_password));

			break;

		case 'configure':
			if (isset($_POST['configure_sitename'])) {
				$sitename=readarg($_POST['configure_sitename']);
			}
			if (isset($_POST['configure_webmaster'])) {
				$webmaster=readarg($_POST['configure_webmaster']);
			}
			if (isset($_POST['configure_content_languages'])) {
				$content_languages=readarg($_POST['configure_content_languages']);
			}
			if (isset($_POST['configure_default_language'])) {
				$default_language=readarg($_POST['configure_default_language']);
			}
			if (isset($_POST['configure_db_reuse'])) {
				$db_reuse=readarg($_POST['configure_db_reuse']) == 'yes' ? true : false;
			}
			if (isset($_POST['configure_db_admin_user'])) {
				$db_admin_user=readarg($_POST['configure_db_admin_user']);
			}
			if (isset($_POST['configure_db_admin_password'])) {
				$db_admin_password=readarg($_POST['configure_db_admin_password']);
			}
			if (isset($_POST['configure_db_name'])) {
				$db_name=readarg($_POST['configure_db_name']);
			}
			if (isset($_POST['configure_db_host'])) {
				$db_host=readarg($_POST['configure_db_host']);
			}
			if (isset($_POST['configure_db_user'])) {
				$db_user=readarg($_POST['configure_db_user']);
			}
			if (isset($_POST['configure_db_password'])) {
				$db_password=readarg($_POST['configure_db_password']);
			}
			if (isset($_POST['configure_db_prefix'])) {
				$db_prefix=readarg($_POST['configure_db_prefix']);
			}
			if (isset($_POST['configure_site_admin_user'])) {
				$site_admin_user=readarg($_POST['configure_site_admin_user']);
			}
			if (isset($_POST['configure_site_admin_password'])) {
				$site_admin_password=readarg($_POST['configure_site_admin_password']);
			}
			break;
		default:
			break;
	}

	$bad_token=false;

	$missing_sitename=false;
	$missing_webmaster=false;

	$missing_content_languages=false;
	$bad_content_languages=false;
	$missing_default_language=false;
	$bad_default_language=false;

	$missing_db_admin_user=false;
	$missing_db_admin_password=false;

	$missing_db_name=false;
	$bad_db_name=false;

	$bad_db_prefix=false;

	$missing_db_host=false;
	$bad_db_host=false;

	$missing_db_user=false;
	$bad_db_user=false;
	$missing_db_password=false;
	$weak_db_password=false;

	$missing_site_admin_user=false;
	$bad_site_admin_user=false;
	$missing_site_admin_password=false;
	$weak_site_admin_password=false;

	$db_error=false;
	$file_error=false;
	$internal_error=false;

	switch($action) {
		case 'configure':
			if (!isset($_SESSION['configure_token']) or $token != $_SESSION['configure_token']) {
				$bad_token=true;
			}
			if (empty($sitename)) {
				$missing_sitename=true;
			}
			if (empty($webmaster)) {
				$missing_webmaster=true;
			}
			if (empty($content_languages)) {
				$missing_content_languages=true;
			}
			else if (!is_array($content_languages)) {
				$bad_content_languages=true;
			}
			else {
				foreach ($content_languages as $clang) {
					if (!in_array($clang, $system_languages)) {
						$bad_content_languages=true;
						break;
					}
				}
				if (empty($default_language)) {
					$default_language=$content_languages[0];
				}
				else if (!in_array($default_language, $content_languages)) {
					$bad_default_language=true;
				}
			}

			if (empty($db_name)) {
				$missing_db_name=true;
			}
			else if (!$db_reuse and !validate_db_name($db_name)) {
				$bad_db_name=true;
			}
			if (!empty($db_prefix) and !validate_db_name($db_prefix)) {
				$bad_db_prefix=true;
			}
			if (!$db_reuse) {
				if (empty($db_admin_user)) {
					$missing_db_admin_user=true;
				}
				if (empty($db_admin_password)) {
					$missing_db_admin_password=true;
				}
			}

			if (empty($db_host)) {
				$missing_db_host=true;
			}
			else if (!(validate_host_name($db_host) or validate_ip_address($db_host))) {
				$bad_db_host=true;
			}
			if (empty($db_user)) {
				$missing_db_user=true;
			}
			else if (!$db_reuse and !validate_db_name($db_user)) {
				$bad_db_user=true;
			}
			if (empty($db_password)) {
				$missing_db_password=true;
			}
			else if (!$db_reuse and !validate_password($db_password)) {
				$weak_db_password=true;
			}
			if (empty($site_admin_user)) {
				$missing_site_admin_user=true;
			}
			else if (!validate_db_name($site_admin_user)) {
				$bad_site_admin_user=true;
			}
			if (empty($site_admin_password)) {
				$missing_site_admin_password=true;
			}
			else if (!validate_password($site_admin_password)) {
				$weak_site_admin_password=true;
			}
			break;
		default:
			break;
	}

	switch($action) {
		case 'configure':
			if ($bad_token or $bad_write_permission or $missing_sitename or $missing_webmaster or $missing_content_languages or $bad_default_language or $missing_db_admin_user or $missing_db_admin_password or $missing_db_name or $bad_db_name or $missing_db_host or $bad_db_host or $missing_db_user or $bad_db_user or $missing_db_password or $weak_db_password or $missing_site_admin_user or $bad_site_admin_user or $missing_site_admin_password or $weak_site_admin_password) {
				break;
			}

			$site_admin_mail=$site_admin_user . '@' . $sitename;

			$languages=array($default_language);
			foreach ($content_languages as $clang) {
				if ($clang != $default_language) {
					$languages[]=$clang;
				}
			}

			if (!$db_reuse) {
				if (!create_db($db_admin_user, $db_admin_password, 'localhost', $db_name, $db_user, $db_password)) {
					$db_error=mysql_error();
					break;
				}
			}

			if (!init_db($db_host, $db_name, $db_user, $db_password, $db_prefix, $site_admin_user, $site_admin_password, $site_admin_mail, $default_language, $sitename)) {
				$db_error=mysql_error();
				break;
			}

			$img=identicon($site_admin_user, AVATAR_SIZE);
			@imagepng($img, AVATARS_DIR . DIRECTORY_SEPARATOR . $site_admin_user . '.png');

			$db_inc = build_db_inc($db_host, $db_name, $db_user, $db_password, $db_prefix);
			$config_inc = build_config_inc($sitename, $webmaster, $site_admin_user, 1, 'homeblog', 'page', $languages);
			$features=array('captcha', 'avatar', 'rssfeed', 'homeblog', 'suggestblog', 'contact', 'user', 'nobody', 'account', 'password', 'newuser', 'search', 'suggest', 'download', 'admin', 'adminuser', 'pagecontent', 'page', 'editpage', 'folder', 'folderedit', 'story', 'storyedit', 'book', 'bookedit', 'newsletter', 'newsletteredit', 'newslettersubscribe', 'newsletterunsubscribe', 'thread', 'threadedit', 'node', 'editnode', 'donation', 'paypalreturn', 'paypalcancel', 'sslverifyclient');
			$aliases_inc = build_aliases_inc($features, $languages);

			if (!$db_inc or !$config_inc or !$aliases_inc) {
				$internal_error=true;
				break;
			}

			if (!@file_put_contents(CONFIG_DIR . DIRECTORY_SEPARATOR . DB_INC, array('<?php', $db_inc))) {
				$file_error=true;
				break;
			}
			if (!@file_put_contents(CONFIG_DIR . DIRECTORY_SEPARATOR . CONFIG_INC, array('<?php', $config_inc))) {
				$file_error=true;
				break;
			}
			if (!@file_put_contents(CONFIG_DIR . DIRECTORY_SEPARATOR . ALIASES_INC, array("<?php", $aliases_inc))) {
				$file_error=true;
				break;
			}

			$sitemap_xml = build_sitemap_xml($sitename, $languages);
			@file_put_contents(ROOT_DIR . DIRECTORY_SEPARATOR . SITEMAP_XML, array('<?xml version="1.0" encoding="UTF-8"?>', $sitemap_xml));

			$logo = strlogo($sitename);
			@imagepng($logo, LOGOS_DIR . DIRECTORY_SEPARATOR . SITELOGO_PNG, 9, PNG_ALL_FILTERS);
			imagedestroy($logo);

			session_reopen();
			reload($base_url);

			return false;

		default:
			break;
	}

	$_SESSION['configure_token'] = $token = token_id();

	$errors = compact('bad_write_permission', 'missing_sitename', 'missing_webmaster', 'missing_content_languages', 'bad_default_language', 'missing_db_admin_user', 'missing_db_admin_password', 'missing_db_name', 'bad_db_name', 'missing_db_host', 'bad_db_host', 'bad_db_prefix', 'missing_db_user', 'bad_db_user', 'missing_db_password', 'weak_db_password', 'missing_site_admin_user', 'bad_site_admin_user', 'missing_site_admin_password', 'weak_site_admin_password');

	$output = view('configure', $lang, compact('token', 'sitename', 'webmaster', 'db_error', 'file_error', 'internal_error', 'content_languages', 'default_language', 'db_reuse', 'db_admin_user', 'db_admin_password', 'db_name', 'db_host', 'db_prefix', 'db_user', 'db_password', 'site_admin_user', 'site_admin_password', 'errors'));

	return $output;
}

function build_db_inc($db_host, $db_name, $db_user, $db_password, $db_prefix) {
	return render(INIT_DIR . DIRECTORY_SEPARATOR . DB_INC, compact('db_host', 'db_name', 'db_user', 'db_password', 'db_prefix'));
}

function build_config_inc($sitename, $webmaster, $username, $root_node, $home_action, $default_action, $languages) {
	$sitekey=function_exists('openssl_random_pseudo_bytes') ? bin2hex(openssl_random_pseudo_bytes(32)) : false;

	return render(INIT_DIR . DIRECTORY_SEPARATOR . CONFIG_INC, compact('sitename', 'webmaster', 'username', 'root_node', 'home_action', 'default_action', 'languages', 'sitekey'));
}

function build_aliases_inc($features, $languages) {
	return render(INIT_DIR . DIRECTORY_SEPARATOR . ALIASES_INC, compact('features', 'languages'));
}

function build_sitemap_xml($sitename, $languages) {
	$date=date('Y-m-d');

	return render(INIT_DIR . DIRECTORY_SEPARATOR . SITEMAP_XML, compact('sitename', 'languages', 'date'));
}

function recover_db($db_admin_user, $db_admin_password, $db_host, $db_name, $db_user) {
	$db_conn=@mysql_connect($db_host, $db_admin_user, $db_admin_password);
	if (!$db_conn) {
		return false;
	}

	$sql="DELETE FROM mysql.`user` WHERE `user`.`Host` = '$db_host' AND `user`.`User` = '$db_user'";
	@mysql_query($sql, $db_conn);

	$sql="DELETE FROM mysql.`db` WHERE `db`.`Host` = '$db_host' AND `db`.`Db` = '$db_name' AND `db`.`User` = '$db_user'";
	@mysql_query($sql, $db_conn);

	$sql="DROP DATABASE `$db_name`";
	@mysql_query($sql, $db_conn);

	return true;
}

function create_db($db_admin_user, $db_admin_password, $db_host, $db_name, $db_user, $db_password) {
	$db_conn=@mysql_connect($db_host, $db_admin_user, $db_admin_password);
	if (!$db_conn) {
		return false;
	}

	$sql="CREATE DATABASE `$db_name` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci";
	if (!@mysql_query($sql, $db_conn)) {
		return false;
	}

	$sql= <<<_SEP_
INSERT INTO mysql.`user` (`Host`, `User`, `Password`, `ssl_cipher`, `x509_issuer`, `x509_subject`)
VALUES ('$db_host', '$db_user', PASSWORD('$db_password'), '', '', '');
_SEP_;
	if (!@mysql_query($sql, $db_conn)) {
		return false;
	}

	$sql= <<<_SEP_
INSERT INTO mysql.`db` (`Host`, `Db`, `User`, `Select_priv`, `Insert_priv`, `Update_priv`, `Delete_priv`, `Create_priv`, `Drop_priv`)
VALUES ('$db_host', '$db_name', '$db_user', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y');
_SEP_;
	if (!@mysql_query($sql, $db_conn)) {
		return false;
	}

	if (!@mysql_query("FLUSH PRIVILEGES", $db_conn)) {
		return false;
	}

	if (!@mysql_close($db_conn)) {
		return false;
	}

	return true;
}

function init_db($db_host, $db_name, $db_user, $db_password, $db_prefix, $site_admin_user, $site_admin_password, $site_admin_mail, $default_language, $sitename) {
	$db_conn=@mysql_connect($db_host, $db_user, $db_password);
	if (!$db_conn) {
		return false;
	}

	if (!@mysql_select_db($db_name, $db_conn)) {
		return false;
	}

	if (!@mysql_query("SET NAMES 'utf8'", $db_conn)) {
		return false;
	}

	$sql= <<<_SEP_
CREATE TABLE `${db_prefix}comment` (
  `comment_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `node_id` int(10) unsigned NOT NULL,
  `locale` enum('en','fr') NOT NULL DEFAULT '$default_language',
  `created` datetime NOT NULL,
  `edited` datetime NOT NULL,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `ip_address` int(10) unsigned NOT NULL,
  `text` text NOT NULL,
  PRIMARY KEY (`comment_id`),
  KEY `NODE` (`node_id`,`locale`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
_SEP_;
	if (!@mysql_query($sql, $db_conn)) {
		return false;
	}

	$sql= <<<_SEP_
CREATE TABLE `${db_prefix}content_download` (
  `content_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `locale` enum('en','fr') NOT NULL DEFAULT '$default_language',
  `name` varchar(50) DEFAULT NULL,
  `path` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`content_id`,`locale`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
_SEP_;
	if (!@mysql_query($sql, $db_conn)) {
		return false;
	}

	$sql= <<<_SEP_
CREATE TABLE `${db_prefix}content_file` (
  `content_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `locale` enum('en','fr') NOT NULL DEFAULT '$default_language',
  `path` varchar(200) DEFAULT NULL,
  `start` int(5) unsigned NOT NULL DEFAULT '0',
  `end` int(5) unsigned NOT NULL DEFAULT '0',
  `format` varchar(20) DEFAULT NULL,
  `lineno` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`content_id`,`locale`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
_SEP_;
	if (!@mysql_query($sql, $db_conn)) {
		return false;
	}

	$sql= <<<_SEP_
CREATE TABLE `${db_prefix}content_infile` (
  `content_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `locale` enum('en','fr') NOT NULL DEFAULT '$default_language',
  `path` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`content_id`,`locale`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
_SEP_;
	if (!@mysql_query($sql, $db_conn)) {
		return false;
	}

	$sql= <<<_SEP_
CREATE TABLE `${db_prefix}content_longtail` (
  `content_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `locale` enum('en','fr') NOT NULL DEFAULT '$default_language',
  `file` varchar(200) DEFAULT NULL,
  `image` varchar(200) DEFAULT NULL,
  `width` int(4) unsigned NOT NULL DEFAULT '0',
  `height` int(4) unsigned NOT NULL DEFAULT '0',
  `icons` tinyint(1) NOT NULL DEFAULT '0',
  `skin` varchar(200) DEFAULT NULL,
  `controlbar` enum('none','bottom','top','over') NOT NULL DEFAULT 'none',
  `duration` int(5) unsigned NOT NULL DEFAULT '0',
  `autostart` tinyint(1) NOT NULL DEFAULT '0',
  `repeat` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`content_id`,`locale`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
_SEP_;
	if (!@mysql_query($sql, $db_conn)) {
		return false;
	}

	$sql= <<<_SEP_
CREATE TABLE `${db_prefix}content_text` (
  `content_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `locale` enum('en','fr') NOT NULL DEFAULT '$default_language',
  `text` text,
  `eval` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`content_id`,`locale`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
_SEP_;
	if (!@mysql_query($sql, $db_conn)) {
		return false;
	}

	$sql= <<<_SEP_
CREATE TABLE `${db_prefix}content_youtube` (
  `content_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `locale` enum('en','fr') NOT NULL DEFAULT '$default_language',
  `id` varchar(20) CHARACTER SET ascii COLLATE ascii_bin DEFAULT NULL,
  `width` int(4) unsigned NOT NULL DEFAULT '0',
  `height` int(4) unsigned NOT NULL DEFAULT '0',
  `autoplay` tinyint(1) NOT NULL DEFAULT '0',
  `controls` tinyint(1) NOT NULL DEFAULT '0',
  `fs` tinyint(1) NOT NULL DEFAULT '0',
  `theme` enum('light','dark') NOT NULL DEFAULT 'dark',
  `rel` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`content_id`,`locale`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
_SEP_;
	if (!@mysql_query($sql, $db_conn)) {
		return false;
	}

	$sql= <<<_SEP_
CREATE TABLE `${db_prefix}newsletter_post` (
  `thread_id` int(10) unsigned NOT NULL,
  `node_id` int(10) unsigned NOT NULL,
  `locale` enum('fr','en') NOT NULL DEFAULT '$default_language',
  `scheduled` datetime NOT NULL,
  `mailed` datetime DEFAULT NULL,
  PRIMARY KEY (`thread_id`,`node_id`,`locale`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
_SEP_;
	if (!@mysql_query($sql, $db_conn)) {
		return false;
	}

	$sql= <<<_SEP_
CREATE TABLE `${db_prefix}newsletter_user` (
  `mail` varchar(100) NOT NULL,
  `locale` enum('fr','en') NOT NULL DEFAULT '$default_language',
  `created` datetime NOT NULL,
  PRIMARY KEY (`mail`),
  KEY `locale` (`locale`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
_SEP_;
	if (!@mysql_query($sql, $db_conn)) {
		return false;
	}

	$sql= <<<_SEP_
CREATE TABLE `${db_prefix}node` (
  `node_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  `nocomment` tinyint(1) NOT NULL DEFAULT '0',
  `nomorecomment` tinyint(1) NOT NULL DEFAULT '0',
  `novote` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `nomorevote` tinyint(1) NOT NULL DEFAULT '0',
  `ilike` tinyint(1) NOT NULL DEFAULT '1',
  `tweet` tinyint(1) NOT NULL DEFAULT '1',
  `plusone` tinyint(1) NOT NULL DEFAULT '1',
  `linkedin` tinyint(1) NOT NULL DEFAULT '1',
  `pinit` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`node_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
_SEP_;
	if (!@mysql_query($sql, $db_conn)) {
		return false;
	}

	$sql= <<<_SEP_
CREATE TABLE `${db_prefix}node_locale` (
  `node_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `locale` enum('en','fr') NOT NULL DEFAULT '$default_language',
  `name` varchar(100) NOT NULL,
  `title` varchar(100) NULL default NULL,
  `abstract` text,
  `cloud` text,
  `image` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`node_id`,`locale`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
_SEP_;
	if (!@mysql_query($sql, $db_conn)) {
		return false;
	}

	$sql= <<<_SEP_
CREATE TABLE `${db_prefix}node_content` (
  `node_id` int(10) unsigned NOT NULL,
  `content_id` int(10) unsigned NOT NULL,
  `content_type` enum('text','file','download','infile','youtube','longtail') NOT NULL DEFAULT 'text',
  `number` int(3) unsigned NOT NULL,
  `ignored` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`node_id`,`content_id`,`content_type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
_SEP_;
	if (!@mysql_query($sql, $db_conn)) {
		return false;
	}

	$sql= <<<_SEP_
CREATE TABLE `${db_prefix}thread` (
  `thread_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL DEFAULT '1',
  `thread_type` enum('thread','folder','story','book','rss','newsletter') NOT NULL DEFAULT 'thread',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  `number` int(4) unsigned NOT NULL,
  `nosearch` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `nocloud` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `nocomment` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `nomorecomment` tinyint(1) NOT NULL DEFAULT '0',
  `novote` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `nomorevote` tinyint(1) NOT NULL DEFAULT '0',
  `ilike` tinyint(1) NOT NULL DEFAULT '1',
  `tweet` tinyint(1) NOT NULL DEFAULT '1',
  `plusone` tinyint(1) NOT NULL DEFAULT '1',
  `linkedin` tinyint(1) NOT NULL DEFAULT '1',
  `pinit` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`thread_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
_SEP_;
	if (!@mysql_query($sql, $db_conn)) {
		return false;
	}

	$sql= <<<_SEP_
CREATE TABLE `${db_prefix}thread_locale` (
  `thread_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `locale` enum('fr','en') NOT NULL DEFAULT 'fr',
  `name` varchar(100) NOT NULL,
  `title` varchar(100) DEFAULT NULL,
  `abstract` text,
  `cloud` text,
  `image` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`thread_id`,`locale`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
_SEP_;
	if (!@mysql_query($sql, $db_conn)) {
		return false;
	}

	$sql= <<<_SEP_
CREATE TABLE `${db_prefix}thread_node` (
  `thread_id` int(10) unsigned NOT NULL,
  `node_id` int(10) unsigned NOT NULL,
  `number` int(4) unsigned NOT NULL,
  `ignored` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`thread_id`,`node_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
_SEP_;
	if (!@mysql_query($sql, $db_conn)) {
		return false;
	}

	$sql= <<<_SEP_
CREATE TABLE `${db_prefix}tag` (
  `tag_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `locale` enum('fr','en') NOT NULL DEFAULT 'fr',
  `name` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`tag_id`,`locale`),
  UNIQUE KEY `locale` (`locale`,`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
_SEP_;
	if (!@mysql_query($sql, $db_conn)) {
		return false;
	}

	$sql= <<<_SEP_
CREATE TABLE `${db_prefix}tag_index` (
  `tag_id` int(10) unsigned NOT NULL,
  `node_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`tag_id`,`node_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
_SEP_;
	if (!@mysql_query($sql, $db_conn)) {
		return false;
	}

	$sql= <<<_SEP_
CREATE TABLE `${db_prefix}user` (
  `user_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(40) DEFAULT NULL,
  `password` char(32) CHARACTER SET ascii NOT NULL,
  `newpassword` char(32) CHARACTER SET ascii DEFAULT NULL,
  `seed` char(8) CHARACTER SET ascii NOT NULL,
  `mail` varchar(100) DEFAULT NULL,
  `website` varchar(100) DEFAULT NULL,
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `accessed` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `logged` int(10) unsigned NOT NULL DEFAULT '0',
  `locale` enum('en','fr') NOT NULL DEFAULT '$default_language',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `banned` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `mail` (`mail`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
_SEP_;
	if (!@mysql_query($sql, $db_conn)) {
		return false;
	}

	$sql= <<<_SEP_
CREATE TABLE IF NOT EXISTS `${db_prefix}user_info` (
  `user_id` int(10) unsigned NOT NULL,
  `lastname` varchar(100) DEFAULT NULL,
  `firstname` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
_SEP_;
	if (!@mysql_query($sql, $db_conn)) {
		return false;
	}

	$sql= <<<_SEP_
CREATE TABLE `${db_prefix}role` (
  `role_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(40) NOT NULL,
  PRIMARY KEY (`role_id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
_SEP_;
	if (!@mysql_query($sql, $db_conn)) {
		return false;
	}

	$sql= <<<_SEP_
CREATE TABLE `${db_prefix}user_role` (
  `user_id` int(10) unsigned NOT NULL,
  `role_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`user_id`,`role_id`),
  KEY `role` (`role_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
_SEP_;
	if (!@mysql_query($sql, $db_conn)) {
		return false;
	}

	$sql= <<<_SEP_
CREATE TABLE `${db_prefix}registry` (
  `name` varchar(100) NOT NULL,
  `value` longtext NOT NULL,
  PRIMARY KEY (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
_SEP_;
	if (!@mysql_query($sql, $db_conn)) {
		return false;
	}

	$sql= <<<_SEP_
CREATE TABLE `${db_prefix}track` (
  `track_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `time_stamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip_address` int(10) unsigned NOT NULL,
  `request_uri` varchar(255) NOT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`track_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
_SEP_;
	if (!@mysql_query($sql, $db_conn)) {
		return false;
	}

	$sql= <<<_SEP_
CREATE TABLE `${db_prefix}vote` (
  `vote_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `content_id` int(10) unsigned NOT NULL,
  `content_type` enum('node','thread','comment') NOT NULL DEFAULT 'node',
  `content_locale` enum('fr','en') NOT NULL DEFAULT 'fr',
  `created` datetime NOT NULL,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `ip_address` int(10) unsigned NOT NULL,
  `value` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`vote_id`),
  UNIQUE KEY `CONTENT` (`content_id`,`content_type`,`content_locale`,`ip_address`,`user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
_SEP_;
	if (!@mysql_query($sql, $db_conn)) {
		return false;
	}

	$sql= <<<_SEP_
INSERT INTO `${db_prefix}role` (`role_id`, `name`) VALUES
(1, 'administrator'),
(2, 'writer'),
(3, 'reader'),
(4, 'moderator'),
(5, 'member');
_SEP_;
	if (!@mysql_query($sql, $db_conn)) {
		return false;
	}

	$seed=substr(md5(uniqid()), 1, 8);

	$sql= <<<_SEP_
INSERT INTO `${db_prefix}user` (`user_id`, `name`, `password`, `seed`, `mail`, `created`, `locale`, `active`, `banned`) VALUES
(1, '$site_admin_user', MD5(CONCAT('$seed', '$site_admin_password')), '$seed', '$site_admin_mail', NOW(), '$default_language', 1, 0);
_SEP_;
	if (!@mysql_query($sql, $db_conn)) {
		return false;
	}

	$sql= <<<_SEP_
INSERT INTO `${db_prefix}user_role` (`user_id`, `role_id`) VALUES
(1, 1),
(1, 2),
(1, 3),
(1, 4);
_SEP_;
	if (!@mysql_query($sql, $db_conn)) {
		return false;
	}

	$sql= <<<_SEP_
INSERT INTO `${db_prefix}comment` (`comment_id`, `node_id`, `locale`, `created`, `edited`, `user_id`, `ip_address`, `text`) VALUES
(1, 3, 'fr', '2011-12-27 09:39:02', '2011-12-27 09:39:02', 1, 2130706433, '[p]J''essaye un commentaire avec une url : [url=http://www.izend.org]iZend[/url] ![/p]'),
(2, 3, 'fr', '2011-12-27 09:41:29', '2011-12-27 09:41:29', 1, 2130706433, '[p][u]Citation[/u] :[/p][quote]J''essaye un commentaire avec une url : [url=http://www.izend.org]iZend[/url] ![/quote]\r\n[p]Non ! On peut mettre une [b]url[/b] dans un commentaire ?\r\n[br]Dis-moi pas que c''est pas vrai ![/p]'),
(3, 3, 'en', '2011-12-27 09:53:47', '2011-12-27 09:53:47', 1, 2130706433, '[p]Let me try a comment with a url: [url=http://www.izend.org]iZend[/url]![/p]'),
(4, 3, 'en', '2011-12-27 09:57:21', '2011-12-27 09:57:21', 1, 2130706433, '[p][u]Quote[/u]:[/p][quote]Let me try a comment with a url: [url=http://www.izend.org]iZend[/url]![/quote]\r\n[p]No! One can put a [b]url[/b] in a comment?\r\n[br]Don''t tell me it''s not true![/p]');
_SEP_;
	if (!@mysql_query($sql, $db_conn)) {
		return false;
	}

	$sql= <<<_SEP_
INSERT INTO `${db_prefix}content_download` (`content_id`, `locale`, `name`, `path`) VALUES
(1, 'fr', 'sysinfo.php', 'files/sysinfo.php'),
(1, 'en', 'sysinfo.php', 'files/sysinfo.php');
_SEP_;
	if (!@mysql_query($sql, $db_conn)) {
		return false;
	}

	$sql= <<<_SEP_
INSERT INTO `${db_prefix}content_file` (`content_id`, `locale`, `path`, `start`, `end`, `format`, `lineno`) VALUES
(1, 'fr', 'files/sysinfo.php', 0, 0, 'html5', 1),
(1, 'en', 'files/sysinfo.php', 0, 0, 'html5', 1);
_SEP_;
	if (!@mysql_query($sql, $db_conn)) {
		return false;
	}

	$sql= <<<_SEP_
INSERT INTO `${db_prefix}content_infile` (`content_id`, `locale`, `path`) VALUES
(1, 'fr', 'files/sysinfo.php'),
(1, 'en', 'files/sysinfo.php'),
(2, 'fr', 'files/fr/tubelist.phtml'),
(2, 'en', 'files/en/tubelist.phtml'),
(3, 'fr', 'views/fr/link.phtml'),
(3, 'en', 'views/en/link.phtml');
_SEP_;
	if (!@mysql_query($sql, $db_conn)) {
		return false;
	}

	$sql= <<<_SEP_
INSERT INTO `${db_prefix}content_longtail` (`content_id`, `locale`, `file`, `image`, `width`, `height`, `icons`, `skin`, `controlbar`, `duration`, `autostart`, `repeat`) VALUES
(1, 'fr', '/files/sounds/smoke.mp3 /files/sounds/smoke.ogg /files/sounds/smoke.m4a', NULL, 200, 24, 0, '/longtail/simple.zip', 'bottom', 0, 0, 1),
(1, 'en', '/files/sounds/smoke.mp3 /files/sounds/smoke.ogg /files/sounds/smoke.m4a', NULL, 200, 24, 0, '/longtail/simple.zip', 'bottom', 0, 0, 1),
(2, 'fr', 'http://www.youtube.com/watch?v=BeP80btBxIE', NULL, 320, 240, 1, '/longtail/modieus.zip', 'over', 0, 0, 0),
(2, 'en', 'http://www.youtube.com/watch?v=BeP80btBxIE', NULL, 320, 240, 1, '/longtail/modieus.zip', 'over', 0, 0, 0),
(3, 'fr', 'http://www.youtube.com/watch?v=eRsGyueVLvQ&hd=1', '/files/videos/sintel.jpg', 512, 218, 0, '/longtail/glow.zip', 'over', 888, 0, 0),
(3, 'en', 'http://www.youtube.com/watch?v=eRsGyueVLvQ&hd=1', '/files/videos/sintel.jpg', 512, 218, 0, '/longtail/glow.zip', 'over', 888, 0, 0),
(4, 'fr', 'http://www.youtube.com/watch?v=eRsGyueVLvQ&hd=1', '/files/videos/sintel.jpg', 512, 218, 0, '/longtail/glow.zip', 'over', 888, 0, 0),
(4, 'en', 'http://www.youtube.com/watch?v=eRsGyueVLvQ&hd=1', '/files/videos/sintel.jpg', 512, 218, 0, '/longtail/glow.zip', 'over', 888, 0, 0);
_SEP_;
	if (!@mysql_query($sql, $db_conn)) {
		return false;
	}

	$sql= <<<_SEP_
INSERT INTO `${db_prefix}content_youtube` (`content_id`, `locale`, `id`, `width`, `height`, `autoplay`, `controls`, `fs`, `theme`, `rel`) VALUES
(1, 'fr', 'b3txQs7jEJ4', 267, 200, 0, 1, 0, 'dark', 0),
(1, 'en', 'b3txQs7jEJ4', 267, 200, 0, 1, 0, 'dark', 0),
(2, 'fr', 'b3txQs7jEJ4', 267, 200, 0, 1, 0, 'dark', 0),
(2, 'en', 'b3txQs7jEJ4', 267, 200, 0, 1, 0, 'dark', 0),
(3, 'fr', 'eRsGyueVLvQ', 512, 218, 0, 1, 1, 'dark', 1),
(3, 'en', 'eRsGyueVLvQ', 512, 218, 0, 1, 1, 'dark', 1);
_SEP_;
	if (!@mysql_query($sql, $db_conn)) {
		return false;
	}

	$sql= <<<_SEP_
INSERT INTO `${db_prefix}content_text` (`content_id`, `locale`, `text`, `eval`) VALUES
(1, 'fr', '<h3>Bienvenue</h3>\r\n<p>Lorem ipsum dolor sit amet, quaeque fabellas indoctum et vel, ut graecis urbanitas eum. Et vix assum assentior. Duo eu inermis propriae labore feugiat.</p>\r\n<p class="readmore"><a href="/fr/article/test">Voir les pages de test</a></p>\r\n<p class="left"><a href="http://www.izend.org"><img src="/logos/izend.png" alt="www.izend.org" title="iZend - Le moteur web" /></a></p>\r\n<p>Perfecto intellegat moderatius ei est. Quod consetetur has ea, id viderer delectus dignissim vel. Et sed homero gubergren.</p>\r\n<ol class="summary">\r\n<li><a href="#">Duo ridens</a></li>\r\n<li><a href="#">Commodo quaestio</a></li>\r\n<li><a href="#">Tale posidonium</a></li>\r\n</ol>\r\n<h6 class="noprint">Aliquam feugait</h6>\r\n<p>Stet choro inimicus eum ea. Nulla utinam semper an has, ex qui ferri dissentias. Ut laboramus assentior nam.</p>', 0),
(1, 'en', '<h3>Welcome</h3>\r\n<p>Lorem ipsum dolor sit amet, quaeque fabellas indoctum et vel, ut graecis urbanitas eum. Et vix assum assentior. Duo eu inermis propriae labore feugiat.</p>\r\n<p class="readmore"><a href="/en/article/test">View the test pages</a></p><p class="left"><a href="http://www.izend.org"><img src="/logos/izend.png" alt="www.izend.org" title="iZend - The web engine" /></a></p>\r\n<p>Perfecto intellegat moderatius ei est. Quod consetetur has ea, id viderer delectus dignissim vel. Et sed homero gubergren.</p>\r\n<ol class="summary">\r\n<li><a href="#">Duo ridens</a></li>\r\n<li><a href="#">Commodo quaestio</a></li>\r\n<li><a href="#">Tale posidonium</a></li>\r\n</ol>\r\n<h6 class="noprint">Aliquam feugait</h6>\r\n<p>Stet choro inimicus eum ea. Nulla utinam semper an has, ex qui ferri dissentias. Ut laboramus assentior nam.</p>', 0),
(2, 'fr', '<div class="vignette"><a href="http://www.izend.org"><img src="/logos/izend.png" alt="" title="" /></a></div>\r\n<p>Lorem ipsum dolor sit amet, alterum antiopam maluisset vis eu, et brute expetenda iracundia has. Eos animal nusquam delicata ad. Cetero legendos in pri, no usu quidam utamur. Vel quodsi voluptua cu, eam ex reque audire vidisse. Te modo omnes sea, ad detracto praesent cotidieque vim, eam quando intellegat an. Aeque erroribus mei te, ei est possit iriure.</p>\r\n<p>Texte en <b>gras</b>, en <i>italique</i>, <u>souligné</u> et <s>barré</s>.</p>\r\n<h4>H4</h4>\r\n<p>Paragraphe avec du <code>code inséré</code> dans le texte.</p>\r\n<h5>H5</h5>\r\n<p>Une série de commandes&nbsp;:</p>\r\n<pre><code>$ ls -l\r\n$ pwd</code></pre>\r\n<h6>H6</h6>\r\n<ol class="summary">\r\n<li><a href="#">Duo ridens</a></li>\r\n<li><a href="#">Tale posidonium</a></li>\r\n<li><a href="#">Cu mea ferri</a></li>\r\n</ol>\r\n<blockquote>Et scaevola principes elaboraret mea. At usu docendi epicurei, et ferri sensibus deterruisset nec, mei solet persius dignissim te. Vix velit rationibus at. Ei eum simul suscipit, assum munere recusabo vix no.</blockquote>\r\n<h6>Image</h6>\r\n<p><img src="/logos/izend.png" alt="" title="www.izend.org" /></p>\r\n<h6>Tableau</h6>\r\n<table>\r\n<thead>\r\n<tr><th>Français</th><th>Anglais</th></tr>\r\n</thead>\r\n<tbody>\r\n<tr><td>Un</td><td>One</td></tr>\r\n<tr><td>Deux</td><td>Two</td></tr>\r\n</tbody>\r\n</table>\r\n<h6>Arbre</h6>\r\n<ol class="tree">\r\n<li class="dirnode firstnode">/dossier\r\n  <ol>\r\n  <li class="dirnode">dossier</li>\r\n  <li class="dirnode">dossier\r\n    <ol>\r\n    <li class="filenode lastnode">fichier</li>\r\n    </ol>\r\n  </li>\r\n  <li class="filenode lastnode">fichier</li>\r\n  </ol>\r\n</li>\r\n</ol>\r\n<h6>Colonnes</h6>\r\n<div class="row bythree">\r\n<p>No dolor invenire adversarium nam, erat suscipit per no. Id duo summo mollis.</p>\r\n<p>Per ut illud tempor. Ut vis laboramus voluptatibus. Vel oporteat ullamcorper id, modus decore luptatum vim ea. Nec ex brute placerat, feugiat percipitur eos ea, fabulas principes ea sit.</p>\r\n<p><img class="left" src="/logos/izend.png" alt="" title="www.izend.org" />Ad eam odio evertitur neglegentur, verterem disputationi eam ex. Sed no solet homero voluptua.</p>\r\n</div>', 0),
(2, 'en', '<div class="vignette"><a href="http://www.izend.org"><img src="/logos/izend.png" alt="" title="" /></a></div>\r\n<p>Lorem ipsum dolor sit amet, alterum antiopam maluisset vis eu, et brute expetenda iracundia has. Eos animal nusquam delicata ad. Cetero legendos in pri, no usu quidam utamur. Vel quodsi voluptua cu, eam ex reque audire vidisse. Te modo omnes sea, ad detracto praesent cotidieque vim, eam quando intellegat an. Aeque erroribus mei te, ei est possit iriure.</p>\r\n<p>Text <b>bold</b>, <i>italics</i>, <u>underlined</u> and <s>striked</s>.</p>\r\n<h4>H4</h4>\r\n<p>Paragraph with some <code>code embedded</code> in the text.</p>\r\n<h5>H5</h5>\r\n<p>A series of commands:</p>\r\n<pre><code>$ ls -l\r\n$ pwd</code></pre>\r\n<h6>H6</h6>\r\n<ol class="summary">\r\n<li><a href="#">Duo ridens</a></li>\r\n<li><a href="#">Tale posidonium</a></li>\r\n<li><a href="#">Cu mea ferri</a></li>\r\n</ol>\r\n<blockquote>Et scaevola principes elaboraret mea. At usu docendi epicurei, et ferri sensibus deterruisset nec, mei solet persius dignissim te. Vix velit rationibus at. Ei eum simul suscipit, assum munere recusabo vix no.</blockquote>\r\n<h6>Image</h6>\r\n<p><img src="/logos/izend.png" alt="" title="www.izend.org" /></p>\r\n<h6>Table</h6>\r\n<table>\r\n<thead>\r\n<tr><th>French</th><th>English</th></tr>\r\n</thead>\r\n<tbody>\r\n<tr><td>Un</td><td>One</td></tr>\r\n<tr><td>Deux</td><td>Two</td></tr>\r\n</tbody>\r\n</table>\r\n<h6>Tree</h6>\r\n<ol class="tree">\r\n<li class="dirnode firstnode">/folder\r\n  <ol>\r\n  <li class="dirnode">folder</li>\r\n  <li class="dirnode">folder\r\n    <ol>\r\n    <li class="filenode lastnode">file</li>\r\n    </ol>\r\n  </li>\r\n  <li class="filenode lastnode">file</li>\r\n  </ol>\r\n</li>\r\n</ol>\r\n<h6>Columns</h6>\r\n<div class="row bythree">\r\n<p>No dolor invenire adversarium nam, erat suscipit per no. Id duo summo mollis.</p>\r\n<p>Per ut illud tempor. Ut vis laboramus voluptatibus. Vel oporteat ullamcorper id, modus decore luptatum vim ea. Nec ex brute placerat, feugiat percipitur eos ea, fabulas principes ea sit.</p>\r\n<p><img class="left" src="/logos/izend.png" alt="" title="www.izend.org" />Ad eam odio evertitur neglegentur, verterem disputationi eam ex. Sed no solet homero voluptua.</p>\r\n</div>', 0),
(3, 'fr', '<h5 class="noprint">HTML5</h5>\r\n<h6 class="noprint">Audio</h6>\r\n<p><audio controls loop>\r\n<source src="/files/sounds/smoke.ogg" type="audio/ogg" />\r\n<source src="/files/sounds/smoke.m4a" type="audio/m4a" />\r\n<source src="/files/sounds/smoke.mp3" type="audio/mpeg" />\r\n</audio>\r\n</p>\r\n<h6 class="noprint">Vidéo</h6>\r\n<p><video controls width="635" height="264" poster="http://video-js.zencoder.com/oceans-clip.png">\r\n<source src="http://video-js.zencoder.com/oceans-clip.ogv" type="video/ogg" />\r\n<source src="http://video-js.zencoder.com/oceans-clip.mp4" type="video/mp4" />\r\n<source src="http://video-js.zencoder.com/oceans-clip.webm" type="video/webm" />\r\n</video></p>\r\n<h6 class="noprint"><img src="/images/youtube.png" alt="" title="YouTube"/></h6>', 0),
(3, 'en', '<h5 class="noprint">HTML5</h5>\r\n<h6 class="noprint">Audio</h6>\r\n<p><audio controls loop>\r\n<source src="/files/sounds/smoke.ogg" type="audio/ogg" />\r\n<source src="/files/sounds/smoke.m4a" type="audio/m4a" />\r\n<source src="/files/sounds/smoke.mp3" type="audio/mpeg" />\r\n</audio>\r\n</p>\r\n<h6 class="noprint">Video</h6>\r\n<p><video controls width="635" height="264" poster="http://video-js.zencoder.com/oceans-clip.png">\r\n<source src="http://video-js.zencoder.com/oceans-clip.ogv" type="video/ogg" />\r\n<source src="http://video-js.zencoder.com/oceans-clip.mp4" type="video/mp4" />\r\n<source src="http://video-js.zencoder.com/oceans-clip.webm" type="video/webm" />\r\n</video></p>\r\n<h6 class="noprint"><img src="/images/youtube.png" alt="" title="YouTube"/></h6>', 0),
(4, 'fr', '<h6 class="noprint">Vidéo</h6>', 0),
(4, 'en', '<h6 class="noprint">Video</h6>', 0),
(5, 'fr', '<h6 class="noprint">Téléchargement</h6>', 0),
(5, 'en', '<h6 class="noprint">Download</h6>', 0),
(6, 'fr', '<h6>PHP</h6>\r\n<code>&lt;p&gt;&lt;i&gt;&lt;?php setlocale(LC_TIME, ''fr_FR.UTF-8''); \$fmt=strtoupper(substr(PHP_OS, 0, 3)) == ''WIN'' ? ''%#d %B %Y'' : ''%e %B %Y''; echo strftime(\$fmt); ?&gt;&lt;/i&gt;&lt;/p&gt;</code>\r\n<p><i><?php setlocale(LC_TIME, ''fr_FR.UTF-8''); \$fmt=strtoupper(substr(PHP_OS, 0, 3)) == ''WIN'' ? ''%#d %B %Y'' : ''%e %B %Y''; echo strftime(\$fmt); ?></i></p>', 1),
(6, 'en', '<h6>PHP</h6>\r\n<code>&lt;p&gt;&lt;i&gt;&lt;?php setlocale(LC_TIME, ''en_US.UTF-8''); \$fmt=strtoupper(substr(PHP_OS, 0, 3)) == ''WIN'' ? ''%B %#d, %Y'' : ''%B %e, %Y''; echo strftime(\$fmt); ?&gt;&lt;/i&gt;&lt;/p&gt;</code>\r\n<p><i><?php setlocale(LC_TIME, ''en_US.UTF-8''); \$fmt=strtoupper(substr(PHP_OS, 0, 3)) == ''WIN'' ? ''%B %#d, %Y'' : ''%B %e, %Y''; echo strftime(\$fmt); ?></i></p>', 1),
(7, 'fr', '<ul id="test-menubar" class="topbar menu">\r\n<li><a href="#">Lorem</a>\r\n<ul>\r\n<li><a href="#">Quaerendum</a></li>\r\n<li><a href="#">Discere</a></li>\r\n<li><a href="#">Bonorum</a></li>\r\n</ul>\r\n</li>\r\n<li><span>Ipsum</span>\r\n<ul>\r\n<li><a href="#">Petentium</a></li>\r\n<li><a href="#">Usu iuvaret</a></li>\r\n</ul>\r\n</li>\r\n<li><a href="#">Dolor</a></li>\r\n</ul>', 0),
(7, 'en', '<ul id="test-menubar" class="topbar menu">\r\n<li><a href="#">Lorem</a>\r\n<ul>\r\n<li><a href="#">Quaerendum</a></li>\r\n<li><a href="#">Discere</a></li>\r\n<li><a href="#">Bonorum</a></li>\r\n</ul>\r\n</li>\r\n<li><span>Ipsum</span>\r\n<ul>\r\n<li><a href="#">Petentium</a></li>\r\n<li><a href="#">Usu iuvaret</a></li>\r\n</ul>\r\n</li>\r\n<li><a href="#">Dolor</a></li>\r\n</ul>', 0),
(8, 'fr', '<h5>Calendrier</h5>\r\n<form action="" method="post">\r\n<p><input type="text" name="test-date" id="test-date" title="aaaa-mm-jj" /></p>\r\n</form>', 0),
(8, 'en', '<h5>Calendar</h5>\r\n<form action="" method="post">\r\n<p><input type="text" name="test-date" id="test-date" title="aaaa-mm-jj" /></p>\r\n</form>', 0),
(9, 'fr', '<?php head(''javascript'', ''jquery-ui''); ?>\r\n<?php head(''javascript'', ''jquery.ui.datepicker-fr''); ?>\r\n<?php head(''stylesheet'', ''jquery-ui'', ''screen''); ?>\r\n<script type="text/javascript">\r\n$(document).ready(function() {\r\n    $(''#test-date'').datepicker({dateFormat: ''yy-mm-dd'', autoSize: true, showAnim: ''drop'', showOn: ''both'', buttonText: ''Calendrier'', buttonImage: ''/images/theme/edit/calendar.png'', buttonImageOnly: true, minDate: ''+1d'', maxDate: ''+2m'', showOtherMonths: true, navigationAsDateFormat: true, prevText: ''MM'', nextText: ''MM'', beforeShowDay: function(date) {return [date.getDay() != 0];}});\r\n});\r\n</script>', 1),
(9, 'en', '<?php head(''javascript'', ''jquery-ui''); ?>\r\n<?php head(''stylesheet'', ''jquery-ui'', ''screen''); ?>\r\n<script type="text/javascript">\r\n$(document).ready(function() {\r\n    $(''#test-date'').datepicker({dateFormat: ''yy-mm-dd'', autoSize: true, showAnim: ''drop'', showOn: ''both'', buttonText: ''Calendar'', buttonImage: ''/images/theme/edit/calendar.png'', buttonImageOnly: true, minDate: ''+1d'', maxDate: ''+2m'', showOtherMonths: true, navigationAsDateFormat: true, prevText: ''MM'', nextText: ''MM'', beforeShowDay: function(date) {return [date.getDay() != 0];}});\r\n});\r\n</script>', 1),
(10, 'fr', '<h5>Accordéon</h5>\r\n<div id="test-accordion" style="width:240px">\r\n<h6><a href="#">Nunc tincidunt</a></h6>\r\n<ul>\r\n<li>Proin elit arcu, rutrum commodo, vehicula tempus, commodo a, risus.</li>\r\n<li>Curabitur nec arcu.</li>\r\n<li>Donec sollicitudin mi sit amet mauris.</li>\r\n</ul>\r\n<h6><a href="#">Proin dolor</a></h6>\r\n<ul>\r\n<li>Praesent in eros vestibulum mi adipiscing adipiscing.</li>\r\n<li>Aenean vel metus. Ut posuere viverra nulla.</li>\r\n</ul>\r\n<h6><a href="#">Aenean lacinia</a></h6>\r\n<ul>\r\n<li>Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos.</li>\r\n</ul>\r\n</div>', 0),
(10, 'en', '<h5>Accordion</h5>\r\n<div id="test-accordion" style="width:240px">\r\n<h6><a href="#">Nunc tincidunt</a></h6>\r\n<ul>\r\n<li>Proin elit arcu, rutrum commodo, vehicula tempus, commodo a, risus.</li>\r\n<li>Curabitur nec arcu.</li>\r\n<li>Donec sollicitudin mi sit amet mauris.</li>\r\n</ul>\r\n<h6><a href="#">Proin dolor</a></h6>\r\n<ul>\r\n<li>Praesent in eros vestibulum mi adipiscing adipiscing.</li>\r\n<li>Aenean vel metus. Ut posuere viverra nulla.</li>\r\n</ul>\r\n<h6><a href="#">Aenean lacinia</a></h6>\r\n<ul>\r\n<li>Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos.</li>\r\n</ul>\r\n</div>', 0),
(11, 'fr', '<?php head(''javascript'', ''jquery-ui''); ?>\r\n<?php head(''stylesheet'', ''jquery-ui'', ''screen''); ?>\r\n<script type="text/javascript">\r\n$(document).ready(function() {\r\n    $(''#test-accordion'').accordion({header: ''h6'', animated: ''bounceslide''});\r\n    $(''#test-accordion'').accordion(''activate'', 1);\r\n});\r\n</script>', 1),
(11, 'en', '<?php head(''javascript'', ''jquery-ui''); ?>\r\n<?php head(''stylesheet'', ''jquery-ui'', ''screen''); ?>\r\n<script type="text/javascript">\r\n$(document).ready(function() {\r\n    $(''#test-accordion'').accordion({header: ''h6'', animated: ''bounceslide''});\r\n    $(''#test-accordion'').accordion(''activate'', 1);\r\n});\r\n</script>', 1),
(12, 'fr', '<h5>Onglets</h5>\r\n<div id="test-tabs" style="max-width:80%">\r\n<ul>\r\n<li><a href="#tabs-1">Nunc tincidunt</a></li>\r\n<li><a href="#tabs-2">Proin dolor</a></li>\r\n<li><a href="#tabs-3">Aenean lacinia</a></li>\r\n</ul>\r\n<div id="tabs-1">\r\n<p>Proin elit arcu, rutrum commodo, vehicula tempus, commodo a, risus. Curabitur nec arcu. Donec sollicitudin mi sit amet mauris. Nam elementum quam ullamcorper ante. Etiam aliquet massa et lorem. Mauris dapibus lacus auctor risus. Aenean tempor ullamcorper leo. Vivamus sed magna quis ligula eleifend adipiscing. Duis orci. Aliquam sodales tortor vitae ipsum. Aliquam nulla. Duis aliquam molestie erat. Ut et mauris vel pede varius sollicitudin. Sed ut dolor nec orci tincidunt interdum. Phasellus ipsum. Nunc tristique tempus lectus.</p>\r\n</div>\r\n<div id="tabs-2">\r\n<p>Morbi tincidunt, dui sit amet facilisis feugiat, odio metus gravida ante, ut pharetra massa metus id nunc. Duis scelerisque molestie turpis. Sed fringilla, massa eget luctus malesuada, metus eros molestie lectus, ut tempus eros massa ut dolor. Aenean aliquet fringilla sem. Suspendisse sed ligula in ligula suscipit aliquam. Praesent in eros vestibulum mi adipiscing adipiscing. Morbi facilisis. Curabitur ornare consequat nunc. Aenean vel metus. Ut posuere viverra nulla. Aliquam erat volutpat. Pellentesque convallis. Maecenas feugiat, tellus pellentesque pretium posuere, felis lorem euismod felis, eu ornare leo nisi vel felis. Mauris consectetur tortor et purus.</p>\r\n</div>\r\n<div id="tabs-3">\r\n<p>Mauris eleifend est et turpis. Duis id erat. Suspendisse potenti. Aliquam vulputate, pede vel vehicula accumsan, mi neque rutrum erat, eu congue orci lorem eget lorem. Vestibulum non ante. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Fusce sodales. Quisque eu urna vel enim commodo pellentesque. Praesent eu risus hendrerit ligula tempus pretium. Curabitur lorem enim, pretium nec, feugiat nec, luctus a, lacus.</p>\r\n<p>Duis cursus. Maecenas ligula eros, blandit nec, pharetra at, semper at, magna. Nullam ac lacus. Nulla facilisi. Praesent viverra justo vitae neque. Praesent blandit adipiscing velit. Suspendisse potenti. Donec mattis, pede vel pharetra blandit, magna ligula faucibus eros, id euismod lacus dolor eget odio. Nam scelerisque. Donec non libero sed nulla mattis commodo. Ut sagittis. Donec nisi lectus, feugiat porttitor, tempor ac, tempor vitae, pede. Aenean vehicula velit eu tellus interdum rutrum. Maecenas commodo. Pellentesque nec elit. Fusce in lacus. Vivamus a libero vitae lectus hendrerit hendrerit.</p>\r\n</div>\r\n</div>', 0),
(12, 'en', '<h5>Tabs</h5>\r\n<div id="test-tabs" style="max-width:80%">\r\n<ul>\r\n<li><a href="#tabs-1">Nunc tincidunt</a></li>\r\n<li><a href="#tabs-2">Proin dolor</a></li>\r\n<li><a href="#tabs-3">Aenean lacinia</a></li>\r\n</ul>\r\n<div id="tabs-1">\r\n<p>Proin elit arcu, rutrum commodo, vehicula tempus, commodo a, risus. Curabitur nec arcu. Donec sollicitudin mi sit amet mauris. Nam elementum quam ullamcorper ante. Etiam aliquet massa et lorem. Mauris dapibus lacus auctor risus. Aenean tempor ullamcorper leo. Vivamus sed magna quis ligula eleifend adipiscing. Duis orci. Aliquam sodales tortor vitae ipsum. Aliquam nulla. Duis aliquam molestie erat. Ut et mauris vel pede varius sollicitudin. Sed ut dolor nec orci tincidunt interdum. Phasellus ipsum. Nunc tristique tempus lectus.</p>\r\n</div>\r\n<div id="tabs-2">\r\n<p>Morbi tincidunt, dui sit amet facilisis feugiat, odio metus gravida ante, ut pharetra massa metus id nunc. Duis scelerisque molestie turpis. Sed fringilla, massa eget luctus malesuada, metus eros molestie lectus, ut tempus eros massa ut dolor. Aenean aliquet fringilla sem. Suspendisse sed ligula in ligula suscipit aliquam. Praesent in eros vestibulum mi adipiscing adipiscing. Morbi facilisis. Curabitur ornare consequat nunc. Aenean vel metus. Ut posuere viverra nulla. Aliquam erat volutpat. Pellentesque convallis. Maecenas feugiat, tellus pellentesque pretium posuere, felis lorem euismod felis, eu ornare leo nisi vel felis. Mauris consectetur tortor et purus.</p>\r\n</div>\r\n<div id="tabs-3">\r\n<p>Mauris eleifend est et turpis. Duis id erat. Suspendisse potenti. Aliquam vulputate, pede vel vehicula accumsan, mi neque rutrum erat, eu congue orci lorem eget lorem. Vestibulum non ante. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Fusce sodales. Quisque eu urna vel enim commodo pellentesque. Praesent eu risus hendrerit ligula tempus pretium. Curabitur lorem enim, pretium nec, feugiat nec, luctus a, lacus.</p>\r\n<p>Duis cursus. Maecenas ligula eros, blandit nec, pharetra at, semper at, magna. Nullam ac lacus. Nulla facilisi. Praesent viverra justo vitae neque. Praesent blandit adipiscing velit. Suspendisse potenti. Donec mattis, pede vel pharetra blandit, magna ligula faucibus eros, id euismod lacus dolor eget odio. Nam scelerisque. Donec non libero sed nulla mattis commodo. Ut sagittis. Donec nisi lectus, feugiat porttitor, tempor ac, tempor vitae, pede. Aenean vehicula velit eu tellus interdum rutrum. Maecenas commodo. Pellentesque nec elit. Fusce in lacus. Vivamus a libero vitae lectus hendrerit hendrerit.</p>\r\n</div>\r\n</div>', 0),
(13, 'fr', '<?php head(''javascript'', ''jquery-ui''); ?>\r\n<?php head(''stylesheet'', ''jquery-ui'', ''screen''); ?>\r\n<?php head(''javascript'', ''jquery.cookie''); ?>\r\n<script type="text/javascript">\r\n$(''#test-tabs'').tabs({fx: { opacity: ''toggle'' }, cookie: { path: ''/'' }});\r\n</script>', 1),
(13, 'en', '<?php head(''javascript'', ''jquery-ui''); ?>\r\n<?php head(''stylesheet'', ''jquery-ui'', ''screen''); ?>\r\n<?php head(''javascript'', ''jquery.cookie''); ?>\r\n<script type="text/javascript">\r\n$(''#test-tabs'').tabs({fx: { opacity: ''toggle'' }, cookie: { path: ''/'' }});\r\n</script>', 1),
(14, 'fr', '<?php head(''javascript'', ''jquery.hoverIntent''); ?>\r\n<?php head(''javascript'', ''jquery.easing''); ?>\r\n<script type="text/javascript">\r\n$(''#test-menubar > li ul'').css({display: ''none'', left: ''auto''});\r\n$(''#test-menubar > li'').hoverIntent(function() {\r\n	$(''>ul'', this).stop(true, true).animate({height: ''show''}, 500, ''easeOutCirc'');\r\n}, function() {\r\n	$(this).css({borderBottom: ''none 0''});\r\n	$(''>ul'', this).stop(true, true).fadeOut(''fast'');\r\n});\r\n$(''#test-menubar ul li'').hoverIntent(function() {\r\n	$(this).stop(true, true).animate({paddingLeft: ''1em''}, 200, ''linear'');\r\n}, function() {\r\n	$(this).stop(true, true).animate({paddingLeft: 0}, 100, ''linear'');\r\n});\r\n</script>', 1),
(14, 'en', '<?php head(''javascript'', ''jquery.hoverIntent''); ?>\r\n<?php head(''javascript'', ''jquery.easing''); ?>\r\n<script type="text/javascript">\r\n$(''#test-menubar > li ul'').css({display: ''none'', left: ''auto''});\r\n$(''#test-menubar > li'').hoverIntent(function() {\r\n	$(''>ul'', this).stop(true, true).animate({height: ''show''}, 500, ''easeOutCirc'');\r\n}, function() {\r\n	$(this).css({borderBottom: ''none 0''});\r\n	$(''>ul'', this).stop(true, true).fadeOut(''fast'');\r\n});\r\n$(''#test-menubar ul li'').hoverIntent(function() {\r\n	$(this).stop(true, true).animate({paddingLeft: ''1em''}, 200, ''linear'');\r\n}, function() {\r\n	$(this).stop(true, true).animate({paddingLeft: 0}, 100, ''linear'');\r\n});\r\n</script>', 1),
(15, 'fr', '<p class="notice">Cliquez sur <img src="/images/theme/icons/user.png" alt="Votre compte" title="Votre compte" /> dans le pied de page pour afficher le formulaire d''identification.<br/>\r\nEntrez le nom et le mot de passe de l''administrateur du site web.<br/><br/>\r\nCliquez sur <img src="/images/theme/icons/edit.png" alt="Éditer" title="Éditer" /> dans la barre d''outils sur la page d''accueil pour entrer dans l''éditeur.<br/>\r\nCliquez sur <img src="/images/theme/icons/work.png" alt="Gestion" title="Gestion" /> dans le pied de page pour gérer votre communauté d''utilisateurs.<br/>\r\nCliquez sur <img src="/images/theme/icons/cancel.png" alt="Déconnexion" title="Déconnexion" /> pour vous déconnecter.</p>\r\n<p class="readmore"><a href="http://www.izend.org/fr/manuel/manuel-utilisateur/accueil">Lire la documentation</a></p>', 0),
(15, 'en', '<p class="notice">Click on <img src="/images/theme/icons/user.png" alt="Your account" title="Your account" /> in the footer to display the identification form.<br/>\r\nEnter the name and the password of the administrator of the website.<br/><br/>\r\nClick on <img src="/images/theme/icons/edit.png" alt="Edit" title="Edit" /> in the toolbar on the home page to enter the editor.<br/>\r\nClick on <img src="/images/theme/icons/work.png" alt="Manage" title="Manage" /> in the footer to manage your community of users.<br/>\r\nClick on <img src="/images/theme/icons/cancel.png" alt="Disconnect" title="Disconnect" /> to disconnect.</p>\r\n<p class="readmore"><a href="http://www.izend.org/en/manual/user-manual/home">Read the documentation</a></p>', 0),
(16, 'fr', '<p class="noprint">Validé avec\r\n<span class="btn_browser" id="browser_firefox" title="Firefox">Firefox</span>,\r\n<span class="btn_browser" id="browser_chrome" title="Chrome">Chrome</span>,\r\n<span class="btn_browser" id="browser_safari" title="Safari">Safari</span>,\r\n<span class="btn_browser" id="browser_opera" title="Opera">Opera</span>\r\net\r\n<span class="nowrap"><span class="btn_browser" id="browser_ie" title="Internet Explorer">Internet Explorer</span></span>.\r\n</p>', 0),
(16, 'en', '<p class="noprint">Validated with\r\n<span class="btn_browser" id="browser_firefox" title="Firefox">Firefox</span>,\r\n<span class="btn_browser" id="browser_chrome" title="Chrome">Chrome</span>,\r\n<span class="btn_browser" id="browser_safari" title="Safari">Safari</span>,\r\n<span class="btn_browser" id="browser_opera" title="Opera">Opera</span>\r\nand\r\n<span class="nowrap"><span class="btn_browser" id="browser_ie" title="Internet Explorer">Internet Explorer</span>.</span>\r\n</p>', 0),
(17, 'fr', '<p><a href="http://www.qrmii.com/"><img src="/files/images/qrmii.png" alt="" title="qrmii - 1 URL 1 QR" /></a></p>\r\n<p>Un QRmii est un code QR qui contient une URL courte qui est automatiquement redirigée vers une URL complète.\r\nFlasher un QRmii avec un smartphone affiche directement la page de l''URL d''origine.</p>', 0),
(17, 'en', '<p><a href="http://www.qrmii.com/"><img src="/files/images/qrmii.png" alt="" title="qrmii - 1 URL 1 QR" /></a></p>\r\n<p>A QRmii is QR code which contains a short URL which is automatically redirected to a complete URL.\r\nFlashing a QRmii with a smartphone directly displays the page of the original URL.</p>', 0),
(18, 'fr', '<h6>Fichier</h6>', 0),
(18, 'en', '<h6>File</h6>', 0),
(19, 'fr', '<h6>Insertion</h6>', 0),
(19, 'en', '<h6>Insertion</h6>', 0),
(20, 'fr', '<p>Téléchargez un QRmii par programme en quelques lignes de code.\r\nCréez un lien dynamique entre vos services ou vos produits et votre public.\r\nUn QRmii est simple, rapide, fiable et fun.\r\nLes applications sont infinies&nbsp;!</p>\r\n<div class="acenter"><a href="http://qrmii.com/a944d525"><img src="/files/images/qr50.png" width="50" height="50" alt="" title="http://qrmii.com/a944d525" /></a> Flashez-moi&nbsp;!</div>', 0),
(20, 'en', '<p>Download a QRmii by program in just a few lines of code.\r\nCreate a dynamic link between your services or your products and your public.\r\nA QRmii is simple, fast, reliable and fun.\r\nThe applications are infinite!</p>\r\n<div class="acenter"><a href="http://qrmii.com/a944d525"><img src="/files/images/qr50.png" width="50" height="50" alt="" title="http://qrmii.com/a944d525" /></a> Flash me!</div>', 0),
(21, 'fr', '<div class="right">', 0),
(21, 'en', '<div class="right">', 0),
(22, 'fr', '</div>\r\n<h6>Atqui noster honestatis <a href="http://www.youtube.com/watch?v=b3txQs7jEJ4" target="_blank"><img src="/images/youtube.png" alt="" title="Rolltop sur YouTube"/></a></h6>\r\n<p>Mel eu aliquando pertinacia, at sit causae cetero aliquip. Atqui noster honestatis sea id, eu illum veritus propriae per. Erat vidit dolores eos ut, ex his elit tota fuisset. Cu utroque moderatius vis, eam magna nihil ut.</p>\r\n<p>Quod quas te vel. Vim summo platonem te, ne sit tale eius simul. Pri id ipsum alienum, feugait incorrupte dissentias eum ad.</p>\r\n<div class="clear"></div>', 0),
(22, 'en', '</div>\r\n<h6>Atqui noster honestatis <a href="http://www.youtube.com/watch?v=b3txQs7jEJ4" target="_blank"><img src="/images/youtube.png" alt="" title="Rolltop on YouTube"/></a></h6>\r\n<p>Mel eu aliquando pertinacia, at sit causae cetero aliquip. Atqui noster honestatis sea id, eu illum veritus propriae per. Erat vidit dolores eos ut, ex his elit tota fuisset. Cu utroque moderatius vis, eam magna nihil ut.</p>\r\n<p>Quod quas te vel. Vim summo platonem te, ne sit tale eius simul. Pri id ipsum alienum, feugait incorrupte dissentias eum ad.</p>\r\n<div class="clear"></div>', 0),
(23, 'fr', '<p>Lorem ipsum dolor sit amet, vis ne nonumes tractatos neglegentur. In eam ludus constituam. Est cu dicat aliquid dissentias, ea esse possim adipiscing eam. Aliquam volumus accumsan id cum, justo vivendum senserit ex eos, ad per nulla oporteat.</p>', 0),
(23, 'en', '<p>Lorem ipsum dolor sit amet, vis ne nonumes tractatos neglegentur. In eam ludus constituam. Est cu dicat aliquid dissentias, ea esse possim adipiscing eam. Aliquam volumus accumsan id cum, justo vivendum senserit ex eos, ad per nulla oporteat.</p>', 0),
(24, 'fr', '<p>An mei solum molestie mandamus, diceret omittam te vim. Vis nemore veritus ne, no euismod consulatu pro, cu sit falli audiam integre. Cu sea habeo nonumy tamquam. An usu meis mutat bonorum, has enim iisque philosophia ut. Zril virtute sed ei.</p>', 0),
(24, 'en', '<p>An mei solum molestie mandamus, diceret omittam te vim. Vis nemore veritus ne, no euismod consulatu pro, cu sit falli audiam integre. Cu sea habeo nonumy tamquam. An usu meis mutat bonorum, has enim iisque philosophia ut. Zril virtute sed ei.</p>', 0),
(25, 'fr', '<p>\r\n<audio controls>\r\n<source src="/files/sounds/thanatos.ogg" type="audio/ogg" />\r\n<source src="/files/sounds/thanatos.m4a" type="audio/m4a" />\r\n<source src="/files/sounds/thanatos.mp3" type="audio/mpeg" />\r\n</audio>\r\n</p>', 0),
(25, 'en', '<p>\r\n<audio controls>\r\n<source src="/files/sounds/thanatos.ogg" type="audio/ogg" />\r\n<source src="/files/sounds/thanatos.m4a" type="audio/m4a" />\r\n<source src="/files/sounds/thanatos.mp3" type="audio/mpeg" />\r\n</audio>\r\n</p>', 0),
(26, 'fr', '<h5 class="noprint">LongTail</h5>\r\n<h6 class="noprint">Audio</h6>', 0),
(26, 'en', '<h5 class="noprint">LongTail</h5>\r\n<h6 class="noprint">Audio</h6>', 0),
(27, 'fr', '<p>Per sale clita similique ex. Eum reque persecuti temporibus id. Facilis albucius ne vim, eu cum phaedrum splendide. Est ne luptatum abhorreant mnesarchum. Brute recteque splendide ei vix.</p>', 0),
(27, 'en', '<p>Per sale clita similique ex. Eum reque persecuti temporibus id. Facilis albucius ne vim, eu cum phaedrum splendide. Est ne luptatum abhorreant mnesarchum. Brute recteque splendide ei vix.</p>', 0),
(28, 'fr', '<div class="row bythree">\r\n<p>No dolor invenire adversarium nam, erat suscipit per no. Id duo summo mollis.</p>\r\n<p>Per ut illud tempor. Ut vis laboramus voluptatibus. Vel oporteat ullamcorper id, modus decore luptatum vim ea. Nec ex brute placerat, feugiat percipitur eos ea, fabulas principes ea sit.</p>\r\n<p><img class="left" src="/logos/izend.png" alt="" title="www.izend.org" />Ad eam odio evertitur neglegentur, verterem disputationi eam ex. Sed no solet homero voluptua.</p>\r\n</div>', 0),
(28, 'en', '<div class="row bythree">\r\n<p>No dolor invenire adversarium nam, erat suscipit per no. Id duo summo mollis.</p>\r\n<p>Per ut illud tempor. Ut vis laboramus voluptatibus. Vel oporteat ullamcorper id, modus decore luptatum vim ea. Nec ex brute placerat, feugiat percipitur eos ea, fabulas principes ea sit.</p>\r\n<p><img class="left" src="/logos/izend.png" alt="" title="www.izend.org" />Ad eam odio evertitur neglegentur, verterem disputationi eam ex. Sed no solet homero voluptua.</p>\r\n</div>', 0),
(29, 'fr', '<div class="right">', 0),
(29, 'en', '<div class="right">', 0),
(30, 'fr', '</div>\r\n<h6>Atqui noster honestatis <a href="http://www.youtube.com/watch?v=b3txQs7jEJ4" target="_blank"><img src="/images/youtube.png" alt="" title="Rolltop on YouTube"/></a></h6>\r\n<p>Mel eu aliquando pertinacia, at sit causae cetero aliquip. Atqui noster honestatis sea id, eu illum veritus propriae per. Erat vidit dolores eos ut, ex his elit tota fuisset. Cu utroque moderatius vis, eam magna nihil ut.</p>\r\n<p>Quod quas te vel. Vim summo platonem te, ne sit tale eius simul. Pri id ipsum alienum, feugait incorrupte dissentias eum ad.</p>', 0),
(30, 'en', '</div>\r\n<h6>Atqui noster honestatis <a href="http://www.youtube.com/watch?v=b3txQs7jEJ4" target="_blank"><img src="/images/youtube.png" alt="" title="Rolltop on YouTube"/></a></h6>\r\n<p>Mel eu aliquando pertinacia, at sit causae cetero aliquip. Atqui noster honestatis sea id, eu illum veritus propriae per. Erat vidit dolores eos ut, ex his elit tota fuisset. Cu utroque moderatius vis, eam magna nihil ut.</p>\r\n<p>Quod quas te vel. Vim summo platonem te, ne sit tale eius simul. Pri id ipsum alienum, feugait incorrupte dissentias eum ad.</p>', 0),
(31, 'fr', '<p>Un QRmii est un code QR qui contient une URL courte qui est automatiquement redirigée vers une URL complète.\r\nFlasher un QRmii avec un smartphone affiche directement la page de l''URL d''origine.</p>\r\n<p class="acenter"><a href="http://www.qrmii.com/"><img src="/files/images/qrmii.png" alt="" title="qrmii - 1 URL 1 QR" /></a></p>', 0),
(31, 'en', '<p>A QRmii is QR code which contains a short URL which is automatically redirected to a complete URL.\r\nFlashing a QRmii with a smartphone directly displays the page of the original URL.</p>\r\n<p class="acenter"><a href="http://www.qrmii.com/"><img src="/files/images/qrmii.png" alt="" title="qrmii - 1 URL 1 QR" /></a></p>', 0),
(32, 'fr', '<div class="row bythree">\r\n<p>No dolor invenire adversarium nam, erat suscipit per no. Id duo summo mollis.</p>\r\n<p>Per ut illud tempor. Ut vis laboramus voluptatibus. Vel oporteat ullamcorper id, modus decore luptatum vim ea. Nec ex brute placerat, feugiat percipitur eos ea, fabulas principes ea sit.</p>\r\n<p><img class="left" src="/logos/izend.png" alt="" title="www.izend.org" />Ad eam odio evertitur neglegentur, verterem disputationi eam ex. Sed no solet homero voluptua.</p>\r\n</div>', 0),
(32, 'en', '<div class="row bythree">\r\n<p>No dolor invenire adversarium nam, erat suscipit per no. Id duo summo mollis.</p>\r\n<p>Per ut illud tempor. Ut vis laboramus voluptatibus. Vel oporteat ullamcorper id, modus decore luptatum vim ea. Nec ex brute placerat, feugiat percipitur eos ea, fabulas principes ea sit.</p>\r\n<p><img class="left" src="/logos/izend.png" alt="" title="www.izend.org" />Ad eam odio evertitur neglegentur, verterem disputationi eam ex. Sed no solet homero voluptua.</p>\r\n</div>', 0),
(33, 'fr', '<p>Un QRmii est un code QR qui contient une URL courte qui est automatiquement redirigée vers une URL complète. Flasher un QRmii avec un smartphone affiche directement la page de l''URL d''origine.</p>', 0),
(33, 'en', '<p>A QRmii is QR code which contains a short URL which is automatically redirected to a complete URL.\r\nFlashing a QRmii with a smartphone directly displays the page of the original URL.</p>', 0),
(34, 'fr', '<p><a href="http://www.${sitename}"><img src="/logos/sitelogo.png" alt="" title="" /></a></p>\r\n<p><a href="http://www.qrmii.com/"><img src="/files/images/qrmii.png" alt="" title="qrmii - 1 URL 1 QR" /></a></p>\r\n<p>Un QRmii est un code QR qui contient une URL courte qui est automatiquement redirigée vers une URL complète.\r\nFlasher un QRmii avec un smartphone affiche directement la page de l''URL d''origine.</p>\r\n<p><a href="http://www.${sitename}/fr/qrmii">Lire l''article</a></p>\r\n<div class="acenter"><a href="http://qrmii.com/a944d525"><img src="/files/images/qr50.png" width="50" height="50" alt="" title="http://qrmii.com/a944d525" /></a> Flashez-moi&nbsp;!</div>', 0),
(34, 'en', '<p><a href="http://www.${sitename}"><img src="/logos/sitelogo.png" alt="" title="" /></a></p>\r\n<p><a href="http://www.qrmii.com/"><img src="/files/images/qrmii.png" alt="" title="qrmii - 1 URL 1 QR" /></a></p>\r\n<p>A QRmii is QR code which contains a short URL which is automatically redirected to a complete URL.\r\nFlashing a QRmii with a smartphone directly displays the page of the original URL.</p>\r\n<p><a href="http://www.${sitename}/en/qrmii">Read the article</a></p>\r\n<div class="acenter"><a href="http://qrmii.com/a944d525"><img src="/files/images/qr50.png" width="50" height="50" alt="" title="http://qrmii.com/a944d525" /></a> Flash me!</div>', 0),
(36, 'fr', '<?php head(''javascript'', ''jquery-ui''); ?>\r\n<?php head(''stylesheet'', ''jquery-ui'', ''screen''); ?>\r\n<?php head(''javascript'', ''jquery.qtip''); ?>\r\n<?php head(''stylesheet'', ''jquery.qtip'', ''screen''); ?>', 1),
(36, 'en', '<?php head(''javascript'', ''jquery-ui''); ?>\r\n<?php head(''stylesheet'', ''jquery-ui'', ''screen''); ?>\r\n<?php head(''javascript'', ''jquery.qtip''); ?>\r\n<?php head(''stylesheet'', ''jquery.qtip'', ''screen''); ?>', 1),
(37, 'fr', '<div id="sidemenu" class="sidemenu">\r\n<div class="sidemenu-tabs">\r\n<ul>\r\n<li><a href="#sidemenu-tabs-1">Blog</a></li>\r\n<li><a href="#sidemenu-tabs-2">Dolor</a></li>\r\n<li><a href="#sidemenu-tabs-3">Lacinia</a></li>\r\n</ul>', 0),
(37, 'en', '<div id="sidemenu" class="sidemenu">\r\n<div class="sidemenu-tabs">\r\n<ul>\r\n<li><a href="#sidemenu-tabs-1">Blog</a></li>\r\n<li><a href="#sidemenu-tabs-2">Dolor</a></li>\r\n<li><a href="#sidemenu-tabs-3">Lacinia</a></li>\r\n</ul>', 0),
(38, 'fr', '<div id="sidemenu-tabs-1">\r\n<div class="sidemenu-accordion">\r\n<h6><a href="#">Sommaire</a></h6>\r\n<div>\r\n<p><a class="sidemenu-qtipbox" href="/fr/rolltop" rel="/fr/contenu/rolltop" title="Rolltop">Rolltop</a></p>\r\n<p><a class="sidemenu-qtipbox" href="/fr/qrmii" rel="/fr/contenu/qrmii" title="QRmii"><img src="/files/images/qrmii.png" alt="" title="qrmii - 1 URL 1 QR" /></a></p>\r\n<p><a class="sidemenu-qtipbox" href="/fr/lorem-ipsum-dolor" rel="/fr/contenu/lorem-ipsum-dolor" title="Lorem Ipsum Dolor"><img src="/logos/izend.png" alt="" title="iZend - Le moteur web" /></a></p>\r\n</div>\r\n<h6><a href="#">Proin dolor</a></h6>\r\n<div>\r\n<p>Praesent in eros vestibulum mi adipiscing adipiscing.\r\nAenean vel metus. Ut posuere viverra nulla.</p>\r\n</div>\r\n<h6><a href="#">Aenean lacinia</a></h6>\r\n<div>\r\n<p>Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos.</p>\r\n</div>\r\n</div>\r\n</div>', 0),
(38, 'en', '<div id="sidemenu-tabs-1">\r\n<div class="sidemenu-accordion">\r\n<h6><a href="#">Summary</a></h6>\r\n<div>\r\n<p><a class="sidemenu-qtipbox" href="/en/rolltop" rel="/en/content/rolltop" title="Rolltop">Rolltop</a></p>\r\n<p><a class="sidemenu-qtipbox" href="/en/qrmii" rel="/en/content/qrmii" title="QRmii"><img src="/files/images/qrmii.png" alt="" title="qrmii - 1 URL 1 QR" /></a></p>\r\n<p><a class="sidemenu-qtipbox" href="/en/lorem-ipsum-dolor" rel="/en/content/lorem-ipsum-dolor" title="Lorem Ipsum Dolor"><img src="/logos/izend.png" alt="" title="iZend - The web engine" /></a></p>\r\n</div>\r\n<h6><a href="#">Proin dolor</a></h6>\r\n<div>\r\n<p>Praesent in eros vestibulum mi adipiscing adipiscing.\r\nAenean vel metus. Ut posuere viverra nulla.</p>\r\n</div>\r\n<h6><a href="#">Aenean lacinia</a></h6>\r\n<div>\r\n<p>Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos.</p>\r\n</div>\r\n</div>\r\n</div>', 0),
(39, 'fr', '<div id="sidemenu-tabs-2">\r\n<p>Morbi tincidunt, dui sit amet facilisis feugiat, odio metus gravida ante, ut pharetra massa metus id nunc.</p>\r\n</div>', 0),
(39, 'en', '<div id="sidemenu-tabs-2">\r\n<p>Morbi tincidunt, dui sit amet facilisis feugiat, odio metus gravida ante, ut pharetra massa metus id nunc.</p>\r\n</div>', 0),
(40, 'fr', '<div id="sidemenu-tabs-3">\r\n<p>Mauris eleifend est et turpis. Duis id erat. Suspendisse potenti. Aliquam vulputate, pede vel vehicula accumsan, mi neque rutrum erat, eu congue orci lorem eget lorem. Vestibulum non ante.</p>\r\n</div>\r\n</div>\r\n</div>', 0),
(40, 'en', '<div id="sidemenu-tabs-3">\r\n<p>Mauris eleifend est et turpis. Duis id erat. Suspendisse potenti. Aliquam vulputate, pede vel vehicula accumsan, mi neque rutrum erat, eu congue orci lorem eget lorem. Vestibulum non ante.</p>\r\n</div>\r\n</div>\r\n</div>', 0),
(42, 'fr', '<script type="text/javascript">\r\n$(document).ready(function() {\r\n    $(''.sidemenu-tabs'').tabs({fx: { opacity: ''toggle'' } });\r\n    $(''.sidemenu-accordion'').accordion({header: ''h6'', autoHeight: false, collapsible: true, active: 0});\r\n    $(''.sidemenu-qtipbox'').each(function() {\r\n        $(this).qtip({\r\n            content: {\r\n                 ajax: {url: $(this).attr(''rel'')},\r\n            },\r\n            position: {\r\n                my: ''center'',\r\n                at: ''center'',\r\n                container: $(''#sidemenu''),\r\n                target: $(window),\r\n                adjust: {resize: true}\r\n            },\r\n           show: {\r\n                event: ''mouseenter'',\r\n                effect: false,\r\n                solo: true\r\n            },\r\n            hide: {\r\n            	target: $(''#sidemenu .qtip''),\r\n            	event: ''click'',\r\n                effect: false,\r\n                fixed: true\r\n            },\r\n            style: {\r\n                def: false,\r\n                tip: {corner: false},\r\n                width: ''480px''\r\n            }\r\n        }).click(function(event) { event.preventDefault(); });\r\n    });\r\n});\r\n</script>', 0),
(42, 'en', '<script type="text/javascript">\r\n$(document).ready(function() {\r\n    $(''.sidemenu-tabs'').tabs({fx: { opacity: ''toggle'' } });\r\n    $(''.sidemenu-accordion'').accordion({header: ''h6'', autoHeight: false, collapsible: true, active: 0});\r\n    $(''.sidemenu-qtipbox'').each(function() {\r\n        $(this).qtip({\r\n            content: {\r\n                 ajax: {url: $(this).attr(''rel'')},\r\n            },\r\n            position: {\r\n                my: ''center'',\r\n                at: ''center'',\r\n                container: $(''#sidemenu''),\r\n                target: $(window),\r\n                adjust: {resize: true}\r\n            },\r\n           show: {\r\n                event: ''mouseenter'',\r\n                effect: false,\r\n                solo: true\r\n            },\r\n            hide: {\r\n            	target: $(''#sidemenu .qtip''),\r\n            	event: ''click'',\r\n                effect: false,\r\n                fixed: true\r\n            },\r\n            style: {\r\n                def: false,\r\n                tip: {corner: false},\r\n                width: ''480px''\r\n            }\r\n        }).click(function(event) { event.preventDefault(); });\r\n    });\r\n});\r\n</script>', 0),
(43, 'fr', '<h6>Qu''est-ce qu''un QRmii ?</h6>\r\n<p>Un QRmii est un code QR qui contient une URL courte qui est automatiquement redirigée vers une URL complète.\r\nFlasher un QRmii avec un smartphone affiche directement la page de l''URL d''origine.</p>\r\n<p class="acenter"><a href="/fr/qrmii"><img src="/files/images/qrmii.png" alt="www.qrmii.com" title="www.qrmii.com" /></a></p>', 0),
(43, 'en', '<h6>What is a QRmii?</h6>\r\n<p>A QRmii is QR code which contains a short URL which is automatically redirected to a complete URL.\r\nFlashing a QRmii with a smartphone directly displays the page of the original URL.</p>\r\n<p class="acenter"><a href="/en/qrmii"><img src="/files/images/qrmii.png" alt="www.qrmii.com" title="www.qrmii.com" /></a></p>', 0),
(44, 'fr', '<h6>Atqui noster honestatis <a href="http://www.youtube.com/watch?v=b3txQs7jEJ4" target="_blank"><img src="/images/youtube.png" alt="" title="Rolltop on YouTube"/></a></h6>\r\n<p>Lorem ipsum dolor sit amet, vis ne nonumes tractatos neglegentur. In eam ludus constituam. Est cu dicat aliquid dissentias, ea esse possim adipiscing eam. Aliquam volumus accumsan id cum, justo vivendum senserit ex eos, ad per nulla oporteat.</p>\r\n<p>Mel eu aliquando pertinacia, at sit causae cetero aliquip. Atqui noster honestatis sea id, eu illum veritus propriae per. Erat vidit dolores eos ut, ex his elit tota fuisset. Cu utroque moderatius vis, eam magna nihil ut.</p>\r\n<p class="readmore"><a href="/fr/rolltop">Lire la suite</a></p>', 0),
(44, 'en', '<h6>Atqui noster honestatis <a href="http://www.youtube.com/watch?v=b3txQs7jEJ4" target="_blank"><img src="/images/youtube.png" alt="" title="Rolltop on YouTube"/></a></h6>\r\n<p>Lorem ipsum dolor sit amet, vis ne nonumes tractatos neglegentur. In eam ludus constituam. Est cu dicat aliquid dissentias, ea esse possim adipiscing eam. Aliquam volumus accumsan id cum, justo vivendum senserit ex eos, ad per nulla oporteat.</p>\r\n<p>Mel eu aliquando pertinacia, at sit causae cetero aliquip. Atqui noster honestatis sea id, eu illum veritus propriae per. Erat vidit dolores eos ut, ex his elit tota fuisset. Cu utroque moderatius vis, eam magna nihil ut.</p>\r\n<p class="readmore"><a href="/en/rolltop">Read more</a></p>', 0),
(45, 'fr', '<p><a href="http://www.izend.org"><img class="left" src="/logos/izend.png" alt="www.izend.org" title="www.izend.org" /></a>\r\nAd eam odio evertitur neglegentur, verterem disputationi eam ex.</p>\r\n<div class="clear"></div>\r\n<h6>Illud tempor</h6>\r\n<p>Per ut illud tempor. Ut vis laboramus voluptatibus. Vel oporteat ullamcorper id, modus decore luptatum vim ea. Nec ex brute placerat, feugiat percipitur eos ea, fabulas principes ea sit.</p>', 0),
(45, 'en', '<p><a href="http://www.izend.org"><img class="left" src="/logos/izend.png" alt="www.izend.org" title="www.izend.org" /></a>\r\nAd eam odio evertitur neglegentur, verterem disputationi eam ex.</p>\r\n<div class="clear"></div>\r\n<h6>Illud tempor</h6>\r\n<p>Per ut illud tempor. Ut vis laboramus voluptatibus. Vel oporteat ullamcorper id, modus decore luptatum vim ea. Nec ex brute placerat, feugiat percipitur eos ea, fabulas principes ea sit.</p>', 0);
_SEP_;
	if (!@mysql_query($sql, $db_conn)) {
		return false;
	}

	$sql= <<<_SEP_
INSERT INTO `${db_prefix}node` (`node_id`, `user_id`, `created`, `modified`, `nocomment`, `nomorecomment`, `novote`, `nomorevote`, `ilike`, `tweet`, `plusone`, `linkedin`) VALUES
(1, 1, '2011-11-18 15:44:55', '2012-06-06 17:13:12', 1, 1, 1, 1, 1, 1, 1, 1),
(2, 1, '2011-12-03 11:04:32', '2012-06-06 19:15:02', 1, 1, 1, 1, 0, 0, 0, 0),
(3, 1, '2011-12-26 22:52:00', '2012-04-10 17:01:55', 0, 1, 0, 0, 1, 1, 1, 1),
(4, 1, '2011-12-27 12:54:12', '2012-06-06 22:36:15', 0, 0, 0, 0, 0, 0, 0, 0),
(5, 1, '2011-12-29 17:28:33', '2011-12-29 17:28:33', 0, 0, 0, 0, 0, 0, 0, 0),
(6, 1, '2012-06-01 20:18:49', '2012-06-06 17:44:05', 0, 0, 0, 0, 1, 1, 1, 1),
(7, 1, '2012-05-28 11:41:18', '2012-06-06 17:50:25', 0, 0, 0, 0, 1, 1, 1, 1),
(8, 1, '2012-05-28 09:40:15', '2012-06-06 17:52:46', 0, 0, 0, 0, 1, 1, 1, 1),
(9, 1, '2012-05-25 14:49:26', '2012-06-06 17:55:20', 0, 0, 0, 0, 1, 1, 1, 1),
(10, 1, '2012-05-25 13:42:53', '2012-06-06 17:51:00', 0, 0, 0, 0, 1, 1, 1, 1),
(11, 1, '2012-05-28 09:57:38', '2012-06-06 17:50:53', 0, 0, 0, 0, 1, 1, 1, 1),
(12, 1, '2012-05-28 16:40:15', '2012-06-06 17:50:42', 0, 0, 0, 0, 1, 1, 1, 1),
(13, 1, '2012-06-01 09:42:21', '2012-06-06 17:31:49', 0, 0, 0, 0, 1, 1, 1, 1),
(14, 1, '2012-06-24 16:34:58', '2012-06-24 17:36:58', 1, 1, 1, 1, 1, 1, 1, 1),
(15, 1, '2012-08-09 11:25:13', '2012-08-09 11:47:51', 1, 1, 1, 1, 1, 1, 1, 1),
(16, 1, '2012-09-22 19:18:14', '2012-09-24 18:53:49', 0, 0, 0, 0, 1, 1, 1, 1),
(17, 1, '2012-09-23 13:06:07', '2012-09-23 22:27:36', 0, 0, 0, 0, 1, 1, 1, 1),
(18, 1, '2012-09-23 13:07:00', '2012-09-23 18:02:28', 0, 0, 0, 0, 1, 1, 1, 1),
(19, 1, '2012-09-23 13:59:28', '2012-09-23 22:28:00', 0, 0, 0, 0, 1, 1, 1, 1);
_SEP_;
	if (!@mysql_query($sql, $db_conn)) {
		return false;
	}

	$sql= <<<_SEP_
INSERT INTO `${db_prefix}node_content` (`node_id`, `content_id`, `content_type`, `number`, `ignored`) VALUES
(1, 1, 'text', 1, 0),
(1, 25, 'text', 2, 0),
(2, 15, 'text', 1, 0),
(2, 16, 'text', 2, 0),
(2, 3, 'infile', 3, 0),
(3, 2, 'text', 1, 0),
(3, 3, 'text', 2, 0),
(3, 3, 'youtube', 3, 0),
(3, 26, 'text', 4, 0),
(3, 1, 'longtail', 5, 0),
(3, 4, 'text', 6, 0),
(3, 2, 'longtail', 7, 0),
(3, 5, 'text', 8, 0),
(3, 1, 'download', 9, 0),
(3, 18, 'text', 10, 0),
(3, 1, 'file', 11, 0),
(3, 19, 'text', 12, 0),
(3, 1, 'infile', 13, 0),
(3, 6, 'text', 14, 0),
(4, 7, 'text', 1, 0),
(4, 14, 'text', 2, 0),
(5, 8, 'text', 1, 0),
(5, 9, 'text', 2, 0),
(5, 10, 'text', 3, 0),
(5, 11, 'text', 4, 0),
(5, 12, 'text', 5, 0),
(5, 13, 'text', 6, 0),
(6, 17, 'text', 1, 0),
(6, 2, 'infile', 2, 0),
(6, 20, 'text', 3, 0),
(7, 21, 'text', 1, 0),
(7, 2, 'youtube', 2, 0),
(7, 22, 'text', 3, 0),
(8, 23, 'text', 1, 0),
(8, 32, 'text', 2, 0),
(9, 24, 'text', 1, 0),
(9, 4, 'longtail', 2, 0),
(10, 27, 'text', 1, 0),
(10, 3, 'longtail', 2, 0),
(11, 28, 'text', 1, 0),
(12, 29, 'text', 1, 0),
(12, 1, 'youtube', 2, 0),
(12, 30, 'text', 3, 0),
(13, 31, 'text', 1, 0),
(14, 33, 'text', 1, 0),
(15, 34, 'text', 1, 0),
(16, 36, 'text', 1, 0),
(16, 37, 'text', 2, 0),
(16, 38, 'text', 3, 0),
(16, 39, 'text', 4, 0),
(16, 40, 'text', 5, 0),
(16, 42, 'text', 6, 0),
(17, 43, 'text', 1, 0),
(18, 44, 'text', 1, 0),
(19, 45, 'text', 1, 0);
_SEP_;
	if (!@mysql_query($sql, $db_conn)) {
		return false;
	}

	$sql= <<<_SEP_
INSERT INTO `${db_prefix}node_locale` (`node_id`, `locale`, `name`, `title`, `abstract`, `cloud`) VALUES
(1, 'fr', 'haut', 'Haut', 'La version spécialisée d''iZend pour écrire un blog.', 'iZend blog moteur web'),
(1, 'en', 'top', 'Top', 'The specialized version of iZend for writing a blog.', 'iZend blog web engine'),
(2, 'fr', 'bas', 'Bas', NULL, 'identification édition'),
(2, 'en', 'bottom', 'Bottom', NULL, 'identification editing'),
(3, 'fr', 'contenus', 'Contenus', NULL, 'contenu texte PHP insertion fichier téléchargement audio vidéo LongTail YouTube HTML5'),
(3, 'en', 'contents', 'Contents', NULL, 'content text PHP insertion file download audio video LongTail YouTube HTML5'),
(4, 'fr', 'menu', 'Menu', 'Un menu en pur CSS avec des animations en jQuery.', 'jQuery menu menubar'),
(4, 'en', 'menu', 'Menu', 'A menu in pure CSS with animations in jQuery.', 'jQuery menu menubar'),
(5, 'fr', 'jquery-ui', 'jQuery UI', 'Des composants jQuery UI dans le style du site web.', 'jQuery UI calendrier onglet accordéon'),
(5, 'en', 'jquery-ui', 'jQuery UI', 'jQuery UI components in the style of the website.', 'jQuery UI calendar tab accordion'),
(6, 'fr', 'qrmii', 'Qu''est-ce qu''un QRmii ?', NULL, 'QRmii QR URL redirection'),
(6, 'en', 'qrmii', 'What is a QRmii?', NULL, 'QRmii QR URL redirection'),
(7, 'fr', 'rolltop', 'Rolltop', NULL, 'Rolltop YouTube latin'),
(7, 'en', 'rolltop', 'Rolltop', NULL, 'Rolltop YouTube latin'),
(8, 'fr', 'lorem-ipsum-dolor', 'Lorem Ipsum Dolor', NULL, 'latin'),
(8, 'en', 'lorem-ipsum-dolor', 'Lorem Ipsum Dolor', NULL, 'latin'),
(9, 'fr', 'sintel', 'Sintel', NULL, 'Sintel vidéo YouTube latin'),
(9, 'en', 'sintel', 'Sintel', NULL, 'Sintel video YouTube latin'),
(10, 'fr', 'sintel', 'Sintel', NULL, 'Sintel vidéo YouTube latin'),
(10, 'en', 'sintel', 'Sintel', NULL, 'Sintel video YouTube latin'),
(11, 'fr', 'lorem-ipsum-dolor', 'Lorem Ipsum Dolor', NULL, 'latin'),
(11, 'en', 'lorem-ipsum-dolor', 'Lorem Ipsum Dolor', NULL, 'latin'),
(12, 'fr', 'rolltop', 'Rolltop', NULL, 'Rolltop YouTube latin'),
(12, 'en', 'rolltop', 'Rolltop', NULL, 'Rolltop YouTube latin'),
(13, 'fr', 'qrmii', 'Qu''est-ce qu''un QRmii ?', NULL, 'QRmii QR URL redirection'),
(13, 'en', 'qrmii', 'What is a QRmii?', NULL, 'QRmii QR URL redirection'),
(14, 'fr', 'qrmii', 'Qu''est-ce qu''un QRmii ?', NULL, NULL),
(14, 'en', 'qrmii', 'What is a QRmii?', NULL, NULL),
(15, 'fr', 'qrmii', 'Qu''est-ce qu''un QRmii ?', 'Un QRmii est un code QR qui contient une URL courte qui est automatiquement redirigée vers une URL complète.', 'QRmii QR URL redirection'),
(15, 'en', 'qrmii', 'What is a QRmii?', 'A QRmii is QR code which contains a short URL which is automatically redirected to a complete URL.', 'QRmii QR URL redirection'),
(16, 'fr', 'qtip', 'qTip', 'Menu vertical en accordéon en jQuery UI qui affiche des contenus avec qTip.', 'jQuery qTip menu accordéon onglet Ajax'),
(16, 'en', 'qtip', 'qTip', 'Vertical accordion menu in jQuery UI which displays contents with qTip.', 'jQuery qTip menu accordion tab Ajax'),
(17, 'fr', 'qrmii', 'QRmii', NULL, NULL),
(17, 'en', 'qrmii', 'QRmii', NULL, NULL),
(18, 'fr', 'rolltop', 'Rolltop', NULL, NULL),
(18, 'en', 'rolltop', 'Rolltop', NULL, NULL),
(19, 'fr', 'lorem-ipsum-dolor', 'Lorem Ipsum Dolor', NULL, NULL),
(19, 'en', 'lorem-ipsum-dolor', 'Lorem Ipsum Dolor', NULL, NULL);
_SEP_;
	if (!@mysql_query($sql, $db_conn)) {
		return false;
	}
	$sql= <<<_SEP_
INSERT INTO `${db_prefix}tag` (`tag_id`, `locale`, `name`) VALUES
(1, 'en', 'iZend'),
(2, 'en', 'blog'),
(3, 'en', 'web'),
(4, 'en', 'engine'),
(5, 'fr', 'iZend'),
(6, 'fr', 'blog'),
(7, 'fr', 'moteur'),
(8, 'fr', 'web'),
(9, 'en', 'identification'),
(10, 'en', 'editing'),
(11, 'fr', 'identification'),
(12, 'fr', 'édition'),
(13, 'en', 'content'),
(14, 'en', 'text'),
(15, 'en', 'PHP'),
(16, 'en', 'insertion'),
(17, 'en', 'file'),
(18, 'en', 'download'),
(19, 'en', 'audio'),
(20, 'en', 'video'),
(21, 'en', 'LongTail'),
(22, 'en', 'YouTube'),
(23, 'en', 'HTML5'),
(24, 'fr', 'contenu'),
(25, 'fr', 'texte'),
(26, 'fr', 'PHP'),
(27, 'fr', 'insertion'),
(28, 'fr', 'fichier'),
(29, 'fr', 'téléchargement'),
(30, 'fr', 'audio'),
(31, 'fr', 'vidéo'),
(32, 'fr', 'LongTail'),
(33, 'fr', 'YouTube'),
(34, 'fr', 'HTML5'),
(35, 'en', 'menu'),
(36, 'en', 'menubar'),
(37, 'en', 'jQuery'),
(38, 'fr', 'menu'),
(39, 'fr', 'menubar'),
(40, 'fr', 'jQuery'),
(41, 'en', 'UI'),
(42, 'en', 'calendar'),
(43, 'en', 'tab'),
(44, 'en', 'accordion'),
(45, 'fr', 'UI'),
(46, 'fr', 'calendrier'),
(47, 'fr', 'onglet'),
(48, 'fr', 'accordéon'),
(49, 'en', 'qTip'),
(50, 'en', 'Ajax'),
(51, 'fr', 'qTip'),
(52, 'fr', 'Ajax'),
(53, 'en', 'QRmii'),
(54, 'en', 'QR'),
(55, 'en', 'URL'),
(56, 'en', 'redirection'),
(57, 'fr', 'QRmii'),
(58, 'fr', 'QR'),
(59, 'fr', 'URL'),
(60, 'fr', 'redirection'),
(61, 'en', 'Rolltop'),
(62, 'en', 'latin'),
(63, 'fr', 'Rolltop'),
(64, 'fr', 'latin'),
(65, 'en', 'Sintel'),
(66, 'fr', 'Sintel');
_SEP_;
	if (!@mysql_query($sql, $db_conn)) {
		return false;
	}

	$sql= <<<_SEP_
INSERT INTO `${db_prefix}tag_index` (`tag_id`, `node_id`) VALUES
(1, 1),
(2, 1),
(3, 1),
(4, 1),
(5, 1),
(6, 1),
(7, 1),
(8, 1),
(9, 2),
(10, 2),
(11, 2),
(12, 2),
(13, 3),
(14, 3),
(15, 3),
(16, 3),
(17, 3),
(18, 3),
(19, 3),
(20, 3),
(20, 9),
(20, 10),
(21, 3),
(22, 3),
(22, 7),
(22, 9),
(22, 10),
(22, 12),
(23, 3),
(24, 3),
(25, 3),
(26, 3),
(27, 3),
(28, 3),
(29, 3),
(30, 3),
(31, 3),
(31, 9),
(31, 10),
(32, 3),
(33, 3),
(33, 7),
(33, 9),
(33, 10),
(33, 12),
(34, 3),
(35, 4),
(35, 16),
(36, 4),
(37, 4),
(37, 5),
(37, 16),
(38, 4),
(38, 16),
(39, 4),
(40, 4),
(40, 5),
(40, 16),
(41, 5),
(41, 16),
(42, 5),
(43, 5),
(43, 16),
(44, 5),
(44, 16),
(45, 5),
(45, 16),
(46, 5),
(47, 5),
(47, 16),
(48, 5),
(48, 16),
(49, 16),
(50, 16),
(51, 16),
(52, 16),
(53, 6),
(53, 13),
(53, 15),
(54, 6),
(54, 13),
(54, 15),
(55, 6),
(55, 13),
(55, 15),
(56, 6),
(56, 13),
(56, 15),
(57, 6),
(57, 13),
(57, 15),
(58, 6),
(58, 13),
(58, 15),
(59, 6),
(59, 13),
(59, 15),
(60, 6),
(60, 13),
(60, 15),
(61, 7),
(61, 12),
(62, 7),
(62, 8),
(62, 9),
(62, 10),
(62, 11),
(62, 12),
(63, 7),
(63, 12),
(64, 7),
(64, 8),
(64, 9),
(64, 10),
(64, 11),
(64, 12),
(65, 9),
(65, 10),
(66, 9),
(66, 10);
_SEP_;
	if (!@mysql_query($sql, $db_conn)) {
		return false;
	}

	$sql= <<<_SEP_
INSERT INTO `${db_prefix}thread` (`thread_id`, `user_id`, `thread_type`, `created`, `modified`, `number`, `nosearch`, `nocloud`, `nocomment`, `nomorecomment`, `novote`, `nomorevote`, `ilike`, `tweet`, `plusone`, `linkedin`) VALUES
(1, 1, 'thread', '2011-12-26 15:44:55', '2012-06-06 17:14:17', 1, 0, 0, 0, 0, 0, 0, 1, 1, 1, 1),
(2, 1, 'story', '2011-12-26 21:32:47', '2012-01-24 21:57:34', 2, 0, 0, 0, 0, 0, 0, 1, 1, 1, 1),
(3, 1, 'folder', '2012-05-13 17:14:32', '2012-06-06 17:14:39', 3, 0, 0, 1, 1, 1, 1, 1, 1, 1, 1),
(4, 1, 'folder', '2012-05-13 22:50:17', '2012-06-06 17:51:22', 4, 0, 0, 0, 0, 0, 0, 1, 1, 1, 1),
(5, 1, 'rss', '2012-06-24 16:23:16', '2012-06-24 16:23:32', 5, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0),
(6, 1, 'newsletter', '2012-08-06 15:01:17', '2012-08-06 15:01:51', 6, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0),
(7, 1, 'folder', '2012-09-23 13:03:32', '2012-09-23 11:56:21', 7, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0);
_SEP_;
	if (!@mysql_query($sql, $db_conn)) {
		return false;
	}

	$sql= <<<_SEP_
INSERT INTO `${db_prefix}thread_locale` (`thread_id`, `locale`, `name`, `title`, `abstract`, `cloud`) VALUES
(1, 'fr', 'classeur', 'Classeur', NULL, NULL),
(1, 'en', 'binder', 'Binder', NULL, NULL),
(2, 'fr', 'test', 'Test', NULL, NULL),
(2, 'en', 'test', 'Test', NULL, NULL),
(3, 'fr', 'blog', 'Blog', NULL, NULL),
(3, 'en', 'blog', 'Blog', NULL, NULL),
(4, 'fr', 'articles', 'Articles', NULL, NULL),
(4, 'en', 'articles', 'Articles', NULL, NULL),
(5, 'fr', 'rss', 'RSS', NULL, NULL),
(5, 'en', 'rss', 'RSS', NULL, NULL),
(6, 'fr', 'infolettre', 'Infolettre', NULL, NULL),
(6, 'en', 'newsletter', 'Newsletter', NULL, NULL),
(7, 'fr', 'contenu', 'Contenu', NULL, NULL),
(7, 'en', 'content', 'Content', NULL, NULL);
_SEP_;
	if (!@mysql_query($sql, $db_conn)) {
		return false;
	}

	$sql= <<<_SEP_
INSERT INTO `${db_prefix}thread_node` (`thread_id`, `node_id`, `number`, `ignored`) VALUES
(1, 1, 1, 0),
(1, 2, 2, 0),
(2, 3, 1, 0),
(2, 4, 2, 0),
(2, 5, 3, 0),
(2, 16, 4, 0),
(3, 10, 4, 0),
(3, 11, 3, 0),
(3, 12, 2, 0),
(3, 13, 1, 0),
(4, 6, 1, 0),
(4, 7, 2, 0),
(4, 8, 3, 0),
(4, 9, 4, 0),
(5, 14, 1, 0),
(6, 15, 1, 0),
(7, 17, 1, 0),
(7, 18, 2, 0),
(7, 19, 3, 0);
_SEP_;
	if (!@mysql_query($sql, $db_conn)) {
		return false;
	}

	if (!@mysql_close($db_conn)) {
		return false;
	}

	return true;
}
