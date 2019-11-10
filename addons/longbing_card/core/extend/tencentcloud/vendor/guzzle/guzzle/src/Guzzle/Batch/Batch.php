<?php  namespace Guzzle\Batch;
class Batch implements BatchInterface 
{
	protected $queue = NULL;
	protected $dividedBatches = NULL;
	protected $transferStrategy = NULL;
	protected $divisionStrategy = NULL;
	public function __construct(BatchTransferInterface $transferStrategy, BatchDivisorInterface $divisionStrategy) 
	{
		$this->transferStrategy = $transferStrategy;
		$this->divisionStrategy = $divisionStrategy;
		$this->queue = new \SplQueue();
		$this->queue->setIteratorMode(\SplQueue::IT_MODE_DELETE);
		$this->dividedBatches = array( );
	}
	public function add($item) 
	{
		$this->queue->enqueue($item);
		return $this;
	}
	public function flush() 
	{
		$this->createBatches();
		$items = array( );
		foreach( $this->dividedBatches as $batchIndex => $dividedBatch ) 
		{
			while( $dividedBatch->valid() ) 
			{
				$batch = $dividedBatch->current();
				$dividedBatch->next();
				try 
				{
					$this->transferStrategy->transfer($batch);
					$items = array_merge($items, $batch);
				}
				catch( \Exception $e ) 
				{
					throw new Exception\BatchTransferException($batch, $items, $e, $this->transferStrategy, $this->divisionStrategy);
				}
			}
			unset($this->dividedBatches[$batchIndex]);
		}
		return $items;
	}
	public function isEmpty() 
	{
		return count($this->queue) == 0 && count($this->dividedBatches) == 0;
	}
	protected function createBatches() 
	{
		if( count($this->queue) && ($batches = $this->divisionStrategy->createBatches($this->queue)) ) 
		{
			if( is_array($batches) ) 
			{
				$batches = new \ArrayIterator($batches);
			}
			$this->dividedBatches[] = $batches;
		}
	}
}
?>