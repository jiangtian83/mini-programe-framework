<?php
/**
 * [zhixue-inc System] Copyright (c) 2019 zhimanfen.com
 */
error_reporting(E_ALL ^ E_NOTICE);
@set_time_limit(0);

ob_start();
define('IA_ROOT', str_replace("\\",'/', dirname(__FILE__)));
define('APP_URL', 'https://zhimanfen.com');
define('APP_STORE_URL', 'http://v2.addons.we7.cc/web');
define('APP_STORE_API', 'http://v2.addons.we7.cc/api.php');
if($_GET['res']) {
	$res = $_GET['res'];
	$reses = tpl_resources();
	if(array_key_exists($res, $reses)) {
		if($res == 'css') {
			header('content-type:text/css');
		} else {
			header('content-type:image/png');
		}
		echo base64_decode($reses[$res]);
		exit();
	}
}

$actions = array('license', 'env', 'db', 'finish');
$action = $_COOKIE['action'];
$action = in_array($action, $actions) ? $action : 'license';
$ispost = strtolower($_SERVER['REQUEST_METHOD']) == 'post';

if(file_exists(IA_ROOT . '/data/install.lock') && $action != 'finish') {
	header('location: ./index.php');
	exit;
}
header('content-type: text/html; charset=utf-8');
if($action == 'license') {
	if($ispost) {
		setcookie('action', 'env');
		header('location: ?refresh');
		exit;
	}
	tpl_install_license();
}
if($action == 'env') {
	if($ispost) {
		setcookie('action', $_POST['do'] == 'continue' ? 'db' : 'license');
		header('location: ?refresh');
		exit;
	}
	$ret = array();
	$ret['server']['os']['value'] = php_uname();
	if(PHP_SHLIB_SUFFIX == 'dll') {
		$ret['server']['os']['remark'] = '建议使用 Linux 系统以提升程序性能';
		$ret['server']['os']['class'] = 'warning';
	}
	$ret['server']['sapi']['value'] = $_SERVER['SERVER_SOFTWARE'];
	if(PHP_SAPI == 'isapi') {
		$ret['server']['sapi']['remark'] = '建议使用 Apache 或 Nginx 以提升程序性能';
		$ret['server']['sapi']['class'] = 'warning';
	}
	$ret['server']['php']['value'] = PHP_VERSION;
	$ret['server']['dir']['value'] = IA_ROOT;
	if(function_exists('disk_free_space')) {
		$ret['server']['disk']['value'] = floor(disk_free_space(IA_ROOT) / (1024*1024)).'M';
	} else {
		$ret['server']['disk']['value'] = 'unknown';
	}
	$ret['server']['upload']['value'] = @ini_get('file_uploads') ? ini_get('upload_max_filesize') : 'unknown';

	$ret['php']['version']['value'] = PHP_VERSION;
	$ret['php']['version']['class'] = 'success';
	if(version_compare(PHP_VERSION, '5.3.0') == -1) {
		$ret['php']['version']['class'] = 'danger';
		$ret['php']['version']['failed'] = true;
		$ret['php']['version']['remark'] = 'PHP版本必须为 5.3.0 以上.';
	}

	$ret['php']['pdo']['ok'] = extension_loaded('pdo') && extension_loaded('pdo_mysql');
	if($ret['php']['pdo']['ok']) {
		$ret['php']['pdo']['value'] = '<span class="glyphicon glyphicon-ok text-success"></span>';
		$ret['php']['pdo']['class'] = 'success';
	} else {
		$ret['php']['pdo']['failed'] = true;
		$ret['php']['pdo']['value'] = '<span class="glyphicon glyphicon-remove text-warning"></span>';
		$ret['php']['pdo']['class'] = 'warning';
		$ret['php']['pdo']['remark'] = '您的PHP环境不支持PDO, 请开启此扩展.';
	}

	$ret['php']['fopen']['ok'] = @ini_get('allow_url_fopen') && function_exists('fsockopen');
	if($ret['php']['fopen']['ok']) {
		$ret['php']['fopen']['value'] = '<span class="glyphicon glyphicon-ok text-success"></span>';
	} else {
		$ret['php']['fopen']['value'] = '<span class="glyphicon glyphicon-remove text-danger"></span>';
	}

	$ret['php']['curl']['ok'] = extension_loaded('curl') && function_exists('curl_init');
	if($ret['php']['curl']['ok']) {
		$ret['php']['curl']['value'] = '<span class="glyphicon glyphicon-ok text-success"></span>';
		$ret['php']['curl']['class'] = 'success';
	} else {
		$ret['php']['curl']['value'] = '<span class="glyphicon glyphicon-remove text-danger"></span>';
		$ret['php']['curl']['class'] = 'danger';
		$ret['php']['curl']['failed'] = true;
		$ret['php']['curl']['remark'] = '您的PHP环境不支持cURL, 也不支持 allow_url_fopen, 系统无法正常运行.';
	}

	$ret['php']['ssl']['ok'] = extension_loaded('openssl');
	if($ret['php']['ssl']['ok']) {
		$ret['php']['ssl']['value'] = '<span class="glyphicon glyphicon-ok text-success"></span>';
		$ret['php']['ssl']['class'] = 'success';
	} else {
		$ret['php']['ssl']['value'] = '<span class="glyphicon glyphicon-remove text-danger"></span>';
		$ret['php']['ssl']['class'] = 'danger';
		$ret['php']['ssl']['failed'] = true;
		$ret['php']['ssl']['remark'] = '没有启用OpenSSL, 将无法访问公众平台的接口, 系统无法正常运行.';
	}

	$ret['php']['gd']['ok'] = extension_loaded('gd');
	if($ret['php']['gd']['ok']) {
		$ret['php']['gd']['value'] = '<span class="glyphicon glyphicon-ok text-success"></span>';
		$ret['php']['gd']['class'] = 'success';
	} else {
		$ret['php']['gd']['value'] = '<span class="glyphicon glyphicon-remove text-danger"></span>';
		$ret['php']['gd']['class'] = 'danger';
		$ret['php']['gd']['failed'] = true;
		$ret['php']['gd']['remark'] = '没有启用GD, 将无法正常上传和压缩图片, 系统无法正常运行.';
	}

	$ret['php']['dom']['ok'] = class_exists('DOMDocument');
	if($ret['php']['dom']['ok']) {
		$ret['php']['dom']['value'] = '<span class="glyphicon glyphicon-ok text-success"></span>';
		$ret['php']['dom']['class'] = 'success';
	} else {
		$ret['php']['dom']['value'] = '<span class="glyphicon glyphicon-remove text-danger"></span>';
		$ret['php']['dom']['class'] = 'danger';
		$ret['php']['dom']['failed'] = true;
		$ret['php']['dom']['remark'] = '没有启用DOMDocument, 将无法正常安装使用模块, 系统无法正常运行.';
	}

	$ret['php']['session']['ok'] = ini_get('session.auto_start');
	if($ret['php']['session']['ok'] == 0 || strtolower($ret['php']['session']['ok']) == 'off') {
		$ret['php']['session']['value'] = '<span class="glyphicon glyphicon-ok text-success"></span>';
		$ret['php']['session']['class'] = 'success';
	} else {
		$ret['php']['session']['value'] = '<span class="glyphicon glyphicon-remove text-danger"></span>';
		$ret['php']['session']['class'] = 'danger';
		$ret['php']['session']['failed'] = true;
		$ret['php']['session']['remark'] = '系统session.auto_start开启, 将无法正常注册会员, 系统无法正常运行.';
	}

	$ret['php']['asp_tags']['ok'] = ini_get('asp_tags');
	if(empty($ret['php']['asp_tags']['ok']) || strtolower($ret['php']['asp_tags']['ok']) == 'off') {
		$ret['php']['asp_tags']['value'] = '<span class="glyphicon glyphicon-ok text-success"></span>';
		$ret['php']['asp_tags']['class'] = 'success';
	} else {
		$ret['php']['asp_tags']['value'] = '<span class="glyphicon glyphicon-remove text-danger"></span>';
		$ret['php']['asp_tags']['class'] = 'danger';
		$ret['php']['asp_tags']['failed'] = true;
		$ret['php']['asp_tags']['remark'] = '请禁用可以使用ASP 风格的标志，配置php.ini中asp_tags = Off';
	}

	$ret['write']['root']['ok'] = local_writeable(IA_ROOT . '/');
	if($ret['write']['root']['ok']) {
		$ret['write']['root']['value'] = '<span class="glyphicon glyphicon-ok text-success"></span>';
		$ret['write']['root']['class'] = 'success';
	} else {
		$ret['write']['root']['value'] = '<span class="glyphicon glyphicon-remove text-danger"></span>';
		$ret['write']['root']['class'] = 'danger';
		$ret['write']['root']['failed'] = true;
		$ret['write']['root']['remark'] = '本地目录无法写入, 将无法使用自动更新功能, 系统无法正常运行.';
		$ret['write']['failed'] = true;
	}
	$ret['write']['data']['ok'] = local_writeable(IA_ROOT . '/data');
	if($ret['write']['data']['ok']) {
		$ret['write']['data']['value'] = '<span class="glyphicon glyphicon-ok text-success"></span>';
		$ret['write']['data']['class'] = 'success';
	} else {
		$ret['write']['data']['value'] = '<span class="glyphicon glyphicon-remove text-danger"></span>';
		$ret['write']['data']['class'] = 'danger';
		$ret['write']['data']['failed'] = true;
		$ret['write']['failed'] = true;
		$ret['write']['data']['remark'] = 'data目录无法写入, 将无法写入配置文件, 系统无法正常安装. ';
	}

	$ret['continue'] = true;
	foreach($ret['php'] as $opt) {
		if($opt['failed']) {
			$ret['continue'] = false;
			break;
		}
	}
	if($ret['write']['failed'] ?? false) {
		$ret['continue'] = false;
	}
	tpl_install_env($ret);
}
if($action == 'db') {
	if($ispost) {
		if($_POST['do'] != 'continue') {
			setcookie('action', 'env');
			header('location: ?refresh');
			exit();
		}
		$family = $_POST['family'] == 'x' ? 'x' : 'v';
		$db = $_POST['db'];
<<<<<<< HEAD
=======
	 	//echo json_encode($_POST);exit;
>>>>>>> c4fb0c5d9411b77b6b482e20e82057ca056d4fbd
		$user = $_POST['user'];
		try {
			$pieces = explode(':', $db['server']);
			$db['server'] = $pieces[0];
			$db['port'] = !empty($pieces[1]) ? $pieces[1] : '3306';
			$link = new PDO("mysql:host={$db['server']};port={$db['port']}", $db['username'], $db['password']); 	// dns可以没有dbname
			$link->exec("SET character_set_connection=utf8, character_set_results=utf8, character_set_client=binary");
			$link->exec("SET sql_mode=''");
			if ($link->errorCode() != '00000') {
				$errorInfo = $link->errorInfo();
				$error = $errorInfo[2];
			} else {
				$statement = $link->query("SHOW DATABASES LIKE '{$db['name']}';");
				$fetch = $statement->fetch();
				if (empty($fetch)){
					if (substr($link->getAttribute(PDO::ATTR_SERVER_VERSION), 0, 3) > '4.1') {
						$link->query("CREATE DATABASE IF NOT EXISTS `{$db['name']}` DEFAULT CHARACTER SET utf8");
					} else {
						$link->query("CREATE DATABASE IF NOT EXISTS `{$db['name']}`");
					}
				}
				$statement = $link->query("SHOW DATABASES LIKE '{$db['name']}';");
				$fetch = $statement->fetch();
				if (empty($fetch)) {
					$error .= "数据库不存在且创建数据库失败. <br />";
				}
				if ($link->errorCode() != '00000') {
					$errorInfo = $link->errorInfo();
					$error .= $errorInfo[2];
				}
			}
		} catch (PDOException $e) {
			$error = $e->getMessage();
			if (strpos($error, 'Access denied for user') !== false) {
				$error = '您的数据库访问用户名或是密码错误. <br />';
			} else {
				$error = iconv('gbk', 'utf8', $error);
			}
		}
		if(empty($error)) {
			$link->exec("USE {$db['name']}");
			$statement = $link->query("SHOW TABLES LIKE '{$db['prefix']}%';");
			if ($statement->fetch()) {
				$error = '您的数据库不为空，请重新建立数据库或是清空该数据库或更改表前缀！';
			}
		}
		if(empty($error)) {
			$config = local_config();
			$cookiepre = local_salt(4) . '_';
			$authkey = local_salt(8);
			$config = str_replace(array(
				'{db-server}', '{db-username}', '{db-password}', '{db-port}', '{db-name}', '{db-tablepre}', '{cookiepre}', '{authkey}', '{attachdir}'
			), array(
				$db['server'], $db['username'], $db['password'], $db['port'], $db['name'], $db['prefix'], $cookiepre, $authkey, 'attachment'
			), $config);
			$verfile = IA_ROOT . '/framework/version.inc.php';
			$dbfile = IA_ROOT . '/data/db.php';

			if($_POST['type'] == 'remote') {
				$link = NULL;
				$ins = remote_install();
				if(empty($ins)) {
					die('<script type="text/javascript">alert("连接不到服务器, 请稍后重试！");history.back();</script>');
				}
				if($ins == 'error') {
					die('<script type="text/javascript">alert("版本错误，请确认是否为最新版安装文件！");history.back();</script>');
				}

				$link = new PDO("mysql:dbname={$db['name']};host={$db['server']};port={$db['port']}", $db['username'], $db['password']);
				$link->exec("SET character_set_connection=utf8, character_set_results=utf8, character_set_client=binary");
				$link->exec("SET sql_mode=''");

				$tmpfile = IA_ROOT . '/we7source.tmp';
				file_put_contents($tmpfile, $ins);

				$zip = new ZipArchive;
				$res = $zip->open($tmpfile);

				if ($res === TRUE) {
					$zip->extractTo(IA_ROOT);
					$zip->close();
				} else {
					die('<script type="text/javascript">alert("安装失败，请确认当前目录是否有写入权限！");history.back();</script>');
				}
				unlink($tmpfile);
			}

			if(file_exists(IA_ROOT . '/index.php') && is_dir(IA_ROOT . '/web') && file_exists($verfile) && file_exists($dbfile)) {
				$dat = require $dbfile;
				if(empty($dat) || !is_array($dat)) {
					die('<script type="text/javascript">alert("安装包不正确, 数据安装脚本缺失.");history.back();</script>');
				}
				foreach($dat['schemas'] as $schema) {
					$sql = local_create_sql($schema);
					local_run($sql);
				}
				foreach($dat['datas'] as $data) {
					local_run($data);
				}
			} else {
<<<<<<< HEAD
				die('<script type="text/javascript">alert("你正在使用本地安装, 但未下载完整安装包, 请从微擎官网下载完整安装包后重试.");history.back();</script>');
=======
				die('<script type="text/javascript">alert("你正在使用本地安装, 但未下载完整安装包, 请从官网下载完整安装包后重试.");history.back();</script>');
>>>>>>> c4fb0c5d9411b77b6b482e20e82057ca056d4fbd
			}

			$salt = local_salt(8);
			$password = sha1("{$user['password']}-{$salt}-{$authkey}");
			$link->exec("INSERT INTO {$db['prefix']}users (username, password, salt, joindate, groupid) VALUES('{$user['username']}', '{$password}', '{$salt}', '" . time() . "', 1)");
			local_mkdirs(IA_ROOT . '/data');
			file_put_contents(IA_ROOT . '/data/config.php', $config);
			touch(IA_ROOT . '/data/install.lock');
			setcookie('action', 'finish');
			header('location: ?refresh');
			exit();
		}
	}
	tpl_install_db($error);

}
if($action == 'finish') {
	setcookie('action', '', -10);
	$dbfile = IA_ROOT . '/data/db.php';
	@unlink($dbfile);
	define('IN_SYS', true);
	require IA_ROOT . '/framework/bootstrap.inc.php';
	require IA_ROOT . '/web/common/bootstrap.sys.inc.php';
	$_W['uid'] = $_W['isfounder'] = 1;
	load()->web('common');
	load()->web('template');
	load()->model('setting');
	load()->model('cache');

	cache_build_frame_menu();
	cache_build_setting();
	cache_build_users_struct();
	cache_build_module_subscribe_type();
	tpl_install_finish();
}

