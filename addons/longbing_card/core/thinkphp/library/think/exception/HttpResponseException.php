<?php  namespace think\exception;
class HttpResponseException extends \RuntimeException 
{
	protected $response = NULL;
	public function __construct(\think\Response $response) 
	{
		$this->response = $response;
	}
	public function getResponse() 
	{
		return $this->response;
	}
}
?>