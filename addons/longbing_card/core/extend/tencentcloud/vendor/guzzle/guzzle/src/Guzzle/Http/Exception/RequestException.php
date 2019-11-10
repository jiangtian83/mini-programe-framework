<?php  namespace Guzzle\Http\Exception;
class RequestException extends \Guzzle\Common\Exception\RuntimeException implements HttpException 
{
	protected $request = NULL;
	public function setRequest(\Guzzle\Http\Message\RequestInterface $request) 
	{
		$this->request = $request;
		return $this;
	}
	public function getRequest() 
	{
		return $this->request;
	}
}
?>