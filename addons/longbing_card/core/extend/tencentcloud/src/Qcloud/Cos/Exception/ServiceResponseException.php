<?php  namespace Qcloud\Cos\Exception;
class ServiceResponseException extends \RuntimeException 
{
	protected $response = NULL;
	protected $request = NULL;
	protected $requestId = NULL;
	protected $exceptionType = NULL;
	protected $exceptionCode = NULL;
	public function setExceptionCode($code) 
	{
		$this->exceptionCode = $code;
	}
	public function getExceptionCode() 
	{
		return $this->exceptionCode;
	}
	public function setExceptionType($type) 
	{
		$this->exceptionType = $type;
	}
	public function getExceptionType() 
	{
		return $this->exceptionType;
	}
	public function setRequestId($id) 
	{
		$this->requestId = $id;
	}
	public function getRequestId() 
	{
		return $this->requestId;
	}
	public function setResponse(\Guzzle\Http\Message\Response $response) 
	{
		$this->response = $response;
	}
	public function getResponse() 
	{
		return $this->response;
	}
	public function setRequest(\Guzzle\Http\Message\RequestInterface $request) 
	{
		$this->request = $request;
	}
	public function getRequest() 
	{
		return $this->request;
	}
	public function getStatusCode() 
	{
		return ($this->response ? $this->response->getStatusCode() : null);
	}
	public function __toString() 
	{
		$message = get_class($this) . ": " . "Cos Error Code: " . $this->getExceptionCode() . ", " . "Status Code: " . $this->getStatusCode() . ", " . "Cos Request ID: " . $this->getRequestId() . ", " . "Cos Error Type: " . $this->getExceptionType() . ", " . "Cos Error Message: " . $this->getMessage();
		if( $this->request ) 
		{
			$message .= ", " . "User-Agent: " . $this->request->getHeader("User-Agent");
		}
		return $message;
	}
	public function getCosRequestId() 
	{
		return $this->requestId;
	}
	public function getCosErrorType() 
	{
		return $this->exceptionType;
	}
	public function getCosErrorCode() 
	{
		return $this->exceptionCode;
	}
}
?>