function local_writeable($dir) {
	$writeable = 0;
	if(!is_dir($dir)) {
		@mkdir($dir, 0777);
	}
	if(is_dir($dir)) {
		if($fp = fopen("$dir/test.txt", 'w')) {
			fclose($fp);
			unlink("$dir/test.txt");
			$writeable = 1;
		} else {
			$writeable = 0;
		}
	}
	return $writeable;
}

function local_salt($length = 8) {
	$result = '';
	while(strlen($result) < $length) {
		$result .= sha1(uniqid('', true));
	}
	return substr($result, 0, $length);
}

function local_config() {
	$cfg = <<<EOF
<?php
defined('IN_IA') or exit('Access Denied');

\$config = array();

\$config['db']['master']['host'] = '{db-server}';
\$config['db']['master']['username'] = '{db-username}';
\$config['db']['master']['password'] = '{db-password}';
\$config['db']['master']['port'] = '{db-port}';
\$config['db']['master']['database'] = '{db-name}';
\$config['db']['master']['charset'] = 'utf8';
\$config['db']['master']['pconnect'] = 0;
\$config['db']['master']['tablepre'] = '{db-tablepre}';

\$config['db']['slave_status'] = false;
\$config['db']['slave']['1']['host'] = '';
\$config['db']['slave']['1']['username'] = '';
\$config['db']['slave']['1']['password'] = '';
\$config['db']['slave']['1']['port'] = '3307';
\$config['db']['slave']['1']['database'] = '';
\$config['db']['slave']['1']['charset'] = 'utf8';
\$config['db']['slave']['1']['pconnect'] = 0;
\$config['db']['slave']['1']['tablepre'] = 'ims_';
\$config['db']['slave']['1']['weight'] = 0;

\$config['db']['common']['slave_except_table'] = array('core_sessions');

// --------------------------  CONFIG COOKIE  --------------------------- //
\$config['cookie']['pre'] = '{cookiepre}';
\$config['cookie']['domain'] = '';
\$config['cookie']['path'] = '/';

// --------------------------  CONFIG SETTING  --------------------------- //
\$config['setting']['charset'] = 'utf-8';
\$config['setting']['cache'] = 'mysql';
\$config['setting']['timezone'] = 'Asia/Shanghai';
\$config['setting']['memory_limit'] = '256M';
\$config['setting']['filemode'] = 0644;
\$config['setting']['authkey'] = '{authkey}';
\$config['setting']['founder'] = '1';
\$config['setting']['development'] = 0;
\$config['setting']['referrer'] = 0;

// --------------------------  CONFIG UPLOAD  --------------------------- //
\$config['upload']['image']['extentions'] = array('gif', 'jpg', 'jpeg', 'png');
\$config['upload']['image']['limit'] = 5000;
\$config['upload']['attachdir'] = '{attachdir}';
\$config['upload']['audio']['extentions'] = array('mp3');
\$config['upload']['audio']['limit'] = 5000;

// --------------------------  CONFIG MEMCACHE  --------------------------- //
\$config['setting']['memcache']['server'] = '';
\$config['setting']['memcache']['port'] = 11211;
\$config['setting']['memcache']['pconnect'] = 1;
\$config['setting']['memcache']['timeout'] = 30;
\$config['setting']['memcache']['session'] = 1;

// --------------------------  CONFIG PROXY  --------------------------- //
\$config['setting']['proxy']['host'] = '';
\$config['setting']['proxy']['auth'] = '';
EOF;
	return trim($cfg);
}

