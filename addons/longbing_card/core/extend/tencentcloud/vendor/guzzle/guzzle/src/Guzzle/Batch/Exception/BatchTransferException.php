<?php  namespace Guzzle\Batch\Exception;
class BatchTransferException extends \Exception implements \Guzzle\Common\Exception\GuzzleException 
{
	protected $batch = NULL;
	protected $transferStrategy = NULL;
	protected $divisorStrategy = NULL;
	protected $transferredItems = NULL;
	public function __construct(array $batch, array $transferredItems, \Exception $exception, \Guzzle\Batch\BatchTransferInterface $transferStrategy = NULL, \Guzzle\Batch\BatchDivisorInterface $divisorStrategy = NULL) 
	{
		$this->batch = $batch;
		$this->transferredItems = $transferredItems;
		$this->transferStrategy = $transferStrategy;
		$this->divisorStrategy = $divisorStrategy;
		parent::__construct("Exception encountered while transferring batch: " . $exception->getMessage(), $exception->getCode(), $exception);
	}
	public function getBatch() 
	{
		return $this->batch;
	}
	public function getTransferredItems() 
	{
		return $this->transferredItems;
	}
	public function getTransferStrategy() 
	{
		return $this->transferStrategy;
	}
	public function getDivisorStrategy() 
	{
		return $this->divisorStrategy;
	}
}
?>