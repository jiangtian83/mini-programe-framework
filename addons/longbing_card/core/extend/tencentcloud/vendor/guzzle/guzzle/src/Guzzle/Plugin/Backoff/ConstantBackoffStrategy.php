<?php  namespace Guzzle\Plugin\Backoff;
class ConstantBackoffStrategy extends AbstractBackoffStrategy 
{
	protected $delay = NULL;
	public function __construct($delay) 
	{
		$this->delay = $delay;
	}
	public function makesDecision() 
	{
		return false;
	}
	protected function getDelay($retries, \Guzzle\Http\Message\RequestInterface $request, \Guzzle\Http\Message\Response $response = NULL, \Guzzle\Http\Exception\HttpException $e = NULL) 
	{
		return $this->delay;
	}
}
?>