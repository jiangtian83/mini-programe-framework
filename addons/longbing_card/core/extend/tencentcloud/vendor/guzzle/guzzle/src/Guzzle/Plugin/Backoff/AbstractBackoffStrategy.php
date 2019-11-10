<?php  namespace Guzzle\Plugin\Backoff;
abstract class AbstractBackoffStrategy implements BackoffStrategyInterface 
{
	protected $next = NULL;
	public function setNext(AbstractBackoffStrategy $next) 
	{
		$this->next = $next;
	}
	public function getNext() 
	{
		return $this->next;
	}
	public function getBackoffPeriod($retries, \Guzzle\Http\Message\RequestInterface $request, \Guzzle\Http\Message\Response $response = NULL, \Guzzle\Http\Exception\HttpException $e = NULL) 
	{
		$delay = $this->getDelay($retries, $request, $response, $e);
		if( $delay === false ) 
		{
			return false;
		}
		if( $delay === null ) 
		{
			return (!$this->next || !$this->next->makesDecision() ? false : $this->next->getBackoffPeriod($retries, $request, $response, $e));
		}
		if( $delay === true ) 
		{
			if( !$this->next ) 
			{
				return 0;
			}
			$next = $this->next;
			while( $next->makesDecision() && $next->getNext() ) 
			{
				$next = $next->getNext();
			}
			return (!$next->makesDecision() ? $next->getBackoffPeriod($retries, $request, $response, $e) : 0);
		}
		return $delay;
	}
	abstract public function makesDecision();
	abstract protected function getDelay($retries, \Guzzle\Http\Message\RequestInterface $request, \Guzzle\Http\Message\Response $response, \Guzzle\Http\Exception\HttpException $e);
}
?>