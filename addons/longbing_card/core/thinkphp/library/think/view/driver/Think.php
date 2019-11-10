<?php  namespace think\view\driver;
class Think 
{
	private $template = NULL;
	protected $config = NULL;
	public function __construct($config = array( )) 
	{
		$this->config = array_merge($this->config, $config);
		if( empty($this->config["view_path"]) ) 
		{
			$this->config["view_path"] = \think\App::$modulePath . "view" . DS;
		}
		$this->template = new \think\Template($this->config);
	}
	public function exists($template) 
	{
		if( "" == pathinfo($template, PATHINFO_EXTENSION) ) 
		{
			$template = $this->parseTemplate($template);
		}
		return is_file($template);
	}
	public function fetch($template, $data = array( ), $config = array( )) 
	{
		if( "" == pathinfo($template, PATHINFO_EXTENSION) ) 
		{
			$template = $this->parseTemplate($template);
		}
		if( !is_file($template) ) 
		{
			throw new \think\exception\TemplateNotFoundException("template not exists:" . $template, $template);
		}
		\think\App::$debug and \think\Log::record("[ VIEW ] " . $template . " [ " . var_export(array_keys($data), true) . " ]", "info");
		$this->template->fetch($template, $data, $config);
	}
	public function display($template, $data = array( ), $config = array( )) 
	{
		$this->template->display($template, $data, $config);
	}
	private function parseTemplate($template) 
	{
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
			$this->template->config($name);
			$this->config = array_merge($this->config, $name);
		}
		else 
		{
			if( is_null($value) ) 
			{
				return $this->template->config($name);
			}
			$this->template->$name = $value;
			$this->config[$name] = $value;
		}
	}
	public function __call($method, $params) 
	{
		return call_user_func_array(array( $this->template, $method ), $params);
	}
}
?>