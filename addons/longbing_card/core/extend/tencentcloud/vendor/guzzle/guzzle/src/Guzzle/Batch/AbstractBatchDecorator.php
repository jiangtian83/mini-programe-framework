<?php  namespace Guzzle\Batch;
abstract class AbstractBatchDecorator implements BatchInterface 
{
	protected $decoratedBatch = NULL;
	public function __construct(BatchInterface $decoratedBatch) 
	{
		$this->decoratedBatch = $decoratedBatch;
	}
	public function __call($method, array $args) 
	{
		return call_user_func_array(array( $this->decoratedBatch, $method ), $args);
	}
	public function add($item) 
	{
		$this->decoratedBatch->add($item);
		return $this;
	}
	public function flush() 
	{
		return $this->decoratedBatch->flush();
	}
	public function isEmpty() 
	{
		return $this->decoratedBatch->isEmpty();
	}
	public function getDecorators() 
	{
		$found = array( $this );
		if( method_exists($this->decoratedBatch, "getDecorators") ) 
		{
			$found = array_merge($found, $this->decoratedBatch->getDecorators());
		}
		return $found;
	}
}
?>