<?php  namespace Guzzle\Plugin\Backoff;
class CallbackBackoffStrategy extends AbstractBackoffStrategy 
{
	protected $callback = NULL;
	protected $decision = NULL;
	public function __construct($callback, $decision, BackoffStrategyInterface $next = NULL) 
	{
		if( !is_callable($callback) ) 
		{
			throw new \Guzzle\Common\Exception\InvalidArgumentException("The callback must be callable");
		}
		$this->callback = $callback;
		$this->decision = (bool) $decision;
		$this->next = $next;
	}
	public function makesDecision() 
	{
		return $this->decision;
	}
	protected function getDelay($retries, \Guzzle\Http\Message\RequestInterface $request, \Guzzle\Http\Message\Response $response = NULL, \Guzzle\Http\Exception\HttpException $e = NULL) 
	{
		return call_user_func($this->callback, $retries, $request, $response, $e);
	}
}
?>