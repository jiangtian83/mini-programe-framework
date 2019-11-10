<?php  namespace think;
class Lang 
{
	private static $lang = array( );
	private static $range = "zh-cn";
	protected static $langDetectVar = "lang";
	protected static $langCookieVar = "think_var";
	protected static $langCookieExpire = 3600;
	protected static $allowLangList = array( );
	protected static $acceptLanguage = array( "zh-hans-cn" => "zh-cn" );
	public static function range($range = "") 
	{
		if( $range ) 
		{
			self::$range = $range;
		}
		return self::$range;
	}
	public static function set($name, $value = NULL, $range = "") 
	{
		$range = ($range ?: self::$range);
		if( !isset(self::$lang[$range]) ) 
		{
			self::$lang[$range] = array( );
		}
		if( is_array($name) ) 
		{
			self::$lang[$range] = array_change_key_case($name) + self::$lang[$range];
			return self::$lang[$range];
		}
		self::$lang[$range][strtolower($name)] = $value;
		return self::$lang[$range][strtolower($name)];
	}
	public static function load($file, $range = "") 
	{
		$range = ($range ?: self::$range);
		$file = (is_string($file) ? array( $file ) : $file);
		if( !isset(self::$lang[$range]) ) 
		{
			self::$lang[$range] = array( );
		}
		$lang = array( );
		foreach( $file as $_file ) 
		{
			if( is_file($_file) ) 
			{
				App::$debug and Log::record("[ LANG ] " . $_file, "info");
				$_lang = include($_file);
				if( is_array($_lang) ) 
				{
					$lang = array_change_key_case($_lang) + $lang;
				}
			}
		}
		if( !empty($lang) ) 
		{
			self::$lang[$range] = $lang + self::$lang[$range];
		}
		return self::$lang[$range];
	}
	public static function has($name, $range = "") 
	{
		$range = ($range ?: self::$range);
		return isset(self::$lang[$range][strtolower($name)]);
	}
	public static function get($name = NULL, $vars = array( ), $range = "") 
	{
		$range = ($range ?: self::$range);
		if( empty($name) ) 
		{
			return self::$lang[$range];
		}
		$key = strtolower($name);
		$value = (isset(self::$lang[$range][$key]) ? self::$lang[$range][$key] : $name);
		if( !empty($vars) && is_array($vars) ) 
		{
			if( key($vars) === 0 ) 
			{
				array_unshift($vars, $value);
				$value = call_user_func_array("sprintf", $vars);
			}
			else 
			{
				$replace = array_keys($vars);
				foreach( $replace as &$v ) 
				{
					$v = "{:" . $v . "}";
				}
				$value = str_replace($replace, $vars, $value);
			}
		}
		return $value;
	}
	public static function detect() 
	{
		$langSet = "";
		if( isset($_GET[self::$langDetectVar]) ) 
		{
			$langSet = strtolower($_GET[self::$langDetectVar]);
		}
		else 
		{
			if( isset($_COOKIE[self::$langCookieVar]) ) 
			{
				$langSet = strtolower($_COOKIE[self::$langCookieVar]);
			}
			else 
			{
				if( isset($_SERVER["HTTP_ACCEPT_LANGUAGE"]) ) 
				{
					preg_match("/^([a-z\\d\\-]+)/i", $_SERVER["HTTP_ACCEPT_LANGUAGE"], $matches);
					$langSet = strtolower($matches[1]);
					$acceptLangs = Config::get("header_accept_lang");
					if( isset($acceptLangs[$langSet]) ) 
					{
						$langSet = $acceptLangs[$langSet];
					}
					else 
					{
						if( isset(self::$acceptLanguage[$langSet]) ) 
						{
							$langSet = self::$acceptLanguage[$langSet];
						}
					}
				}
			}
		}
		if( empty($allowLangList) || in_array($langSet, self::$allowLangList) ) 
		{
			self::$range = ($langSet ?: self::$range);
		}
		return self::$range;
	}
	public static function setLangDetectVar($var) 
	{
		self::$langDetectVar = $var;
	}
	public static function setLangCookieVar($var) 
	{
		self::$langCookieVar = $var;
	}
	public static function setLangCookieExpire($expire) 
	{
		self::$langCookieExpire = $expire;
	}
	public static function setAllowLangList($list) 
	{
		self::$allowLangList = $list;
	}
}
?>