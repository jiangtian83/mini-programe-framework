<?php  namespace Guzzle\Plugin\Backoff;
class CurlBackoffStrategy extends AbstractErrorCodeBackoffStrategy 
{
	protected static $defaultErrorCodes = NULL;
	protected function getDelay($retries, \Guzzle\Http\Message\RequestInterface $request, \Guzzle\Http\Message\Response $response = NULL, \Guzzle\Http\Exception\HttpException $e = NULL) 
	{
		if( $e && $e instanceof \Guzzle\Http\Exception\CurlException ) 
		{
			return (isset($this->errorCodes[$e->getErrorNo()]) ? true : null);
		}
	}
}
?>