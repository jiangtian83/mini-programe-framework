<?php  namespace Guzzle\Tests\Batch;
class ExceptionBufferingBatchTest extends \Guzzle\Tests\GuzzleTestCase 
{
	public function testFlushesEntireBatchWhileBufferingErroredBatches() 
	{
		$t = $this->getMockBuilder("Guzzle\\Batch\\BatchTransferInterface")->setMethods(array( "transfer" ))->getMock();
		$d = new \Guzzle\Batch\BatchSizeDivisor(1);
		$batch = new \Guzzle\Batch\Batch($t, $d);
		$called = 0;
		$t->expects($this->exactly(3))->method("transfer")->will($this->returnCallback(function($batch) use (&$called) 
		{
			if( ++$called === 2 ) 
			{
				throw new \Exception("Foo");
			}
		}
		));
		$decorator = new \Guzzle\Batch\ExceptionBufferingBatch($batch);
		$decorator->add("foo")->add("baz")->add("bar");
		$result = $decorator->flush();
		$e = $decorator->getExceptions();
		$this->assertEquals(1, count($e));
		$this->assertEquals(array( "baz" ), $e[0]->getBatch());
		$decorator->clearExceptions();
		$this->assertEquals(0, count($decorator->getExceptions()));
		$this->assertEquals(array( "foo", "bar" ), $result);
	}
}
?>