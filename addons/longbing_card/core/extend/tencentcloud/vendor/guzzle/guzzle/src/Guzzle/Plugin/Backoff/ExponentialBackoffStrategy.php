<?php  namespace Guzzle\Plugin\Backoff;
class ExponentialBackoffStrategy extends AbstractBackoffStrategy 
{
	public function makesDecision() 
	{
		return false;
	}
	protected function getDelay($retries, \Guzzle\Http\Message\RequestInterface $request, \Guzzle\Http\Message\Response $response = NULL, \Guzzle\Http\Exception\HttpException $e = NULL) 
	{
		return (int) pow(2, $retries);
	}
}
?>