<?php  namespace Guzzle\Batch;
class BatchClosureTransfer implements BatchTransferInterface 
{
	protected $callable = NULL;
	protected $context = NULL;
	public function __construct($callable, $context = NULL) 
	{
		if( !is_callable($callable) ) 
		{
			throw new \Guzzle\Common\Exception\InvalidArgumentException("Argument must be callable");
		}
		$this->callable = $callable;
		$this->context = $context;
	}
	public function transfer(array $batch) 
	{
		return (empty($batch) ? null : call_user_func($this->callable, $batch, $this->context));
	}
}
?>