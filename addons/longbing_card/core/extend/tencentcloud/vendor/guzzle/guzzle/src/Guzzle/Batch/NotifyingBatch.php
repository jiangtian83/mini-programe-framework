<?php  namespace Guzzle\Batch;
class NotifyingBatch extends AbstractBatchDecorator 
{
	protected $callable = NULL;
	public function __construct(BatchInterface $decoratedBatch, $callable) 
	{
		if( !is_callable($callable) ) 
		{
			throw new \Guzzle\Common\Exception\InvalidArgumentException("The passed argument is not callable");
		}
		$this->callable = $callable;
		parent::__construct($decoratedBatch);
	}
	public function flush() 
	{
		$items = $this->decoratedBatch->flush();
		call_user_func($this->callable, $items);
		return $items;
	}
}
?>