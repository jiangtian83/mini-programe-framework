<?php  namespace think;
class Response 
{
	protected $data = NULL;
	protected $contentType = "text/html";
	protected $charset = "utf-8";
	protected $code = 200;
	protected $options = array( );
	protected $header = array( );
	protected $content = NULL;
	public function __construct($data = "", $code = 200, array $header = array( ), $options = array( )) 
	{
		$this->data($data);
		if( !empty($options) ) 
		{
			$this->options = array_merge($this->options, $options);
		}
		$this->contentType($this->contentType, $this->charset);
		$this->header = array_merge($this->header, $header);
		$this->code = $code;
	}
	public static function create($data = "", $type = "", $code = 200, array $header = array( ), $options = array( )) 
	{
		$class = (false !== strpos($type, "\\") ? $type : "\\think\\response\\" . ucfirst(strtolower($type)));
		if( class_exists($class) ) 
		{
			$response = new $class($data, $code, $header, $options);
		}
		else 
		{
			$response = new static($data, $code, $header, $options);
		}
		return $response;
	}
	public function send() 
	{
		Hook::listen("response_send", $this);
		$data = $this->getContent();
		if( Env::get("app_trace", Config::get("app_trace")) ) 
		{
			Debug::inject($this, $data);
		}
		if( 200 == $this->code ) 
		{
			$cache = Request::instance()->getCache();
			if( $cache ) 
			{
				$this->header["Cache-Control"] = "max-age=" . $cache[1] . ",must-revalidate";
				$this->header["Last-Modified"] = gmdate("D, d M Y H:i:s") . " GMT";
				$this->header["Expires"] = gmdate("D, d M Y H:i:s", $_SERVER["REQUEST_TIME"] + $cache[1]) . " GMT";
				Cache::tag($cache[2])->set($cache[0], array( $data, $this->header ), $cache[1]);
			}
		}
		if( !headers_sent() && !empty($this->header) ) 
		{
			http_response_code($this->code);
			foreach( $this->header as $name => $val ) 
			{
				if( is_null($val) ) 
				{
					header($name);
				}
				else 
				{
					header($name . ":" . $val);
				}
			}
		}
		echo $data;
		if( function_exists("fastcgi_finish_request") ) 
		{
			fastcgi_finish_request();
		}
		Hook::listen("response_end", $this);
		if( !$this instanceof response\Redirect ) 
		{
			Session::flush();
		}
	}
	protected function output($data) 
	{
		return $data;
	}
	public function options($options = array( )) 
	{
		$this->options = array_merge($this->options, $options);
		return $this;
	}
	public function data($data) 
	{
		$this->data = $data;
		return $this;
	}
	public function header($name, $value = NULL) 
	{
		if( is_array($name) ) 
		{
			$this->header = array_merge($this->header, $name);
		}
		else 
		{
			$this->header[$name] = $value;
		}
		return $this;
	}
	public function content($content) 
	{
		if( null !== $content && !is_string($content) && !is_numeric($content) && !is_callable(array( $content, "__toString" )) ) 
		{
			throw new \InvalidArgumentException(sprintf("variable type error： %s", gettype($content)));
		}
		$this->content = (string) $content;
		return $this;
	}
	public function code($code) 
	{
		$this->code = $code;
		return $this;
	}
	public function lastModified($time) 
	{
		$this->header["Last-Modified"] = $time;
		return $this;
	}
	public function expires($time) 
	{
		$this->header["Expires"] = $time;
		return $this;
	}
	public function eTag($eTag) 
	{
		$this->header["ETag"] = $eTag;
		return $this;
	}
	public function cacheControl($cache) 
	{
		$this->header["Cache-control"] = $cache;
		return $this;
	}
	public function contentType($contentType, $charset = "utf-8") 
	{
		$this->header["Content-Type"] = $contentType . "; charset=" . $charset;
		return $this;
	}
	public function getHeader($name = "") 
	{
		if( !empty($name) ) 
		{
			return (isset($this->header[$name]) ? $this->header[$name] : null);
		}
		return $this->header;
	}
	public function getData() 
	{
		return $this->data;
	}
	public function getContent() 
	{
		if( null == $this->content ) 
		{
			$content = $this->output($this->data);
			if( null !== $content && !is_string($content) && !is_numeric($content) && !is_callable(array( $content, "__toString" )) ) 
			{
				throw new \InvalidArgumentException(sprintf("variable type error： %s", gettype($content)));
			}
			$this->content = (string) $content;
		}
		return $this->content;
	}
	public function getCode() 
	{
		return $this->code;
	}
}
?>