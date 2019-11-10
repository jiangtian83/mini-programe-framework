<?php  namespace think\exception;
class HttpException extends \RuntimeException 
{
	private $statusCode = NULL;
	private $headers = NULL;
	public function __construct($statusCode, $message = NULL, \Exception $previous = NULL, array $headers = array( ), $code = 0) 
	{
		$this->statusCode = $statusCode;
		$this->headers = $headers;
		parent::__construct($message, $code, $previous);
	}
	public function getStatusCode() 
	{
		return $this->statusCode;
	}
	public function getHeaders() 
	{
		return $this->headers;
	}
}
?>