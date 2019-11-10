<?php  namespace Guzzle\Tests\Batch;
class BatchRequestTransferTest extends \Guzzle\Tests\GuzzleTestCase 
{
	public function testCreatesBatchesBasedOnCurlMultiHandles() 
	{
		$client1 = new \Guzzle\Http\Client("http://www.example.com");
		$client1->setCurlMulti(new \Guzzle\Http\Curl\CurlMulti());
		$client2 = new \Guzzle\Http\Client("http://www.example.com");
		$client2->setCurlMulti(new \Guzzle\Http\Curl\CurlMulti());
		$request1 = $client1->get();
		$request2 = $client2->get();
		$request3 = $client1->get();
		$request4 = $client2->get();
		$request5 = $client1->get();
		$queue = new \SplQueue();
		$queue[] = $request1;
		$queue[] = $request2;
		$queue[] = $request3;
		$queue[] = $request4;
		$queue[] = $request5;
		$batch = new \Guzzle\Batch\BatchRequestTransfer(2);
		$this->assertEquals(array( array( $request1, $request3 ), array( $request3 ), array( $request2, $request4 ) ), $batch->createBatches($queue));
	}
	public function testEnsuresAllItemsAreRequests() 
	{
		$queue = new \SplQueue();
		$queue[] = "foo";
		$batch = new \Guzzle\Batch\BatchRequestTransfer(2);
		$batch->createBatches($queue);
	}
	public function testTransfersBatches() 
	{
		$client = new \Guzzle\Http\Client("http://127.0.0.1:123");
		$request = $client->get();
		$request->dispatch("request.clone");
		$multi = $this->getMock("Guzzle\\Http\\Curl\\CurlMultiInterface");
		$client->setCurlMulti($multi);
		$multi->expects($this->once())->method("add")->with($request);
		$multi->expects($this->once())->method("send");
		$batch = new \Guzzle\Batch\BatchRequestTransfer(2);
		$batch->transfer(array( $request ));
	}
	public function testDoesNotTransfersEmptyBatches() 
	{
		$batch = new \Guzzle\Batch\BatchRequestTransfer(2);
		$batch->transfer(array( ));
	}
}
?>