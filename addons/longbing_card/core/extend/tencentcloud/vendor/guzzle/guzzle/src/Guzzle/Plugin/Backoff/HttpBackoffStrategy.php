<?php  namespace Guzzle\Plugin\Backoff;
class HttpBackoffStrategy extends AbstractErrorCodeBackoffStrategy 
{
	protected static $defaultErrorCodes = array( 500, 503 );
	protected function getDelay($retries, \Guzzle\Http\Message\RequestInterface $request, \Guzzle\Http\Message\Response $response = NULL, \Guzzle\Http\Exception\HttpException $e = NULL) 
	{
		if( $response ) 
		{
			if( $response->isSuccessful() ) 
			{
				return false;
			}
			return (isset($this->errorCodes[$response->getStatusCode()]) ? true : null);
		}
	}
}
?>