<?php  define("THINK_VERSION", "5.0.21");
define("THINK_START_TIME", microtime(true));
define("THINK_START_MEM", memory_get_usage());
define("EXT", ".php");
define("DS", DIRECTORY_SEPARATOR);
defined("THINK_PATH") or define("THINK_PATH", __DIR__ . DS);
define("LIB_PATH", THINK_PATH . "library" . DS);
define("CORE_PATH", LIB_PATH . "think" . DS);
define("TRAIT_PATH", LIB_PATH . "traits" . DS);
defined("APP_PATH") or define("APP_PATH", dirname($_SERVER["SCRIPT_FILENAME"]) . DS);
defined("ROOT_PATH") or define("ROOT_PATH", dirname(realpath(APP_PATH)) . DS);
defined("EXTEND_PATH") or define("EXTEND_PATH", ROOT_PATH . "extend" . DS);
defined("VENDOR_PATH") or define("VENDOR_PATH", ROOT_PATH . "vendor" . DS);
defined("RUNTIME_PATH") or define("RUNTIME_PATH", ROOT_PATH . "runtime" . DS);
defined("LOG_PATH") or define("LOG_PATH", RUNTIME_PATH . "log" . DS);
defined("CACHE_PATH") or define("CACHE_PATH", RUNTIME_PATH . "cache" . DS);
defined("TEMP_PATH") or define("TEMP_PATH", RUNTIME_PATH . "temp" . DS);
defined("CONF_PATH") or define("CONF_PATH", APP_PATH);
defined("CONF_EXT") or define("CONF_EXT", EXT);
defined("ENV_PREFIX") or define("ENV_PREFIX", "PHP_");
defined("APP_NAME") or define("APP_NAME", "longbing_multi");
defined("APP_STATIC_PATH") or define("APP_STATIC_PATH", "//" . $_SERVER["HTTP_HOST"] . "/static/");
if( defined("ADDON_PATH") ) 
{
	defined("PUBLIC_PATH") or define("PUBLIC_PATH", ADDON_PATH . "/core/public" . DS);
}
else 
{
	defined("PUBLIC_PATH") or define("PUBLIC_PATH", ROOT_PATH . "public" . DS);
}
define("IS_CLI", (PHP_SAPI == "cli" ? true : false));
define("IS_WIN", strpos(PHP_OS, "WIN") !== false);
require(CORE_PATH . "Loader.php");
if( is_file(ROOT_PATH . ".env") ) 
{
	$env = parse_ini_file(ROOT_PATH . ".env", true);
	foreach( $env as $key => $val ) 
	{
		$name = ENV_PREFIX . strtoupper($key);
		if( is_array($val) ) 
		{
			foreach( $val as $k => $v ) 
			{
				$item = $name . "_" . strtoupper($k);
				putenv((string) $item . "=" . $v);
			}
		}
		else 
		{
			putenv((string) $name . "=" . $val);
		}
	}
}
think\Loader::register();
think\Error::register();
think\Config::set(include(THINK_PATH . "convention" . EXT));
?>