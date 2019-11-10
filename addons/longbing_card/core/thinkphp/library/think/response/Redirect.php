<?php  namespace think\response;
class Redirect extends \think\Response 
{
	protected $options = array( );
	protected $params = array( );
	public function __construct($data = "", $code = 302, array $header = array( ), array $options = array( )) 
	{
		parent::__construct($data, $code, $header, $options);
		$this->cacheControl("no-cache,must-revalidate");
	}
	protected function output($data) 
	{
		$this->header["Location"] = $this->getTargetUrl();
	}
	public function with($name, $value = NULL) 
	{
		if( is_array($name) ) 
		{
			foreach( $name as $key => $val ) 
			{
				\think\Session::flash($key, $val);
			}
		}
		else 
		{
			\think\Session::flash($name, $value);
		}
		return $this;
	}
	public function getTargetUrl() 
	{
		if( strpos($this->data, "://") || 0 === strpos($this->data, "/") && empty($this->params) ) 
		{
			return $this->data;
		}
		return \think\Url::build($this->data, $this->params);
	}
	public function params($params = array( )) 
	{
		$this->params = $params;
		return $this;
	}
	public function remember() 
	{
		\think\Session::set("redirect_url", \think\Request::instance()->url());
		return $this;
	}
	public function restore() 
	{
		if( \think\Session::has("redirect_url") ) 
		{
			$this->data = \think\Session::get("redirect_url");
			\think\Session::delete("redirect_url");
		}
		return $this;
	}
}
?>