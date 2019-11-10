<?php  namespace Guzzle\Batch;
class BatchBuilder 
{
	protected $autoFlush = false;
	protected $history = false;
	protected $exceptionBuffering = false;
	protected $afterFlush = NULL;
	protected $transferStrategy = NULL;
	protected $divisorStrategy = NULL;
	protected static $mapping = array( "request" => "Guzzle\\Batch\\BatchRequestTransfer", "command" => "Guzzle\\Batch\\BatchCommandTransfer" );
	public static function factory() 
	{
		return new self();
	}
	public function autoFlushAt($threshold) 
	{
		$this->autoFlush = $threshold;
		return $this;
	}
	public function keepHistory() 
	{
		$this->history = true;
		return $this;
	}
	public function bufferExceptions() 
	{
		$this->exceptionBuffering = true;
		return $this;
	}
	public function notify($callable) 
	{
		$this->afterFlush = $callable;
		return $this;
	}
	public function transferRequests($batchSize = 50) 
	{
		$className = self::$mapping["request"];
		$this->transferStrategy = new $className($batchSize);
		$this->divisorStrategy = $this->transferStrategy;
		return $this;
	}
	public function transferCommands($batchSize = 50) 
	{
		$className = self::$mapping["command"];
		$this->transferStrategy = new $className($batchSize);
		$this->divisorStrategy = $this->transferStrategy;
		return $this;
	}
	public function createBatchesWith(BatchDivisorInterface $divisorStrategy) 
	{
		$this->divisorStrategy = $divisorStrategy;
		return $this;
	}
	public function transferWith(BatchTransferInterface $transferStrategy) 
	{
		$this->transferStrategy = $transferStrategy;
		return $this;
	}
	public function build() 
	{
		if( !$this->transferStrategy ) 
		{
			throw new \Guzzle\Common\Exception\RuntimeException("No transfer strategy has been specified");
		}
		if( !$this->divisorStrategy ) 
		{
			throw new \Guzzle\Common\Exception\RuntimeException("No divisor strategy has been specified");
		}
		$batch = new Batch($this->transferStrategy, $this->divisorStrategy);
		if( $this->exceptionBuffering ) 
		{
			$batch = new ExceptionBufferingBatch($batch);
		}
		if( $this->afterFlush ) 
		{
			$batch = new NotifyingBatch($batch, $this->afterFlush);
		}
		if( $this->autoFlush ) 
		{
			$batch = new FlushingBatch($batch, $this->autoFlush);
		}
		if( $this->history ) 
		{
			$batch = new HistoryBatch($batch);
		}
		return $batch;
	}
}
?>