<?php  if( !function_exists("load_trait") ) 
{
	function load_trait($class, $ext = EXT) 
	{
		return think\Loader::import($class, TRAIT_PATH, $ext);
	}
}
if( !function_exists("exception") ) 
{
	function exception($msg, $code = 0, $exception = "") 
	{
		$e = ($exception ?: "\\think\\Exception");
		throw new $e($msg, $code);
	}
}
if( !function_exists("debug") ) 
{
	function debug($start, $end = "", $dec = 6) 
	{
		if( "" == $end ) 
		{
			think\Debug::remark($start);
		}
		else 
		{
			return ("m" == $dec ? think\Debug::getRangeMem($start, $end) : think\Debug::getRangeTime($start, $end, $dec));
		}
	}
}
if( !function_exists("lang") ) 
{
	function lang($name, $vars = array( ), $lang = "") 
	{
		return think\Lang::get($name, $vars, $lang);
	}
}
if( !function_exists("config") ) 
{
	function config($name = "", $value = NULL, $range = "") 
	{
		if( is_null($value) && is_string($name) ) 
		{
			return (0 === strpos($name, "?") ? think\Config::has(substr($name, 1), $range) : think\Config::get($name, $range));
		}
		return think\Config::set($name, $value, $range);
	}
}
if( !function_exists("input") ) 
{
	function input($key = "", $default = NULL, $filter = "") 
	{
		if( 0 === strpos($key, "?") ) 
		{
			$key = substr($key, 1);
			$has = true;
		}
		if( $pos = strpos($key, ".") ) 
		{
			list($method, $key) = explode(".", $key, 2);
			if( !in_array($method, array( "get", "post", "put", "patch", "delete", "route", "param", "request", "session", "cookie", "server", "env", "path", "file" )) ) 
			{
				$key = $method . "." . $key;
				$method = "param";
			}
		}
		else 
		{
			$method = "param";
		}
		if( isset($has) ) 
		{
			return request()->has($key, $method, $default);
		}
		return request()->$method($key, $default, $filter);
	}
}
if( !function_exists("widget") ) 
{
	function widget($name, $data = array( )) 
	{
		return think\Loader::action($name, $data, "widget");
	}
}
if( !function_exists("model") ) 
{
	function model($name = "", $layer = "model", $appendSuffix = false) 
	{
		return think\Loader::model($name, $layer, $appendSuffix);
	}
}
if( !function_exists("validate") ) 
{
	function validate($name = "", $layer = "validate", $appendSuffix = false) 
	{
		return think\Loader::validate($name, $layer, $appendSuffix);
	}
}
if( !function_exists("db") ) 
{
	function db($name = "", $config = array( ), $force = false) 
	{
		return think\Db::connect($config, $force)->name($name);
	}
}
if( !function_exists("controller") ) 
{
	function controller($name, $layer = "controller", $appendSuffix = false) 
	{
		return think\Loader::controller($name, $layer, $appendSuffix);
	}
}
if( !function_exists("action") ) 
{
	function action($url, $vars = array( ), $layer = "controller", $appendSuffix = false) 
	{
		return think\Loader::action($url, $vars, $layer, $appendSuffix);
	}
}
if( !function_exists("import") ) 
{
	function import($class, $baseUrl = "", $ext = EXT) 
	{
		return think\Loader::import($class, $baseUrl, $ext);
	}
}
if( !function_exists("vendor") ) 
{
	function vendor($class, $ext = EXT) 
	{
		return think\Loader::import($class, VENDOR_PATH, $ext);
	}
}
if( !function_exists("dump") ) 
{
	function dump($var, $echo = true, $label = NULL) 
	{
		return think\Debug::dump($var, $echo, $label);
	}
}
if( !function_exists("url") ) 
{
	function url($url = "", $vars = "", $suffix = true, $domain = false) 
	{
		return think\Url::build($url, $vars, $suffix, $domain);
	}
}
if( !function_exists("session") ) 
{
	function session($name, $value = "", $prefix = NULL) 
	{
		if( is_array($name) ) 
		{
			think\Session::init($name);
		}
		else 
		{
			if( is_null($name) ) 
			{
				think\Session::clear(("" === $value ? NULL : $value));
			}
			else 
			{
				if( "" === $value ) 
				{
					return (0 === strpos($name, "?") ? think\Session::has(substr($name, 1), $prefix) : think\Session::get($name, $prefix));
				}
				if( is_null($value) ) 
				{
					return think\Session::delete($name, $prefix);
				}
				return think\Session::set($name, $value, $prefix);
			}
		}
	}
}
if( !function_exists("cookie") ) 
{
	function cookie($name, $value = "", $option = NULL) 
	{
		if( is_array($name) ) 
		{
			think\Cookie::init($name);
		}
		else 
		{
			if( is_null($name) ) 
			{
				think\Cookie::clear($value);
			}
			else 
			{
				if( "" === $value ) 
				{
					return (0 === strpos($name, "?") ? think\Cookie::has(substr($name, 1), $option) : think\Cookie::get($name, $option));
				}
				if( is_null($value) ) 
				{
					return think\Cookie::delete($name);
				}
				return think\Cookie::set($name, $value, $option);
			}
		}
	}
}
if( !function_exists("cache") ) 
{
	function cache($name, $value = "", $options = NULL, $tag = NULL) 
	{
		if( is_array($options) ) 
		{
			$cache = think\Cache::connect($options);
		}
		else 
		{
			if( is_array($name) ) 
			{
				return think\Cache::connect($name);
			}
			$cache = think\Cache::init();
		}
		if( is_null($name) ) 
		{
			return $cache->clear($value);
		}
		if( "" === $value ) 
		{
			return (0 === strpos($name, "?") ? $cache->has(substr($name, 1)) : $cache->get($name));
		}
		if( is_null($value) ) 
		{
			return $cache->rm($name);
		}
		if( 0 === strpos($name, "?") && "" !== $value ) 
		{
			$expire = (is_numeric($options) ? $options : NULL);
			return $cache->remember(substr($name, 1), $value, $expire);
		}
		if( is_array($options) ) 
		{
			$expire = (isset($options["expire"]) ? $options["expire"] : NULL);
		}
		else 
		{
			$expire = (is_numeric($options) ? $options : NULL);
		}
		if( is_null($tag) ) 
		{
			return $cache->set($name, $value, $expire);
		}
		return $cache->tag($tag)->set($name, $value, $expire);
	}
}
if( !function_exists("trace") ) 
{
	function trace($log = "[think]", $level = "log") 
	{
		if( "[think]" === $log ) 
		{
			return think\Log::getLog();
		}
		think\Log::record($log, $level);
	}
}
if( !function_exists("request") ) 
{
	function request() 
	{
		return think\Request::instance();
	}
}
if( !function_exists("response") ) 
{
	function response($data = array( ), $code = 200, $header = array( ), $type = "html") 
	{
		return think\Response::create($data, $type, $code, $header);
	}
}
if( !function_exists("view") ) 
{
	function view($template = "", $vars = array( ), $replace = array( ), $code = 200) 
	{
		return think\Response::create($template, "view", $code)->replace($replace)->assign($vars);
	}
}
if( !function_exists("json") ) 
{
	function json($data = array( ), $code = 200, $header = array( ), $options = array( )) 
	{
		return think\Response::create($data, "json", $code, $header, $options);
	}
}
if( !function_exists("jsonp") ) 
{
	function jsonp($data = array( ), $code = 200, $header = array( ), $options = array( )) 
	{
		return think\Response::create($data, "jsonp", $code, $header, $options);
	}
}
if( !function_exists("xml") ) 
{
	function xml($data = array( ), $code = 200, $header = array( ), $options = array( )) 
	{
		return think\Response::create($data, "xml", $code, $header, $options);
	}
}
if( !function_exists("redirect") ) 
{
	function redirect($url = array( ), $params = array( ), $code = 302, $with = array( )) 
	{
		if( is_integer($params) ) 
		{
			$code = $params;
			$params = array( );
		}
		return think\Response::create($url, "redirect", $code)->params($params)->with($with);
	}
}
if( !function_exists("abort") ) 
{
	function abort($code, $message = NULL, $header = array( )) 
	{
		if( $code instanceof think\Response ) 
		{
			throw new think\exception\HttpResponseException($code);
		}
		throw new think\exception\HttpException($code, $message, NULL, $header);
	}
}
if( !function_exists("halt") ) 
{
	function halt($var) 
	{
		dump($var);
		throw new think\exception\HttpResponseException(new think\Response());
	}
}
if( !function_exists("token") ) 
{
	function token($name = "__token__", $type = "md5") 
	{
		$token = think\Request::instance()->token($name, $type);
		return "<input type=\"hidden\" name=\"" . $name . "\" value=\"" . $token . "\" />";
	}
}
if( !function_exists("load_relation") ) 
{
	function load_relation($resultSet, $relation) 
	{
		$item = current($resultSet);
		if( $item instanceof think\Model ) 
		{
			$item->eagerlyResultSet($resultSet, $relation);
		}
		return $resultSet;
	}
}
if( !function_exists("collection") ) 
{
	function collection($resultSet) 
	{
		$item = current($resultSet);
		if( $item instanceof think\Model ) 
		{
			return think\model\Collection::make($resultSet);
		}
		return think\Collection::make($resultSet);
	}
}
?>