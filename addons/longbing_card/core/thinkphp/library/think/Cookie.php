<?php  namespace think;
class Cookie 
{
	protected static $config = array( "prefix" => "", "expire" => 0, "path" => "/", "domain" => "", "secure" => false, "httponly" => false, "setcookie" => true );
	protected static $init = NULL;
	public static function init(array $config = array( )) 
	{
		if( empty($config) ) 
		{
			$config = Config::get("cookie");
		}
		self::$config = array_merge(self::$config, array_change_key_case($config));
		if( !empty(self::$config["httponly"]) ) 
		{
			ini_set("session.cookie_httponly", 1);
		}
		self::$init = true;
	}
	public static function prefix($prefix = "") 
	{
		if( empty($prefix) ) 
		{
			return self::$config["prefix"];
		}
		self::$config["prefix"] = $prefix;
		return self::$config["prefix"];
	}
	public static function set($name, $value = "", $option = NULL) 
	{
		!isset($init) and self::init();
		if( !is_null($option) ) 
		{
			if( is_numeric($option) ) 
			{
				$option = array( "expire" => $option );
			}
			else 
			{
				if( is_string($option) ) 
				{
					parse_str($option, $option);
				}
			}
			$config = array_merge(self::$config, array_change_key_case($option));
		}
		else 
		{
			$config = self::$config;
		}
		$name = $config["prefix"] . $name;
		if( is_array($value) ) 
		{
			array_walk_recursive($value, "self::jsonFormatProtect", "encode");
			$value = "think:" . json_encode($value);
		}
		$expire = (!empty($config["expire"]) ? $_SERVER["REQUEST_TIME"] + intval($config["expire"]) : 0);
		if( $config["setcookie"] ) 
		{
			setcookie($name, $value, $expire, $config["path"], $config["domain"], $config["secure"], $config["httponly"]);
		}
		$_COOKIE[$name] = $value;
	}
	public static function forever($name, $value = "", $option = NULL) 
	{
		if( is_null($option) || is_numeric($option) ) 
		{
			$option = array( );
		}
		$option["expire"] = 315360000;
		self::set($name, $value, $option);
	}
	public static function has($name, $prefix = NULL) 
	{
		!isset($init) and self::init();
		$prefix = (!is_null($prefix) ? $prefix : self::$config["prefix"]);
		return isset($_COOKIE[$prefix . $name]);
	}
	public static function get($name = "", $prefix = NULL) 
	{
		!isset($init) and self::init();
		$prefix = (!is_null($prefix) ? $prefix : self::$config["prefix"]);
		$key = $prefix . $name;
		if( "" == $name ) 
		{
			if( $prefix ) 
			{
				$value = array( );
				foreach( $_COOKIE as $k => $val ) 
				{
					if( 0 === strpos($k, $prefix) ) 
					{
						$value[$k] = $val;
					}
				}
			}
			else 
			{
				$value = $_COOKIE;
			}
		}
		else 
		{
			if( isset($_COOKIE[$key]) ) 
			{
				$value = $_COOKIE[$key];
				if( 0 === strpos($value, "think:") ) 
				{
					$value = json_decode(substr($value, 6), true);
					array_walk_recursive($value, "self::jsonFormatProtect", "decode");
				}
			}
			else 
			{
				$value = null;
			}
		}
		return $value;
	}
	public static function delete($name, $prefix = NULL) 
	{
		!isset($init) and self::init();
		$config = self::$config;
		$prefix = (!is_null($prefix) ? $prefix : $config["prefix"]);
		$name = $prefix . $name;
		if( $config["setcookie"] ) 
		{
			setcookie($name, "", $_SERVER["REQUEST_TIME"] - 3600, $config["path"], $config["domain"], $config["secure"], $config["httponly"]);
		}
		unset($_COOKIE[$name]);
	}
	public static function clear($prefix = NULL) 
	{
		if( empty($_COOKIE) ) 
		{
			return NULL;
		}
		!isset($init) and self::init();
		$config = self::$config;
		$prefix = (!is_null($prefix) ? $prefix : $config["prefix"]);
		if( $prefix ) 
		{
			foreach( $_COOKIE as $key => $val ) 
			{
				if( 0 === strpos($key, $prefix) ) 
				{
					if( $config["setcookie"] ) 
					{
						setcookie($key, "", $_SERVER["REQUEST_TIME"] - 3600, $config["path"], $config["domain"], $config["secure"], $config["httponly"]);
					}
					unset($_COOKIE[$key]);
				}
			}
		}
	}
	protected static function jsonFormatProtect(&$val, $key, $type = "encode") 
	{
		if( !empty($val) && true !== $val ) 
		{
			$val = ("decode" == $type ? urldecode($val) : urlencode($val));
		}
	}
}
?>