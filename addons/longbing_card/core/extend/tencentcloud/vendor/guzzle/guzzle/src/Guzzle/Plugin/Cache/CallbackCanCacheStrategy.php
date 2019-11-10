<?php  namespace Guzzle\Plugin\Cache;
class CallbackCanCacheStrategy extends DefaultCanCacheStrategy 
{
	protected $requestCallback = NULL;
	protected $responseCallback = NULL;
	public function __construct($requestCallback = NULL, $responseCallback = NULL) 
	{
		if( $requestCallback && !is_callable($requestCallback) ) 
		{
			throw new \Guzzle\Common\Exception\InvalidArgumentException("Method must be callable");
		}
		if( $responseCallback && !is_callable($responseCallback) ) 
		{
			throw new \Guzzle\Common\Exception\InvalidArgumentException("Method must be callable");
		}
		$this->requestCallback = $requestCallback;
		$this->responseCallback = $responseCallback;
	}
	public function canCacheRequest(\Guzzle\Http\Message\RequestInterface $request) 
	{
		return ($this->requestCallback ? call_user_func($this->requestCallback, $request) : parent::canCacheRequest($request));
	}
	public function canCacheResponse(\Guzzle\Http\Message\Response $response) 
	{
		return ($this->responseCallback ? call_user_func($this->responseCallback, $response) : parent::canCacheResponse($response));
	}
}
?>