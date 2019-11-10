<?php  namespace Guzzle\Tests\Plugin\Backoff;
class TruncatedBackoffStrategyTest extends \Guzzle\Tests\GuzzleTestCase 
{
	public function testRetriesWhenLessThanMax() 
	{
		$strategy = new \Guzzle\Plugin\Backoff\TruncatedBackoffStrategy(2);
		$this->assertTrue($strategy->makesDecision());
		$request = $this->getMock("Guzzle\\Http\\Message\\Request", array( ), array( ), "", false);
		$this->assertFalse($strategy->getBackoffPeriod(0, $request));
		$this->assertFalse($strategy->getBackoffPeriod(1, $request));
		$this->assertFalse($strategy->getBackoffPeriod(2, $request));
		$response = new \Guzzle\Http\Message\Response(500);
		$strategy->setNext(new \Guzzle\Plugin\Backoff\HttpBackoffStrategy(null, new \Guzzle\Plugin\Backoff\ConstantBackoffStrategy(10)));
		$this->assertEquals(10, $strategy->getBackoffPeriod(0, $request, $response));
		$this->assertEquals(10, $strategy->getBackoffPeriod(1, $request, $response));
		$this->assertFalse($strategy->getBackoffPeriod(2, $request, $response));
	}
}
?>