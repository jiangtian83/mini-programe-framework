<?php  namespace Guzzle\Batch;
class HistoryBatch extends AbstractBatchDecorator 
{
	protected $history = array( );
	public function add($item) 
	{
		$this->history[] = $item;
		$this->decoratedBatch->add($item);
		return $this;
	}
	public function getHistory() 
	{
		return $this->history;
	}
	public function clearHistory() 
	{
		$this->history = array( );
	}
}
?>