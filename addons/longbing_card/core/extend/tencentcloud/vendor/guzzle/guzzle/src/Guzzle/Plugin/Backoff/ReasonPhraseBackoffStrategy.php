<?php  namespace Guzzle\Plugin\Backoff;
class ReasonPhraseBackoffStrategy extends AbstractErrorCodeBackoffStrategy 
{
	public function makesDecision() 
	{
		return true;
	}
	protected function getDelay($retries, \Guzzle\Http\Message\RequestInterface $request, \Guzzle\Http\Message\Response $response = NULL, \Guzzle\Http\Exception\HttpException $e = NULL) 
	{
		if( $response ) 
		{
			return (isset($this->errorCodes[$response->getReasonPhrase()]) ? true : null);
		}
	}
}
?>