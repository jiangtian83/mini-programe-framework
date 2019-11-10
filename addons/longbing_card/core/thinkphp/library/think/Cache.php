<?php  namespace think;
class Cache 
{
	public static $instance = array( );
	public static $readTimes = 0;
	public static $writeTimes = 0;
	public static $handler = NULL;
	public static function connect(array $options = array( ), $name = false) 
	{
		$type = (!empty($options["type"]) ? $options["type"] : "File");
		if( false === $name ) 
		{
			$name = md5(serialize($options));
		}
		if( true === $name || !isset(self::$instance[$name]) ) 
		{
			$class = (false === strpos($type, "\\") ? "\\think\\cache\\driver\\" . ucwords($type) : $type);
			App::$debug and Log::record("[ CACHE ] INIT " . $type, "info");
			if( true === $name ) 
			{
				return new $class($options);
			}
			self::$instance[$name] = new $class($options);
		}
		return self::$instance[$name];
	}
	public static function init(array $options = array( )) 
	{
		if( is_null(self::$handler) ) 
		{
			if( empty($options) && "complex" == Config::get("cache.type") ) 
			{
				$default = Config::get("cache.default");
				$options = (Config::get("cache." . $default["type"]) ?: $default);
			}
			else 
			{
				if( empty($options) ) 
				{
					$options = Config::get("cache");
				}
			}
			self::$handler = self::connect($options);
		}
		return self::$handler;
	}
	public static function store($name = "") 
	{
		if( "" !== $name && "complex" == Config::get("cache.type") ) 
		{
			return self::connect(Config::get("cache." . $name), strtolower($name));
		}
		return self::init();
	}
	public static function has($name) 
	{
		self::$readTimes++;
		return self::init()->has($name);
	}
	public static function get($name, $default = false) 
	{
		self::$readTimes++;
		return self::init()->get($name, $default);
	}
	public static function set($name, $value, $expire = NULL) 
	{
		self::$writeTimes++;
		return self::init()->set($name, $value, $expire);
	}
	public static function inc($name, $step = 1) 
	{
		self::$writeTimes++;
		return self::init()->inc($name, $step);
	}
	public static function dec($name, $step = 1) 
	{
		self::$writeTimes++;
		return self::init()->dec($name, $step);
	}
	public static function rm($name) 
	{
		self::$writeTimes++;
		return self::init()->rm($name);
	}
	public static function clear($tag = NULL) 
	{
		self::$writeTimes++;
		return self::init()->clear($tag);
	}
	public static function pull($name) 
	{
		self::$readTimes++;
		self::$writeTimes++;
		return self::init()->pull($name);
	}
	public static function remember($name, $value, $expire = NULL) 
	{
		self::$readTimes++;
		return self::init()->remember($name, $value, $expire);
	}
	public static function tag($name, $keys = NULL, $overlay = false) 
	{
		return self::init()->tag($name, $keys, $overlay);
	}
}
?>