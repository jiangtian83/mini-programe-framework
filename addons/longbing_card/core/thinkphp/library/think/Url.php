<?php  namespace think;
class Url 
{
	protected static $root = NULL;
	protected static $bindCheck = NULL;
	public static function build($url = "", $vars = "", $suffix = true, $domain = false) 
	{
		if( false === $domain && Route::rules("domain") ) 
		{
			$domain = true;
		}
		if( 0 === strpos($url, "[") && ($pos = strpos($url, "]")) ) 
		{
			$name = substr($url, 1, $pos - 1);
			$url = "name" . substr($url, $pos + 1);
		}
		if( false === strpos($url, "://") && 0 !== strpos($url, "/") ) 
		{
			$info = parse_url($url);
			$url = (!empty($info["path"]) ? $info["path"] : "");
			if( isset($info["fragment"]) ) 
			{
				$anchor = $info["fragment"];
				if( false !== strpos($anchor, "?") ) 
				{
					list($anchor, $info["query"]) = explode("?", $anchor, 2);
				}
				if( false !== strpos($anchor, "@") ) 
				{
					list($anchor, $domain) = explode("@", $anchor, 2);
				}
			}
			else 
			{
				if( strpos($url, "@") && false === strpos($url, "\\") ) 
				{
					list($url, $domain) = explode("@", $url, 2);
				}
			}
		}
		if( is_string($vars) ) 
		{
			parse_str($vars, $vars);
		}
		if( $url ) 
		{
			$rule = Route::name((isset($name) ? $name : $url . ((isset($info["query"]) ? "?" . $info["query"] : ""))));
			if( is_null($rule) && isset($info["query"]) ) 
			{
				$rule = Route::name($url);
				parse_str($info["query"], $params);
				$vars = array_merge($params, $vars);
				unset($info["query"]);
			}
		}
		if( !empty($rule) && ($match = self::getRuleUrl($rule, $vars)) ) 
		{
			$url = $match[0];
			$url = preg_replace(array( "/(\\W)\\?\$/", "/(\\W)\\?/" ), array( "", "\\1" ), $url);
			if( !empty($match[1]) ) 
			{
				$domain = $match[1];
			}
			if( !is_null($match[2]) ) 
			{
				$suffix = $match[2];
			}
		}
		else 
		{
			if( !empty($rule) && isset($name) ) 
			{
				throw new \InvalidArgumentException("route name not exists:" . $name);
			}
			$alias = Route::rules("alias");
			$matchAlias = false;
			if( $alias ) 
			{
				foreach( $alias as $key => $val ) 
				{
					if( is_array($val) ) 
					{
						$val = $val[0];
					}
					if( 0 === strpos($url, $val) ) 
					{
						$url = $key . substr($url, strlen($val));
						$matchAlias = true;
						break;
					}
				}
			}
			if( !$matchAlias ) 
			{
				$url = self::parseUrl($url, $domain);
			}
			if( isset($info["query"]) ) 
			{
				parse_str($info["query"], $params);
				$vars = array_merge($params, $vars);
			}
		}
		if( !self::$bindCheck ) 
		{
			$type = Route::getBind("type");
			if( $type ) 
			{
				$bind = Route::getBind($type);
				if( $bind && 0 === strpos($url, $bind) ) 
				{
					$url = substr($url, strlen($bind) + 1);
				}
			}
		}
		$depr = Config::get("pathinfo_depr");
		$url = str_replace("/", $depr, $url);
		$suffix = (in_array($url, array( "/", "" )) ? "" : self::parseSuffix($suffix));
		$anchor = (!empty($anchor) ? "#" . $anchor : "");
		if( !empty($vars) ) 
		{
			if( Config::get("url_common_param") ) 
			{
				$vars = http_build_query($vars);
				$url .= $suffix . "?" . $vars . $anchor;
			}
			else 
			{
				$paramType = Config::get("url_param_type");
				foreach( $vars as $var => $val ) 
				{
					if( "" !== trim($val) ) 
					{
						if( $paramType ) 
						{
							$url .= $depr . urlencode($val);
						}
						else 
						{
							$url .= $depr . $var . $depr . urlencode($val);
						}
					}
				}
				$url .= $suffix . $anchor;
			}
		}
		else 
		{
			$url .= $suffix . $anchor;
		}
		$domain = self::parseDomain($url, $domain);
		$url = $domain . rtrim((self::$root ?: Request::instance()->root()), "/") . "/" . ltrim($url, "/");
		self::$bindCheck = false;
		return $url;
	}
	protected static function parseUrl($url, &$domain) 
	{
		$request = Request::instance();
		if( 0 === strpos($url, "/") ) 
		{
			$url = substr($url, 1);
		}
		else 
		{
			if( false !== strpos($url, "\\") ) 
			{
				$url = ltrim(str_replace("\\", "/", $url), "/");
			}
			else 
			{
				if( 0 === strpos($url, "@") ) 
				{
					$url = substr($url, 1);
				}
				else 
				{
					$module = $request->module();
					$domains = Route::rules("domain");
					if( true === $domain && 2 == substr_count($url, "/") ) 
					{
						$current = $request->host();
						$match = array( );
						$pos = array( );
						foreach( $domains as $key => $item ) 
						{
							if( isset($item["[bind]"]) && 0 === strpos($url, $item["[bind]"][0]) ) 
							{
								$pos[$key] = strlen($item["[bind]"][0]) + 1;
								$match[] = $key;
								$module = "";
							}
						}
						if( $match ) 
						{
							$domain = current($match);
							foreach( $match as $item ) 
							{
								if( 0 === strpos($current, $item) ) 
								{
									$domain = $item;
								}
							}
							self::$bindCheck = true;
							$url = substr($url, $pos[$domain]);
						}
					}
					else 
					{
						if( $domain && isset($domains[$domain]["[bind]"][0]) ) 
						{
							$bindModule = $domains[$domain]["[bind]"][0];
							if( $bindModule && !in_array($bindModule[0], array( "\\", "@" )) ) 
							{
								$module = "";
							}
						}
					}
					$module = ($module ? $module . "/" : "");
					$controller = $request->controller();
					if( "" == $url ) 
					{
						$action = $request->action();
					}
					else 
					{
						$path = explode("/", $url);
						$action = array_pop($path);
						$controller = (empty($path) ? $controller : array_pop($path));
						$module = (empty($path) ? $module : array_pop($path) . "/");
					}
					if( Config::get("url_convert") ) 
					{
						$action = strtolower($action);
						$controller = Loader::parseName($controller);
					}
					$url = $module . $controller . "/" . $action;
				}
			}
		}
		return $url;
	}
	protected static function parseDomain(&$url, $domain) 
	{
		if( !$domain ) 
		{
			return "";
		}
		$request = Request::instance();
		$rootDomain = Config::get("url_domain_root");
		if( true === $domain ) 
		{
			$domain = (Config::get("app_host") ?: $request->host());
			$domains = Route::rules("domain");
			if( $domains ) 
			{
				$route_domain = array_keys($domains);
				foreach( $route_domain as $domain_prefix ) 
				{
					if( 0 === strpos($domain_prefix, "*.") && strpos($domain, ltrim($domain_prefix, "*.")) !== false ) 
					{
						foreach( $domains as $key => $rule ) 
						{
							$rule = (is_array($rule) ? $rule[0] : $rule);
							if( is_string($rule) && false === strpos($key, "*") && 0 === strpos($url, $rule) ) 
							{
								$url = ltrim($url, $rule);
								$domain = $key;
								if( !empty($rootDomain) ) 
								{
									$domain .= $rootDomain;
								}
								break;
							}
							if( false !== strpos($key, "*") ) 
							{
								if( !empty($rootDomain) ) 
								{
									$domain .= $rootDomain;
								}
								break;
							}
						}
					}
				}
			}
		}
		else 
		{
			if( empty($rootDomain) ) 
			{
				$host = (Config::get("app_host") ?: $request->host());
				$rootDomain = (1 < substr_count($host, ".") ? substr(strstr($host, "."), 1) : $host);
			}
			if( substr_count($domain, ".") < 2 && !strpos($domain, $rootDomain) ) 
			{
				$domain .= "." . $rootDomain;
			}
		}
		if( false !== strpos($domain, "://") ) 
		{
			$scheme = "";
		}
		else 
		{
			$scheme = ($request->isSsl() || Config::get("is_https") ? "https://" : "http://");
		}
		return $scheme . $domain;
	}
	protected static function parseSuffix($suffix) 
	{
		if( $suffix ) 
		{
			$suffix = (true === $suffix ? Config::get("url_html_suffix") : $suffix);
			if( $pos = strpos($suffix, "|") ) 
			{
				$suffix = substr($suffix, 0, $pos);
			}
		}
		return (empty($suffix) || 0 === strpos($suffix, ".") ? $suffix : "." . $suffix);
	}
	public static function getRuleUrl($rule, &$vars = array( )) 
	{
		foreach( $rule as $item ) 
		{
			list($url, $pattern, $domain, $suffix) = $item;
			if( empty($pattern) ) 
			{
				return array( rtrim($url, "\$"), $domain, $suffix );
			}
			$type = Config::get("url_common_param");
			foreach( $pattern as $key => $val ) 
			{
				if( isset($vars[$key]) ) 
				{
					$url = str_replace(array( "[:" . $key . "]", "<" . $key . "?>", ":" . $key . "", "<" . $key . ">" ), ($type ? $vars[$key] : urlencode($vars[$key])), $url);
					unset($vars[$key]);
					$result = array( $url, $domain, $suffix );
				}
				else 
				{
					if( 2 == $val ) 
					{
						$url = str_replace(array( "/[:" . $key . "]", "[:" . $key . "]", "<" . $key . "?>" ), "", $url);
						$result = array( $url, $domain, $suffix );
					}
					else 
					{
						break;
					}
				}
			}
			if( isset($result) ) 
			{
				return $result;
			}
		}
		return false;
	}
	public static function root($root) 
	{
		self::$root = $root;
		Request::instance()->root($root);
	}
}