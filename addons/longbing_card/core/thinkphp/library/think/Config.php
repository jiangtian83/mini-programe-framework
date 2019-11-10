<?php  namespace think;
class Config 
{
	private static $config = array( );
	private static $range = "_sys_";
	public static function range($range) 
	{
		self::$range = $range;
		if( !isset(self::$config[$range]) ) 
		{
			self::$config[$range] = array( );
		}
	}
	public static function parse($config, $type = "", $name = "", $range = "") 
	{
		$range = ($range ?: self::$range);
		if( empty($type) ) 
		{
			$type = pathinfo($config, PATHINFO_EXTENSION);
		}
		$class = (false !== strpos($type, "\\") ? $type : "\\think\\config\\driver\\" . ucwords($type));
		return self::set((new $class())->parse($config), $name, $range);
	}
	public static function load($file, $name = "", $range = "") 
	{
		$range = ($range ?: self::$range);
		if( !isset(self::$config[$range]) ) 
		{
			self::$config[$range] = array( );
		}
		if( is_file($file) ) 
		{
			$name = strtolower($name);
			$type = pathinfo($file, PATHINFO_EXTENSION);
			if( "php" == $type ) 
			{
				return self::set(include($file), $name, $range);
			}
			if( "yaml" == $type && function_exists("yaml_parse_file") ) 
			{
				return self::set(yaml_parse_file($file), $name, $range);
			}
			return self::parse($file, $type, $name, $range);
		}
		return self::$config[$range];
	}
	public static function has($name, $range = "") 
	{
		$range = ($range ?: self::$range);
		if( !strpos($name, ".") ) 
		{
			return isset(self::$config[$range][strtolower($name)]);
		}
		$name = explode(".", $name, 2);
		return isset(self::$config[$range][strtolower($name[0])][$name[1]]);
	}
	public static function get($name = NULL, $range = "") 
	{
		$range = ($range ?: self::$range);
		if( empty($name) && isset(self::$config[$range]) ) 
		{
			return self::$config[$range];
		}
		if( !strpos($name, ".") ) 
		{
			$name = strtolower($name);
			return (isset(self::$config[$range][$name]) ? self::$config[$range][$name] : null);
		}
		$name = explode(".", $name, 2);
		$name[0] = strtolower($name[0]);
		if( !isset(self::$config[$range][$name[0]]) ) 
		{
			$module = Request::instance()->module();
			$file = CONF_PATH . (($module ? $module . DS : "")) . "extra" . DS . $name[0] . CONF_EXT;
			is_file($file) and self::load($file, $name[0]);
		}
		return (isset(self::$config[$range][$name[0]][$name[1]]) ? self::$config[$range][$name[0]][$name[1]] : null);
	}
	public static function set($name, $value = NULL, $range = "") 
	{
		$range = ($range ?: self::$range);
		if( !isset(self::$config[$range]) ) 
		{
			self::$config[$range] = array( );
		}
		if( is_string($name) ) 
		{
			if( !strpos($name, ".") ) 
			{
				self::$config[$range][strtolower($name)] = $value;
			}
			else 
			{
				$name = explode(".", $name, 2);
				self::$config[$range][strtolower($name[0])][$name[1]] = $value;
			}
			return $value;
		}
		if( is_array($name) ) 
		{
			if( !empty($value) ) 
			{
				self::$config[$range][$value] = (isset(self::$config[$range][$value]) ? array_merge(self::$config[$range][$value], $name) : $name);
				return self::$config[$range][$value];
			}
			self::$config[$range] = array_merge(self::$config[$range], array_change_key_case($name));
			return self::$config[$range];
		}
		return self::$config[$range];
	}
	public static function reset($range = "") 
	{
		$range = ($range ?: self::$range);
		if( true === $range ) 
		{
			self::$config = array( );
		}
		else 
		{
			self::$config[$range] = array( );
		}
	}
}
?>