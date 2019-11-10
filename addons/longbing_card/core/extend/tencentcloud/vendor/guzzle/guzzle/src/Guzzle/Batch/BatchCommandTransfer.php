<?php  namespace Guzzle\Batch;
class BatchCommandTransfer implements BatchTransferInterface, BatchDivisorInterface 
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
			if( !$item instanceof \Guzzle\Service\Command\CommandInterface ) 
			{
				throw new \Guzzle\Common\Exception\InvalidArgumentException("All items must implement Guzzle\\Service\\Command\\CommandInterface");
			}
			$client = $item->getClient();
			if( !$groups->contains($client) ) 
			{
				$groups->attach($client, new \ArrayObject(array( $item )));
			}
			else 
			{
				$groups[$client]->append($item);
			}
		}
		$batches = array( );
		foreach( $groups as $batch ) 
		{
			$batches = array_merge($batches, array_chunk($groups[$batch]->getArrayCopy(), $this->batchSize));
		}
		return $batches;
	}
	public function transfer(array $batch) 
	{
		if( empty($batch) ) 
		{
			return NULL;
		}
		$client = reset($batch)->getClient();
		$invalid = array_filter($batch, function($command) use ($client) 
		{
			return $command->getClient() !== $client;
		}
		);
		if( !empty($invalid) ) 
		{
			throw new \Guzzle\Service\Exception\InconsistentClientTransferException($invalid);
		}
		$client->execute($batch);
	}
}
?>