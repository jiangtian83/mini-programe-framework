<?php  namespace Guzzle\Tests\Plugin\Backoff;
class LinearBackoffStrategyTest extends \Guzzle\Tests\GuzzleTestCase 
{
	public function testRetriesWithLinearDelay() 
	{
		$strategy = new \Guzzle\Plugin\Backoff\LinearBackoffStrategy(5);
		$this->assertFalse($strategy->makesDecision());
		$request = $this->getMock("Guzzle\\Http\\Message\\Request", array( ), array( ), "", false);
		$this->assertEquals(0, $strategy->getBackoffPeriod(0, $request));
		$this->assertEquals(5, $strategy->getBackoffPeriod(1, $request));
		$this->assertEquals(10, $strategy->getBackoffPeriod(2, $request));
	}
}
?>