<?php  namespace Guzzle\Tests\Batch;
class HistoryBatchTest extends \Guzzle\Tests\GuzzleTestCase 
{
	public function testMaintainsHistoryOfItemsAddedToBatch() 
	{
		$batch = new \Guzzle\Batch\Batch($this->getMock("Guzzle\\Batch\\BatchTransferInterface"), $this->getMock("Guzzle\\Batch\\BatchDivisorInterface"));
		$history = new \Guzzle\Batch\HistoryBatch($batch);
		$history->add("foo")->add("baz");
		$this->assertEquals(array( "foo", "baz" ), $history->getHistory());
		$history->clearHistory();
		$this->assertEquals(array( ), $history->getHistory());
	}
}
?>