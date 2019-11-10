<?php  namespace think\controller;
abstract class Rest 
{
	protected $method = NULL;
	protected $type = NULL;
	protected $restMethodList = "get|post|put|delete";
	protected $restDefaultMethod = "get";
	protected $restTypeList = "html|xml|json|rss";
	protected $restDefaultType = "html";
	protected $restOutputType = array( "xml" => "application/xml", "json" => "application/json", "html" => "text/html" );
	public function __construct() 
	{
		$request = \think\Request::instance();
		$ext = $request->ext();
		if( "" == $ext ) 
		{
			$this->type = $request->type();
		}
		else 
		{
			if( !preg_match("/(" . $this->restTypeList . ")\$/i", $ext) ) 
			{
				$this->type = $this->restDefaultType;
			}
			else 
			{
				$this->type = $ext;
			}
		}
		$method = strtolower($request->method());
		if( !preg_match("/(" . $this->restMethodList . ")\$/i", $method) ) 
		{
			$method = $this->restDefaultMethod;
		}
		$this->method = $method;
	}
	public function _empty($method) 
	{
		if( method_exists($this, $method . "_" . $this->method . "_" . $this->type) ) 
		{
			$fun = $method . "_" . $this->method . "_" . $this->type;
		}
		else 
		{
			if( $this->method == $this->restDefaultMethod && method_exists($this, $method . "_" . $this->type) ) 
			{
				$fun = $method . "_" . $this->type;
			}
			else 
			{
				if( $this->type == $this->restDefaultType && method_exists($this, $method . "_" . $this->method) ) 
				{
					$fun = $method . "_" . $this->method;
				}
			}
		}
		if( isset($fun) ) 
		{
			return \think\App::invokeMethod(array( $this, $fun ));
		}
		throw new \Exception("error action :" . $method);
	}
	protected function response($data, $type = "json", $code = 200) 
	{
		return \think\Response::create($data, $type)->code($code);
	}
}
?>