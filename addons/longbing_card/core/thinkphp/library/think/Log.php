<?php  namespace think;
class Log 
{
	protected static $log = array( );
	protected static $config = array( );
	protected static $type = array( "log", "error", "info", "sql", "notice", "alert", "debug" );
	protected static $driver = NULL;
	protected static $key = NULL;
	const LOG = "log";
	const ERROR = "error";
	const INFO = "info";
	const SQL = "sql";
	const NOTICE = "notice";
	const ALERT = "alert";
	const DEBUG = "debug";
	public static function init($config = array( )) 
	{
		$type = (isset($config["type"]) ? $config["type"] : "File");
		$class = (false !== strpos($type, "\\") ? $type : "\\think\\log\\driver\\" . ucwords($type));
		self::$config = $config;
		unset($config["type"]);
		if( class_exists($class) ) 
		{
			self::$driver = new $class($config);
			App::$debug and Log::record("[ LOG ] INIT " . $type, "info");
		}
		else 
		{
			throw new exception\ClassNotFoundException("class not exists:" . $class, $class);
		}
	}
	public static function getLog($type = "") 
	{
		return ($type ? self::$log[$type] : self::$log);
	}
	public static function record($msg, $type = "log") 
	{
		self::$log[$type][] = $msg;
		IS_CLI and self::save();
	}
	public static function clear() 
	{
		self::$log = array( );
	}
	public static function key($key) 
	{
		self::$key = $key;
	}
	public static function check($config) 
	{
		return !self::$key || empty($config["allow_key"]) || in_array(self::$key, $config["allow_key"]);
	}
	public static function save() 
	{
		if( empty($log) ) 
		{
			return true;
		}
		is_null(self::$driver) and self::init(Config::get("log"));
		if( !self::check(self::$config) ) 
		{
			return false;
		}
		if( empty(self::$config["level"]) ) 
		{
			$log = self::$log;
			if( !App::$debug && isset($log["debug"]) ) 
			{
				unset($log["debug"]);
			}
		}
		else 
		{
			$log = array( );
			foreach( self::$config["level"] as $level ) 
			{
				if( isset(self::$log[$level]) ) 
				{
					$log[$level] = self::$log[$level];
				}
			}
		}
		if( $result = self::$driver->save($log) ) 
		{
			self::$log = array( );
		}
		Hook::listen("log_write_done", $log);
		return $result;
	}
	public static function write($msg, $type = "log", $force = false) 
	{
		$log = self::$log;
		if( true !== $force && !empty(self::$config["level"]) && !in_array($type, self::$config["level"]) ) 
		{
			return false;
		}
		$log[$type][] = $msg;
		Hook::listen("log_write", $log);
		is_null(self::$driver) and self::init(Config::get("log"));
		if( $result = self::$driver->save($log) ) 
		{
			self::$log = array( );
		}
		return $result;
	}
	public static function __callStatic($method, $args) 
	{
		if( in_array($method, self::$type) ) 
		{
			array_push($args, $method);
			call_user_func_array("\\think\\Log::record", $args);
		}
	}
}
?>