<?php

define('IN_IA', true); // 标示是否bootstap
define('STARTTIME', microtime());
define('IA_ROOT', str_replace("\\", '/', dirname(dirname(__FILE__))));
define('MAGIC_QUOTES_GPC', (function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()) || @ini_get('magic_quotes_sybase'));
define('TIMESTAMP', time());

// $_GPC变量存储的是经过转义，安全过滤的请求或cookie变量等
// $_W存储的过程中赋值的配置数据，是系统$config的拷贝
$_W = $_GPC = array();
$configfile = IA_ROOT . "/data/config.php";

// 检测配置文件，不存在则需要安装
if(!file_exists($configfile)) {
	if(file_exists(IA_ROOT . '/install.php')) {
		header('Content-Type: text/html; charset=utf-8');
		require IA_ROOT . '/framework/version.inc.php';
		echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
		echo "·如果你还没安装本程序，请运行<a href='".(strpos($_SERVER['SCRIPT_NAME'], 'web') === false ? './install.php' : '../install.php')."'> install.php 进入安装&gt;&gt; </a><br/><br/>";
		exit();
	} else {
		header('Content-Type: text/html; charset=utf-8');
		exit('配置文件及安装文件' . (strpos($_SERVER['SCRIPT_NAME'], 'web') === false ? './install.php' : '../install.php') . '都不u 存在，请检查“data/config”文件及安装文件！');
	}
}

// 引入配置文件
require $configfile;
require IA_ROOT . '/framework/version.inc.php';
require IA_ROOT . '/framework/const.inc.php';
require IA_ROOT . '/framework/class/loader.class.php'; // 生成loader自动加载

// func加载的是函数，classs加载的是类，model加载的模型，libary加载库
load()->func('global');
load()->func('compat');
load()->func('pdo');
load()->classs('account');
load()->model('cache');
load()->model('account');
load()->model('setting');
load()->model('module');
load()->library('agent');
load()->classs('db');
load()->func('communication');

define('CLIENT_IP', getip());

$_W['config'] = $config; // 引入的配置项
$_W['config']['db']['tablepre'] = !empty($_W['config']['db']['master']['tablepre'])
    ? $_W['config']['db']['master']['tablepre']
    : $_W['config']['db']['tablepre']; // 数据表前缀

$_W['timestamp'] = TIMESTAMP;
$_W['charset'] = $_W['config']['setting']['charset'];
$_W['clientip'] = CLIENT_IP;

unset($configfile, $config);

// 附件基目录
define('ATTACHMENT_ROOT', IA_ROOT .'/attachment/');
error_reporting(0);

if(!in_array($_W['config']['setting']['cache'], array('mysql', 'memcache', 'redis'))) {
	$_W['config']['setting']['cache'] = 'mysql';
}
load()->func('cache');

// 设置系统时区
if(function_exists('date_default_timezone_set')) {
	date_default_timezone_set($_W['config']['setting']['timezone']);
}
// 设置系统内存使用
if(!empty($_W['config']['setting']['memory_limit']) && function_exists('ini_get') && function_exists('ini_set')) {
	if(@ini_get('memory_limit') != $_W['config']['setting']['memory_limit']) {
		@ini_set('memory_limit', $_W['config']['setting']['memory_limit']);
	}
}
// 协议是否https
if (isset($_W['config']['setting']['https']) && $_W['config']['setting']['https'] == '1') {
	$_W['ishttps'] = $_W['config']['setting']['https'];
} else {
	$_W['ishttps'] = $_SERVER['SERVER_PORT'] == 443 ||
	(isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) != 'off') ||
	strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) == 'https' ||
	strtolower($_SERVER['HTTP_X_CLIENT_SCHEME']) == 'https' ? true : false;
}
// 是否ajax
$_W['isajax'] = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
// 是否post
$_W['ispost'] = $_SERVER['REQUEST_METHOD'] == 'POST';
// 协议头
$_W['sitescheme'] = $_W['ishttps'] ? 'https://' : 'http://';
// 脚本名
$_W['script_name'] = htmlspecialchars(scriptname());
// 脚本相对路径
$sitepath = substr($_SERVER['PHP_SELF'], 0, strrpos($_SERVER['PHP_SELF'], '/'));
// 完整脚本路径
$_W['siteroot'] = htmlspecialchars($_W['sitescheme'] . $_SERVER['HTTP_HOST'] . $sitepath);

