<?php  namespace Guzzle\Batch;
class BatchSizeDivisor implements BatchDivisorInterface 
{
	protected $size = NULL;
	public function __construct($size) 
	{
		$this->size = $size;
	}
	public function setSize($size) 
	{
		$this->size = $size;
		return $this;
	}
	public function getSize() 
	{
		return $this->size;
	}
	public function createBatches(\SplQueue $queue) 
	{
		return array_chunk(iterator_to_array($queue, false), $this->size);
	}
}
?>