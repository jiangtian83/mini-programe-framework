<?php  namespace Guzzle\Tests\Batch;
class BatchClosureTransferTest extends \Guzzle\Tests\GuzzleTestCase 
{
	protected $transferStrategy = NULL;
	protected $itemsTransferred = NULL;
	protected function setUp() 
	{
		$this->itemsTransferred = null;
		$itemsTransferred =& $this->itemsTransferred;
		$this->transferStrategy = new \Guzzle\Batch\BatchClosureTransfer(function(array $batch) use (&$itemsTransferred) 
		{
			$itemsTransferred = $batch;
		}
		);
	}
	public function testTransfersBatch() 
	{
		$batchedItems = array( "foo", "bar", "baz" );
		$this->transferStrategy->transfer($batchedItems);
		$this->assertEquals($batchedItems, $this->itemsTransferred);
	}
	public function testTransferBailsOnEmptyBatch() 
	{
		$batchedItems = array( );
		$this->transferStrategy->transfer($batchedItems);
		$this->assertNull($this->itemsTransferred);
	}
	public function testEnsuresCallableIsCallable() 
	{
		$foo = new \Guzzle\Batch\BatchClosureTransfer("uh oh!");
	}
}
?>