if(substr($_W['siteroot'], -1) != '/') {
	$_W['siteroot'] .= '/';
}
$urls = parse_url($_W['siteroot']);
$urls['path'] = str_replace(array('/web', '/app', '/payment/wechat', '/payment/alipay', '/payment/jueqiymf', '/api'), '', $urls['path']);
$_W['siteroot'] = $urls['scheme'].'://'.$urls['host'].((!empty($urls['port']) && $urls['port']!='80') ? ':'.$urls['port'] : '').$urls['path'];

// 处理接受的参数，去反斜杠
if(MAGIC_QUOTES_GPC) {
	$_GET = istripslashes($_GET);
	$_POST = istripslashes($_POST);
	$_COOKIE = istripslashes($_COOKIE);
}
foreach($_GET as $key => $value) {
	if (is_string($value) && !is_numeric($value)) {
		$value = safe_gpc_string($value);
	}
	$_GET[$key] = $_GPC[$key] = $value;
}
$cplen = strlen($_W['config']['cookie']['pre']);
foreach($_COOKIE as $key => $value) {
	if(substr($key, 0, $cplen) == $_W['config']['cookie']['pre']) {
		$_GPC[substr($key, $cplen)] = $value;
	}
}
unset($cplen, $key, $value);
echo 9999;die;
// 合并接受到的参数并转为html实体
$_GPC = array_merge($_GPC, $_POST);
$_GPC = ihtmlspecialchars($_GPC);

// 拼接访问url
$_W['siteurl'] = $urls['scheme'].'://'.$urls['host'].((!empty($urls['port']) && $urls['port']!='80') ? ':'.$urls['port'] : '') . $_W['script_name'] . '?' . http_build_query($_GET, '', '&');

// 非ajax请求，从php://input获取值
if (!$_W['isajax']) {
	$input = file_get_contents("php://input");
	if (!empty($input)) {
		$__input = @json_decode($input, true);
		if (!empty($__input)) {
			$_GPC['__input'] = $__input;
			$_W['isajax'] = true;
		}
	}
	unset($input, $__input);
}

setting_load();
if (empty($_W['setting']['upload'])) {
    // 仅向 array_merge() 函数输入一个数组，且键名是整数，则该函数将返回带有整数键名的新数组，其键名以 0 开始进行重新索引
	$_W['setting']['upload'] = array_merge($_W['config']['upload']);
}

// 定义开发环境常量
define('DEVELOPMENT', $_W['setting']['copyright']['develop_status'] == 1 || $_W['config']['setting']['development'] == 1);
if(DEVELOPMENT) {
	ini_set('display_errors', '1');
	error_reporting(E_ALL ^ E_NOTICE);
}
if ($_W['config']['setting']['development'] == 2) {
	load()->library('sentry');
	if (class_exists('Raven_Autoloader')) {
		error_reporting(E_ALL ^ E_NOTICE);
		Raven_Autoloader::register();
		$client = new Raven_Client('http://8d52c70dbbed4133b72e3b8916663ae3:0d84397f72204bf1a3f721edf9c782e1@sentry.w7.cc/6');
		$error_handler = new Raven_ErrorHandler($client);
		$error_handler->registerExceptionHandler();
		$error_handler->registerErrorHandler();
		$error_handler->registerShutdownFunction();
	}
}

$_W['os'] = Agent::deviceType();
if($_W['os'] == Agent::DEVICE_MOBILE) {
	$_W['os'] = 'mobile';
} elseif($_W['os'] == Agent::DEVICE_DESKTOP) {
	$_W['os'] = 'windows';
} else {
	$_W['os'] = 'unknown';
}

$_W['container'] = Agent::browserType();
if(Agent::isMicroMessage() == Agent::MICRO_MESSAGE_YES) {
	$_W['container'] = 'wechat';
} elseif ($_W['container'] == Agent::BROWSER_TYPE_ANDROID) {
	$_W['container'] = 'android';
} elseif ($_W['container'] == Agent::BROWSER_TYPE_IPAD) {
	$_W['container'] = 'ipad';
} elseif ($_W['container'] == Agent::BROWSER_TYPE_IPHONE) {
	$_W['container'] = 'iphone';
} elseif ($_W['container'] == Agent::BROWSER_TYPE_IPOD) {
	$_W['container'] = 'ipod';
} elseif ($_W['container'] == Agent::BROWSER_TYPE_XZAPP) {
	$_W['container'] = 'baidu';
} else {
	$_W['container'] = 'unknown';
}

if ($_W['container'] == 'wechat' || $_W['container'] == 'baidu') {
	$_W['platform'] = 'account';
}

$controller = $_GPC['c'];
$action = $_GPC['a'];
$do = $_GPC['do'];
header('Content-Type: text/html; charset=' . $_W['charset']);
