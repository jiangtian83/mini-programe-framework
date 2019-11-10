<?php  namespace Guzzle\Plugin\Backoff;
interface BackoffStrategyInterface 
{
	public function getBackoffPeriod($retries, \Guzzle\Http\Message\RequestInterface $request, \Guzzle\Http\Message\Response $response, \Guzzle\Http\Exception\HttpException $e);
}
?>