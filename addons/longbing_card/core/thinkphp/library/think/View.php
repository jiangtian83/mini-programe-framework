<?php  namespace think;
class View 
{
	protected static $instance = NULL;
	public $engine = NULL;
	protected $data = array( );
	protected static $var = array( );
	protected $replace = array( );
	public function __construct($engine = array( ), $replace = array( )) 
	{
		$this->engine($engine);
		$request = Request::instance();
		$base = $request->root();
		$root = (strpos($base, ".") ? ltrim(dirname($base), DS) : $base);
		if( "" != $root ) 
		{
			$root = "/" . ltrim($root, "/");
		}
		$baseReplace = array( "__ROOT__" => $root, "__URL__" => $base . "/" . $request->module() . "/" . Loader::parseName($request->controller()), "__STATIC__" => $root . "/static", "__CSS__" => $root . "/static/css", "__JS__" => $root . "/static/js" );
		$this->replace = array_merge($baseReplace, (array) $replace);
	}
	public static function instance($engine = array( ), $replace = array( )) 
	{
		if( is_null(self::$instance) ) 
		{
			self::$instance = new self($engine, $replace);
		}
		return self::$instance;
	}
	public static function share($name, $value = "") 
	{
		if( is_array($name) ) 
		{
			self::$var = array_merge(self::$var, $name);
		}
		else 
		{
			self::$var[$name] = $value;
		}
	}
	public function assign($name, $value = "") 
	{
		if( is_array($name) ) 
		{
			$this->data = array_merge($this->data, $name);
		}
		else 
		{
			$this->data[$name] = $value;
		}
		return $this;
	}
	public function engine($options = array( )) 
	{
		if( is_string($options) ) 
		{
			$type = $options;
			$options = array( );
		}
		else 
		{
			$type = (!empty($options["type"]) ? $options["type"] : "Think");
		}
		$class = (false !== strpos($type, "\\") ? $type : "\\think\\view\\driver\\" . ucfirst($type));
		if( isset($options["type"]) ) 
		{
			unset($options["type"]);
		}
		$this->engine = new $class($options);
		return $this;
	}
	public function config($name, $value = NULL) 
	{
		$this->engine->config($name, $value);
		return $this;
	}
	public function fetch($template = "", $vars = array( ), $replace = array( ), $config = array( ), $renderContent = false) 
	{
		$vars = array_merge(self::$var, $this->data, $vars);
		ob_start();
		ob_implicit_flush(0);
		try 
		{
			$method = ($renderContent ? "display" : "fetch");
			$replace = array_merge($this->replace, $replace, (array) $this->engine->config("tpl_replace_string"));
			$this->engine->config("tpl_replace_string", $replace);
			$this->engine->$method($template, $vars, $config);
		}
		catch( \Exception $e ) 
		{
			ob_end_clean();
			throw $e;
		}
		$content = ob_get_clean();
		Hook::listen("view_filter", $content);
		return $content;
	}
	public function replace($content, $replace = "") 
	{
		if( is_array($content) ) 
		{
			$this->replace = array_merge($this->replace, $content);
		}
		else 
		{
			$this->replace[$content] = $replace;
		}
		return $this;
	}
	public function display($content, $vars = array( ), $replace = array( ), $config = array( )) 
	{
		return $this->fetch($content, $vars, $replace, $config, true);
	}
	public function __set($name, $value) 
	{
		$this->data[$name] = $value;
	}
	public function __get($name) 
	{
		return $this->data[$name];
	}
	public function __isset($name) 
	{
		return isset($this->data[$name]);
	}
}
?>