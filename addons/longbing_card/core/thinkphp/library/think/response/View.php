<?php  namespace think\response;
class View extends \think\Response 
{
	protected $options = array( );
	protected $vars = array( );
	protected $replace = array( );
	protected $contentType = "text/html";
	protected function output($data) 
	{
		return \think\View::instance(\think\Config::get("template"), \think\Config::get("view_replace_str"))->fetch($data, $this->vars, $this->replace);
	}
	public function getVars($name = NULL) 
	{
		if( is_null($name) ) 
		{
			return $this->vars;
		}
		return (isset($this->vars[$name]) ? $this->vars[$name] : null);
	}
	public function assign($name, $value = "") 
	{
		if( is_array($name) ) 
		{
			$this->vars = array_merge($this->vars, $name);
			return $this;
		}
		$this->vars[$name] = $value;
		return $this;
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
}
?>