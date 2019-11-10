<?php  namespace think\view\driver;
class Php 
{
	protected $config = NULL;
	protected $template = NULL;
	protected $content = NULL;
	public function __construct($config = array( )) 
	{
		$this->config = array_merge($this->config, $config);
	}
	public function exists($template) 
	{
		if( "" == pathinfo($template, PATHINFO_EXTENSION) ) 
		{
			$template = $this->parseTemplate($template);
		}
		return is_file($template);
	}
	public function fetch($template, $data = array( )) 
	{
		if( "" == pathinfo($template, PATHINFO_EXTENSION) ) 
		{
			$template = $this->parseTemplate($template);
		}
		if( !is_file($template) ) 
		{
			throw new \think\exception\TemplateNotFoundException("template not exists:" . $template, $template);
		}
		$this->template = $template;
		\think\App::$debug and \think\Log::record("[ VIEW ] " . $template . " [ " . var_export(array_keys($data), true) . " ]", "info");
		extract($data, EXTR_OVERWRITE);
		include($this->template);
	}
	public function display($content, $data = array( )) 
	{
		$this->content = $content;
		extract($data, EXTR_OVERWRITE);
		eval("?>" . $this->content);
	}
	private function parseTemplate($template) 
	{
		if( empty($this->config["view_path"]) ) 
		{
			$this->config["view_path"] = \think\App::$modulePath . "view" . DS;
		}
		$request = \think\Request::instance();
		if( strpos($template, "@") ) 
		{
			list($module, $template) = explode("@", $template);
		}
		if( $this->config["view_base"] ) 
		{
			$module = (isset($module) ? $module : $request->module());
			$path = $this->config["view_base"] . (($module ? $module . DS : ""));
		}
		else 
		{
			$path = (isset($module) ? APP_PATH . $module . DS . "view" . DS : $this->config["view_path"]);
		}
		$depr = $this->config["view_depr"];
		if( 0 !== strpos($template, "/") ) 
		{
			$template = str_replace(array( "/", ":" ), $depr, $template);
			$controller = \think\Loader::parseName($request->controller());
			if( $controller ) 
			{
				if( "" == $template ) 
				{
					$template = str_replace(".", DS, $controller) . $depr . ((1 == $this->config["auto_rule"] ? \think\Loader::parseName($request->action(true)) : $request->action()));
				}
				else 
				{
					if( false === strpos($template, $depr) ) 
					{
						$template = str_replace(".", DS, $controller) . $depr . $template;
					}
				}
			}
		}
		else 
		{
			$template = str_replace(array( "/", ":" ), $depr, substr($template, 1));
		}
		return $path . ltrim($template, "/") . "." . ltrim($this->config["view_suffix"], ".");
	}
	public function config($name, $value = NULL) 
	{
		if( is_array($name) ) 
		{
			$this->config = array_merge($this->config, $name);
		}
		else 
		{
			if( is_null($value) ) 
			{
				return (isset($this->config[$name]) ? $this->config[$name] : null);
			}
			$this->config[$name] = $value;
		}
	}
}