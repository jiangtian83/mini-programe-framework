<?php  namespace think;
class Route 
{
	private static $rules = array( "get" => array( ), "post" => array( ), "put" => array( ), "delete" => array( ), "patch" => array( ), "head" => array( ), "options" => array( ), "*" => array( ), "alias" => array( ), "domain" => array( ), "pattern" => array( ), "name" => array( ) );
	private static $rest = array( "index" => array( "get", "", "index" ), "create" => array( "get", "/create", "create" ), "edit" => array( "get", "/:id/edit", "edit" ), "read" => array( "get", "/:id", "read" ), "save" => array( "post", "", "save" ), "update" => array( "put", "/:id", "update" ), "delete" => array( "delete", "/:id", "delete" ) );
	private static $methodPrefix = array( "get" => "get", "post" => "post", "put" => "put", "delete" => "delete", "patch" => "patch" );
	private static $subDomain = "";
	private static $bind = array( );
	private static $group = array( );
	private static $domainBind = NULL;
	private static $domainRule = NULL;
	private static $domain = NULL;
	private static $option = array( );
	public static function pattern($name = NULL, $rule = "") 
	{
		if( is_array($name) ) 
		{
			self::$rules["pattern"] = array_merge(self::$rules["pattern"], $name);
		}
		else 
		{
			self::$rules["pattern"][$name] = $rule;
		}
	}
	public static function domain($domain, $rule = "", $option = array( ), $pattern = array( )) 
	{
		if( is_array($domain) ) 
		{
			foreach( $domain as $key => $item ) 
			{
				self::domain($key, $item, $option, $pattern);
			}
		}
		else 
		{
			if( $rule instanceof \Closure ) 
			{
				self::setDomain($domain);
				call_user_func_array($rule, array( ));
				self::setDomain(null);
			}
			else 
			{
				if( is_array($rule) ) 
				{
					self::setDomain($domain);
					self::group("", function() use ($rule) 
					{
						self::registerRules($rule);
					}
					, $option, $pattern);
					self::setDomain(null);
				}
				else 
				{
					self::$rules["domain"][$domain]["[bind]"] = array( $rule, $option, $pattern );
				}
			}
		}
	}
	private static function setDomain($domain) 
	{
		self::$domain = $domain;
	}
	public static function bind($bind, $type = "module") 
	{
		self::$bind = array( "type" => $type, $type => $bind );
	}
	public static function name($name = "", $value = NULL) 
	{
		if( is_array($name) ) 
		{
			self::$rules["name"] = $name;
			return self::$rules["name"];
		}
		if( "" === $name ) 
		{
			return self::$rules["name"];
		}
		if( !is_null($value) ) 
		{
			self::$rules["name"][strtolower($name)][] = $value;
		}
		else 
		{
			$name = strtolower($name);
			return (isset(self::$rules["name"][$name]) ? self::$rules["name"][$name] : null);
		}
	}
	public static function getBind($type) 
	{
		return (isset(self::$bind[$type]) ? self::$bind[$type] : null);
	}
	public static function import(array $rule, $type = "*") 
	{
		if( isset($rule["__domain__"]) ) 
		{
			self::domain($rule["__domain__"]);
			unset($rule["__domain__"]);
		}
		if( isset($rule["__pattern__"]) ) 
		{
			self::pattern($rule["__pattern__"]);
			unset($rule["__pattern__"]);
		}
		if( isset($rule["__alias__"]) ) 
		{
			self::alias($rule["__alias__"]);
			unset($rule["__alias__"]);
		}
		if( isset($rule["__rest__"]) ) 
		{
			self::resource($rule["__rest__"]);
			unset($rule["__rest__"]);
		}
		self::registerRules($rule, strtolower($type));
	}
	protected static function registerRules($rules, $type = "*") 
	{
		foreach( $rules as $key => $val ) 
		{
			if( is_numeric($key) ) 
			{
				$key = array_shift($val);
			}
			if( empty($val) ) 
			{
				continue;
			}
			if( is_string($key) && 0 === strpos($key, "[") ) 
			{
				$key = substr($key, 1, -1);
				self::group($key, $val);
			}
			else 
			{
				if( is_array($val) ) 
				{
					self::setRule($key, $val[0], $type, $val[1], (isset($val[2]) ? $val[2] : array( )));
				}
				else 
				{
					self::setRule($key, $val, $type);
				}
			}
		}
	}
	public static function rule($rule, $route = "", $type = "*", $option = array( ), $pattern = array( )) 
	{
		$group = self::getGroup("name");
		if( !is_null($group) ) 
		{
			$option = array_merge(self::getGroup("option"), $option);
			$pattern = array_merge(self::getGroup("pattern"), $pattern);
		}
		$type = strtolower($type);
		if( strpos($type, "|") ) 
		{
			$option["method"] = $type;
			$type = "*";
		}
		if( is_array($rule) && empty($route) ) 
		{
			foreach( $rule as $key => $val ) 
			{
				if( is_numeric($key) ) 
				{
					$key = array_shift($val);
				}
				if( is_array($val) ) 
				{
					$route = $val[0];
					$option1 = array_merge($option, $val[1]);
					$pattern1 = array_merge($pattern, (isset($val[2]) ? $val[2] : array( )));
				}
				else 
				{
					$option1 = null;
					$pattern1 = null;
					$route = $val;
				}
				self::setRule($key, $route, $type, (!is_null($option1) ? $option1 : $option), (!is_null($pattern1) ? $pattern1 : $pattern), $group);
			}
		}
		else 
		{
			self::setRule($rule, $route, $type, $option, $pattern, $group);
		}
	}
	protected static function setRule($rule, $route, $type = "*", $option = array( ), $pattern = array( ), $group = "") 
	{
		if( is_array($rule) ) 
		{
			list($name, $rule) = $rule;
		}
		else 
		{
			if( is_string($route) ) 
			{
				$name = $route;
			}
		}
		if( !isset($option["complete_match"]) ) 
		{
			if( Config::get("route_complete_match") ) 
			{
				$option["complete_match"] = true;
			}
			else 
			{
				if( "\$" == substr($rule, -1, 1) ) 
				{
					$option["complete_match"] = true;
				}
			}
		}
		else 
		{
			if( empty($option["complete_match"]) && "\$" == substr($rule, -1, 1) ) 
			{
				$option["complete_match"] = true;
			}
		}
		if( "\$" == substr($rule, -1, 1) ) 
		{
			$rule = substr($rule, 0, -1);
		}
		if( "/" != $rule || $group ) 
		{
			$rule = trim($rule, "/");
		}
		$vars = self::parseVar($rule);
		if( isset($name) ) 
		{
			$key = ($group ? $group . (($rule ? "/" . $rule : "")) : $rule);
			$suffix = (isset($option["ext"]) ? $option["ext"] : null);
			self::name($name, array( $key, $vars, self::$domain, $suffix ));
		}
		if( isset($option["modular"]) ) 
		{
			$route = $option["modular"] . "/" . $route;
		}
		if( $group ) 
		{
			if( "*" != $type ) 
			{
				$option["method"] = $type;
			}
			if( self::$domain ) 
			{
				self::$rules["domain"][self::$domain]["*"][$group]["rule"][] = array( "rule" => $rule, "route" => $route, "var" => $vars, "option" => $option, "pattern" => $pattern );
			}
			else 
			{
				self::$rules["*"][$group]["rule"][] = array( "rule" => $rule, "route" => $route, "var" => $vars, "option" => $option, "pattern" => $pattern );
			}
		}
		else 
		{
			if( "*" != $type && isset(self::$rules["*"][$rule]) ) 
			{
				unset(self::$rules["*"][$rule]);
			}
			if( self::$domain ) 
			{
				self::$rules["domain"][self::$domain][$type][$rule] = array( "rule" => $rule, "route" => $route, "var" => $vars, "option" => $option, "pattern" => $pattern );
			}
			else 
			{
				self::$rules[$type][$rule] = array( "rule" => $rule, "route" => $route, "var" => $vars, "option" => $option, "pattern" => $pattern );
			}
			if( "*" == $type ) 
			{
				foreach( array( "get", "post", "put", "delete", "patch", "head", "options" ) as $method ) 
				{
					if( self::$domain && !isset(self::$rules["domain"][self::$domain][$method][$rule]) ) 
					{
						self::$rules["domain"][self::$domain][$method][$rule] = true;
					}
					else 
					{
						if( !self::$domain && !isset(self::$rules[$method][$rule]) ) 
						{
							self::$rules[$method][$rule] = true;
						}
					}
				}
			}
		}
	}
	protected static function setOption($options = array( )) 
	{
		self::$option[] = $options;
	}
	public static function getOption() 
	{
		return self::$option;
	}
	public static function getGroup($type) 
	{
		if( isset(self::$group[$type]) ) 
		{
			return self::$group[$type];
		}
		return ("name" == $type ? null : array( ));
	}
	public static function setGroup($name, $option = array( ), $pattern = array( )) 
	{
		self::$group["name"] = $name;
		self::$group["option"] = ($option ?: array( ));
		self::$group["pattern"] = ($pattern ?: array( ));
	}
	public static function group($name, $routes, $option = array( ), $pattern = array( )) 
	{
		if( is_array($name) ) 
		{
			$option = $name;
			$name = (isset($option["name"]) ? $option["name"] : "");
		}
		$currentGroup = self::getGroup("name");
		if( $currentGroup ) 
		{
			$name = $currentGroup . (($name ? "/" . ltrim($name, "/") : ""));
		}
		if( !empty($name) ) 
		{
			if( $routes instanceof \Closure ) 
			{
				$currentOption = self::getGroup("option");
				$currentPattern = self::getGroup("pattern");
				self::setGroup($name, array_merge($currentOption, $option), array_merge($currentPattern, $pattern));
				call_user_func_array($routes, array( ));
				self::setGroup($currentGroup, $currentOption, $currentPattern);
				if( $currentGroup != $name ) 
				{
					self::$rules["*"][$name]["route"] = "";
					self::$rules["*"][$name]["var"] = self::parseVar($name);
					self::$rules["*"][$name]["option"] = $option;
					self::$rules["*"][$name]["pattern"] = $pattern;
				}
			}
			else 
			{
				$item = array( );
				$completeMatch = Config::get("route_complete_match");
				foreach( $routes as $key => $val ) 
				{
					if( is_numeric($key) ) 
					{
						$key = array_shift($val);
					}
					if( is_array($val) ) 
					{
						$route = $val[0];
						$option1 = array_merge($option, (isset($val[1]) ? $val[1] : array( )));
						$pattern1 = array_merge($pattern, (isset($val[2]) ? $val[2] : array( )));
					}
					else 
					{
						$route = $val;
					}
					$options = (isset($option1) ? $option1 : $option);
					$patterns = (isset($pattern1) ? $pattern1 : $pattern);
					if( "\$" == substr($key, -1, 1) ) 
					{
						$options["complete_match"] = true;
						$key = substr($key, 0, -1);
					}
					else 
					{
						if( $completeMatch ) 
						{
							$options["complete_match"] = true;
						}
					}
					$key = trim($key, "/");
					$vars = self::parseVar($key);
					$item[] = array( "rule" => $key, "route" => $route, "var" => $vars, "option" => $options, "pattern" => $patterns );
					$suffix = (isset($options["ext"]) ? $options["ext"] : null);
					self::name($route, array( $name . (($key ? "/" . $key : "")), $vars, self::$domain, $suffix ));
				}
				self::$rules["*"][$name] = array( "rule" => $item, "route" => "", "var" => array( ), "option" => $option, "pattern" => $pattern );
			}
			foreach( array( "get", "post", "put", "delete", "patch", "head", "options" ) as $method ) 
			{
				if( !isset(self::$rules[$method][$name]) ) 
				{
					self::$rules[$method][$name] = true;
				}
				else 
				{
					if( is_array(self::$rules[$method][$name]) ) 
					{
						self::$rules[$method][$name] = array_merge(self::$rules["*"][$name], self::$rules[$method][$name]);
					}
				}
			}
		}
		else 
		{
			if( $routes instanceof \Closure ) 
			{
				$currentOption = self::getGroup("option");
				$currentPattern = self::getGroup("pattern");
				self::setGroup("", array_merge($currentOption, $option), array_merge($currentPattern, $pattern));
				call_user_func_array($routes, array( ));
				self::setGroup($currentGroup, $currentOption, $currentPattern);
			}
			else 
			{
				self::rule($routes, "", "*", $option, $pattern);
			}
		}
	}
	public static function any($rule, $route = "", $option = array( ), $pattern = array( )) 
	{
		self::rule($rule, $route, "*", $option, $pattern);
	}
	public static function get($rule, $route = "", $option = array( ), $pattern = array( )) 
	{
		self::rule($rule, $route, "GET", $option, $pattern);
	}
	public static function post($rule, $route = "", $option = array( ), $pattern = array( )) 
	{
		self::rule($rule, $route, "POST", $option, $pattern);
	}
	public static function put($rule, $route = "", $option = array( ), $pattern = array( )) 
	{
		self::rule($rule, $route, "PUT", $option, $pattern);
	}
	public static function delete($rule, $route = "", $option = array( ), $pattern = array( )) 
	{
		self::rule($rule, $route, "DELETE", $option, $pattern);
	}
	public static function patch($rule, $route = "", $option = array( ), $pattern = array( )) 
	{
		self::rule($rule, $route, "PATCH", $option, $pattern);
	}
	public static function resource($rule, $route = "", $option = array( ), $pattern = array( )) 
	{
		if( is_array($rule) ) 
		{
			foreach( $rule as $key => $val ) 
			{
				if( is_array($val) ) 
				{
					list($val, $option, $pattern) = array_pad($val, 3, array( ));
				}
				self::resource($key, $val, $option, $pattern);
			}
		}
		else 
		{
			if( strpos($rule, ".") ) 
			{
				$array = explode(".", $rule);
				$last = array_pop($array);
				$item = array( );
				foreach( $array as $val ) 
				{
					$item[] = $val . "/:" . ((isset($option["var"][$val]) ? $option["var"][$val] : $val . "_id"));
				}
				$rule = implode("/", $item) . "/" . $last;
			}
			foreach( self::$rest as $key => $val ) 
			{
				if( isset($option["only"]) && !in_array($key, $option["only"]) || isset($option["except"]) && in_array($key, $option["except"]) ) 
				{
					continue;
				}
				if( isset($last) && strpos($val[1], ":id") && isset($option["var"][$last]) ) 
				{
					$val[1] = str_replace(":id", ":" . $option["var"][$last], $val[1]);
				}
				else 
				{
					if( strpos($val[1], ":id") && isset($option["var"][$rule]) ) 
					{
						$val[1] = str_replace(":id", ":" . $option["var"][$rule], $val[1]);
					}
				}
				$item = ltrim($rule . $val[1], "/");
				$option["rest"] = $key;
				self::rule($item . "\$", $route . "/" . $val[2], $val[0], $option, $pattern);
			}
		}
	}
	public static function controller($rule, $route = "", $option = array( ), $pattern = array( )) 
	{
		foreach( self::$methodPrefix as $type => $val ) 
		{
			self::$type($rule . "/:action", $route . "/" . $val . ":action", $option, $pattern);
		}
	}
	public static function alias($rule = NULL, $route = "", $option = array( )) 
	{
		if( is_array($rule) ) 
		{
			self::$rules["alias"] = array_merge(self::$rules["alias"], $rule);
		}
		else 
		{
			self::$rules["alias"][$rule] = ($option ? array( $route, $option ) : $route);
		}
	}
	public static function setMethodPrefix($method, $prefix = "") 
	{
		if( is_array($method) ) 
		{
			self::$methodPrefix = array_merge(self::$methodPrefix, array_change_key_case($method));
		}
		else 
		{
			self::$methodPrefix[strtolower($method)] = $prefix;
		}
	}
	public static function rest($name, $resource = array( )) 
	{
		if( is_array($name) ) 
		{
			self::$rest = ($resource ? $name : array_merge(self::$rest, $name));
		}
		else 
		{
			self::$rest[$name] = $resource;
		}
	}
	public static function miss($route, $method = "*", $option = array( )) 
	{
		self::rule("__miss__", $route, $method, $option, array( ));
	}
	public static function auto($route) 
	{
		self::rule("__auto__", $route, "*", array( ), array( ));
	}
	public static function rules($rules = "") 
	{
		if( is_array($rules) ) 
		{
			self::$rules = $rules;
		}
		else 
		{
			if( $rules ) 
			{
				return (true === $rules ? self::$rules : self::$rules[strtolower($rules)]);
			}
			$rules = self::$rules;
			unset($rules["pattern"]);
			unset($rules["alias"]);
			unset($rules["domain"]);
			unset($rules["name"]);
			return $rules;
		}
	}
	public static function checkDomain($request, &$currentRules, $method = "get") 
	{
		$rules = self::$rules["domain"];
		if( !empty($rules) ) 
		{
			$host = $request->host(true);
			if( isset($rules[$host]) ) 
			{
				$item = $rules[$host];
			}
			else 
			{
				$rootDomain = Config::get("url_domain_root");
				if( $rootDomain ) 
				{
					$domain = explode(".", rtrim(stristr($host, $rootDomain, true), "."));
				}
				else 
				{
					$domain = explode(".", $host, -2);
				}
				if( !empty($domain) ) 
				{
					$subDomain = implode(".", $domain);
					self::$subDomain = $subDomain;
					$domain2 = array_pop($domain);
					if( $domain ) 
					{
						$domain3 = array_pop($domain);
					}
					if( $subDomain && isset($rules[$subDomain]) ) 
					{
						$item = $rules[$subDomain];
					}
					else 
					{
						if( isset($rules["*." . $domain2]) && !empty($domain3) ) 
						{
							$item = $rules["*." . $domain2];
							$panDomain = $domain3;
						}
						else 
						{
							if( isset($rules["*"]) && !empty($domain2) && "www" != $domain2 ) 
							{
								$item = $rules["*"];
								$panDomain = $domain2;
							}
						}
					}
				}
			}
			if( !empty($item) ) 
			{
				if( isset($panDomain) ) 
				{
					$request->route(array( "__domain__" => $panDomain ));
				}
				if( isset($item["[bind]"]) ) 
				{
					list($rule, $option, $pattern) = $item["[bind]"];
					if( !empty($option["https"]) && !$request->isSsl() ) 
					{
						throw new exception\HttpException(404, "must use https request:" . $host);
					}
					if( strpos($rule, "?") ) 
					{
						$array = parse_url($rule);
						$result = $array["path"];
						parse_str($array["query"], $params);
						if( isset($panDomain) ) 
						{
							$pos = array_search("*", $params);
							if( false !== $pos ) 
							{
								$params[$pos] = $panDomain;
							}
						}
						$_GET = array_merge($_GET, $params);
					}
					else 
					{
						$result = $rule;
					}
					if( 0 === strpos($result, "\\") ) 
					{
						self::$bind = array( "type" => "namespace", "namespace" => $result );
					}
					else 
					{
						if( 0 === strpos($result, "@") ) 
						{
							self::$bind = array( "type" => "class", "class" => substr($result, 1) );
						}
						else 
						{
							self::$bind = array( "type" => "module", "module" => $result );
						}
					}
					self::$domainBind = true;
				}
				else 
				{
					self::$domainRule = $item;
					$currentRules = (isset($item[$method]) ? $item[$method] : $item["*"]);
				}
			}
		}
	}
	public static function check($request, $url, $depr = "/", $checkDomain = false) 
	{
		if( !App::$debug && Config::get("route_check_cache") ) 
		{
			$key = self::getCheckCacheKey($request);
			if( Cache::has($key) ) 
			{
				list($rule, $route, $pathinfo, $option, $matches) = Cache::get($key);
				return self::parseRule($rule, $route, $pathinfo, $option, $matches, true);
			}
		}
		$url = str_replace($depr, "|", $url);
		if( isset(self::$rules["alias"][$url]) || isset(self::$rules["alias"][strstr($url, "|", true)]) ) 
		{
			$result = self::checkRouteAlias($request, $url, $depr);
			if( false !== $result ) 
			{
				return $result;
			}
		}
		$method = strtolower($request->method());
		$rules = (isset(self::$rules[$method]) ? self::$rules[$method] : array( ));
		if( $checkDomain ) 
		{
			self::checkDomain($request, $rules, $method);
		}
		$return = self::checkUrlBind($url, $rules, $depr);
		if( false !== $return ) 
		{
			return $return;
		}
		if( "|" != $url ) 
		{
			$url = rtrim($url, "|");
		}
		$item = str_replace("|", "/", $url);
		if( isset($rules[$item]) ) 
		{
			$rule = $rules[$item];
			if( true === $rule ) 
			{
				$rule = self::getRouteExpress($item);
			}
			if( !empty($rule["route"]) && self::checkOption($rule["option"], $request) ) 
			{
				self::setOption($rule["option"]);
				return self::parseRule($item, $rule["route"], $url, $rule["option"]);
			}
		}
		if( !empty($rules) ) 
		{
			return self::checkRoute($request, $rules, $url, $depr);
		}
		return false;
	}
	private static function getRouteExpress($key) 
	{
		return (self::$domainRule ? self::$domainRule["*"][$key] : self::$rules["*"][$key]);
	}
	private static function checkRoute($request, $rules, $url, $depr = "/", $group = "", $options = array( )) 
	{
		foreach( $rules as $key => $item ) 
		{
			if( true === $item ) 
			{
				$item = self::getRouteExpress($key);
			}
			if( !isset($item["rule"]) ) 
			{
				continue;
			}
			$rule = $item["rule"];
			$route = $item["route"];
			$vars = $item["var"];
			$option = $item["option"];
			$pattern = $item["pattern"];
			if( !self::checkOption($option, $request) ) 
			{
				continue;
			}
			if( isset($option["ext"]) ) 
			{
				$url = preg_replace("/\\." . $request->ext() . "\$/i", "", $url);
			}
			if( is_array($rule) ) 
			{
				$pos = strpos(str_replace("<", ":", $key), ":");
				if( false !== $pos ) 
				{
					$str = substr($key, 0, $pos);
				}
				else 
				{
					$str = $key;
				}
				if( is_string($str) && $str && 0 !== stripos(str_replace("|", "/", $url), $str) ) 
				{
					continue;
				}
				self::setOption($option);
				$result = self::checkRoute($request, $rule, $url, $depr, $key, $option);
				if( false !== $result ) 
				{
					return $result;
				}
			}
			else 
			{
				if( $route ) 
				{
					if( "__miss__" == $rule || "__auto__" == $rule ) 
					{
						$var = trim($rule, "__");
						$
						{
							$var}
						= $item;
						continue;
					}
					if( $group ) 
					{
						$rule = $group . (($rule ? "/" . ltrim($rule, "/") : ""));
					}
					self::setOption($option);
					if( isset($options["bind_model"]) && isset($option["bind_model"]) ) 
					{
						$option["bind_model"] = array_merge($options["bind_model"], $option["bind_model"]);
					}
					$result = self::checkRule($rule, $route, $url, $pattern, $option, $depr);
					if( false !== $result ) 
					{
						return $result;
					}
				}
			}
		}
		if( isset($auto) ) 
		{
			return self::parseUrl($auto["route"] . "/" . $url, $depr);
		}
		if( isset($miss) ) 
		{
			return self::parseRule("", $miss["route"], $url, $miss["option"]);
		}
		return false;
	}
	private static function checkRouteAlias($request, $url, $depr) 
	{
		$array = explode("|", $url);
		$alias = array_shift($array);
		$item = self::$rules["alias"][$alias];
		if( is_array($item) ) 
		{
			list($rule, $option) = $item;
			$action = $array[0];
			if( isset($option["allow"]) && !in_array($action, explode(",", $option["allow"])) ) 
			{
				return false;
			}
			if( isset($option["except"]) && in_array($action, explode(",", $option["except"])) ) 
			{
				return false;
			}
			if( isset($option["method"][$action]) ) 
			{
				$option["method"] = $option["method"][$action];
			}
		}
		else 
		{
			$rule = $item;
		}
		$bind = implode("|", $array);
		if( isset($option) && !self::checkOption($option, $request) ) 
		{
			return false;
		}
		if( 0 === strpos($rule, "\\") ) 
		{
			return self::bindToClass($bind, substr($rule, 1), $depr);
		}
		if( 0 === strpos($rule, "@") ) 
		{
			return self::bindToController($bind, substr($rule, 1), $depr);
		}
		return self::bindToModule($bind, $rule, $depr);
	}
	private static function checkUrlBind(&$url, &$rules, $depr = "/") 
	{
		if( !empty($bind) ) 
		{
			$type = self::$bind["type"];
			$bind = self::$bind[$type];
			App::$debug and Log::record("[ BIND ] " . var_export($bind, true), "info");
			switch( $type ) 
			{
				case "class": return self::bindToClass($url, $bind, $depr);
				case "controller": return self::bindToController($url, $bind, $depr);
				case "namespace": return self::bindToNamespace($url, $bind, $depr);
			}
		}
		return false;
	}
	public static function bindToClass($url, $class, $depr = "/") 
	{
		$url = str_replace($depr, "|", $url);
		$array = explode("|", $url, 2);
		$action = (!empty($array[0]) ? $array[0] : Config::get("default_action"));
		if( !empty($array[1]) ) 
		{
			self::parseUrlParams($array[1]);
		}
		return array( "type" => "method", "method" => array( $class, $action ), "var" => array( ) );
	}
	public static function bindToNamespace($url, $namespace, $depr = "/") 
	{
		$url = str_replace($depr, "|", $url);
		$array = explode("|", $url, 3);
		$class = (!empty($array[0]) ? $array[0] : Config::get("default_controller"));
		$method = (!empty($array[1]) ? $array[1] : Config::get("default_action"));
		if( !empty($array[2]) ) 
		{
			self::parseUrlParams($array[2]);
		}
		return array( "type" => "method", "method" => array( $namespace . "\\" . Loader::parseName($class, 1), $method ), "var" => array( ) );
	}
	public static function bindToController($url, $controller, $depr = "/") 
	{
		$url = str_replace($depr, "|", $url);
		$array = explode("|", $url, 2);
		$action = (!empty($array[0]) ? $array[0] : Config::get("default_action"));
		if( !empty($array[1]) ) 
		{
			self::parseUrlParams($array[1]);
		}
		return array( "type" => "controller", "controller" => $controller . "/" . $action, "var" => array( ) );
	}
	public static function bindToModule($url, $controller, $depr = "/") 
	{
		$url = str_replace($depr, "|", $url);
		$array = explode("|", $url, 2);
		$action = (!empty($array[0]) ? $array[0] : Config::get("default_action"));
		if( !empty($array[1]) ) 
		{
			self::parseUrlParams($array[1]);
		}
		return array( "type" => "module", "module" => $controller . "/" . $action );
	}
	private static function checkOption($option, $request) 
	{
		if( isset($option["method"]) && is_string($option["method"]) && false === stripos($option["method"], $request->method()) || isset($option["ajax"]) && $option["ajax"] && !$request->isAjax() || isset($option["ajax"]) && !$option["ajax"] && $request->isAjax() || isset($option["pjax"]) && $option["pjax"] && !$request->isPjax() || isset($option["pjax"]) && !$option["pjax"] && $request->isPjax() || isset($option["ext"]) && false === stripos("|" . $option["ext"] . "|", "|" . $request->ext() . "|") || isset($option["deny_ext"]) && false !== stripos("|" . $option["deny_ext"] . "|", "|" . $request->ext() . "|") || isset($option["domain"]) && !in_array($option["domain"], array( $_SERVER["HTTP_HOST"], self::$subDomain )) || isset($option["https"]) && $option["https"] && !$request->isSsl() || isset($option["https"]) && !$option["https"] && $request->isSsl() || !empty($option["before_behavior"]) && false === Hook::exec($option["before_behavior"]) || !empty($option["callback"]) && is_callable($option["callback"]) && false === call_user_func($option["callback"]) ) 
		{
			return false;
		}
		return true;
	}
	private static function checkRule($rule, $route, $url, $pattern, $option, $depr) 
	{
		if( isset($pattern["__url__"]) && !preg_match((0 === strpos($pattern["__url__"], "/") ? $pattern["__url__"] : "/^" . $pattern["__url__"] . "/"), str_replace("|", $depr, $url)) ) 
		{
			return false;
		}
		if( isset($option["param_depr"]) ) 
		{
			$url = str_replace(array( "|", $option["param_depr"] ), array( $depr, "|" ), $url);
		}
		$len1 = substr_count($url, "|");
		$len2 = substr_count($rule, "/");
		$merge = !empty($option["merge_extra_vars"]);
		if( $merge && $len2 < $len1 ) 
		{
			$url = str_replace("|", $depr, $url);
			$url = implode("|", explode($depr, $url, $len2 + 1));
		}
		if( $len2 <= $len1 || strpos($rule, "[") ) 
		{
			if( !empty($option["complete_match"]) && !$merge && $len1 != $len2 && (false === strpos($rule, "[") || $len2 < $len1 || $len1 < $len2 - substr_count($rule, "[")) ) 
			{
				return false;
			}
			$pattern = array_merge(self::$rules["pattern"], $pattern);
			if( false !== ($match = self::match($url, $rule, $pattern)) ) 
			{
				return self::parseRule($rule, $route, $url, $option, $match);
			}
		}
		return false;
	}
	public static function parseUrl($url, $depr = "/", $autoSearch = false) 
	{
		if( isset(self::$bind["module"]) ) 
		{
			$bind = str_replace("/", $depr, self::$bind["module"]);
			$url = $bind . (("." != substr($bind, -1) ? $depr : "")) . ltrim($url, $depr);
		}
		$url = str_replace($depr, "|", $url);
		list($path, $var) = self::parseUrlPath($url);
		$route = array( null, null, null );
		if( isset($path) ) 
		{
			$module = (Config::get("app_multi_module") ? array_shift($path) : null);
			if( $autoSearch ) 
			{
				$dir = APP_PATH . (($module ? $module . DS : "")) . Config::get("url_controller_layer");
				$suffix = (App::$suffix || Config::get("controller_suffix") ? ucfirst(Config::get("url_controller_layer")) : "");
				$item = array( );
				$find = false;
				foreach( $path as $val ) 
				{
					$item[] = $val;
					$file = $dir . DS . str_replace(".", DS, $val) . $suffix . EXT;
					$file = pathinfo($file, PATHINFO_DIRNAME) . DS . Loader::parseName(pathinfo($file, PATHINFO_FILENAME), 1) . EXT;
					if( is_file($file) ) 
					{
						$find = true;
						break;
					}
					$dir .= DS . Loader::parseName($val);
				}
				if( $find ) 
				{
					$controller = implode(".", $item);
					$path = array_slice($path, count($item));
				}
				else 
				{
					$controller = array_shift($path);
				}
			}
			else 
			{
				$controller = (!empty($path) ? array_shift($path) : null);
			}
			$action = (!empty($path) ? array_shift($path) : null);
			self::parseUrlParams((empty($path) ? "" : implode("|", $path)));
			$route = array( $module, $controller, $action );
			$name = strtolower($module . "/" . Loader::parseName($controller, 1) . "/" . $action);
			$name2 = "";
			if( empty($module) || isset($bind) && $module == $bind ) 
			{
				$name2 = strtolower(Loader::parseName($controller, 1) . "/" . $action);
			}
			if( isset(self::$rules["name"][$name]) || isset(self::$rules["name"][$name2]) ) 
			{
				throw new exception\HttpException(404, "invalid request:" . str_replace("|", $depr, $url));
			}
		}
		return array( "type" => "module", "module" => $route );
	}
	private static function parseUrlPath($url) 
	{
		$url = str_replace("|", "/", $url);
		$url = trim($url, "/");
		$var = array( );
		if( false !== strpos($url, "?") ) 
		{
			$info = parse_url($url);
			$path = explode("/", $info["path"]);
			parse_str($info["query"], $var);
		}
		else 
		{
			if( strpos($url, "/") ) 
			{
				$path = explode("/", $url);
			}
			else 
			{
				$path = array( $url );
			}
		}
		return array( $path, $var );
	}
	private static function match($url, $rule, $pattern) 
	{
		$m2 = explode("/", $rule);
		$m1 = explode("|", $url);
		$var = array( );
		foreach( $m2 as $key => $val ) 
		{
			if( false !== strpos($val, "<") && preg_match_all("/<(\\w+(\\??))>/", $val, $matches) ) 
			{
				$value = array( );
				$replace = array( );
				foreach( $matches[1] as $name ) 
				{
					if( strpos($name, "?") ) 
					{
						$name = substr($name, 0, -1);
						$replace[] = "(" . ((isset($pattern[$name]) ? $pattern[$name] : "\\w+")) . ")?";
					}
					else 
					{
						$replace[] = "(" . ((isset($pattern[$name]) ? $pattern[$name] : "\\w+")) . ")";
					}
					$value[] = $name;
				}
				$val = str_replace($matches[0], $replace, $val);
				if( preg_match("/^" . $val . "\$/", (isset($m1[$key]) ? $m1[$key] : ""), $match) ) 
				{
					array_shift($match);
					foreach( $value as $k => $name ) 
					{
						if( isset($match[$k]) ) 
						{
							$var[$name] = $match[$k];
						}
					}
					continue;
				}
				else 
				{
					return false;
				}
			}
			else 
			{
				if( 0 === strpos($val, "[:") ) 
				{
					$val = substr($val, 1, -1);
					$optional = true;
				}
				else 
				{
					$optional = false;
				}
				if( 0 === strpos($val, ":") ) 
				{
					$name = substr($val, 1);
					if( !$optional && !isset($m1[$key]) ) 
					{
						return false;
					}
					if( isset($m1[$key]) && isset($pattern[$name]) ) 
					{
						if( $pattern[$name] instanceof \Closure ) 
						{
							$result = call_user_func_array($pattern[$name], array( $m1[$key] ));
							if( false === $result ) 
							{
								return false;
							}
						}
						else 
						{
							if( !preg_match((0 === strpos($pattern[$name], "/") ? $pattern[$name] : "/^" . $pattern[$name] . "\$/"), $m1[$key]) ) 
							{
								return false;
							}
						}
					}
					$var[$name] = (isset($m1[$key]) ? $m1[$key] : "");
				}
				else 
				{
					if( !isset($m1[$key]) || 0 !== strcasecmp($val, $m1[$key]) ) 
					{
						return false;
					}
				}
			}
		}
		return $var;
	}
	private static function parseRule($rule, $route, $pathinfo, $option = array( ), $matches = array( ), $fromCache = false) 
	{
		$request = Request::instance();
		if( Config::get("route_check_cache") && !$fromCache ) 
		{
			try 
			{
				$key = self::getCheckCacheKey($request);
				Cache::tag("route_check")->set($key, array( $rule, $route, $pathinfo, $option, $matches ));
			}
			catch( \Exception $e ) 
			{
			}
		}
		if( $rule ) 
		{
			$rule = explode("/", $rule);
			$paths = explode("|", $pathinfo);
			foreach( $rule as $item ) 
			{
				$fun = "";
				if( 0 === strpos($item, "[:") ) 
				{
					$item = substr($item, 1, -1);
				}
				if( 0 === strpos($item, ":") ) 
				{
					$var = substr($item, 1);
					$matches[$var] = array_shift($paths);
				}
				else 
				{
					array_shift($paths);
				}
			}
		}
		else 
		{
			$paths = explode("|", $pathinfo);
		}
		if( is_string($route) && isset($option["prefix"]) ) 
		{
			$route = $option["prefix"] . $route;
		}
		if( is_string($route) && !empty($matches) ) 
		{
			foreach( $matches as $key => $val ) 
			{
				if( false !== strpos($route, ":" . $key) ) 
				{
					$route = str_replace(":" . $key, $val, $route);
				}
			}
		}
		if( isset($option["bind_model"]) ) 
		{
			$bind = array( );
			foreach( $option["bind_model"] as $key => $val ) 
			{
				if( $val instanceof \Closure ) 
				{
					$result = call_user_func_array($val, array( $matches ));
				}
				else 
				{
					if( is_array($val) ) 
					{
						$fields = explode("&", $val[1]);
						$model = $val[0];
						$exception = (isset($val[2]) ? $val[2] : true);
					}
					else 
					{
						$fields = array( "id" );
						$model = $val;
						$exception = true;
					}
					$where = array( );
					$match = true;
					foreach( $fields as $field ) 
					{
						if( !isset($matches[$field]) ) 
						{
							$match = false;
							break;
						}
						$where[$field] = $matches[$field];
					}
					if( $match ) 
					{
						$query = (strpos($model, "\\") ? $model::where($where) : Loader::model($model)->where($where));
						$result = $query->failException($exception)->find();
					}
				}
				if( !empty($result) ) 
				{
					$bind[$key] = $result;
				}
			}
			$request->bind($bind);
		}
		if( !empty($option["response"]) ) 
		{
			Hook::add("response_send", $option["response"]);
		}
		self::parseUrlParams((empty($paths) ? "" : implode("|", $paths)), $matches);
		$request->routeInfo(array( "rule" => $rule, "route" => $route, "option" => $option, "var" => $matches ));
		if( !empty($option["after_behavior"]) ) 
		{
			if( $option["after_behavior"] instanceof \Closure ) 
			{
				$result = call_user_func_array($option["after_behavior"], array( ));
			}
			else 
			{
				foreach( (array) $option["after_behavior"] as $behavior ) 
				{
					$result = Hook::exec($behavior, "");
					if( !is_null($result) ) 
					{
						break;
					}
				}
			}
			if( $result instanceof Response ) 
			{
				return array( "type" => "response", "response" => $result );
			}
			if( is_array($result) ) 
			{
				return $result;
			}
		}
		if( $route instanceof \Closure ) 
		{
			$result = array( "type" => "function", "function" => $route );
		}
		else 
		{
			if( 0 === strpos($route, "/") || strpos($route, "://") ) 
			{
				$result = array( "type" => "redirect", "url" => $route, "status" => (isset($option["status"]) ? $option["status"] : 301) );
			}
			else 
			{
				if( false !== strpos($route, "\\") ) 
				{
					list($path, $var) = self::parseUrlPath($route);
					$route = str_replace("/", "@", implode("/", $path));
					$method = (strpos($route, "@") ? explode("@", $route) : $route);
					$result = array( "type" => "method", "method" => $method, "var" => $var );
				}
				else 
				{
					if( 0 === strpos($route, "@") ) 
					{
						$route = substr($route, 1);
						list($route, $var) = self::parseUrlPath($route);
						$result = array( "type" => "controller", "controller" => implode("/", $route), "var" => $var );
						$request->action(array_pop($route));
						$request->controller(($route ? array_pop($route) : Config::get("default_controller")));
						$request->module(($route ? array_pop($route) : Config::get("default_module")));
						App::$modulePath = APP_PATH . ((Config::get("app_multi_module") ? $request->module() . DS : ""));
					}
					else 
					{
						$result = self::parseModule($route, (isset($option["convert"]) ? $option["convert"] : false));
					}
				}
			}
		}
		if( $request->isGet() && isset($option["cache"]) ) 
		{
			$cache = $option["cache"];
			if( is_array($cache) ) 
			{
				list($key, $expire, $tag) = array_pad($cache, 3, null);
			}
			else 
			{
				$key = str_replace("|", "/", $pathinfo);
				$expire = $cache;
				$tag = null;
			}
			$request->cache($key, $expire, $tag);
		}
		return $result;
	}
	private static function parseModule($url, $convert = false) 
	{
		list($path, $var) = self::parseUrlPath($url);
		$action = array_pop($path);
		$controller = (!empty($path) ? array_pop($path) : null);
		$module = (Config::get("app_multi_module") && !empty($path) ? array_pop($path) : null);
		$method = Request::instance()->method();
		if( Config::get("use_action_prefix") && !empty(self::$methodPrefix[$method]) ) 
		{
			$action = (0 !== strpos($action, self::$methodPrefix[$method]) ? self::$methodPrefix[$method] . $action : $action);
		}
		Request::instance()->route($var);
		return array( "type" => "module", "module" => array( $module, $controller, $action ), "convert" => $convert );
	}
	private static function parseUrlParams($url, &$var = array( )) 
	{
		if( $url ) 
		{
			if( Config::get("url_param_type") ) 
			{
				$var += explode("|", $url);
			}
			else 
			{
				preg_replace_callback("/(\\w+)\\|([^\\|]+)/", function($match) use (&$var) 
				{
					$var[$match[1]] = strip_tags($match[2]);
				}
				, $url);
			}
		}
		Request::instance()->route($var);
	}
	private static function parseVar($rule) 
	{
		$var = array( );
		foreach( explode("/", $rule) as $val ) 
		{
			$optional = false;
			if( false !== strpos($val, "<") && preg_match_all("/<(\\w+(\\??))>/", $val, $matches) ) 
			{
				foreach( $matches[1] as $name ) 
				{
					if( strpos($name, "?") ) 
					{
						$name = substr($name, 0, -1);
						$optional = true;
					}
					else 
					{
						$optional = false;
					}
					$var[$name] = ($optional ? 2 : 1);
				}
			}
			if( 0 === strpos($val, "[:") ) 
			{
				$optional = true;
				$val = substr($val, 1, -1);
			}
			if( 0 === strpos($val, ":") ) 
			{
				$name = substr($val, 1);
				$var[$name] = ($optional ? 2 : 1);
			}
		}
		return $var;
	}
	private static function getCheckCacheKey(Request $request) 
	{
		static $key = NULL;
		if( empty($key) ) 
		{
			if( $callback = Config::get("route_check_cache_key") ) 
			{
				$key = call_user_func($callback, $request);
			}
			else 
			{
				$key = (string) $request->host(true) . "|" . $request->method() . "|" . $request->path();
			}
		}
		return $key;
	}
}
?>