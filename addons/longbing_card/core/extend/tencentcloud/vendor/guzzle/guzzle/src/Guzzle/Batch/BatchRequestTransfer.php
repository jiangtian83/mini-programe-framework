<?php  namespace Guzzle\Batch;
class BatchRequestTransfer implements BatchTransferInterface, BatchDivisorInterface 
{
	protected $batchSize = NULL;
	public function __construct($batchSize = 50) 
	{
		$this->batchSize = $batchSize;
	}
	public function createBatches(\SplQueue $queue) 
	{
		$groups = new \SplObjectStorage();
		foreach( $queue as $item ) 
		{
			if( !$item instanceof \Guzzle\Http\Message\RequestInterface ) 
			{
				throw new \Guzzle\Common\Exception\InvalidArgumentException("All items must implement Guzzle\\Http\\Message\\RequestInterface");
			}
			$client = $item->getClient();
			if( !$groups->contains($client) ) 
			{
				$groups->attach($client, array( $item ));
			}
			else 
			{
				$current = $groups[$client];
				$current[] = $item;
				$groups[$client] = $current;
			}
		}
		$batches = array( );
		foreach( $groups as $batch ) 
		{
			$batches = array_merge($batches, array_chunk($groups[$batch], $this->batchSize));
		}
		return $batches;
	}
	public function transfer(array $batch) 
	{
		if( $batch ) 
		{
			reset($batch)->getClient()->send($batch);
		}
	}
}
?>