function local_mkdirs($path) {
	if(!is_dir($path)) {
		local_mkdirs(dirname($path));
		mkdir($path);
	}
	return is_dir($path);
}

function local_run($sql) {
	global $link, $db;

	if(!isset($sql) || empty($sql)) return;

	$sql = str_replace("\r", "\n", str_replace(' ims_', ' '.$db['prefix'], $sql));
	$sql = str_replace("\r", "\n", str_replace(' `ims_', ' `'.$db['prefix'], $sql));
	$ret = array();
	$num = 0;
	foreach(explode(";\n", trim($sql)) as $query) {
		$ret[$num] = '';
		$queries = explode("\n", trim($query));
		foreach($queries as $query) {
			$ret[$num] .= (isset($query[0]) && $query[0] == '#') || (isset($query[1]) && isset($query[1]) && $query[0].$query[1] == '--') ? '' : $query;
		}
		$num++;
	}
	unset($sql);
	foreach($ret as $query) {
		$query = trim($query);
		if($query) {
			$link->exec($query);
			if($link->errorCode() != '00000') {
				$errorInfo = $link->errorInfo();
				echo $errorInfo[0] . ": " . $errorInfo[2] . "<br />";
				exit($query);
			}
		}
	}
}

function local_create_sql($schema) {
	$pieces = explode('_', $schema['charset']);
	$charset = $pieces[0];
	$engine = $schema['engine'];
	$sql = "CREATE TABLE IF NOT EXISTS `{$schema['tablename']}` (\n";
	foreach ($schema['fields'] as $value) {
		if(!empty($value['length'])) {
			$length = "({$value['length']})";
		} else {
			$length = '';
		}

		$signed  = empty($value['signed']) ? ' unsigned' : '';
		if(empty($value['null'])) {
			$null = ' NOT NULL';
		} else {
			$null = '';
		}
		if(isset($value['default'])) {
			$default = " DEFAULT '" . $value['default'] . "'";
		} else {
			$default = '';
		}
		if($value['increment']) {
			$increment = ' AUTO_INCREMENT';
		} else {
			$increment = '';
		}

		$sql .= "`{$value['name']}` {$value['type']}{$length}{$signed}{$null}{$default}{$increment},\n";
	}
	foreach ($schema['indexes'] as $value) {
		$fields = implode('`,`', $value['fields']);
		if($value['type'] == 'index') {
			$sql .= "KEY `{$value['name']}` (`{$fields}`),\n";
		}
		if($value['type'] == 'unique') {
			$sql .= "UNIQUE KEY `{$value['name']}` (`{$fields}`),\n";
		}
		if($value['type'] == 'primary') {
			$sql .= "PRIMARY KEY (`{$fields}`),\n";
		}
	}
	$sql = rtrim($sql);
	$sql = rtrim($sql, ',');

	$sql .= "\n) ENGINE=$engine DEFAULT CHARSET=$charset;\n\n";
	return $sql;
}

