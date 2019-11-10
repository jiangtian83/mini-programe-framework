<?php  namespace think;
class Db 
{
	private static $instance = array( );
	public static $queryTimes = 0;
	public static $executeTimes = 0;
	public static function connect($config = array( ), $name = false) 
	{
		if( false === $name ) 
		{
			$name = md5(serialize($config));
		}
		if( true === $name || !isset(self::$instance[$name]) ) 
		{
			$options = self::parseConfig($config);
			if( empty($options["type"]) ) 
			{
				throw new \InvalidArgumentException("Undefined db type");
			}
			$class = (false !== strpos($options["type"], "\\") ? $options["type"] : "\\think\\db\\connector\\" . ucwords($options["type"]));
			if( App::$debug ) 
			{
				Log::record("[ DB ] INIT " . $options["type"], "info");
			}
			if( true === $name ) 
			{
				$name = md5(serialize($config));
			}
			self::$instance[$name] = new $class($options);
		}
		return self::$instance[$name];
	}
	public static function clear() 
	{
		self::$instance = array( );
	}
	private static function parseConfig($config) 
	{
		if( empty($config) ) 
		{
			$config = Config::get("database");
		}
		else 
		{
			if( is_string($config) && false === strpos($config, "/") ) 
			{
				$config = Config::get($config);
			}
		}
		return (is_string($config) ? self::parseDsn($config) : $config);
	}
	private static function parseDsn($dsnStr) 
	{
		$info = parse_url($dsnStr);
		if( !$info ) 
		{
			return array( );
		}
		$dsn = array( "type" => $info["scheme"], "username" => (isset($info["user"]) ? $info["user"] : ""), "password" => (isset($info["pass"]) ? $info["pass"] : ""), "hostname" => (isset($info["host"]) ? $info["host"] : ""), "hostport" => (isset($info["port"]) ? $info["port"] : ""), "database" => (!empty($info["path"]) ? ltrim($info["path"], "/") : ""), "charset" => (isset($info["fragment"]) ? $info["fragment"] : "utf8") );
		if( isset($info["query"]) ) 
		{
			parse_str($info["query"], $dsn["params"]);
		}
		else 
		{
			$dsn["params"] = array( );
		}
		return $dsn;
	}
	public static function __callStatic($method, $params) 
	{
		return call_user_func_array(array( self::connect(), $method ), $params);
	}
}
?>