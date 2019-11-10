<?php  namespace Guzzle\Plugin\Backoff;
class LinearBackoffStrategy extends AbstractBackoffStrategy 
{
	protected $step = NULL;
	public function __construct($step = 1) 
	{
		$this->step = $step;
	}
	public function makesDecision() 
	{
		return false;
	}
	protected function getDelay($retries, \Guzzle\Http\Message\RequestInterface $request, \Guzzle\Http\Message\Response $response = NULL, \Guzzle\Http\Exception\HttpException $e = NULL) 
	{
		return $retries * $this->step;
	}
}
?>