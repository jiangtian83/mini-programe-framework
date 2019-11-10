<?php  namespace Guzzle\Tests\Batch;
class BatchSizeDivisorTest extends \Guzzle\Tests\GuzzleTestCase 
{
	public function testDividesBatch() 
	{
		$queue = new \SplQueue();
		$queue[] = "foo";
		$queue[] = "baz";
		$queue[] = "bar";
		$d = new \Guzzle\Batch\BatchSizeDivisor(3);
		$this->assertEquals(3, $d->getSize());
		$d->setSize(2);
		$batches = $d->createBatches($queue);
		$this->assertEquals(array( array( "foo", "baz" ), array( "bar" ) ), $batches);
	}
}
?>