function remote_install() {
	global $family;
	$token = '';
	$pars = array();
	$pars['host'] = $_SERVER['http_HOST'];
	$pars['version'] = '1.0';
	$pars['type'] = 'install';
	$pars['method'] = 'application.install';
	$url = 'http://v2.addons.we7.cc/gateway.php';
	$urlset = parse_url($url);
	$cloudip = gethostbyname($urlset['host']);
	$headers[] = "Host: {$urlset['host']}";
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $urlset['scheme'] . '://' . $cloudip . $urlset['path']);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($pars, '', '&'));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	$content = curl_exec($ch);
	curl_close($ch);

	if (empty($content)) {
		return showerror(-1, '获取安装信息失败，可能是由于网络不稳定，请重试。');
	}

	return $content;
}

function tpl_frame() {
	global $action, $actions;
	$action = $_COOKIE['action'];
	$step = array_search($action, $actions);
	$steps = array();
	for($i = 0; $i <= $step; $i++) {
		if($i == $step) {
			$steps[$i] = ' list-group-item-info';
		} else {
			$steps[$i] = ' list-group-item-success';
		}
	}
	$progress = $step * 25 + 25;
	$content = ob_get_contents();
	ob_clean();
	$tpl = <<<EOF
<!DOCTYPE html>
<html lang="zh-cn">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>安装系统 - 智满分 - 公众平台自助开源引擎</title>
		<link rel="stylesheet" href="./app/resource/css/bootstrap.min.css">
		<style>
			html,body{font-size:13px;font-family:"Microsoft YaHei UI", "微软雅黑", "宋体";}
			.pager li.previous a{margin-right:10px;}
			.header a{color:#FFF;}
			.header a:hover{color:#428bca;}
			.footer{padding:10px;}
			.footer a,.footer{color:#eee;font-size:14px;line-height:25px;}
		</style>
		<!--[if lt IE 9]>
		  <script src="./assets/html5shiv.min.js"></script>
		  <script src="./assets/respond.min.js"></script>
		<![endif]-->
	</head>
	<body style="background-color:#28b0e4;">
		<div class="container">
			<div class="header" style="margin:15px auto;">
				<ul class="nav nav-pills pull-right" role="tablist">
					<li role="presentation" class="active"><a href="javascript:;">安装公众平台自助系统</a></li>
					<li role="presentation"><a href="https://zhimanfen.com">智满分官网</a></li>
				</ul>
				<img src="?res=logo" style="height:48px" />
			</div>
			<div class="row well" style="margin:auto 0;">
				<div class="col-xs-3">
					<div class="progress" title="安装进度">
						<div class="progress-bar progress-bar-info progress-bar-striped active" role="progressbar" aria-valuenow="{$progress}" aria-valuemin="0" aria-valuemax="100" style="width: {$progress}%;">
							{$progress}%
						</div>
					</div>
					<div class="panel panel-default">
						<div class="panel-heading">
							安装步骤
						</div>
						<ul class="list-group">
							<a href="javascript:;" class="list-group-item{$steps[0]}"><span class="glyphicon glyphicon-copyright-mark"></span> &nbsp; 许可协议</a>
							<a href="javascript:;" class="list-group-item{$steps[1]}"><span class="glyphicon glyphicon-eye-open"></span> &nbsp; 环境监测</a>
							<a href="javascript:;" class="list-group-item{$steps[2]}"><span class="glyphicon glyphicon-cog"></span> &nbsp; 参数配置</a>
							<a href="javascript:;" class="list-group-item{$steps[3]}"><span class="glyphicon glyphicon-ok"></span> &nbsp; 成功</a>
						</ul>
					</div>
				</div>
				<div class="col-xs-9">
					{$content}
				</div>
			</div>
			<div class="footer" style="margin:15px auto;">
				<div class="text-center">
					<a href="https://zhimanfen.com">关于智满分</a>
				</div>
			</div>
		</div>
		<script src="./assets/jquery.min.js"></script>
		<script src="./assets/bootstrap.min.js"></script>
	</body>
</html>
EOF;
	echo trim($tpl);
}

function tpl_install_license() {
	echo <<<EOF
		<div class="panel panel-default">
			<div class="panel-heading">关于智满分</div>
			<div class="panel-body" style="overflow-y:scroll;max-height:400px;line-height:20px;">
				<h3>全国免费热线:  400-0598-097</h3>
				<p>
				<b>1.公司定位</b>

宜昌智满分教育科技公司以“人工智能改变教育，让学习更简单”为理念，专注于K12教育领域，是一家全学科智能教育科技企业。
				</p>
				<p>
				<b>2.公司架构</b>
总公司在武汉，主要从事软件开发业务，很早有接触到校园教务、教学等软件开发，顺应时代前景，2016年筹备宜昌分公司专项从事智能教育软件开发。
				</p>
				<p>
				<b>3.团队情况</b>
全公司目前在职员工117人，其中研发团队38人，教研团队16人，运营团队12人，市场团队45人，其它6人
智学AI项目（宜昌智满分教育科技有限公司）目前在职员工34人
智满分创始团队大多数为多年的教育行业从业者，并拥有丰富的一线教学教研经验。教研团队负责人为原新动态英语集团教研总监，原昂立国际教育集团湖北分公司教学主管，研发负责人为原小马过河教育集团CTO，运营负责人为原飞利信科技集团――中国国家培训网运营总监。
				</p>
				<p>
				<b>4.荣誉资质</b>
软件产品拥有自主知识产权，已经申请了多项国家软件著作权，并拥有智能教育行业多项受保护商标。
评定机构
评定机构：赛宝体系认证、中国质量认证监督委员会、全国品牌认证联盟、
《ISO9001质量管理体系认证》《AAA级诚信经营示范单位》《全国教育行业特色加盟品牌｛连锁企业｝》《中国 教育行业驰名品牌》
中国教育行业最具影响力品牌 中国自主创新最具影响力品牌 湖北省创新、创优质量品牌双承诺单位 中国教育行业首选产品 
				</p>
				<p>
  				<b>5.公司发展历程</b>
总公司（泰拉科技有限公司）位于武汉，主要从事软件开发业务，拥有多套自主研发的前后端框架，帮助上海交通大学、新疆电力集团有限公司、山东省互联网传媒集体股份有限公司等开发管理系统，并于2018年取得软件著作权证书，并于当年实现销售额累积3000万元。
为了响应国家互联网+、人工智能、大数据助力产业升级及习总书记“让亿万孩子同在蓝天下共享优质教育”的号召，于2016年2月正式启动智学AI人工智能教育系统项目，并同时成立运营实体――智满分教育科技有限公司，集创始人覃守伟及8人创始团队8-10年传统教育机构教学教研及教育软件研发经验之大成，推出覆盖k12全学科智学AI教育系统，宜昌分公司于2019年1月正式成立。 
				</p>
			</div>
		</div>
		<form class="form-inline" role="form" method="post">
			<ul class="pager">
				<li class="pull-left" style="display:block;padding:5px 10px 5px 0;">
					<div class="checkbox">
						<label>
							<input type="checkbox"> 我已经阅读并同意此协议
						</label>
					</div>
				</li>
				<li class="previous"><a href="javascript:;" onclick="if(jQuery(':checkbox:checked').length == 1){jQuery('form')[0].submit();}else{alert('您必须同意软件许可协议才能安装！')};">继续 <span class="glyphicon glyphicon-chevron-right"></span></a></li>
			</ul>
		</form>
EOF;
	tpl_frame();
}

function tpl_install_env($ret = array()) {
	if(empty($ret['continue'])) {
		$continue = '<li class="previous disabled"><a href="javascript:;">请先解决环境问题后继续</a></li>';
	} else {
		$continue = '<li class="previous"><a href="javascript:;" onclick="$(\'#do\').val(\'continue\');$(\'form\')[0].submit();">继续 <span class="glyphicon glyphicon-chevron-right"></span></a></li>';
	}
	echo <<<EOF
		<div class="panel panel-default">
			<div class="panel-heading">服务器信息</div>
			<table class="table table-striped">
				<tr>
					<th style="width:150px;">参数</th>
					<th>值</th>
					<th></th>
				</tr>
				<tr class="{$ret['server']['os']['class']}">
					<td>服务器操作系统</td>
					<td>{$ret['server']['os']['value']}</td>
					<td>{$ret['server']['os']['remark']}</td>
				</tr>
				<tr class="{$ret['server']['sapi']['class']}">
					<td>Web服务器环境</td>
					<td>{$ret['server']['sapi']['value']}</td>
					<td>{$ret['server']['sapi']['remark']}</td>
				</tr>
				<tr class="{$ret['server']['php']['class']}">
					<td>PHP版本</td>
					<td>{$ret['server']['php']['value']}</td>
					<td>{$ret['server']['php']['remark']}</td>
				</tr>
				<tr class="{$ret['server']['dir']['class']}">
					<td>程序安装目录</td>
					<td>{$ret['server']['dir']['value']}</td>
					<td>{$ret['server']['dir']['remark']}</td>
				</tr>
				<tr class="{$ret['server']['disk']['class']}">
					<td>磁盘空间</td>
					<td>{$ret['server']['disk']['value']}</td>
					<td>{$ret['server']['disk']['remark']}</td>
				</tr>
				<tr class="{$ret['server']['upload']['class']}">
					<td>上传限制</td>
					<td>{$ret['server']['upload']['value']}</td>
					<td>{$ret['server']['upload']['remark']}</td>
				</tr>
			</table>
		</div>

		<div class="alert alert-info">PHP环境要求必须满足下列所有条件，否则系统或系统部份功能将无法使用。</div>
		<div class="panel panel-default">
			<div class="panel-heading">PHP环境要求</div>
			<table class="table table-striped">
				<tr>
					<th style="width:150px;">选项</th>
					<th style="width:180px;">要求</th>
					<th style="width:50px;">状态</th>
					<th>说明及帮助</th>
				</tr>
				<tr class="{$ret['php']['version']['class']}">
					<td>PHP版本</td>
					<td>5.3或者5.3以上</td>
					<td>{$ret['php']['version']['value']}</td>
					<td>{$ret['php']['version']['remark']}</td>
				</tr>
				<tr class="{$ret['php']['curl']['class']}">
					<td>cURL</td>
					<td>支持</td>
					<td>{$ret['php']['curl']['value']}</td>
					<td>{$ret['php']['curl']['remark']}</td>
				</tr>
				<tr class="{$ret['php']['pdo']['class']}">
					<td>PDO</td>
					<td>支持</td>
					<td>{$ret['php']['pdo']['value']}</td>
					<td>{$ret['php']['pdo']['remark']}</td>
				</tr>
				<tr class="{$ret['php']['ssl']['class']}">
					<td>openSSL</td>
					<td>支持</td>
					<td>{$ret['php']['ssl']['value']}</td>
					<td>{$ret['php']['ssl']['remark']}</td>
				</tr>
				<tr class="{$ret['php']['gd']['class']}">
					<td>GD2</td>
					<td>支持</td>
					<td>{$ret['php']['gd']['value']}</td>
					<td>{$ret['php']['gd']['remark']}</td>
				</tr>
				<tr class="{$ret['php']['dom']['class']}">
					<td>DOM</td>
					<td>支持</td>
					<td>{$ret['php']['dom']['value']}</td>
					<td>{$ret['php']['dom']['remark']}</td>
				</tr>
				<tr class="{$ret['php']['session']['class']}">
					<td>session.auto_start</td>
					<td>关闭</td>
					<td>{$ret['php']['session']['value']}</td>
					<td>{$ret['php']['session']['remark']}</td>
				</tr>
				<tr class="{$ret['php']['asp_tags']['class']}">
					<td>asp_tags</td>
					<td>关闭</td>
					<td>{$ret['php']['asp_tags']['value']}</td>
					<td>{$ret['php']['asp_tags']['remark']}</td>
				</tr>
			</table>
		</div>

		<div class="alert alert-info">系统要求整个安装目录必须可写, 才能使用所有功能。</div>
		<div class="panel panel-default">
			<div class="panel-heading">目录权限监测</div>
			<table class="table table-striped">
				<tr>
					<th style="width:150px;">目录</th>
					<th style="width:180px;">要求</th>
					<th style="width:50px;">状态</th>
					<th>说明及帮助</th>
				</tr>
				<tr class="{$ret['write']['root']['class']}">
					<td>/</td>
					<td>整目录可写</td>
					<td>{$ret['write']['root']['value']}</td>
					<td>{$ret['write']['root']['remark']}</td>
				</tr>
				<tr class="{$ret['write']['data']['class']}">
					<td>/</td>
					<td>data目录可写</td>
					<td>{$ret['write']['data']['value']}</td>
					<td>{$ret['write']['data']['remark']}</td>
				</tr>
			</table>
		</div>
		<form class="form-inline" role="form" method="post">
			<input type="hidden" name="do" id="do" />
			<ul class="pager">
				<li class="previous"><a href="javascript:;" onclick="$('#do').val('back');$('form')[0].submit();"><span class="glyphicon glyphicon-chevron-left"></span> 返回</a></li>
				{$continue}
			</ul>
		</form>
EOF;
	tpl_frame();
}

function tpl_install_db($error = '') {
	if(!empty($error)) {
		$message = '<div class="alert alert-danger">发生错误: ' . $error . '</div>';
	}
	$insTypes = array();
	if(file_exists(IA_ROOT . '/index.php') && is_dir(IA_ROOT . '/app') && is_dir(IA_ROOT . '/web')) {
		$insTypes['local'] = ' checked="checked"';
	} else {
		$insTypes['remote'] = ' checked="checked"';
	}
	if (!empty($_POST['type'])) {
		$insTypes = array();
		$insTypes[$_POST['type']] = ' checked="checked"';
	}
	$disabled = empty($insTypes['local']) ? ' disabled="disabled"' : '';
	echo <<<EOF
	{$message}
	<form class="form-horizontal" method="post" role="form">
		<div class="panel panel-default">
			<div class="panel-heading">安装选项</div>
			<div class="panel-body">
				<div class="form-group">
					<label class="col-sm-2 control-label">安装方式</label>
					<div class="col-sm-10">
						
						<label class="radio-inline">
							<input type="radio" name="type" value="local"{$insTypes['local']}{$disabled}> 离线安装
						</label>
						
					</div>
				</div>
			</div>
		</div>
		<div class="panel panel-default">
			<div class="panel-heading">数据库选项</div>
			<div class="panel-body">
				<div class="form-group">
					<label class="col-sm-2 control-label">数据库主机</label>
					<div class="col-sm-4">
						<input class="form-control" type="text" name="db[server]" value="127.0.0.1">
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label">数据库用户</label>
					<div class="col-sm-4">
						<input class="form-control" type="text" name="db[username]" value="root">
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label">数据库密码</label>
					<div class="col-sm-4">
						<input class="form-control" type="text" name="db[password]">
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label">表前缀</label>
					<div class="col-sm-4">
						<input class="form-control" type="text" name="db[prefix]" value="ims_">
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label">数据库名称</label>
					<div class="col-sm-4">
						<input class="form-control" type="text" name="db[name]" value="we7">
					</div>
				</div>
			</div>
		</div>
		<div class="panel panel-default">
			<div class="panel-heading">管理选项</div>
			<div class="panel-body">
				<div class="form-group">
					<label class="col-sm-2 control-label">管理员账号</label>
					<div class="col-sm-4">
						<input class="form-control" type="username" name="user[username]">
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label">管理员密码</label>
					<div class="col-sm-4">
						<input class="form-control" type="password" name="user[password]">
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label">确认密码</label>
					<div class="col-sm-4">
						<input class="form-control" type="password"">
					</div>
				</div>
			</div>
		</div>
		<input type="hidden" name="do" id="do" />
		<ul class="pager">
			<li class="previous"><a href="javascript:;" onclick="$('#do').val('back');$('form')[0].submit();"><span class="glyphicon glyphicon-chevron-left"></span> 返回</a></li>
			<li class="previous"><a href="javascript:;" onclick="if(check(this)){jQuery('#do').val('continue');if($('input[name=type]:checked').val() == 'remote'){alert('在线安装时，安装程序会下载精简版快速完成安装，完成后请务必注册云服务更新到完整版。')}$('form')[0].submit();}">继续 <span class="glyphicon glyphicon-chevron-right"></span></a></li>
		</ul>
	</form>
	<script>
		var lock = false;
		function check(obj) {
			if(lock) {
				return;
			}
			$('.form-control').parent().parent().removeClass('has-error');
			var error = false;
			$('.form-control').each(function(){
				if($(this).val() == '') {
					$(this).parent().parent().addClass('has-error');
					this.focus();
					error = true;
				}
			});
			if(error) {
				alert('请检查未填项');
				return false;
			}
			if($(':password').eq(0).val() != $(':password').eq(1).val()) {
				$(':password').parent().parent().addClass('has-error');
				alert('确认密码不正确.');
				return false;
			}
			lock = true;
			$(obj).parent().addClass('disabled');
			$(obj).html('正在执行安装');
			return true;
		}
	</script>
EOF;
	tpl_frame();
}

function tpl_install_finish() {
	$modules = get_store_module();
	$themes = get_store_theme();
	echo <<<EOF
	<div class="page-header"><h3>安装完成</h3></div>
	<div class="alert alert-success">
		恭喜您!已成功安装“微擎 - 公众平台自助开源引擎”系统，您现在可以: <a target="_blank" class="btn btn-success" href="./web/index.php">访问网站首页</a>
	</div>
	
EOF;
	tpl_frame();
}

function tpl_resources() {
	static $res = array(
		'logo' => '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',
	);
	return $res;
}

function showerror($errno, $message = '') {
	return array(
		'errno' => $errno,
		'error' => $message,
	);
}

function get_store_module() {
	load()->func('communication');
	$response = ihttp_request(APP_STORE_API, array('controller' => 'store', 'action' => 'api', 'do' => 'module'));
	$response = json_decode($response['content'], true);

	$modules = '';
	foreach ($response['message'] as $key => $module) {
		if ($key % 3 < 1) {
			$modules .= '</tr><tr>';
		}
		$module['detail_link'] = APP_STORE_URL . trim($module['detail_link'], '.');
		$modules .= '<td>';
		$modules .= '<div class="col-sm-4">';
		$modules .= '<a href="' . $module['detail_link'] . '" title="查看详情" target="_blank">';
		$modules .= '<img src="' . $module['logo']. '"' . ' width="50" height="50" ' . $module['title'] . '" /></a>';
		$modules .= '</div>';
		$modules .= '<div class="col-sm-8">';
		$modules .= '<p><a href="' . $module['detail_link'] .'" title="查看详情" target="_blank">' . $module['title'] . '</a></p>';
		$modules .= '<p>安装量：<span class="text-danger">' . $module['purchases'] . '</span></p>';
		$modules .= '</div>';
		$modules .= '</td>';
	}
	$modules = substr($modules, 5) . '</tr>';

	return $modules;
}

function get_store_theme() {
	load()->func('communication');
	$response = ihttp_request(APP_STORE_API, array('controller' => 'store', 'action' => 'api', 'do' => 'theme'));
	$response = json_decode($response['content'], true);

	$themes = '<tr><td colspan="' . count($response['message']) . '">';
	$themes .= '<div class="form-group">';
	foreach ($response['message'] as $key => $theme) {
		$theme['detail_link'] = APP_STORE_URL . trim($theme['detail_link'], '.');
		$themes .= '<div class="col-sm-2" style="padding-left: 7px;margin-right: 25px;">';
		$themes .= '<a href="' . $theme['detail_link'] .'" title="查看详情" target="_blank" /><img src="' . $theme['logo']. '" /></a>';
		$themes .= '<p></p><p class="text-right">';
		$themes .= '<a href="' . $theme['detail_link']. '" title="查看详情" target="_blank">'  . $theme['title'] . '</a></p>';
		$themes .= '</div>';
	}
	$themes .= '</div>';

	return $themes;
}
