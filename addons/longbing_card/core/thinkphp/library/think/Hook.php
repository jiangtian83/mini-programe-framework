<?php  namespace think;
class Hook 
{
	private static $tags = array( );
	public static function add($tag, $behavior, $first = false) 
	{
		isset(self::$tags[$tag]) or self::$tags[$tag] = array( );
		if( is_array($behavior) && !is_callable($behavior) ) 
		{
			if( !array_key_exists("_overlay", $behavior) || !$behavior["_overlay"] ) 
			{
				unset($behavior["_overlay"]);
				self::$tags[$tag] = array_merge(self::$tags[$tag], $behavior);
			}
			else 
			{
				unset($behavior["_overlay"]);
				self::$tags[$tag] = $behavior;
			}
		}
		else 
		{
			if( $first ) 
			{
				array_unshift(self::$tags[$tag], $behavior);
			}
			else 
			{
				self::$tags[$tag][] = $behavior;
			}
		}
	}
	public static function import(array $tags, $recursive = true) 
	{
		if( $recursive ) 
		{
			foreach( $tags as $tag => $behavior ) 
			{
				self::add($tag, $behavior);
			}
		}
		else 
		{
			self::$tags = $tags + self::$tags;
		}
	}
	public static function get($tag = "") 
	{
		if( empty($tag) ) 
		{
			return self::$tags;
		}
		return (array_key_exists($tag, self::$tags) ? self::$tags[$tag] : array( ));
	}
	public static function listen($tag, &$params = NULL, $extra = NULL, $once = false) 
	{
		$results = array( );
		foreach( static::get($tag) as $key => $name ) 
		{
			$results[$key] = self::exec($name, $tag, $params, $extra);
			if( false === $results[$key] || !is_null($results[$key]) && $once ) 
			{
				break;
			}
		}
		return ($once ? end($results) : $results);
	}
	public static function exec($class, $tag = "", &$params = NULL, $extra = NULL) 
	{
		App::$debug and Debug::remark("behavior_start", "time");
		$method = Loader::parseName($tag, 1, false);
		if( $class instanceof \Closure ) 
		{
			$result = call_user_func_array($class, array( $params, $extra ));
			$class = "Closure";
		}
		else 
		{
			if( is_array($class) ) 
			{
				list($class, $method) = $class;
				$result = (new $class())->$method($params, $extra);
				$class = $class . "->" . $method;
			}
			else 
			{
				if( is_object($class) ) 
				{
					$result = $class->$method($params, $extra);
					$class = get_class($class);
				}
				else 
				{
					if( strpos($class, "::") ) 
					{
						$result = call_user_func_array($class, array( $params, $extra ));
					}
					else 
					{
						$obj = new $class();
						$method = ($tag && is_callable(array( $obj, $method )) ? $method : "run");
						$result = $obj->$method($params, $extra);
					}
				}
			}
		}
		if( App::$debug ) 
		{
			Debug::remark("behavior_end", "time");
			Log::record("[ BEHAVIOR ] Run " . $class . " @" . $tag . " [ RunTime:" . Debug::getRangeTime("behavior_start", "behavior_end") . "s ]", "info");
		}
		return $result;
	}
}
?>