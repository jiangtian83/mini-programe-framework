<?php  namespace think;
class Loader 
{
	protected static $instance = array( );
	protected static $classMap = array( );
	protected static $namespaceAlias = array( );
	private static $prefixLengthsPsr4 = array( );
	private static $prefixDirsPsr4 = array( );
	private static $fallbackDirsPsr4 = array( );
	private static $prefixesPsr0 = array( );
	private static $fallbackDirsPsr0 = array( );
	private static $files = array( );
	public static function autoload($class) 
	{
		if( !empty($namespaceAlias) ) 
		{
			$namespace = dirname($class);
			if( isset(self::$namespaceAlias[$namespace]) ) 
			{
				$original = self::$namespaceAlias[$namespace] . "\\" . basename($class);
				if( class_exists($original) ) 
				{
					return class_alias($original, $class, false);
				}
			}
		}
		if( ($file = self::findFile($class)) && (!IS_WIN || pathinfo($file, PATHINFO_FILENAME) == pathinfo(realpath($file), PATHINFO_FILENAME)) ) 
		{
			__include_file($file);
			return true;
		}
		return false;
	}
	private static function findFile($class) 
	{
		if( !empty(self::$classMap[$class]) ) 
		{
			return self::$classMap[$class];
		}
		$logicalPathPsr4 = strtr($class, "\\", DS) . EXT;
		$first = $class[0];
		if( isset(self::$prefixLengthsPsr4[$first]) ) 
		{
			foreach( self::$prefixLengthsPsr4[$first] as $prefix => $length ) 
			{
				if( 0 === strpos($class, $prefix) ) 
				{
					foreach( self::$prefixDirsPsr4[$prefix] as $dir ) 
					{
						if( is_file($file = $dir . DS . substr($logicalPathPsr4, $length)) ) 
						{
							return $file;
						}
					}
				}
			}
		}
		foreach( self::$fallbackDirsPsr4 as $dir ) 
		{
			if( is_file($file = $dir . DS . $logicalPathPsr4) ) 
			{
				return $file;
			}
		}
		if( false !== ($pos = strrpos($class, "\\")) ) 
		{
			$logicalPathPsr0 = substr($logicalPathPsr4, 0, $pos + 1) . strtr(substr($logicalPathPsr4, $pos + 1), "_", DS);
		}
		else 
		{
			$logicalPathPsr0 = strtr($class, "_", DS) . EXT;
		}
		if( isset(self::$prefixesPsr0[$first]) ) 
		{
			foreach( self::$prefixesPsr0[$first] as $prefix => $dirs ) 
			{
				if( 0 === strpos($class, $prefix) ) 
				{
					foreach( $dirs as $dir ) 
					{
						if( is_file($file = $dir . DS . $logicalPathPsr0) ) 
						{
							return $file;
						}
					}
				}
			}
		}
		foreach( self::$fallbackDirsPsr0 as $dir ) 
		{
			if( is_file($file = $dir . DS . $logicalPathPsr0) ) 
			{
				return $file;
			}
		}
		self::$classMap[$class] = false;
		return self::$classMap[$class];
	}
	public static function addClassMap($class, $map = "") 
	{
		if( is_array($class) ) 
		{
			self::$classMap = array_merge(self::$classMap, $class);
		}
		else 
		{
			self::$classMap[$class] = $map;
		}
	}
	public static function addNamespace($namespace, $path = "") 
	{
		if( is_array($namespace) ) 
		{
			foreach( $namespace as $prefix => $paths ) 
			{
				self::addPsr4($prefix . "\\", rtrim($paths, DS), true);
			}
		}
		else 
		{
			self::addPsr4($namespace . "\\", rtrim($path, DS), true);
		}
	}
	private static function addPsr0($prefix, $paths, $prepend = false) 
	{
		if( !$prefix ) 
		{
			self::$fallbackDirsPsr0 = ($prepend ? array_merge((array) $paths, self::$fallbackDirsPsr0) : array_merge(self::$fallbackDirsPsr0, (array) $paths));
		}
		else 
		{
			$first = $prefix[0];
			if( !isset(self::$prefixesPsr0[$first][$prefix]) ) 
			{
				self::$prefixesPsr0[$first][$prefix] = (array) $paths;
			}
			else 
			{
				self::$prefixesPsr0[$first][$prefix] = ($prepend ? array_merge((array) $paths, self::$prefixesPsr0[$first][$prefix]) : array_merge(self::$prefixesPsr0[$first][$prefix], (array) $paths));
			}
		}
	}
	private static function addPsr4($prefix, $paths, $prepend = false) 
	{
		if( !$prefix ) 
		{
			self::$fallbackDirsPsr4 = ($prepend ? array_merge((array) $paths, self::$fallbackDirsPsr4) : array_merge(self::$fallbackDirsPsr4, (array) $paths));
		}
		else 
		{
			if( !isset(self::$prefixDirsPsr4[$prefix]) ) 
			{
				$length = strlen($prefix);
				if( "\\" !== $prefix[$length - 1] ) 
				{
					throw new \InvalidArgumentException("A non-empty PSR-4 prefix must end with a namespace separator.");
				}
				self::$prefixLengthsPsr4[$prefix[0]][$prefix] = $length;
				self::$prefixDirsPsr4[$prefix] = (array) $paths;
			}
			else 
			{
				self::$prefixDirsPsr4[$prefix] = ($prepend ? array_merge((array) $paths, self::$prefixDirsPsr4[$prefix]) : array_merge(self::$prefixDirsPsr4[$prefix], (array) $paths));
			}
		}
	}
	public static function addNamespaceAlias($namespace, $original = "") 
	{
		if( is_array($namespace) ) 
		{
			self::$namespaceAlias = array_merge(self::$namespaceAlias, $namespace);
		}
		else 
		{
			self::$namespaceAlias[$namespace] = $original;
		}
	}
	public static function register($autoload = NULL) 
	{
		spl_autoload_register(($autoload ?: "think\\Loader::autoload"), true, true);
		if( is_dir(VENDOR_PATH . "composer") ) 
		{
			if( 50600 <= PHP_VERSION_ID && is_file(VENDOR_PATH . "composer" . DS . "autoload_static.php") ) 
			{
				require(VENDOR_PATH . "composer" . DS . "autoload_static.php");
				$declaredClass = get_declared_classes();
				$composerClass = array_pop($declaredClass);
				foreach( array( "prefixLengthsPsr4", "prefixDirsPsr4", "fallbackDirsPsr4", "prefixesPsr0", "fallbackDirsPsr0", "classMap", "files" ) as $attr ) 
				{
					if( property_exists($composerClass, $attr) ) 
					{
						self::$
						{
							$attr}
						= $composerClass::$
						{
							$attr}
						;
					}
				}
			}
			else 
			{
				self::registerComposerLoader();
			}
		}
		self::addNamespace(array( "think" => LIB_PATH . "think" . DS, "behavior" => LIB_PATH . "behavior" . DS, "traits" => LIB_PATH . "traits" . DS ));
		if( is_file(RUNTIME_PATH . "classmap" . EXT) ) 
		{
			self::addClassMap(__include_file(RUNTIME_PATH . "classmap" . EXT));
		}
		self::loadComposerAutoloadFiles();
		self::$fallbackDirsPsr4[] = rtrim(EXTEND_PATH, DS);
	}
	private static function registerComposerLoader() 
	{
		if( is_file(VENDOR_PATH . "composer/autoload_namespaces.php") ) 
		{
			$map = require(VENDOR_PATH . "composer/autoload_namespaces.php");
			foreach( $map as $namespace => $path ) 
			{
				self::addPsr0($namespace, $path);
			}
		}
		if( is_file(VENDOR_PATH . "composer/autoload_psr4.php") ) 
		{
			$map = require(VENDOR_PATH . "composer/autoload_psr4.php");
			foreach( $map as $namespace => $path ) 
			{
				self::addPsr4($namespace, $path);
			}
		}
		if( is_file(VENDOR_PATH . "composer/autoload_classmap.php") ) 
		{
			$classMap = require(VENDOR_PATH . "composer/autoload_classmap.php");
			if( $classMap ) 
			{
				self::addClassMap($classMap);
			}
		}
		if( is_file(VENDOR_PATH . "composer/autoload_files.php") ) 
		{
			self::$files = require(VENDOR_PATH . "composer/autoload_files.php");
		}
	}
	public static function loadComposerAutoloadFiles() 
	{
		foreach( self::$files as $fileIdentifier => $file ) 
		{
			if( empty($GLOBALS["__composer_autoload_files"][$fileIdentifier]) ) 
			{
				__require_file($file);
				$GLOBALS["__composer_autoload_files"][$fileIdentifier] = true;
			}
		}
	}
	public static function import($class, $baseUrl = "", $ext = EXT) 
	{
		static $_file = array( );
		$key = $class . $baseUrl;
		$class = str_replace(array( ".", "#" ), array( DS, "." ), $class);
		if( isset($_file[$key]) ) 
		{
			return true;
		}
		if( empty($baseUrl) ) 
		{
			list($name, $class) = explode(DS, $class, 2);
			if( isset(self::$prefixDirsPsr4[$name . "\\"]) ) 
			{
				$baseUrl = self::$prefixDirsPsr4[$name . "\\"];
			}
			else 
			{
				if( "@" == $name ) 
				{
					$baseUrl = App::$modulePath;
				}
				else 
				{
					if( is_dir(EXTEND_PATH . $name) ) 
					{
						$baseUrl = EXTEND_PATH . $name . DS;
					}
					else 
					{
						$baseUrl = APP_PATH . $name . DS;
					}
				}
			}
		}
		else 
		{
			if( substr($baseUrl, -1) != DS ) 
			{
				$baseUrl .= DS;
			}
		}
		if( is_array($baseUrl) ) 
		{
			foreach( $baseUrl as $path ) 
			{
				if( is_file($filename = $path . DS . $class . $ext) ) 
				{
					break;
				}
			}
		}
		else 
		{
			$filename = $baseUrl . $class . $ext;
		}
		if( !empty($filename) && is_file($filename) && (!IS_WIN || pathinfo($filename, PATHINFO_FILENAME) == pathinfo(realpath($filename), PATHINFO_FILENAME)) ) 
		{
			__include_file($filename);
			$_file[$key] = true;
			return true;
		}
		return false;
	}
	public static function model($name = "", $layer = "model", $appendSuffix = false, $common = "common") 
	{
		$uid = $name . $layer;
		if( isset(self::$instance[$uid]) ) 
		{
			return self::$instance[$uid];
		}
		list($module, $class) = self::getModuleAndClass($name, $layer, $appendSuffix);
		if( class_exists($class) ) 
		{
			$model = new $class();
		}
		else 
		{
			$class = str_replace("\\" . $module . "\\", "\\" . $common . "\\", $class);
			if( class_exists($class) ) 
			{
				$model = new $class();
			}
			else 
			{
				throw new exception\ClassNotFoundException("class not exists:" . $class, $class);
			}
		}
		self::$instance[$uid] = $model;
		return self::$instance[$uid];
	}
	public static function controller($name, $layer = "controller", $appendSuffix = false, $empty = "") 
	{
		list($module, $class) = self::getModuleAndClass($name, $layer, $appendSuffix);
		if( class_exists($class) ) 
		{
			return App::invokeClass($class);
		}
		if( $empty ) 
		{
			$emptyClass = self::parseClass($module, $layer, $empty, $appendSuffix);
			if( class_exists($emptyClass) ) 
			{
				return new $emptyClass(Request::instance());
			}
		}
		throw new exception\ClassNotFoundException("class not exists:" . $class, $class);
	}
	public static function validate($name = "", $layer = "validate", $appendSuffix = false, $common = "common") 
	{
		$name = ($name ?: Config::get("default_validate"));
		if( empty($name) ) 
		{
			return new Validate();
		}
		$uid = $name . $layer;
		if( isset(self::$instance[$uid]) ) 
		{
			return self::$instance[$uid];
		}
		list($module, $class) = self::getModuleAndClass($name, $layer, $appendSuffix);
		if( class_exists($class) ) 
		{
			$validate = new $class();
		}
		else 
		{
			$class = str_replace("\\" . $module . "\\", "\\" . $common . "\\", $class);
			if( class_exists($class) ) 
			{
				$validate = new $class();
			}
			else 
			{
				throw new exception\ClassNotFoundException("class not exists:" . $class, $class);
			}
		}
		self::$instance[$uid] = $validate;
		return self::$instance[$uid];
	}
	protected static function getModuleAndClass($name, $layer, $appendSuffix) 
	{
		if( false !== strpos($name, "\\") ) 
		{
			$module = Request::instance()->module();
			$class = $name;
		}
		else 
		{
			if( strpos($name, "/") ) 
			{
				list($module, $name) = explode("/", $name, 2);
			}
			else 
			{
				$module = Request::instance()->module();
			}
			$class = self::parseClass($module, $layer, $name, $appendSuffix);
		}
		return array( $module, $class );
	}
	public static function db($config = array( ), $name = false) 
	{
		return Db::connect($config, $name);
	}
	public static function action($url, $vars = array( ), $layer = "controller", $appendSuffix = false) 
	{
		$info = pathinfo($url);
		$action = $info["basename"];
		$module = ("." != $info["dirname"] ? $info["dirname"] : Request::instance()->controller());
		$class = self::controller($module, $layer, $appendSuffix);
		if( $class ) 
		{
			if( is_scalar($vars) ) 
			{
				if( strpos($vars, "=") ) 
				{
					parse_str($vars, $vars);
				}
				else 
				{
					$vars = array( $vars );
				}
			}
			return App::invokeMethod(array( $class, $action . Config::get("action_suffix") ), $vars);
		}
		return false;
	}
	public static function parseName($name, $type = 0, $ucfirst = true) 
	{
		if( $type ) 
		{
			$name = preg_replace_callback("/_([a-zA-Z])/", function($match) 
			{
				return strtoupper($match[1]);
			}
			, $name);
			return ($ucfirst ? ucfirst($name) : lcfirst($name));
		}
		return strtolower(trim(preg_replace("/[A-Z]/", "_\\0", $name), "_"));
	}
	public static function parseClass($module, $layer, $name, $appendSuffix = false) 
	{
		$array = explode("\\", str_replace(array( "/", "." ), "\\", $name));
		$class = self::parseName(array_pop($array), 1);
		$class = $class . ((App::$suffix || $appendSuffix ? ucfirst($layer) : ""));
		$path = ($array ? implode("\\", $array) . "\\" : "");
		return App::$namespace . "\\" . (($module ? $module . "\\" : "")) . $layer . "\\" . $path . $class;
	}
	public static function clearInstance() 
	{
		self::$instance = array( );
	}
}
function __include_file($file) 
{
	return include($file);
}
function __require_file($file) 
{
	return require($file);
}
?>