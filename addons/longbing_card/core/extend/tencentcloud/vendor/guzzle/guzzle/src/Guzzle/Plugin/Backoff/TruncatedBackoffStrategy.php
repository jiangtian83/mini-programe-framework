<?php  namespace Guzzle\Plugin\Backoff;
class TruncatedBackoffStrategy extends AbstractBackoffStrategy 
{
	protected $max = NULL;
	public function __construct($maxRetries, BackoffStrategyInterface $next = NULL) 
	{
		$this->max = $maxRetries;
		$this->next = $next;
	}
	public function makesDecision() 
	{
		return true;
	}
	protected function getDelay($retries, \Guzzle\Http\Message\RequestInterface $request, \Guzzle\Http\Message\Response $response = NULL, \Guzzle\Http\Exception\HttpException $e = NULL) 
	{
		return ($retries < $this->max ? null : false);
	}
}
?>