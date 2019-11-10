<?php  namespace think;
class App 
{
	protected static $init = false;
	public static $modulePath = NULL;
	public static $debug = true;
	public static $namespace = "app";
	public static $suffix = false;
	protected static $routeCheck = NULL;
	protected static $routeMust = NULL;
	protected static $dispatch = NULL;
	protected static $file = array( );
	public static function run(Request $request = NULL) 
	{
		$request = (is_null($request) ? Request::instance() : $request);
		try 
		{
			$config = self::initCommon();
			if( defined("BIND_MODULE") ) 
			{
				BIND_MODULE and Route::bind(BIND_MODULE);
			}
			else 
			{
				if( $config["auto_bind_module"] ) 
				{
					$name = pathinfo($request->baseFile(), PATHINFO_FILENAME);
					if( $name && "index" != $name && is_dir(APP_PATH . $name) ) 
					{
						Route::bind($name);
					}
				}
			}
			$request->filter($config["default_filter"]);
			Lang::range($config["default_lang"]);
			$config["lang_switch_on"] and Lang::detect();
			$request->langset(Lang::range());
			Lang::load(array( THINK_PATH . "lang" . DS . $request->langset() . EXT, APP_PATH . "lang" . DS . $request->langset() . EXT ));
			Hook::listen("app_dispatch", self::$dispatch);
			$dispatch = self::$dispatch;
			if( empty($dispatch) ) 
			{
				$dispatch = self::routeCheck($request, $config);
			}
			$request->dispatch($dispatch);
			if( self::$debug ) 
			{
				Log::record("[ ROUTE ] " . var_export($dispatch, true), "info");
				Log::record("[ HEADER ] " . var_export($request->header(), true), "info");
				Log::record("[ PARAM ] " . var_export($request->param(), true), "info");
			}
			Hook::listen("app_begin", $dispatch);
			$request->cache($config["request_cache"], $config["request_cache_expire"], $config["request_cache_except"]);
			$data = self::exec($dispatch, $config);
		}
		catch( exception\HttpResponseException $exception ) 
		{
			$data = $exception->getResponse();
		}
		Loader::clearInstance();
		if( $data instanceof Response ) 
		{
			$response = $data;
		}
		else 
		{
			if( !is_null($data) ) 
			{
				$type = ($request->isAjax() ? Config::get("default_ajax_return") : Config::get("default_return_type"));
				$response = Response::create($data, $type);
			}
			else 
			{
				$response = Response::create();
			}
		}
		Hook::listen("app_end", $response);
		return $response;
	}
	public static function initCommon() 
	{
		if( empty($init) ) 
		{
			if( defined("APP_NAMESPACE") ) 
			{
				self::$namespace = APP_NAMESPACE;
			}
			Loader::addNamespace(self::$namespace, APP_PATH);
			$config = self::init();
			self::$suffix = $config["class_suffix"];
			self::$debug = Env::get("app_debug", Config::get("app_debug"));
			if( !self::$debug ) 
			{
				ini_set("display_errors", "Off");
			}
			else 
			{
				if( !IS_CLI ) 
				{
					if( 0 < ob_get_level() ) 
					{
						$output = ob_get_clean();
					}
					ob_start();
					if( !empty($output) ) 
					{
						echo $output;
					}
				}
			}
			if( !empty($config["root_namespace"]) ) 
			{
				Loader::addNamespace($config["root_namespace"]);
			}
			if( !empty($config["extra_file_list"]) ) 
			{
				foreach( $config["extra_file_list"] as $file ) 
				{
					$file = (strpos($file, ".") ? $file : APP_PATH . $file . EXT);
					if( is_file($file) && !isset(self::$file[$file]) ) 
					{
						include($file);
						self::$file[$file] = true;
					}
				}
			}
			date_default_timezone_set($config["default_timezone"]);
			Hook::listen("app_init");
			self::$init = true;
		}
		return Config::get();
	}
	private static function init($module = "") 
	{
		$module = ($module ? $module . DS : "");
		if( is_file(APP_PATH . $module . "init" . EXT) ) 
		{
			include(APP_PATH . $module . "init" . EXT);
		}
		else 
		{
			if( is_file(RUNTIME_PATH . $module . "init" . EXT) ) 
			{
				include(RUNTIME_PATH . $module . "init" . EXT);
			}
			else 
			{
				$config = Config::load(CONF_PATH . $module . "config" . CONF_EXT);
				$filename = CONF_PATH . $module . "database" . CONF_EXT;
				Config::load($filename, "database");
				if( is_dir(CONF_PATH . $module . "extra") ) 
				{
					$dir = CONF_PATH . $module . "extra";
					$files = scandir($dir);
					foreach( $files as $file ) 
					{
						if( "." . pathinfo($file, PATHINFO_EXTENSION) === CONF_EXT ) 
						{
							$filename = $dir . DS . $file;
							Config::load($filename, pathinfo($file, PATHINFO_FILENAME));
						}
					}
				}
				if( $config["app_status"] ) 
				{
					Config::load(CONF_PATH . $module . $config["app_status"] . CONF_EXT);
				}
				if( is_file(CONF_PATH . $module . "tags" . EXT) ) 
				{
					Hook::import(include(CONF_PATH . $module . "tags" . EXT));
				}
				$path = APP_PATH . $module;
				if( is_file($path . "common" . EXT) ) 
				{
					include($path . "common" . EXT);
				}
				if( $module ) 
				{
					Lang::load($path . "lang" . DS . Request::instance()->langset() . EXT);
				}
			}
		}
		return Config::get();
	}
	public static function dispatch($dispatch, $type = "module") 
	{
		self::$dispatch = array( "type" => $type, $type => $dispatch );
	}
	public static function invokeFunction($function, $vars = array( )) 
	{
		$reflect = new \ReflectionFunction($function);
		$args = self::bindParams($reflect, $vars);
		self::$debug and Log::record("[ RUN ] " . $reflect->__toString(), "info");
		return $reflect->invokeArgs($args);
	}
	public static function invokeMethod($method, $vars = array( )) 
	{
		if( is_array($method) ) 
		{
			$class = (is_object($method[0]) ? $method[0] : self::invokeClass($method[0]));
			$reflect = new \ReflectionMethod($class, $method[1]);
		}
		else 
		{
			$reflect = new \ReflectionMethod($method);
		}
		$args = self::bindParams($reflect, $vars);
		self::$debug and Log::record("[ RUN ] " . $reflect->class . "->" . $reflect->name . "[ " . $reflect->getFileName() . " ]", "info");
		return $reflect->invokeArgs((isset($class) ? $class : null), $args);
	}
	public static function invokeClass($class, $vars = array( )) 
	{
		$reflect = new \ReflectionClass($class);
		$constructor = $reflect->getConstructor();
		$args = ($constructor ? self::bindParams($constructor, $vars) : array( ));
		return $reflect->newInstanceArgs($args);
	}
	private static function bindParams($reflect, $vars = array( )) 
	{
		if( empty($vars) ) 
		{
			$vars = (Config::get("url_param_type") ? Request::instance()->route() : Request::instance()->param());
		}
		$args = array( );
		if( 0 < $reflect->getNumberOfParameters() ) 
		{
			reset($vars);
			$type = (key($vars) === 0 ? 1 : 0);
			foreach( $reflect->getParameters() as $param ) 
			{
				$args[] = self::getParamValue($param, $vars, $type);
			}
		}
		return $args;
	}
	private static function getParamValue($param, &$vars, $type) 
	{
		$name = $param->getName();
		$class = $param->getClass();
		if( $class ) 
		{
			$className = $class->getName();
			$bind = Request::instance()->$name;
			if( $bind instanceof $className ) 
			{
				$result = $bind;
			}
			else 
			{
				if( method_exists($className, "invoke") ) 
				{
					$method = new \ReflectionMethod($className, "invoke");
					if( $method->isPublic() && $method->isStatic() ) 
					{
						return $className::invoke(Request::instance());
					}
				}
				$result = (method_exists($className, "instance") ? $className::instance() : new $className());
			}
		}
		else 
		{
			if( 1 == $type && !empty($vars) ) 
			{
				$result = array_shift($vars);
			}
			else 
			{
				if( 0 == $type && isset($vars[$name]) ) 
				{
					$result = $vars[$name];
				}
				else 
				{
					if( $param->isDefaultValueAvailable() ) 
					{
						$result = $param->getDefaultValue();
					}
					else 
					{
						throw new \InvalidArgumentException("method param miss:" . $name);
					}
				}
			}
		}
		return $result;
	}
	protected static function exec($dispatch, $config) 
	{
		switch( $dispatch["type"] ) 
		{
			case "redirect": $data = Response::create($dispatch["url"], "redirect")->code($dispatch["status"]);
			break;
			case "module": $data = self::module($dispatch["module"], $config, (isset($dispatch["convert"]) ? $dispatch["convert"] : null));
			break;
			case "controller": $vars = array_merge(Request::instance()->param(), $dispatch["var"]);
			$data = Loader::action($dispatch["controller"], $vars, $config["url_controller_layer"], $config["controller_suffix"]);
			break;
			case "method": $vars = array_merge(Request::instance()->param(), $dispatch["var"]);
			$data = self::invokeMethod($dispatch["method"], $vars);
			break;
			case "function": $data = self::invokeFunction($dispatch["function"]);
			break;
			case "response": $data = $dispatch["response"];
			break;
			default: throw new \InvalidArgumentException("dispatch type not support");
		}
		return $data;
	}
	public static function module($result, $config, $convert = NULL) 
	{
		if( is_string($result) ) 
		{
			$result = explode("/", $result);
		}
		$request = Request::instance();
		if( $config["app_multi_module"] ) 
		{
			$module = strip_tags(strtolower(($result[0] ?: $config["default_module"])));
			$bind = Route::getBind("module");
			$available = false;
			if( $bind ) 
			{
				list($bindModule) = explode("/", $bind);
				if( empty($result[0]) ) 
				{
					$module = $bindModule;
					$available = true;
				}
				else 
				{
					if( $module == $bindModule ) 
					{
						$available = true;
					}
				}
			}
			else 
			{
				if( !in_array($module, $config["deny_module_list"]) && is_dir(APP_PATH . $module) ) 
				{
					$available = true;
				}
			}
			if( $module && $available ) 
			{
				$request->module($module);
				$config = self::init($module);
				$request->cache($config["request_cache"], $config["request_cache_expire"], $config["request_cache_except"]);
			}
			else 
			{
				throw new exception\HttpException(404, "module not exists:" . $module);
			}
		}
		else 
		{
			$module = "";
			$request->module($module);
		}
		$request->filter($config["default_filter"]);
		App::$modulePath = APP_PATH . (($module ? $module . DS : ""));
		$convert = (is_bool($convert) ? $convert : $config["url_convert"]);
		$controller = strip_tags(($result[1] ?: $config["default_controller"]));
		$controller = ($convert ? strtolower($controller) : $controller);
		if( !preg_match("/^[A-Za-z](\\w|\\.)*\$/", $controller) ) 
		{
			throw new exception\HttpException(404, "controller not exists:" . $controller);
		}
		$actionName = strip_tags(($result[2] ?: $config["default_action"]));
		if( !empty($config["action_convert"]) ) 
		{
			$actionName = Loader::parseName($actionName, 1);
		}
		else 
		{
			$actionName = ($convert ? strtolower($actionName) : $actionName);
		}
		$request->controller(Loader::parseName($controller, 1))->action($actionName);
		Hook::listen("module_init", $request);
		try 
		{
			$instance = Loader::controller($controller, $config["url_controller_layer"], $config["controller_suffix"], $config["empty_controller"]);
		}
		catch( exception\ClassNotFoundException $e ) 
		{
			throw new exception\HttpException(404, "controller not exists:" . $e->getClass());
		}
		$action = $actionName . $config["action_suffix"];
		$vars = array( );
		if( is_callable(array( $instance, $action )) ) 
		{
			$call = array( $instance, $action );
			$reflect = new \ReflectionMethod($instance, $action);
			$methodName = $reflect->getName();
			$suffix = $config["action_suffix"];
			$actionName = ($suffix ? substr($methodName, 0, 0 - strlen($suffix)) : $methodName);
			$request->action($actionName);
		}
		else 
		{
			if( is_callable(array( $instance, "_empty" )) ) 
			{
				$call = array( $instance, "_empty" );
				$vars = array( $actionName );
			}
			else 
			{
				throw new exception\HttpException(404, "method not exists:" . get_class($instance) . "->" . $action . "()");
			}
		}
		Hook::listen("action_begin", $call);
		return self::invokeMethod($call, $vars);
	}
	public static function routeCheck($request, array $config) 
	{
		$path = $request->path();
		$depr = $config["pathinfo_depr"];
		$result = false;
		$check = (!is_null(self::$routeCheck) ? self::$routeCheck : $config["url_route_on"]);
		if( $check ) 
		{
			if( is_file(RUNTIME_PATH . "route.php") ) 
			{
				$rules = include(RUNTIME_PATH . "route.php");
				is_array($rules) and Route::rules($rules);
			}
			else 
			{
				$files = $config["route_config_file"];
				foreach( $files as $file ) 
				{
					if( is_file(CONF_PATH . $file . CONF_EXT) ) 
					{
						$rules = include(CONF_PATH . $file . CONF_EXT);
						is_array($rules) and Route::import($rules);
					}
				}
			}
			$result = Route::check($request, $path, $depr, $config["url_domain_deploy"]);
			$must = (!is_null(self::$routeMust) ? self::$routeMust : $config["url_route_must"]);
			if( $must && false === $result ) 
			{
				throw new exception\RouteNotFoundException();
			}
		}
		if( false === $result ) 
		{
			$result = Route::parseUrl($path, $depr, $config["controller_auto_search"]);
		}
		return $result;
	}
	public static function route($route, $must = false) 
	{
		self::$routeCheck = $route;
		self::$routeMust = $must;
	}
}
?>