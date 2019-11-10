<?php  namespace think;
class Session 
{
	protected static $prefix = "";
	protected static $init = NULL;
	public static function prefix($prefix = "") 
	{
		empty($init) and self::boot();
		if( empty($prefix) && null !== $prefix ) 
		{
			return self::$prefix;
		}
		self::$prefix = $prefix;
	}
	public static function init(array $config = array( )) 
	{
		if( empty($config) ) 
		{
			$config = Config::get("session");
		}
		App::$debug and Log::record("[ SESSION ] INIT " . var_export($config, true), "info");
		$isDoStart = false;
		if( isset($config["use_trans_sid"]) ) 
		{
			ini_set("session.use_trans_sid", ($config["use_trans_sid"] ? 1 : 0));
		}
		if( !empty($config["auto_start"]) && PHP_SESSION_ACTIVE != session_status() ) 
		{
			ini_set("session.auto_start", 0);
			$isDoStart = true;
		}
		if( isset($config["prefix"]) && ("" === self::$prefix || null === self::$prefix) ) 
		{
			self::$prefix = $config["prefix"];
		}
		if( isset($config["var_session_id"]) && isset($_REQUEST[$config["var_session_id"]]) ) 
		{
			session_id($_REQUEST[$config["var_session_id"]]);
		}
		else 
		{
			if( isset($config["id"]) && !empty($config["id"]) ) 
			{
				session_id($config["id"]);
			}
		}
		if( isset($config["name"]) ) 
		{
			session_name($config["name"]);
		}
		if( isset($config["path"]) ) 
		{
			session_save_path($config["path"]);
		}
		if( isset($config["domain"]) ) 
		{
			ini_set("session.cookie_domain", $config["domain"]);
		}
		if( isset($config["expire"]) ) 
		{
			ini_set("session.gc_maxlifetime", $config["expire"]);
			ini_set("session.cookie_lifetime", $config["expire"]);
		}
		if( isset($config["secure"]) ) 
		{
			ini_set("session.cookie_secure", $config["secure"]);
		}
		if( isset($config["httponly"]) ) 
		{
			ini_set("session.cookie_httponly", $config["httponly"]);
		}
		if( isset($config["use_cookies"]) ) 
		{
			ini_set("session.use_cookies", ($config["use_cookies"] ? 1 : 0));
		}
		if( isset($config["cache_limiter"]) ) 
		{
			session_cache_limiter($config["cache_limiter"]);
		}
		if( isset($config["cache_expire"]) ) 
		{
			session_cache_expire($config["cache_expire"]);
		}
		if( !empty($config["type"]) ) 
		{
			$class = (false !== strpos($config["type"], "\\") ? $config["type"] : "\\think\\session\\driver\\" . ucwords($config["type"]));
			if( !class_exists($class) || !session_set_save_handler(new $class($config)) ) 
			{
				throw new exception\ClassNotFoundException("error session handler:" . $class, $class);
			}
		}
		if( $isDoStart ) 
		{
			session_start();
			self::$init = true;
		}
		else 
		{
			self::$init = false;
		}
	}
	public static function boot() 
	{
		if( is_null(self::$init) ) 
		{
			self::init();
		}
		else 
		{
			if( false === self::$init ) 
			{
				if( PHP_SESSION_ACTIVE != session_status() ) 
				{
					session_start();
				}
				self::$init = true;
			}
		}
	}
	public static function set($name, $value = "", $prefix = NULL) 
	{
		empty($init) and self::boot();
		$prefix = (!is_null($prefix) ? $prefix : self::$prefix);
		if( strpos($name, ".") ) 
		{
			list($name1, $name2) = explode(".", $name);
			if( $prefix ) 
			{
				$_SESSION[$prefix][$name1][$name2] = $value;
			}
			else 
			{
				$_SESSION[$name1][$name2] = $value;
			}
		}
		else 
		{
			if( $prefix ) 
			{
				$_SESSION[$prefix][$name] = $value;
			}
			else 
			{
				$_SESSION[$name] = $value;
			}
		}
	}
	public static function get($name = "", $prefix = NULL) 
	{
		empty($init) and self::boot();
		$prefix = (!is_null($prefix) ? $prefix : self::$prefix);
		if( "" == $name ) 
		{
			$value = ($prefix ? (!empty($_SESSION[$prefix]) ? $_SESSION[$prefix] : array( )) : $_SESSION);
		}
		else 
		{
			if( $prefix ) 
			{
				if( strpos($name, ".") ) 
				{
					list($name1, $name2) = explode(".", $name);
					$value = (isset($_SESSION[$prefix][$name1][$name2]) ? $_SESSION[$prefix][$name1][$name2] : null);
				}
				else 
				{
					$value = (isset($_SESSION[$prefix][$name]) ? $_SESSION[$prefix][$name] : null);
				}
			}
			else 
			{
				if( strpos($name, ".") ) 
				{
					list($name1, $name2) = explode(".", $name);
					$value = (isset($_SESSION[$name1][$name2]) ? $_SESSION[$name1][$name2] : null);
				}
				else 
				{
					$value = (isset($_SESSION[$name]) ? $_SESSION[$name] : null);
				}
			}
		}
		return $value;
	}
	public static function pull($name, $prefix = NULL) 
	{
		$result = self::get($name, $prefix);
		if( $result ) 
		{
			self::delete($name, $prefix);
			return $result;
		}
	}
	public static function flash($name, $value) 
	{
		self::set($name, $value);
		if( !self::has("__flash__.__time__") ) 
		{
			self::set("__flash__.__time__", $_SERVER["REQUEST_TIME_FLOAT"]);
		}
		self::push("__flash__", $name);
	}
	public static function flush() 
	{
		if( self::$init ) 
		{
			$item = self::get("__flash__");
			if( !empty($item) ) 
			{
				$time = $item["__time__"];
				if( $time < $_SERVER["REQUEST_TIME_FLOAT"] ) 
				{
					unset($item["__time__"]);
					self::delete($item);
					self::set("__flash__", array( ));
				}
			}
		}
	}
	public static function delete($name, $prefix = NULL) 
	{
		empty($init) and self::boot();
		$prefix = (!is_null($prefix) ? $prefix : self::$prefix);
		if( is_array($name) ) 
		{
			foreach( $name as $key ) 
			{
				self::delete($key, $prefix);
			}
		}
		else 
		{
			if( strpos($name, ".") ) 
			{
				list($name1, $name2) = explode(".", $name);
				if( $prefix ) 
				{
					unset($_SESSION[$prefix][$name1][$name2]);
				}
				else 
				{
					unset($_SESSION[$name1][$name2]);
				}
			}
			else 
			{
				if( $prefix ) 
				{
					unset($_SESSION[$prefix][$name]);
				}
				else 
				{
					unset($_SESSION[$name]);
				}
			}
		}
	}
	public static function clear($prefix = NULL) 
	{
		empty($init) and self::boot();
		$prefix = (!is_null($prefix) ? $prefix : self::$prefix);
		if( $prefix ) 
		{
			unset($_SESSION[$prefix]);
		}
		else 
		{
			$_SESSION = array( );
		}
	}
	public static function has($name, $prefix = NULL) 
	{
		empty($init) and self::boot();
		$prefix = (!is_null($prefix) ? $prefix : self::$prefix);
		if( strpos($name, ".") ) 
		{
			list($name1, $name2) = explode(".", $name);
			return ($prefix ? isset($_SESSION[$prefix][$name1][$name2]) : isset($_SESSION[$name1][$name2]));
		}
		return ($prefix ? isset($_SESSION[$prefix][$name]) : isset($_SESSION[$name]));
	}
	public static function push($key, $value) 
	{
		$array = self::get($key);
		if( is_null($array) ) 
		{
			$array = array( );
		}
		$array[] = $value;
		self::set($key, $array);
	}
	public static function start() 
	{
		session_start();
		self::$init = true;
	}
	public static function destroy() 
	{
		if( !empty($_SESSION) ) 
		{
			$_SESSION = array( );
		}
		session_unset();
		session_destroy();
		self::$init = null;
	}
	public static function regenerate($delete = false) 
	{
		session_regenerate_id($delete);
	}
	public static function pause() 
	{
		session_write_close();
		self::$init = false;
	}
}
?>