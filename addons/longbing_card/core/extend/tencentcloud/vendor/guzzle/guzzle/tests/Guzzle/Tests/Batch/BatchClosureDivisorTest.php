<?php  namespace Guzzle\Tests\Batch;
class BatchClosureDivisorTest extends \Guzzle\Tests\GuzzleTestCase 
{
	public function testEnsuresCallableIsCallable() 
	{
		$d = new \Guzzle\Batch\BatchClosureDivisor(new \stdClass());
	}
	public function testDividesBatch() 
	{
		$queue = new \SplQueue();
		$queue[] = "foo";
		$queue[] = "baz";
		$d = new \Guzzle\Batch\BatchClosureDivisor(function(\SplQueue $queue, $context) 
		{
			return array( array( "foo" ), array( "baz" ) );
		}
		, "Bar!");
		$batches = $d->createBatches($queue);
		$this->assertEquals(array( array( "foo" ), array( "baz" ) ), $batches);
	}
}
?>