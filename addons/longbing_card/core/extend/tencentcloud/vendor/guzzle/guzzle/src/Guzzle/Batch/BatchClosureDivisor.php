<?php  namespace Guzzle\Batch;
class BatchClosureDivisor implements BatchDivisorInterface 
{
	protected $callable = NULL;
	protected $context = NULL;
	public function __construct($callable, $context = NULL) 
	{
		if( !is_callable($callable) ) 
		{
			throw new \Guzzle\Common\Exception\InvalidArgumentException("Must pass a callable");
		}
		$this->callable = $callable;
		$this->context = $context;
	}
	public function createBatches(\SplQueue $queue) 
	{
		return call_user_func($this->callable, $queue, $this->context);
	}
}
?>