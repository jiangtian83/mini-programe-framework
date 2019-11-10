<?php  namespace Qiniu\Http;
final class Request 
{
	public $url = NULL;
	public $headers = NULL;
	public $body = NULL;
	public $method = NULL;
	public function __construct($method, $url, array $headers = array( ), $body = NULL) 
	{
		$this->method = strtoupper($method);
		$this->url = $url;
		$this->headers = $headers;
		$this->body = $body;
	}
}
?>